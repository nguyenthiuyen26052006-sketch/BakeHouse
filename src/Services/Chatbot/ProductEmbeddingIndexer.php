<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Repositories\ProductEmbeddingRepository;

/** Keeps product_embeddings in sync with products; only re-embeds rows whose text changed. */
final class ProductEmbeddingIndexer
{
    private const BATCH = 50;

    public function __construct(
        private readonly AzureOpenAiClient $client = new AzureOpenAiClient(),
        private readonly ProductEmbeddingRepository $repo = new ProductEmbeddingRepository(),
    ) {
    }

    /**
     * @param int|null $productId only this product (used right after an admin edit)
     * @param int $limit stop after embedding this many products (0 = no limit), keeps request time bounded
     * @return int number of products (re)embedded
     */
    public function sync(?int $productId = null, int $limit = 0): int
    {
        $existing = $this->repo->hashes();
        $todo = [];
        foreach ($this->repo->documents($productId) as $doc) {
            $text = $this->textFor($doc);
            $hash = hash('sha256', $text);
            if (($existing[(int) $doc['id']] ?? null) !== $hash) {
                $todo[] = ['id' => (int) $doc['id'], 'text' => $text, 'hash' => $hash];
                if ($limit > 0 && count($todo) >= $limit) {
                    break;
                }
            }
        }

        foreach (array_chunk($todo, self::BATCH) as $chunk) {
            $vectors = $this->client->embed(array_column($chunk, 'text'));
            foreach ($chunk as $i => $item) {
                $this->repo->upsert($item['id'], $item['hash'], $vectors[$i]);
            }
        }

        return count($todo);
    }

    /** @param array<string, mixed> $doc */
    private function textFor(array $doc): string
    {
        $parts = [
            (string) $doc['name'],
            'Danh mục: ' . $doc['category_name'],
            $doc['brand_name'] ? 'Thương hiệu: ' . $doc['brand_name'] : '',
            $doc['tag_names'] ? 'Từ khóa: ' . $doc['tag_names'] : '',
            'Đơn vị: ' . $doc['unit'],
            (string) ($doc['description'] ?? ''),
        ];

        return trim(implode("\n", array_filter($parts, static fn (string $p): bool => $p !== '')));
    }
}
