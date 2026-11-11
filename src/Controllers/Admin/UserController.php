<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;

final class UserController extends BaseController
{
    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $role = (string) $request->getQuery('role', '');
        $active = (string) $request->getQuery('active', '');
        $paginator = Paginator::fromRequest($request, $this->users->adminCount($search, $role, $active), 10);

        return $this->render('admin.users.index', [
            'title' => 'Người dùng',
            'rows' => $this->users->adminList($search, $role, $active, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'search' => $search,
            'role' => $role,
            'active' => $active,
            'currentId' => (int) Auth::id(),
        ], 'layouts.admin');
    }

    public function show(Request $request, string $id): Response
    {
        $user = $this->users->findById((int) $id);
        if ($user === null) {
            return ErrorHandler::render(404, $request);
        }

        return $this->render('admin.users.show', [
            'title' => 'Chi tiết người dùng',
            'user' => $user,
            'activity' => $this->users->activitySummary((int) $id),
            'isSelf' => (int) $id === (int) Auth::id(),
        ], 'layouts.admin');
    }

    public function updateRole(Request $request, string $id): Response
    {
        $role = (string) $request->getInput('role');
        if ((int) $id === (int) Auth::id()) {
            $this->flash('danger', 'Bạn không thể tự thay đổi vai trò của chính mình.');
        } elseif ($this->users->findById((int) $id) === null || !in_array($role, ['user', 'admin'], true)) {
            return ErrorHandler::render(404, $request);
        } else {
            $this->users->setRole((int) $id, $role);
            $this->flash('success', 'Đã cập nhật vai trò.');
        }

        return $this->redirect('/admin/users/' . (int) $id);
    }

    public function toggleActive(Request $request, string $id): Response
    {
        $user = $this->users->findById((int) $id);
        if ($user === null) {
            return ErrorHandler::render(404, $request);
        }
        if ((int) $id === (int) Auth::id()) {
            $this->flash('danger', 'Bạn không thể tự khóa tài khoản của mình.');
        } else {
            $this->users->setActive((int) $id, (int) $user['is_active'] !== 1);
            $this->flash('success', 'Đã cập nhật trạng thái tài khoản.');
        }

        return $this->redirectBack('/admin/users');
    }
}
