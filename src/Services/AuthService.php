<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Env;
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

    /** Create a reset token and "send" it (development: written to storage/logs/mail.log). */
    public function sendPasswordReset(string $identity): void
    {
        $user = $this->users->findByIdentity(mb_strtolower(trim($identity)));
        if ($user === null) {
            return; // do not reveal whether the account exists
        }

        $token = bin2hex(random_bytes(32));
        $this->users->createPasswordReset(
            (int) $user['id'],
            hash('sha256', $token),
            date('Y-m-d H:i:s', time() + 3600)
        );

        $link = rtrim((string) Env::get('APP_URL', ''), '/') . '/reset-password/' . $token;
        $line = sprintf(
            "[%s] To: %s | Subject: Đặt lại mật khẩu BakeHouse | Link (hết hạn sau 1 giờ): %s\n",
            date('Y-m-d H:i:s'),
            $user['email'] ?? $user['phone'],
            $link
        );
        @file_put_contents(BASE_PATH . '/storage/logs/mail.log', $line, FILE_APPEND);
    }

    public function isResetTokenValid(string $token): bool
    {
        return $this->users->findValidReset(hash('sha256', $token)) !== null;
    }

    /** @throws DomainException when token invalid/expired */
    public function resetPassword(string $token, string $newPassword): void
    {
        $reset = $this->users->findValidReset(hash('sha256', $token));
        if ($reset === null) {
            throw new DomainException('Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        }
        $this->users->updatePassword((int) $reset['user_id'], password_hash($newPassword, PASSWORD_DEFAULT));
        $this->users->deleteResets((int) $reset['user_id']);
    }

    public function updateProfile(int $userId, string $fullName, ?string $phone, ?string $address): void
    {
        $this->users->updateProfile($userId, $fullName, $phone, $address);
    }
}
