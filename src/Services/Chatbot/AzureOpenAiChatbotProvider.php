<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Core\Env;
use RuntimeException;

/**
 * Placeholder for the future Azure OpenAI integration.
 * Reads AZURE_OPENAI_* from .env; the HTTP call is intentionally not implemented yet.
 */
final class AzureOpenAiChatbotProvider implements ChatbotProviderInterface
{
    public function suggest(string $dish, int $budget): array
    {
        $endpoint = Env::get('AZURE_OPENAI_ENDPOINT');
        $apiKey = Env::get('AZURE_OPENAI_API_KEY');
        if ($endpoint === null || $apiKey === null) {
            throw new RuntimeException('Azure OpenAI chưa được cấu hình (AZURE_OPENAI_ENDPOINT / AZURE_OPENAI_API_KEY).');
        }

        throw new RuntimeException('Azure OpenAI provider chưa được triển khai.');
    }
}
