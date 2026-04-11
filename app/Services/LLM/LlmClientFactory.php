<?php

namespace App\Services\LLM;

use InvalidArgumentException;

class LlmClientFactory
{
    public const PROVIDERS = ['anthropic', 'google', 'openai', 'deepseek', 'custom'];

    public static function make(
        string $provider,
        string $apiKey,
        ?string $baseUrl = null,
        ?string $model = null,
    ): LlmClient {
        return match ($provider) {
            'anthropic' => new AnthropicClient($apiKey),
            'google' => new GoogleClient($apiKey),
            'openai' => new OpenAiCompatibleClient(
                $apiKey,
                'https://api.openai.com/v1',
                'gpt-4o-mini',
                enforceJson: true,
            ),
            'deepseek' => new OpenAiCompatibleClient(
                $apiKey,
                'https://api.deepseek.com/v1',
                'deepseek-chat',
                enforceJson: true,
            ),
            'custom' => new OpenAiCompatibleClient(
                $apiKey,
                $baseUrl ?? throw new InvalidArgumentException('Custom provider requires a base URL.'),
                $model ?? throw new InvalidArgumentException('Custom provider requires a model name.'),
                enforceJson: false,
            ),
            default => throw new InvalidArgumentException("Unknown LLM provider: {$provider}"),
        };
    }

    public static function label(string $provider): string
    {
        return match ($provider) {
            'anthropic' => 'Anthropic Claude',
            'google' => 'Google Gemini',
            'openai' => 'OpenAI',
            'deepseek' => 'DeepSeek',
            'custom' => 'Custom',
            default => $provider,
        };
    }
}
