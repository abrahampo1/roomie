<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $kpis = $this->stats->aggregateKpis($user);
        $funnel = $this->stats->crossCampaignFunnel($user);
        $topCampaigns = $this->stats->topCampaigns($user);
        $campaigns = $user->campaigns()->latest()->limit(8)->get();

        return view('dashboard.index', compact('kpis', 'funnel', 'topCampaigns', 'campaigns'));
    }

    public function analytics(): View
    {
        $user = auth()->user();

        $kpis = $this->stats->aggregateKpis($user);
        $funnel = $this->stats->crossCampaignFunnel($user);
        $timeSeries = $this->stats->recentActivity($user);
        $countryBreakdown = $this->stats->countryBreakdown($user);
        $segmentBreakdown = $this->stats->segmentBreakdown($user);

        return view('dashboard.analytics', compact('kpis', 'funnel', 'timeSeries', 'countryBreakdown', 'segmentBreakdown'));
    }

    public function sendHistory(): View
    {
        $user = auth()->user();

        $recipients = CampaignRecipient::query()
            ->join('campaigns', 'campaign_recipients.campaign_id', '=', 'campaigns.id')
            ->where('campaigns.user_id', $user->id)
            ->select('campaign_recipients.*', 'campaigns.name as campaign_name', 'campaigns.objective as campaign_objective')
            ->latest('campaign_recipients.last_sent_at')
            ->paginate(30);

        return view('dashboard.send-history', compact('recipients'));
    }

    public function emailPreviews(): View
    {
        $user = auth()->user();

        $campaigns = $user->campaigns()
            ->where('status', 'completed')
            ->whereNotNull('creative')
            ->latest()
            ->paginate(12);

        return view('dashboard.email-previews', compact('campaigns'));
    }
}
