<?php

namespace App\Services\LLM;

use InvalidArgumentException;

class LlmClientFactory
{
    public const PROVIDERS = ['anthropic', 'google'];

    public static function make(string $provider, string $apiKey): LlmClient
    {
        return match ($provider) {
            'anthropic' => new AnthropicClient($apiKey),
            'google' => new GoogleClient($apiKey),
            default => throw new InvalidArgumentException("Unknown LLM provider: {$provider}"),
        };
    }

    public static function label(string $provider): string
    {
        return match ($provider) {
            'anthropic' => 'Anthropic Claude',
            'google' => 'Google Gemini',
            default => $provider,
        };
    }
}
