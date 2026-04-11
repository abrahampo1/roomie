<?php

namespace App\Http\Controllers;

use App\Jobs\RunCampaignPipeline;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use App\Services\LLM\LlmClientFactory;
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
            'provider' => ['required', 'string', Rule::in(LlmClientFactory::PROVIDERS)],
            'api_key' => ['required', 'string', 'min:8', 'max:200'],
            'api_base_url' => ['nullable', 'required_if:provider,custom', 'url', 'max:255'],
            'api_model' => ['nullable', 'required_if:provider,custom', 'string', 'max:100'],
        ]);

        $campaign = Campaign::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'] ?? null,
            'objective' => $validated['objective'],
            'api_provider' => $validated['provider'],
            'api_key' => $validated['api_key'],
            'api_base_url' => $validated['provider'] === 'custom' ? $validated['api_base_url'] : null,
            'api_model' => $validated['provider'] === 'custom' ? $validated['api_model'] : null,
            'status' => 'pending',
        ]);

        RunCampaignPipeline::dispatch($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('message', 'Campaña en proceso. Los 4 agentes están trabajando...');
    }

    public function show(Campaign $campaign)
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        return view('campaigns.show', compact('campaign'));
    }
}
