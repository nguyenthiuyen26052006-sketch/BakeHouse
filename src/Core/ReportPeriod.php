<?php

declare(strict_types=1);

namespace App\Core;

/** Reporting window used by the dashboard and the printable report (?range=7d|30d|month|all). */
final class ReportPeriod
{
    public const OPTIONS = ['7d' => '7 ngày', '30d' => '30 ngày', 'month' => 'Tháng này', 'all' => 'Tất cả'];

    private function __construct(
        public readonly string $key,
        public readonly ?string $from,
        public readonly ?string $to,
    ) {
    }

    public static function fromKey(string $key): self
    {
        $today = date('Y-m-d');

        return match ($key) {
            '7d' => new self('7d', date('Y-m-d', strtotime('-6 days')), $today),
            'month' => new self('month', date('Y-m-01'), $today),
            'all' => new self('all', null, null),
            default => new self('30d', date('Y-m-d', strtotime('-29 days')), $today),
        };
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromKey((string) $request->getQuery('range', '30d'));
    }

    public function label(): string
    {
        return match ($this->key) {
            '7d' => '7 ngày gần nhất',
            'month' => 'Tháng ' . date('m/Y'),
            'all' => 'Toàn bộ thời gian',
            default => '30 ngày gần nhất',
        };
    }
}
