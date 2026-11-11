<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
        $search = trim((string) $request->getQuery('q', ''));
        $status = (string) $request->getQuery('status', '');
        $paginator = Paginator::fromRequest($request, $this->contacts->adminCount($search, $status), 10);

        return $this->render('admin.contacts.index', [
            'title' => 'Liên hệ',
            'rows' => $this->contacts->adminList($search, $status, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'search' => $search,
            'status' => $status,
        ], 'layouts.admin');
    }

    public function updateStatus(Request $request, string $id): Response
    {
        $status = (string) $request->getInput('status');
        if (in_array($status, ['new', 'read', 'replied'], true)) {
            $this->contacts->setStatus((int) $id, $status);
            $this->flash('success', 'Đã cập nhật trạng thái liên hệ.');
        }

        return $this->redirectBack('/admin/contacts');
    }
}
