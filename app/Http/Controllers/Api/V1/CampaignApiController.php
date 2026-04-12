<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignRecipientResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignStatsResource;
use App\Jobs\RunCampaignPipeline;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignPipeline;
use App\Services\Email\CampaignSender;
use App\Services\Email\RecipientSelector;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CampaignApiController extends Controller
{
    public function __construct(
        private readonly RecipientSelector $selector,
        private readonly CampaignSender $sender,
    ) {}

    /**
     * List the authenticated user's campaigns, most recent first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $campaigns = Campaign::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(perPage: (int) $request->integer('per_page', 25));

        return CampaignResource::collection($campaigns);
    }

    /**
     * Create a campaign and dispatch the 4-agent pipeline asynchronously.
     * Returns 202 Accepted with a `poll_url` so the client knows where to
     * check the status.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'objective' => ['required', 'string', 'min:10', 'max:1000'],
            'aggressiveness' => ['required', 'integer', 'between:0,5'],
            'persuasion_patterns' => ['required', 'integer', 'between:0,5'],
            'provider' => ['required', 'string', Rule::in(LlmClientFactory::PROVIDERS)],
            'api_key' => ['required', 'string', 'min:8', 'max:200'],
            'api_base_url' => ['nullable', 'required_if:provider,custom', 'url', 'max:255'],
            'api_model' => ['nullable', 'required_if:provider,custom', 'string', 'max:100'],
        ]);

        $retentionDays = (int) config('services.roomie.followup_max_retention_days', 14);

        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'] ?? null,
            'objective' => $validated['objective'],
            'aggressiveness' => $validated['aggressiveness'],
            'persuasion_patterns' => $validated['persuasion_patterns'],
            'api_provider' => $validated['provider'],
            'api_key' => $validated['api_key'],
            'api_base_url' => $validated['provider'] === 'custom' ? $validated['api_base_url'] : null,
            'api_model' => $validated['provider'] === 'custom' ? $validated['api_model'] : null,
            'api_key_retained_for_followups' => true,
            'api_key_retention_expires_at' => now()->addDays($retentionDays),
            'status' => 'pending',
        ]);

        WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.created', [
            'campaign' => (new CampaignResource($campaign))->toArray($request),
        ]);

        RunCampaignPipeline::dispatch($campaign);

        return response()->json([
            'id' => $campaign->id,
            'status' => $campaign->status,
            'poll_url' => route('api.v1.campaigns.status', ['campaign' => $campaign->id]),
        ], 202);
    }

    /**
     * Full campaign detail — analysis + strategy + creative + audit + followup
     * variants + send/followup config.
     */
    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorizeCampaign($campaign);

        return new CampaignResource($campaign);
    }

    /**
     * Lightweight polling endpoint. Use this to check whether the pipeline
     * is still running and to grab small previews as each agent completes.
     */
    public function status(Campaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($campaign);

        return response()->json([
            'id' => $campaign->id,
            'status' => $campaign->status,
            'quality_score' => $campaign->quality_score,
            'has_analysis' => $campaign->analysis !== null,
            'has_strategy' => $campaign->strategy !== null,
            'has_creative' => $campaign->creative !== null,
            'has_audit' => $campaign->audit !== null,
            'analysis_preview' => $campaign->analysis ? [
                'segments_count' => is_array($campaign->analysis['segments'] ?? null) ? count($campaign->analysis['segments']) : 0,
                'focus_segment' => $campaign->analysis['recommended_focus_segment'] ?? null,
            ] : null,
            'strategy_preview' => $campaign->strategy ? [
                'hotel_name' => $campaign->strategy['recommended_hotel']['name'] ?? null,
                'channel' => $campaign->strategy['channel'] ?? null,
                'segment' => $campaign->strategy['target_segment']['name'] ?? null,
            ] : null,
            'creative_preview' => $campaign->creative ? [
                'subject' => $campaign->creative['subject_line'] ?? null,
            ] : null,
            'audit_preview' => $campaign->audit ? [
                'verdict' => $campaign->audit['final_verdict'] ?? null,
            ] : null,
        ]);
    }

    /**
     * Trigger the send. Accepts the same recipient_mode + follow-up config
     * as the web drawer. Returns 200 with the number of recipients queued
     * for the initial batch.
     */
    public function send(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($campaign);

        if (! $campaign->isComplete()) {
            return response()->json([
                'error' => 'campaign_not_ready',
                'message' => 'The campaign pipeline has not completed yet.',
            ], 422);
        }

        $validated = $request->validate([
            'recipient_mode' => ['required', 'string', 'in:50,100,200,custom,all'],
            'recipient_count_custom' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'enable_followups' => ['nullable', 'boolean'],
            'followup_api_key' => ['nullable', 'string', 'min:8', 'max:200'],
            'followup_max_attempts' => ['nullable', 'integer', 'min:2', 'max:5'],
            'followup_cooldown_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
        ]);

        $limit = match ($validated['recipient_mode']) {
            '50' => 50,
            '100' => 100,
            '200' => 200,
            'custom' => (int) ($validated['recipient_count_custom'] ?? 50),
            'all' => null,
        };

        $customers = $this->selector->pickForCampaign($campaign, $limit);

        if ($customers->isEmpty()) {
            return response()->json([
                'error' => 'no_recipients',
                'message' => 'The recipient selector did not find anyone matching the strategy.',
            ], 422);
        }

        if ($request->boolean('enable_followups')) {
            $hasStoredKey = $campaign->getRawOriginal('api_key') !== null;
            $newKey = $validated['followup_api_key'] ?? null;

            if (! $hasStoredKey && ! $newKey) {
                return response()->json([
                    'error' => 'no_key_for_followups',
                    'message' => 'The campaign has no stored key; supply `followup_api_key` to enable follow-ups.',
                ], 422);
            }

            $maxDays = (int) config('services.roomie.followup_max_retention_days', 14);
            $updates = [
                'api_key_retained_for_followups' => true,
                'api_key_retention_expires_at' => now()->addDays($maxDays),
                'followups_enabled' => true,
                'followup_max_attempts' => $validated['followup_max_attempts'] ?? 3,
                'followup_cooldown_hours' => $validated['followup_cooldown_hours'] ?? 48,
            ];

            if ($newKey !== null) {
                $updates['api_key'] = $newKey;
            }

            $campaign->update($updates);
        }

        $created = $this->sender->dispatchInitialSend($campaign, $customers);

        WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.send_started', [
            'campaign_id' => $campaign->id,
            'dispatched' => $created,
            'total_queued' => $campaign->recipients()->count(),
        ]);

        return response()->json([
            'dispatched' => $created,
            'total_queued' => $campaign->recipients()->count(),
        ]);
    }

    /**
     * Apply a free-form refinement prompt to the current creative. The LLM
     * reads the full campaign context (objective, strategy, current creative)
     * plus the user's instruction and returns an updated creative JSON.
     * Requires the campaign to still have a retained API key.
     */
    public function refineCreative(Request $request, Campaign $campaign): CampaignResource
    {
        $this->authorizeCampaign($campaign);

        if (! $campaign->isComplete()) {
            abort(response()->json([
                'error' => 'campaign_not_ready',
                'message' => 'The campaign pipeline has not completed yet.',
            ], 422));
        }

        $validated = $request->validate([
            'refinement_prompt' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($campaign->getRawOriginal('api_key') === null) {
            abort(response()->json([
                'error' => 'key_not_available',
                'message' => 'The campaign has no stored API key, so the creative cannot be refined.',
            ], 422));
        }

        $client = LlmClientFactory::make(
            $campaign->api_provider,
            $campaign->api_key,
            $campaign->api_base_url,
            $campaign->api_model,
        );

        $pipeline = new CampaignPipeline($client, new MarketIntelligenceService);
        $newCreative = $pipeline->refineCreative($campaign, $validated['refinement_prompt']);

        $campaign->update(['creative' => $newCreative]);

        WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.creative_refined', [
            'campaign_id' => $campaign->id,
            'creative' => $newCreative,
            'instructions' => $validated['refinement_prompt'],
        ]);

        return new CampaignResource($campaign->fresh());
    }

    /**
     * Stop the follow-up loop and wipe the retained LLM key.
     */
    public function stopFollowups(Campaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($campaign);

        $campaign->update([
            'followups_enabled' => false,
            'api_key' => null,
            'api_key_retained_for_followups' => false,
            'api_key_retention_expires_at' => null,
        ]);

        return response()->json([
            'stopped' => true,
            'key_wiped' => true,
        ]);
    }

    /**
     * Full dashboard payload for a campaign that has been sent.
     */
    public function stats(Campaign $campaign): CampaignStatsResource
    {
        $this->authorizeCampaign($campaign);

        return new CampaignStatsResource($campaign);
    }

    /**
     * Paginated list of recipients for a campaign.
     */
    public function recipients(Request $request, Campaign $campaign): AnonymousResourceCollection
    {
        $this->authorizeCampaign($campaign);

        $recipients = $campaign->recipients()
            ->orderByDesc('last_sent_at')
            ->paginate(perPage: (int) $request->integer('per_page', 50));

        return CampaignRecipientResource::collection($recipients);
    }

    /**
     * Manual conversion override — flips a recipient between `sent` and
     * `converted`. Mirrors the web button in the stats table.
     */
    public function toggleConversion(Campaign $campaign, CampaignRecipient $recipient): CampaignRecipientResource
    {
        $this->authorizeCampaign($campaign);

        if ($recipient->campaign_id !== $campaign->id) {
            abort(404);
        }

        if ($recipient->isConverted()) {
            $recipient->update([
                'status' => 'sent',
                'converted_at' => null,
            ]);
        } else {
            $recipient->update([
                'status' => 'converted',
                'converted_at' => now(),
            ]);
        }

        return new CampaignRecipientResource($recipient->fresh());
    }

    private function authorizeCampaign(Campaign $campaign): void
    {
        if ($campaign->user_id !== Auth::id()) {
            abort(response()->json([
                'error' => 'forbidden',
                'message' => 'This campaign does not belong to your account.',
            ], 403));
        }
    }
}
