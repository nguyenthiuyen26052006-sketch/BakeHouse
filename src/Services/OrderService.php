<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use DomainException;

final class OrderService
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly CartRepository $carts = new CartRepository()
    ) {
    }

    public function getShippingFee(): int
    {
        return max(0, Env::int('SHIPPING_FEE', 30000));
    }

    /**
     * Place an order atomically: lock products, check stock, decrement stock, insert order + items + history.
     *
     * @param array<int, int> $lines product_id => quantity
     * @param array{recipient_name: string, shipping_phone: string, shipping_address: string, note: ?string} $shipping
     * @return string order code
     * @throws DomainException on stock/product problems
     */
    public function placeOrder(int $userId, array $lines, array $shipping, PaymentMethod $payment, bool $fromCart): string
    {
        if ($lines === []) {
            throw new DomainException('Giỏ hàng trống.');
        }
        ksort($lines); // consistent lock order prevents deadlocks

        return Database::transaction(function () use ($userId, $lines, $shipping, $payment, $fromCart): string {
            $products = [];
            $subtotal = 0;
            foreach ($lines as $productId => $quantity) {
                $product = $this->orders->lockProduct((int) $productId);
                if ($product === null) {
                    throw new DomainException('Một sản phẩm trong đơn không còn được bán.');
                }
                if ($quantity < 1 || $quantity > (int) $product['stock_quantity']) {
                    throw new DomainException(
                        sprintf('"%s" chỉ còn %d sản phẩm trong kho.', $product['name'], (int) $product['stock_quantity'])
                    );
                }
                $products[$productId] = $product;
                $subtotal += (int) $product['price'] * $quantity;
            }

            $fee = $this->getShippingFee();
            $orderId = $this->orders->createOrder([
                'order_code' => 'TMP' . bin2hex(random_bytes(8)),
                'user_id' => $userId,
                'recipient_name' => $shipping['recipient_name'],
                'shipping_phone' => $shipping['shipping_phone'],
                'shipping_address' => $shipping['shipping_address'],
                'note' => $shipping['note'],
                'payment_method' => $payment->value,
                'payment_status' => 'unpaid',
                'status' => OrderStatus::Pending->value,
                'shipping_fee' => $fee,
                'total_amount' => $subtotal + $fee,
            ]);
            $code = $this->codeForId($orderId);
            $this->orders->updateOrderCode($orderId, $code);

            foreach ($lines as $productId => $quantity) {
                $this->orders->createItem($orderId, $products[$productId], $quantity);
                $this->orders->decrementStock((int) $productId, $quantity);
            }
            $this->orders->createHistory($orderId, null, OrderStatus::Pending->value, $userId);

            if ($fromCart) {
                $cartId = $this->carts->getOrCreateCartId($userId);
                foreach (array_keys($lines) as $productId) {
                    $this->carts->removeItem($cartId, (int) $productId);
                }
            }

            return $code;
        });
    }

    /** Sequential order code derived from the row id: BH01, BH02, … BH100, … */
    private function codeForId(int $orderId): string
    {
        return 'BH' . str_pad((string) $orderId, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Move all items in the order forward to $newStatus, then re-derive the overall status.
     *
     * @throws DomainException on invalid transition / permission
     */
    public function advanceOrder(int $orderId, int $adminId, string $newStatus): void
    {
        $target = OrderStatus::tryFrom($newStatus);
        $order = $target === null ? null : $this->orders->findById($orderId);
        $current = $order === null ? null : OrderStatus::from($order['status']);
        if ($current === null || $current->next() !== $target) {
            throw new DomainException('Cập nhật trạng thái thất bại.');
        }

        Database::transaction(function () use ($orderId, $adminId, $target, $order): void {
            $this->orders->updateAllItemsStatus($orderId, $target->value);
            $this->orders->updateStatus($orderId, $target->value);
            $this->orders->createHistory($orderId, $order['status'], $target->value, $adminId);
            if ($target === OrderStatus::Delivered && $order['payment_method'] === 'cod') {
                $this->orders->updatePaymentStatus($orderId, 'paid');
            }
        });
    }

    /** @throws DomainException */
    public function confirmPayment(int $orderId, int $adminId): void
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Không tìm thấy đơn hàng.');
        }
        if ($order['payment_method'] !== 'vietqr' || $order['payment_status'] !== 'unpaid') {
            throw new DomainException('Đơn này không cần xác nhận thanh toán.');
        }
        $this->orders->updatePaymentStatus($orderId, 'paid');
    }
}
