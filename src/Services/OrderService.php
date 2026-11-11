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
            $code = $this->generateCode();
            $orderId = $this->orders->createOrder([
                'order_code' => $code,
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

    private function generateCode(): string
    {
        do {
            $code = 'BH' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while ($this->orders->findByCode($code) !== null);

        return $code;
    }

    /**
     * Move this admin's items in the order forward to $newStatus, then re-derive the overall status.
     *
     * @throws DomainException on invalid transition / permission
     */
    public function advanceVendorItems(int $orderId, int $adminId, string $newStatus): void
    {
        $target = OrderStatus::tryFrom($newStatus);
        if ($target === null) {
            throw new DomainException('Trạng thái không hợp lệ.');
        }
        $order = $this->orders->findById($orderId);
        if ($order === null || !$this->orders->hasVendor($orderId, $adminId)) {
            throw new DomainException('Bạn không có quyền xử lý đơn hàng này.');
        }

        Database::transaction(function () use ($orderId, $adminId, $target, $order): void {
            $changed = 0;
            foreach ($this->orders->vendorItems($orderId, $adminId) as $item) {
                if (OrderStatus::from($item['status'])->rank() < $target->rank()) {
                    $this->orders->updateItemStatus((int) $item['id'], $target->value);
                    $changed++;
                }
            }
            if ($changed === 0) {
                throw new DomainException('Chỉ được chuyển sang trạng thái tiếp theo, không thể quay lại.');
            }

            $newOverall = OrderStatus::derive($this->orders->itemStatuses($orderId));
            if ($newOverall->value !== $order['status']) {
                $this->orders->updateStatus($orderId, $newOverall->value);
                $this->orders->createHistory($orderId, $order['status'], $newOverall->value, $adminId);
                if ($newOverall === OrderStatus::Delivered && $order['payment_method'] === 'cod') {
                    $this->orders->updatePaymentStatus($orderId, 'paid');
                }
            }
        });
    }

    /** @throws DomainException */
    public function confirmPayment(int $orderId, int $adminId): void
    {
        $order = $this->orders->findById($orderId);
        if ($order === null || !$this->orders->hasVendor($orderId, $adminId)) {
            throw new DomainException('Bạn không có quyền xử lý đơn hàng này.');
        }
        if ($order['payment_method'] !== 'vietqr' || $order['payment_status'] !== 'unpaid') {
            throw new DomainException('Đơn này không cần xác nhận thanh toán.');
        }
        $this->orders->updatePaymentStatus($orderId, 'paid');
    }
}
