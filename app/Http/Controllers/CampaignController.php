<?php

namespace App\Http\Controllers;

use App\Jobs\RunCampaignPipeline;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use App\Services\Campaign\CampaignPipeline;
use App\Services\Email\CampaignStatsService;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
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

            $pipeline = new CampaignPipeline($client, new MarketIntelligenceService());
            $newCreative = $pipeline->refineCreative($campaign, $validated['refinement_prompt']);

            $campaign->update(['creative' => $newCreative]);
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

        $stats = null;
        $funnel = null;
        $timeSeries = null;
        $countryBreakdown = null;
        $segmentBreakdown = null;
        $followupPerformance = null;
        $recipients = null;

        if ($campaign->send_enabled) {
            $statsService = new CampaignStatsService();
            $stats = $statsService->forCampaign($campaign);
            $funnel = $statsService->funnelFor($campaign);
            $timeSeries = $statsService->timeSeriesFor($campaign);
            $countryBreakdown = $statsService->countryBreakdownFor($campaign);
            $segmentBreakdown = $statsService->segmentBreakdownFor($campaign);
            $followupPerformance = $statsService->followupPerformanceFor($campaign);
            $recipients = $campaign->recipients()
                ->orderByDesc('last_sent_at')
                ->paginate(25);
        }

        // Count distinct emails, not rows: the seeder can produce several
        // customer rows per guest (one per reservation) and they collapse to a
        // single outbound email. Showing the row count would over-promise
        // how many unique inboxes "Todos" can actually reach.
        $maxRecipients = Customer::query()
            ->whereNotNull('email')
            ->distinct()
            ->count('email');

        return view('campaigns.show', compact(
            'campaign',
            'stats',
            'funnel',
            'timeSeries',
            'countryBreakdown',
            'segmentBreakdown',
            'followupPerformance',
            'recipients',
            'maxRecipients',
        ));
    }
}
