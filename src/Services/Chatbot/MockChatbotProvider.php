<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Repositories\ProductRepository;

/** Rule-based stand-in for an LLM: maps dishes to keywords and picks real products from the DB. */
final class MockChatbotProvider implements ChatbotProviderInterface
{
    private const RECIPES = [
        ['match' => ['bánh mì', 'banh mi', 'bread'], 'keywords' => ['bột mì số 11', 'đường', 'bơ']],
        ['match' => ['bông lan', 'bong lan', 'sponge'], 'keywords' => ['bột mì đa dụng', 'đường cát', 'dầu ăn', 'vani']],
        ['match' => ['cookie', 'quy', 'bánh quy'], 'keywords' => ['bột mì đa dụng', 'bơ', 'đường nâu', 'yến mạch']],
        ['match' => ['brownie', 'socola', 'chocolate', 'sô cô la'], 'keywords' => ['cacao', 'bơ', 'đường', 'bột mì đa dụng']],
        ['match' => ['tiramisu', 'kem', 'mousse', 'cream'], 'keywords' => ['whipping', 'cacao', 'đường', 'vani']],
        ['match' => ['muffin', 'cupcake'], 'keywords' => ['bột mì đa dụng', 'bơ', 'đường', 'khuôn']],
    ];

    public function __construct(private readonly ProductRepository $products = new ProductRepository())
    {
    }

    public function suggest(string $dish, int $budget): array
    {
        $keywords = $this->keywordsFor(mb_strtolower(trim($dish)));
        $picked = [];
        $total = 0;
        $skipped = 0;

        foreach ($keywords as $keyword) {
            $found = $this->products->searchByKeywords([$keyword], 1);
            if ($found === [] || isset($picked[$found[0]['id']])) {
                continue;
            }
            $price = (int) $found[0]['price'];
            if ($budget > 0 && $total + $price > $budget) {
                $skipped++;
                continue;
            }
            $picked[$found[0]['id']] = $found[0];
            $total += $price;
        }

        $products = array_values($picked);
        if ($products === []) {
            return [
                'message' => 'Mình chưa tìm được nguyên liệu phù hợp với yêu cầu và ngân sách này. Bạn thử tăng ngân sách hoặc mô tả món khác nhé!',
                'products' => [],
                'total' => 0,
            ];
        }

        $message = sprintf('Gợi ý %d nguyên liệu cho món "%s"', count($products), trim($dish));
        $message .= $budget > 0 ? sprintf(' trong ngân sách %s ₫.', number_format($budget, 0, ',', '.')) : '.';
        if ($skipped > 0) {
            $message .= sprintf(' Có %d mục vượt ngân sách nên mình đã bỏ qua.', $skipped);
        }

        return ['message' => $message, 'products' => $products, 'total' => $total];
    }

    /** @return array<int, string> */
    private function keywordsFor(string $dish): array
    {
        foreach (self::RECIPES as $recipe) {
            foreach ($recipe['match'] as $needle) {
                if (str_contains($dish, $needle)) {
                    return $recipe['keywords'];
                }
            }
        }

        // Unknown dish: use its own words, then a generic baking starter set.
        $words = array_filter(
            preg_split('/\s+/u', $dish) ?: [],
            static fn (string $w): bool => mb_strlen($w) >= 3
        );

        return array_merge(array_values($words), ['bột mì đa dụng', 'đường', 'bơ']);
    }
}
