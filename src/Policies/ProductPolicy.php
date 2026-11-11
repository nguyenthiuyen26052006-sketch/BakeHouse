<?php

declare(strict_types=1);

namespace App\Policies;

/** Multi-vendor rule: an admin may modify only the products they created. */
final class ProductPolicy
{
    public static function canModify(?array $user, array $product): bool
    {
        return $user !== null
            && ($user['role'] ?? null) === 'admin'
            && (int) $product['created_by'] === (int) $user['id'];
    }
}
