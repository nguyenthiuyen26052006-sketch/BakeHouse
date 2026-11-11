<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use DomainException;

final class CartService
{
    public function __construct(
        private readonly CartRepository $carts = new CartRepository(),
        private readonly ProductRepository $products = new ProductRepository()
    ) {
    }

    /** @return array{items: array<int, array<string, mixed>>, subtotal: int, count: int} */
    public function summary(int $userId): array
    {
        $cartId = $this->carts->getOrCreateCartId($userId);
        $items = $this->carts->items($cartId);
        $subtotal = 0;
        $count = 0;
        foreach ($items as &$item) {
            $item['line_total'] = (int) $item['price'] * (int) $item['quantity'];
            $item['available'] = (int) $item['is_active'] === 1 && (int) $item['stock_quantity'] > 0;
            $subtotal += $item['available'] ? $item['line_total'] : 0;
            $count += (int) $item['quantity'];
        }

        return ['items' => $items, 'subtotal' => $subtotal, 'count' => $count];
    }

    /** @throws DomainException */
    public function add(int $userId, int $productId, int $quantity): void
    {
        $product = $this->activeProduct($productId);
        $cartId = $this->carts->getOrCreateCartId($userId);
        $existing = $this->carts->findItem($cartId, $productId);
        $newQuantity = max(1, $quantity) + (int) ($existing['quantity'] ?? 0);

        if ($newQuantity > (int) $product['stock_quantity']) {
            throw new DomainException('Chỉ còn ' . (int) $product['stock_quantity'] . ' sản phẩm trong kho.');
        }
        $this->carts->setQuantity($cartId, $productId, $newQuantity);
    }

    /** @throws DomainException */
    public function updateQuantity(int $userId, int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($userId, $productId);

            return;
        }
        $product = $this->activeProduct($productId);
        if ($quantity > (int) $product['stock_quantity']) {
            throw new DomainException('Chỉ còn ' . (int) $product['stock_quantity'] . ' sản phẩm trong kho.');
        }
        $this->carts->setQuantity($this->carts->getOrCreateCartId($userId), $productId, $quantity);
    }

    public function remove(int $userId, int $productId): void
    {
        $this->carts->removeItem($this->carts->getOrCreateCartId($userId), $productId);
    }

    /** @throws DomainException */
    private function activeProduct(int $productId): array
    {
        $found = $this->products->findActiveByIds([$productId]);
        if ($found === []) {
            throw new DomainException('Sản phẩm không tồn tại hoặc đã ngừng bán.');
        }
        if ((int) $found[0]['stock_quantity'] < 1) {
            throw new DomainException('Sản phẩm đã hết hàng.');
        }

        return $found[0];
    }
}
