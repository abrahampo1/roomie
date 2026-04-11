<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Email\CampaignSender;
use App\Services\Email\CampaignStatsService;
use App\Services\Email\RecipientSelector;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignSendController extends Controller
{
    public function __construct(
        private readonly RecipientSelector $selector,
        private readonly CampaignSender $sender,
    ) {}

    public function stats(Campaign $campaign): View
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $statsService = new CampaignStatsService;
        $stats = $statsService->forCampaign($campaign);
        $funnel = $statsService->funnelFor($campaign);
        $timeSeries = $statsService->timeSeriesFor($campaign);
        $countryBreakdown = $statsService->countryBreakdownFor($campaign);
        $segmentBreakdown = $statsService->segmentBreakdownFor($campaign);
        $followupPerformance = $statsService->followupPerformanceFor($campaign);

        $recipients = $campaign->recipients()
            ->orderByDesc('last_sent_at')
            ->paginate(25);

        return view('campaigns._stats_section', compact(
            'campaign',
            'stats',
            'funnel',
            'timeSeries',
            'countryBreakdown',
            'segmentBreakdown',
            'followupPerformance',
            'recipients',
        ));
    }

    public function toggleConversion(Campaign $campaign, CampaignRecipient $recipient): RedirectResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($recipient->campaign_id === $campaign->id, 404);

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

        return redirect()->route('campaigns.show', $campaign);
    }

    public function send(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422, 'La campaña todavía no está completada.');

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
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('message', 'No se encontraron destinatarios que encajen con la estrategia.');
        }

        if ($request->boolean('enable_followups')) {
            $hasStoredKey = $campaign->getRawOriginal('api_key') !== null;
            $newKey = $validated['followup_api_key'] ?? null;

            if (! $hasStoredKey && ! $newKey) {
                return redirect()
                    ->back()
                    ->withErrors(['followup_api_key' => 'La clave de la campaña ya no está guardada. Pégala de nuevo para activar los follow-ups.'])
                    ->withInput();
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

        $message = $created > 0
            ? "Enviando a {$created} destinatarios. Los emails se escriben al log en modo demo."
            : 'La campaña ya tenía destinatarios cargados. No se añadieron nuevos.';

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('message', $message);
    }

    public function stopFollowups(Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $campaign->update([
            'followups_enabled' => false,
            'api_key' => null,
            'api_key_retained_for_followups' => false,
            'api_key_retention_expires_at' => null,
        ]);

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('message', 'Secuencia detenida. La clave se ha borrado del servidor.');
    }
}
