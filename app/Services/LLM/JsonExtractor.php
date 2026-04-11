<?php

namespace App\Services\LLM;

use RuntimeException;

class JsonExtractor
{
    /**
     * @return array<string, mixed>
     */
    public static function fromText(string $text, string $context): array
    {
        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $decoded = json_decode($matches[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException("Invalid JSON from {$context}: {$text}");
    }
}
