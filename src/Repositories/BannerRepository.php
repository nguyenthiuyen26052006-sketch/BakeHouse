<?php

declare(strict_types=1);

namespace App\Repositories;

final class BannerRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function active(): array
    {
        return $this->fetchAll('SELECT * FROM banners WHERE is_active = 1 ORDER BY id');
    }
}
