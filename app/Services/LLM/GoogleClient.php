<?php

namespace App\Services\LLM;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleClient implements LlmClient
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gemini-2.0-flash',
    ) {}

    public function complete(string $prompt, string $agent): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $response = Http::withHeaders([
            'content-type' => 'application/json',
        ])
            ->withQueryParameters(['key' => $this->apiKey])
            ->timeout(120)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'maxOutputTokens' => 4096,
                    'temperature' => 0.7,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Google API error in {$agent}: ".$response->body());
        }

        return JsonExtractor::fromText(
            $response->json('candidates.0.content.parts.0.text', ''),
            "Google ({$agent})"
        );
    }
}
