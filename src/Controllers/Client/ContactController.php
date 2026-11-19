<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ContactRepository;

final class ContactController extends BaseController
{
    public function __construct(private readonly ContactRepository $contacts = new ContactRepository())
    {
    }

    public function index(Request $request): Response
    {
        $userId = (int) Auth::id();
        $paginator = Paginator::fromRequest($request, $this->contacts->countByUser($userId), 10);

        return $this->render('client.contacts.index', [
            'title' => 'Liên hệ của tôi',
            'rows' => $this->contacts->listByUser($userId, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
        ]);
    }
}
