<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\Campaign\CampaignPipeline;
use App\Services\Campaign\EmailBlockParser;
use App\Services\Campaign\EmailBlockRenderer;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignEditorController extends Controller
{
    public function edit(Campaign $campaign)
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422, 'El pipeline todavía no ha terminado.');

        $blocks = $campaign->creative_blocks;

        if (empty($blocks) && ! empty($campaign->creative['body_html'] ?? '')) {
            $parser = new EmailBlockParser;
            $blocks = $parser->parse($campaign->creative['body_html']);
            $campaign->update(['creative_blocks' => $blocks]);
        }

        $bankImages = $campaign->user?->bankImages()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'alt_text', 'category', 'disk_path'])
            ->map(fn ($img) => [
                'id' => $img->id,
                'title' => $img->title,
                'alt' => $img->alt_text,
                'category' => $img->category,
                'url' => $img->url(),
            ]) ?? collect();

        return view('campaigns.editor', [
            'campaign' => $campaign,
            'blocks' => $blocks ?? [],
            'creative' => $campaign->creative ?? [],
            'strategy' => $campaign->strategy ?? [],
            'bankImages' => $bankImages,
        ]);
    }

    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422);

        $validated = $request->validate([
            'blocks' => ['required', 'array'],
            'blocks.*.id' => ['required', 'string'],
            'blocks.*.type' => ['required', 'string'],
            'subject_line' => ['required', 'string', 'max:200'],
            'preview_text' => ['nullable', 'string', 'max:300'],
            'headline' => ['required', 'string', 'max:200'],
            'cta_text' => ['required', 'string', 'max:100'],
        ]);

        $renderer = new EmailBlockRenderer;
        $bodyHtml = $renderer->render($validated['blocks']);

        $creative = $campaign->creative ?? [];
        $creative['subject_line'] = $validated['subject_line'];
        $creative['preview_text'] = $validated['preview_text'] ?? '';
        $creative['headline'] = $validated['headline'];
        $creative['body_html'] = $bodyHtml;
        $creative['cta_text'] = $validated['cta_text'];

        $campaign->update([
            'creative' => $creative,
            'creative_blocks' => $validated['blocks'],
        ]);

        return response()->json([
            'success' => true,
            'creative' => $creative,
        ]);
    }

    public function refineBlock(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422);

        $validated = $request->validate([
            'block_index' => ['required', 'integer', 'min:0'],
            'prompt' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($campaign->getRawOriginal('api_key') === null) {
            return response()->json(['error' => 'key_not_available', 'message' => 'La clave API ya se borró.'], 422);
        }

        $blocks = $campaign->creative_blocks ?? [];
        $blockIndex = $validated['block_index'];

        if (! isset($blocks[$blockIndex])) {
            return response()->json(['error' => 'block_not_found'], 404);
        }

        $block = $blocks[$blockIndex];

        try {
            $client = LlmClientFactory::make(
                $campaign->api_provider,
                $campaign->api_key,
                $campaign->api_base_url,
                $campaign->api_model,
            );

            $pipeline = new CampaignPipeline($client, new MarketIntelligenceService);
            $updatedBlock = $pipeline->refineBlock($campaign, $block, $validated['prompt']);

            $blocks[$blockIndex] = array_merge($block, $updatedBlock);
            $campaign->update(['creative_blocks' => $blocks]);

            $renderer = new EmailBlockRenderer;
            $bodyHtml = $renderer->render($blocks);
            $creative = $campaign->creative ?? [];
            $creative['body_html'] = $bodyHtml;
            $campaign->update(['creative' => $creative]);

            return response()->json([
                'success' => true,
                'block' => $blocks[$blockIndex],
                'body_html' => $bodyHtml,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'refine_failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function regenerateAll(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->user_id === auth()->id(), 403);
        abort_unless($campaign->isComplete(), 422);

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($campaign->getRawOriginal('api_key') === null) {
            return response()->json(['error' => 'key_not_available', 'message' => 'La clave API ya se borró.'], 422);
        }

        try {
            $client = LlmClientFactory::make(
                $campaign->api_provider,
                $campaign->api_key,
                $campaign->api_base_url,
                $campaign->api_model,
            );

            $pipeline = new CampaignPipeline($client, new MarketIntelligenceService);
            $newCreative = $pipeline->refineCreative($campaign, $validated['prompt']);

            $parser = new EmailBlockParser;
            $blocks = $parser->parse($newCreative['body_html'] ?? '');

            $campaign->update([
                'creative' => $newCreative,
                'creative_blocks' => $blocks,
            ]);

            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.creative_refined', [
                'campaign_id' => $campaign->id,
                'creative' => $newCreative,
                'instructions' => $validated['prompt'],
            ]);

            return response()->json([
                'success' => true,
                'creative' => $newCreative,
                'blocks' => $blocks,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'regenerate_failed', 'message' => $e->getMessage()], 500);
        }
    }
}
