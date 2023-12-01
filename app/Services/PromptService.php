<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

class PromptService
{
    const MODEL = 'gpt-3.5-turbo-16k';

    const MAX_TOKENS = 8000;

    public static function prepareMessage(string $role, string $message): array
    {
        return [
            'role' => $role,
            'content' => $message,
        ];
    }

    public static function generate(array $messages): CreateResponse
    {
        return OpenAI::chat()->create([
            'model' => self::MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'messages' => $messages,
        ]);
    }
}
