<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuthService;
use DomainException;

final class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $auth = new AuthService())
    {
    }

    public function showLogin(Request $request): Response
    {
        return $this->render('client.auth.login', ['title' => 'Đăng nhập']);
    }

    public function login(Request $request): Response
    {
        $validator = new Validator($request->getAllInput(), ['identity' => 'required', 'password' => 'required']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/login', $validator->errors());
        }

        try {
            $user = $this->auth->attempt((string) $request->getInput('identity'), (string) $request->getInput('password'));
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());

            return $this->redirectWithErrors($request, '/login', []);
        }
        if ($user === null) {
            $this->flash('danger', 'Email/SĐT hoặc mật khẩu không đúng.');

            return $this->redirectWithErrors($request, '/login', []);
        }

        $intended = (string) Session::get('intended_url', '/');
        $this->auth->login($user);
        Session::remove('intended_url');
        $this->flash('success', 'Đăng nhập thành công. Chào mừng bạn trở lại!');

        return $this->redirect(str_starts_with($intended, '/') && !str_starts_with($intended, '//') ? $intended : '/');
    }

    public function showRegister(Request $request): Response
    {
        return $this->render('client.auth.register', ['title' => 'Đăng ký']);
    }

    public function register(Request $request): Response
    {
        $input = $request->getAllInput();
        $validator = new Validator($input, [
            'full_name' => 'required|max:150',
            'identity' => 'required|max:190',
            'password' => 'required|password|confirmed',
        ]);
        $errors = $validator->validate() ? [] : $validator->errors();
        if (!isset($errors['password']) && ($input['password'] ?? '') !== ($input['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Xác nhận mật khẩu không khớp.';
        }
        $identity = trim((string) ($input['identity'] ?? ''));
        if (!isset($errors['identity']) && !$this->isValidIdentity($identity)) {
            $errors['identity'] = lang('validation.identity');
        }
        if ($errors !== []) {
            return $this->redirectWithErrors($request, '/register', $errors);
        }

        try {
            $userId = $this->auth->register($identity, (string) $input['password'], trim((string) $input['full_name']));
        } catch (DomainException $e) {
            return $this->redirectWithErrors($request, '/register', ['identity' => $e->getMessage()]);
        }

        Auth::login($userId);
        $this->flash('success', 'Đăng ký thành công! Chào mừng bạn đến với BakeHouse.');

        return $this->redirect('/');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        Session::start();
        $this->flash('success', 'Bạn đã đăng xuất.');

        return $this->redirect('/');
    }

    private const RESET_SESSION_KEY = 'pwd_reset_user_id';
    private const RESET_EXPIRES_KEY = 'pwd_reset_expires';
    private const RESET_TTL_SECONDS = 600;

    public function showForgot(Request $request): Response
    {
        return $this->render('client.auth.forgot-password', [
            'title' => 'Quên mật khẩu',
            'step' => $this->pendingResetUserId() !== null ? 'reset' : 'identity',
        ]);
    }

    public function forgot(Request $request): Response
    {
        $validator = new Validator($request->getAllInput(), ['identity' => 'required']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/forgot-password', $validator->errors());
        }

        $user = $this->auth->findUserForReset((string) $request->getInput('identity'));
        if ($user === null) {
            $this->flash('danger', 'Không tìm thấy tài khoản với email hoặc số điện thoại này.');

            return $this->redirect('/forgot-password');
        }

        Session::set(self::RESET_SESSION_KEY, (int) $user['id']);
        Session::set(self::RESET_EXPIRES_KEY, time() + self::RESET_TTL_SECONDS);

        return $this->redirect('/forgot-password');
    }

    public function resetDirect(Request $request): Response
    {
        $userId = $this->pendingResetUserId();
        if ($userId === null) {
            $this->flash('danger', 'Phiên đặt lại mật khẩu đã hết hạn, vui lòng thử lại.');

            return $this->redirect('/forgot-password');
        }

        $validator = new Validator($request->getAllInput(), ['password' => 'required|password|confirmed']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/forgot-password', $validator->errors());
        }

        $this->auth->resetPasswordForUser($userId, (string) $request->getInput('password'));
        Session::remove(self::RESET_SESSION_KEY);
        Session::remove(self::RESET_EXPIRES_KEY);
        $this->flash('success', 'Đặt lại mật khẩu thành công. Hãy đăng nhập bằng mật khẩu mới.');

        return $this->redirect('/login');
    }

    private function pendingResetUserId(): ?int
    {
        $expires = Session::get(self::RESET_EXPIRES_KEY);
        if ($expires === null || time() > (int) $expires) {
            Session::remove(self::RESET_SESSION_KEY);
            Session::remove(self::RESET_EXPIRES_KEY);

            return null;
        }

        $id = Session::get(self::RESET_SESSION_KEY);

        return $id === null ? null : (int) $id;
    }

    private function isValidIdentity(string $identity): bool
    {
        return AuthService::isEmail($identity)
            ? filter_var($identity, FILTER_VALIDATE_EMAIL) !== false
            : (bool) preg_match('/^(0|\+84)\d{9,10}$/', $identity);
    }
}
