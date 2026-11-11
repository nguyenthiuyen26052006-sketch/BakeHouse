<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\FileUploader;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use RuntimeException;

final class AccountController extends BaseController
{
    public function show(Request $request): Response
    {
        return $this->render('client.account.profile', ['title' => 'Tài khoản của tôi', 'user' => Auth::user()]);
    }

    public function update(Request $request): Response
    {
        $validator = new Validator($request->getAllInput(), [
            'full_name' => 'required|max:150',
            'shipping_phone' => 'phone',
            'shipping_address' => 'max:255',
        ]);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/account', $validator->errors());
        }

        $userId = (int) Auth::id();
        (new AuthService())->updateProfile(
            $userId,
            trim((string) $request->getInput('full_name')),
            trim((string) $request->getInput('shipping_phone')) ?: null,
            trim((string) $request->getInput('shipping_address')) ?: null
        );

        $avatar = $request->getFile('avatar');
        if ($avatar !== null && $avatar['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploader = new FileUploader();
            try {
                $path = $uploader->upload($avatar, 'avatars');
                $uploader->delete(Auth::user()['avatar_path'] ?? null);
                (new UserRepository())->updateAvatar($userId, $path);
            } catch (RuntimeException $e) {
                return $this->redirectWithErrors($request, '/account', ['avatar' => $e->getMessage()]);
            }
        }

        $this->flash('success', 'Đã cập nhật thông tin tài khoản.');

        return $this->redirect('/account');
    }
}
