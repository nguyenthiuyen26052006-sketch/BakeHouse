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

    public function showForgot(Request $request): Response
    {
        return $this->render('client.auth.forgot-password', ['title' => 'Quên mật khẩu']);
    }

    public function forgot(Request $request): Response
    {
        $validator = new Validator($request->getAllInput(), ['identity' => 'required']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/forgot-password', $validator->errors());
        }
        $this->auth->sendPasswordReset((string) $request->getInput('identity'));
        $this->flash('success', 'Nếu tài khoản tồn tại, liên kết đặt lại mật khẩu đã được gửi (môi trường dev: xem storage/logs/mail.log).');

        return $this->redirect('/forgot-password');
    }

    public function showReset(Request $request, string $token): Response
    {
        if (!$this->auth->isResetTokenValid($token)) {
            $this->flash('danger', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');

            return $this->redirect('/forgot-password');
        }

        return $this->render('client.auth.reset-password', ['title' => 'Đặt lại mật khẩu', 'token' => $token]);
    }

    public function reset(Request $request, string $token): Response
    {
        $validator = new Validator($request->getAllInput(), ['password' => 'required|password|confirmed']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/reset-password/' . $token, $validator->errors());
        }
        try {
            $this->auth->resetPassword($token, (string) $request->getInput('password'));
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());

            return $this->redirect('/forgot-password');
        }
        $this->flash('success', 'Đặt lại mật khẩu thành công. Hãy đăng nhập.');

        return $this->redirect('/login');
    }

    private function isValidIdentity(string $identity): bool
    {
        return AuthService::isEmail($identity)
            ? filter_var($identity, FILTER_VALIDATE_EMAIL) !== false
            : (bool) preg_match('/^(0|\+84)\d{9,10}$/', $identity);
    }
}
