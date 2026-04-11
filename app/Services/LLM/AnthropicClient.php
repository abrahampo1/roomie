<?php

namespace App\Services\LLM;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicClient implements LlmClient
{
    public function __construct(
        private string $apiKey,
        private string $model = 'claude-sonnet-4-20250514',
    ) {}

    public function complete(string $prompt, string $agent): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Anthropic API error in {$agent}: ".$response->body());
        }

        return JsonExtractor::fromText(
            $response->json('content.0.text', ''),
            "Anthropic ({$agent})"
        );
    }
}
