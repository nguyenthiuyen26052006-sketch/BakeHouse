<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function rank(): int
    {
        return match ($this) {
            self::Pending => 0,
            self::Processing => 1,
            self::Shipped => 2,
            self::Delivered => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xác nhận',
            self::Processing => 'Đang xử lý',
            self::Shipped => 'Đang giao',
            self::Delivered => 'Đã giao',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-secondary',
            self::Processing => 'bg-warning text-dark',
            self::Shipped => 'bg-info text-dark',
            self::Delivered => 'bg-success',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Derive the overall order status from its item statuses.
     *
     * @param array<int, string> $itemStatuses
     */
    public static function derive(array $itemStatuses): self
    {
        $ranks = array_map(static fn (string $s): int => self::from($s)->rank(), $itemStatuses);
        if ($ranks === []) {
            return self::Pending;
        }
        if (min($ranks) >= self::Delivered->rank()) {
            return self::Delivered;
        }
        if (min($ranks) >= self::Shipped->rank()) {
            return self::Shipped;
        }
        if (max($ranks) > self::Pending->rank()) {
            return self::Processing;
        }

        return self::Pending;
    }
}
