<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Repositories\UserRepository;
use DomainException;

final class AuthService
{
    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }

    public static function isEmail(string $identity): bool
    {
        return str_contains($identity, '@');
    }

    /** @throws DomainException when email/phone already registered */
    public function register(string $identity, string $password, string $fullName): int
    {
        $identity = trim($identity);
        $isEmail = self::isEmail($identity);
        $email = $isEmail ? mb_strtolower($identity) : null;
        $phone = $isEmail ? null : $identity;

        if ($email !== null && $this->users->emailExists($email)) {
            throw new DomainException('Email này đã được đăng ký.');
        }
        if ($phone !== null && $this->users->phoneExists($phone)) {
            throw new DomainException('Số điện thoại này đã được đăng ký.');
        }

        return $this->users->createWithProfile($email, $phone, password_hash($password, PASSWORD_DEFAULT), $fullName);
    }

    /** Verify credentials. Returns the user row or null. */
    public function attempt(string $identity, string $password, bool $requireAdmin = false): ?array
    {
        $user = $this->users->findByIdentity(mb_strtolower(trim($identity)))
            ?? $this->users->findByIdentity(trim($identity));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        if ((int) $user['is_active'] !== 1) {
            throw new DomainException('Tài khoản của bạn đã bị vô hiệu hóa.');
        }
        if ($requireAdmin && $user['role'] !== 'admin') {
            throw new DomainException('Tài khoản không có quyền quản trị.');
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        return $user;
    }

    public function login(array $user): void
    {
        Auth::login((int) $user['id']);
    }

    /** Look up the account to reset by email/phone, or null if none matches. */
    public function findUserForReset(string $identity): ?array
    {
        return $this->users->findByIdentity(mb_strtolower(trim($identity)))
            ?? $this->users->findByIdentity(trim($identity));
    }

    public function resetPasswordForUser(int $userId, string $newPassword): void
    {
        $this->users->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    public function updateProfile(int $userId, string $fullName, ?string $phone, ?string $address): void
    {
        $this->users->updateProfile($userId, $fullName, $phone, $address);
    }
}
