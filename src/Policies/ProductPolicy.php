<?php

declare(strict_types=1);

namespace App\Policies;

final class ProductPolicy
{
    public static function canModify(?array $user): bool
    {
        return $user !== null && ($user['role'] ?? null) === 'admin';
    }
}
