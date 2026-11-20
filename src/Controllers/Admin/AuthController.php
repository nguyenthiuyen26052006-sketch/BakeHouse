<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

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
    public function showLogin(Request $request): Response
    {
        if ($redirect = $this->alreadySignedIn()) {
            return $redirect;
        }

        return $this->render('admin.auth.login', ['title' => 'Đăng nhập quản trị'], 'layouts.admin-auth');
    }

    public function login(Request $request): Response
    {
        if ($redirect = $this->alreadySignedIn()) {
            return $redirect;
        }

        $validator = new Validator($request->getAllInput(), ['identity' => 'required', 'password' => 'required']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/admin/login', $validator->errors());
        }

        $service = new AuthService();
        try {
            $user = $service->attempt((string) $request->getInput('identity'), (string) $request->getInput('password'), true);
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());

            return $this->redirectWithErrors($request, '/admin/login', []);
        }
        if ($user === null) {
            $this->flash('danger', 'Tài khoản hoặc mật khẩu không đúng.');

            return $this->redirectWithErrors($request, '/admin/login', []);
        }

        $service->login($user);

        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        Session::start();
        $this->flash('success', 'Bạn đã đăng xuất.');

        return $this->redirect('/admin/login');
    }

    /**
     * One session = one role. An admin goes straight to the back office; a customer must log out first
     * (a customer session is never silently replaced by an admin one).
     */
    private function alreadySignedIn(): ?Response
    {
        if (Auth::isAdmin()) {
            return $this->redirect('/admin');
        }
        if (Auth::check()) {
            $this->flash('warning', 'Bạn đang đăng nhập bằng tài khoản khách. Hãy đăng xuất trước khi vào khu vực quản trị.');

            return $this->redirect('/');
        }

        return null;
    }
}
