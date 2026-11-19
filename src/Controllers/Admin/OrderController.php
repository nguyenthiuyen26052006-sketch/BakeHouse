<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use DomainException;

final class OrderController extends BaseController
{
    public function __construct(private readonly OrderRepository $orders = new OrderRepository())
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $status = (string) $request->getQuery('status', '');
        $paginator = Paginator::fromRequest($request, $this->orders->adminCount($search, $status), 10);

        return $this->render('admin.orders.index', [
            'title' => 'Đơn hàng',
            'rows' => $this->orders->adminList($search, $status, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'search' => $search,
            'status' => $status,
        ], 'layouts.admin');
    }

    public function show(Request $request, string $id): Response
    {
        $order = $this->orders->findById((int) $id);
        if ($order === null) {
            return ErrorHandler::render(404, $request);
        }

        $customer = (new \App\Repositories\UserRepository())->findById((int) $order['user_id']);

        return $this->render('admin.orders.show', [
            'title' => 'Đơn ' . $order['order_code'],
            'order' => $order,
            'customer' => $customer,
            'items' => $this->orders->items((int) $id),
            'histories' => $this->orders->histories((int) $id),
        ], 'layouts.admin');
    }

    public function updateStatus(Request $request, string $id): Response
    {
        try {
            (new OrderService())->advanceOrder((int) $id, (int) Auth::id(), (string) $request->getInput('status'));
            $this->flash('success', 'Cập nhật trạng thái thành công.');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/admin/orders/' . (int) $id);
    }

    public function confirmPayment(Request $request, string $id): Response
    {
        try {
            (new OrderService())->confirmPayment((int) $id, (int) Auth::id());
            $this->flash('success', 'Đã xác nhận nhận tiền.');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/admin/orders/' . (int) $id);
    }
}
