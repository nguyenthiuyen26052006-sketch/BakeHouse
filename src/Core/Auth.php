<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class Auth
{
    private const SESSION_KEY = 'auth_user_id';
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function id(): ?int
    {
        $user = self::user();

        return $user === null ? null : (int) $user['id'];
    }

    /** Current user row (with profile), or null when guest / deactivated. */
    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get(self::SESSION_KEY);
            if ($id !== null) {
                try {
                    $user = (new UserRepository())->findById((int) $id);
                } catch (\Throwable) {
                    return null; // database unavailable: treat as guest for this request
                }
                if ($user !== null && (int) $user['is_active'] === 1) {
                    self::$user = $user;
                } else {
                    Session::remove(self::SESSION_KEY);
                }
            }
        }

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set(self::SESSION_KEY, $userId);
        self::$loaded = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$loaded = true;
        self::$user = null;
    }
}
