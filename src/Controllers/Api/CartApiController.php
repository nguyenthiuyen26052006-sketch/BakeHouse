<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\CartService;
use DomainException;

final class CartApiController extends BaseController
{
    public function add(Request $request): Response
    {
        return $this->mutate(fn (CartService $cart, int $userId) => $cart->add(
            $userId,
            (int) $request->getInput('product_id'),
            max(1, (int) $request->getInput('quantity', 1))
        ), 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request): Response
    {
        return $this->mutate(fn (CartService $cart, int $userId) => $cart->updateQuantity(
            $userId,
            (int) $request->getInput('product_id'),
            (int) $request->getInput('quantity')
        ), 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request): Response
    {
        return $this->mutate(fn (CartService $cart, int $userId) => $cart->remove(
            $userId,
            (int) $request->getInput('product_id')
        ), 'Đã xóa khỏi giỏ hàng.');
    }

    /** @param callable(CartService, int): mixed $action */
    private function mutate(callable $action, string $successMessage): Response
    {
        $cart = new CartService();
        $userId = (int) Auth::id();

        try {
            $action($cart, $userId);
        } catch (DomainException $e) {
            $summary = $cart->summary($userId);

            return $this->json(['ok' => false, 'message' => $e->getMessage(), 'count' => $summary['count']], 422);
        }

        $summary = $cart->summary($userId);
        $lines = [];
        foreach ($summary['items'] as $item) {
            $lines[(int) $item['product_id']] = [
                'quantity' => (int) $item['quantity'],
                'line_total' => format_vnd((int) $item['line_total']),
            ];
        }

        return $this->json([
            'ok' => true,
            'message' => $successMessage,
            'count' => $summary['count'],
            'subtotal' => format_vnd($summary['subtotal']),
            'subtotal_raw' => $summary['subtotal'],
            'lines' => $lines,
        ]);
    }
}
