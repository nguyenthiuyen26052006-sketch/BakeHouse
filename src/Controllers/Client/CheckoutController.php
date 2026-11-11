<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Enums\PaymentMethod;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\OrderService;
use DomainException;

final class CheckoutController extends BaseController
{
    public function show(Request $request): Response
    {
        $resolved = $this->resolveLines($request, $request->getAllQuery());
        if ($resolved === null) {
            $this->flash('warning', 'Không có sản phẩm nào để thanh toán.');

            return $this->redirect('/cart');
        }

        return $this->render('client.checkout.index', [
            'title' => 'Thanh toán',
            'items' => $resolved['items'],
            'subtotal' => $resolved['subtotal'],
            'shippingFee' => (new OrderService())->getShippingFee(),
            'mode' => $resolved['mode'],
            'buyNow' => $resolved['buy_now'],
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): Response
    {
        $input = $request->getAllInput();
        $back = $this->checkoutUrl($input);
        $validator = new Validator($input, [
            'recipient_name' => 'required|max:150',
            'shipping_phone' => 'required|phone',
            'shipping_address' => 'required|max:255',
            'payment_method' => 'required|in:cod,vietqr',
            'note' => 'max:500',
        ]);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, $back, $validator->errors());
        }

        $resolved = $this->resolveLines($request, $input);
        if ($resolved === null) {
            $this->flash('warning', 'Không có sản phẩm nào để thanh toán.');

            return $this->redirect('/cart');
        }

        try {
            $code = (new OrderService())->placeOrder(
                (int) Auth::id(),
                $resolved['lines'],
                [
                    'recipient_name' => trim((string) $input['recipient_name']),
                    'shipping_phone' => trim((string) $input['shipping_phone']),
                    'shipping_address' => trim((string) $input['shipping_address']),
                    'note' => trim((string) ($input['note'] ?? '')) ?: null,
                ],
                PaymentMethod::from((string) $input['payment_method']),
                $resolved['mode'] === 'cart'
            );
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());

            return $this->redirectWithErrors($request, $back, []);
        }

        $this->flash('success', 'Đặt hàng thành công!');

        return $this->redirect('/orders/' . $code);
    }

    private function checkoutUrl(array $input): string
    {
        if (($input['mode'] ?? 'cart') === 'buy_now') {
            return '/checkout?' . http_build_query(['buy_now' => (int) ($input['product_id'] ?? 0), 'qty' => (int) ($input['qty'] ?? 1)]);
        }

        return '/checkout';
    }

    /**
     * Work out which lines to buy: either the whole cart or a single "buy now" product.
     *
     * @return array{mode: string, lines: array<int, int>, items: array<int, array<string, mixed>>, subtotal: int, buy_now: ?array}|null
     */
    private function resolveLines(Request $request, array $source): ?array
    {
        $userId = (int) Auth::id();
        $buyNowId = (int) ($source['buy_now'] ?? ($source['product_id'] ?? 0));

        if ((($source['mode'] ?? '') === 'buy_now' || isset($source['buy_now'])) && $buyNowId > 0) {
            $qty = max(1, (int) ($source['qty'] ?? 1));
            $found = (new ProductRepository())->findActiveByIds([$buyNowId]);
            if ($found === []) {
                return null;
            }
            $product = $found[0];
            $qty = min($qty, max(1, (int) $product['stock_quantity']));
            $product['quantity'] = $qty;
            $product['line_total'] = (int) $product['price'] * $qty;

            return [
                'mode' => 'buy_now',
                'lines' => [$buyNowId => $qty],
                'items' => [$product],
                'subtotal' => $product['line_total'],
                'buy_now' => ['product_id' => $buyNowId, 'qty' => $qty],
            ];
        }

        $summary = (new CartService())->summary($userId);
        $items = array_values(array_filter($summary['items'], static fn (array $i): bool => $i['available']));
        if ($items === []) {
            return null;
        }
        $lines = [];
        foreach ($items as $item) {
            $lines[(int) $item['product_id']] = (int) $item['quantity'];
        }

        return ['mode' => 'cart', 'lines' => $lines, 'items' => $items, 'subtotal' => $summary['subtotal'], 'buy_now' => null];
    }
}
