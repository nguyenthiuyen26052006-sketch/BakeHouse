<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return $this === self::Admin ? 'Quản trị viên' : 'Người dùng';
    }
}
