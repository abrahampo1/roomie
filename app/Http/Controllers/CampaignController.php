<?php

namespace App\Http\Controllers;

use App\Jobs\RunCampaignPipeline;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->get();

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
            'objective' => 'required|string|min:10|max:1000',
        ]);

        $campaign = Campaign::create([
            'objective' => $validated['objective'],
            'status' => 'pending',
        ]);

        RunCampaignPipeline::dispatch($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('message', 'Campaña en proceso. Los 4 agentes están trabajando...');
    }

    public function show(Campaign $campaign)
    {
        return view('campaigns.show', compact('campaign'));
    }
}
