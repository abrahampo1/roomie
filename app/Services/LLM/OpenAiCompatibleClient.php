<?php

namespace App\Services\LLM;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCompatibleClient implements LlmClient
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $model,
        private bool $enforceJson = false,
    ) {}

    public function complete(string $prompt, string $agent): array
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if ($this->enforceJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $url = rtrim($this->baseUrl, '/').'/chat/completions';

        $response = Http::withToken($this->apiKey)
            ->withHeaders(['content-type' => 'application/json'])
            ->timeout(120)
            ->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException("LLM API error in {$agent}: ".$response->body());
        }

        return JsonExtractor::fromText(
            $response->json('choices.0.message.content', ''),
            $agent
        );
    }
}
