<?php

namespace App\Services\LLM;

interface LlmClient
{
    /**
     * Send a prompt to the LLM and return the JSON-decoded response.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException When the API call fails or the response is not valid JSON.
     */
    public function complete(string $prompt, string $agent): array;
}
