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
            self::Pending => 'Chờ xử lý',
            self::Processing => 'Đã xác nhận',
            self::Shipped => 'Đang giao',
            self::Delivered => 'Hoàn tất',
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

    /** The single next status in the pipeline, or null if this is the last one. */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::Processing,
            self::Processing => self::Shipped,
            self::Shipped => self::Delivered,
            self::Delivered => null,
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
