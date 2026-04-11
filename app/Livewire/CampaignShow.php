<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Customer;
use App\Services\Email\CampaignStatsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignShow extends Component
{
    use WithPagination;

    #[Locked]
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    #[Computed]
    public function statsData(): array
    {
        if (! $this->campaign->send_enabled) {
            return [
                'stats' => null,
                'funnel' => null,
                'timeSeries' => null,
                'countryBreakdown' => null,
                'segmentBreakdown' => null,
                'followupPerformance' => null,
                'recipients' => null,
            ];
        }

        $service = new CampaignStatsService;

        return [
            'stats' => $service->forCampaign($this->campaign),
            'funnel' => $service->funnelFor($this->campaign),
            'timeSeries' => $service->timeSeriesFor($this->campaign),
            'countryBreakdown' => $service->countryBreakdownFor($this->campaign),
            'segmentBreakdown' => $service->segmentBreakdownFor($this->campaign),
            'followupPerformance' => $service->followupPerformanceFor($this->campaign),
            'recipients' => $this->campaign->recipients()
                ->orderByDesc('last_sent_at')
                ->paginate(25),
        ];
    }

    #[Computed]
    public function maxRecipients(): int
    {
        return Customer::query()
            ->whereNotNull('email')
            ->distinct()
            ->count('email');
    }

    public function render()
    {
        return view('livewire.campaign-show');
    }
}
