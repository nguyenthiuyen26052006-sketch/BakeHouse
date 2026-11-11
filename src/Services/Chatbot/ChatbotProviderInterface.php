<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

interface ChatbotProviderInterface
{
    /**
     * Suggest ingredients/tools for a dish within a budget (VND, 0 = unlimited).
     *
     * @return array{message: string, products: array<int, array<string, mixed>>, total: int}
     */
    public function suggest(string $dish, int $budget): array;
}
