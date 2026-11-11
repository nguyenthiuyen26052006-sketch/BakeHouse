<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\ErrorHandler;
use App\Services\Chatbot\AzureOpenAiChatbotProvider;
use App\Services\Chatbot\ChatbotProviderInterface;
use App\Services\Chatbot\MockChatbotProvider;
use Throwable;

final class ChatbotService
{
    private readonly ChatbotProviderInterface $provider;

    public function __construct(?ChatbotProviderInterface $provider = null)
    {
        $this->provider = $provider ?? match (Env::get('CHATBOT_PROVIDER', 'mock')) {
            'azure' => new AzureOpenAiChatbotProvider(),
            default => new MockChatbotProvider(),
        };
    }

    /** @return array{message: string, products: array<int, array<string, mixed>>, total: int} */
    public function suggest(string $dish, int $budget): array
    {
        try {
            return $this->provider->suggest($dish, $budget);
        } catch (Throwable $e) {
            ErrorHandler::logException($e);

            return (new MockChatbotProvider())->suggest($dish, $budget);
        }
    }
}
