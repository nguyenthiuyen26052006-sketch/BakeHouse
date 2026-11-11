<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ContactRepository;

final class PageController extends BaseController
{
    private const PAGES = [
        'about' => 'Giới thiệu',
        'faq' => 'Câu hỏi thường gặp',
        'privacy' => 'Chính sách bảo mật',
        'terms' => 'Điều khoản sử dụng',
        'gallery' => 'Thư viện',
        'blog' => 'Blog',
    ];

    public function about(Request $request): Response
    {
        return $this->page('about');
    }

    public function faq(Request $request): Response
    {
        return $this->page('faq');
    }

    public function privacy(Request $request): Response
    {
        return $this->page('privacy');
    }

    public function terms(Request $request): Response
    {
        return $this->page('terms');
    }

    public function gallery(Request $request): Response
    {
        return $this->page('gallery');
    }

    public function blog(Request $request): Response
    {
        return $this->page('blog');
    }

    public function contact(Request $request): Response
    {
        return $this->render('client.pages.contact', ['title' => 'Liên hệ']);
    }

    public function sendContact(Request $request): Response
    {
        $input = $request->getAllInput();
        $validator = new Validator($input, [
            'full_name' => 'required|max:150',
            'email' => 'email|max:190',
            'phone' => 'phone',
            'message' => 'required|min:10|max:2000',
        ]);

        $errors = $validator->validate() ? [] : $validator->errors();
        if (trim((string) ($input['email'] ?? '')) === '' && trim((string) ($input['phone'] ?? '')) === '') {
            $errors['email'] = 'Vui lòng nhập email hoặc số điện thoại để chúng tôi phản hồi.';
        }
        if ($errors !== []) {
            return $this->redirectWithErrors($request, '/contact', $errors);
        }

        (new ContactRepository())->create(
            trim((string) $input['full_name']),
            trim((string) ($input['email'] ?? '')) ?: null,
            trim((string) ($input['phone'] ?? '')) ?: null,
            trim((string) $input['message'])
        );
        $this->flash('success', 'Cảm ơn bạn! Chúng tôi sẽ phản hồi sớm nhất.');

        return $this->redirect('/contact');
    }

    private function page(string $name): Response
    {
        return $this->render('client.pages.' . $name, ['title' => self::PAGES[$name]]);
    }
}
