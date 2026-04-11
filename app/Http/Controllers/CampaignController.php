<?php

namespace App\Http\Controllers;

use App\Http\Resources\CampaignResource;
use App\Jobs\RunCampaignPipeline;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use App\Services\Campaign\CampaignPipeline;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $hotelCount = Hotel::count();
        $customerCount = Customer::count();

        return view('campaigns.create', compact('hotelCount', 'customerCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'objective' => 'required|string|min:10|max:1000',
            'aggressiveness' => ['required', 'integer', 'between:0,5'],
            'manipulation' => ['required', 'integer', 'between:0,5'],
            'provider' => ['required', 'string', Rule::in(LlmClientFactory::PROVIDERS)],
            'api_key' => ['required', 'string', 'min:8', 'max:200'],
            'api_base_url' => ['nullable', 'required_if:provider,custom', 'url', 'max:255'],
            'api_model' => ['nullable', 'required_if:provider,custom', 'string', 'max:100'],
        ]);

        $retentionDays = (int) config('services.roomie.followup_max_retention_days', 14);

        $campaign = Campaign::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'] ?? null,
            'objective' => $validated['objective'],
            'aggressiveness' => $validated['aggressiveness'],
            'manipulation' => $validated['manipulation'],
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

        return redirect()->route('campaigns.show', $campaign)
            ->with('message', 'Campaña en proceso. Los 4 agentes están trabajando...');
    }

    public function refineCreative(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422, 'El pipeline todavía no ha terminado.');

        $validated = $request->validate([
            'refinement_prompt' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($campaign->getRawOriginal('api_key') === null) {
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('message', 'La clave API de la campaña ya ha sido borrada. No se puede refinar.');
        }

        try {
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
        } catch (\Throwable $e) {
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('message', 'No se pudo refinar el creative: '.$e->getMessage());
        }

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('message', 'Creative actualizado con tus indicaciones.');
    }

    public function show(Campaign $campaign)
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        return view('campaigns.show', compact('campaign'));
    }
}
