<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Services\VietQrService;

final class OrderController extends BaseController
{
    public function index(Request $request): Response
    {
        $orders = new OrderRepository();
        $userId = (int) Auth::id();
        $paginator = Paginator::fromRequest($request, $orders->countByUser($userId), 8);

        return $this->render('client.orders.index', [
            'title' => 'Đơn hàng của tôi',
            'orders' => $orders->listByUser($userId, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
        ]);
    }

    public function show(Request $request, string $code): Response
    {
        $orders = new OrderRepository();
        $order = $orders->findByCode($code);
        if ($order === null || (int) $order['user_id'] !== (int) Auth::id()) {
            return ErrorHandler::render(404, $request);
        }

        $qr = new VietQrService();
        $showQr = $order['payment_method'] === 'vietqr' && $order['payment_status'] === 'unpaid';

        return $this->render('client.orders.show', [
            'title' => 'Đơn hàng ' . $order['order_code'],
            'order' => $order,
            'items' => $orders->items((int) $order['id']),
            'histories' => $orders->histories((int) $order['id']),
            'qrUrl' => $showQr ? $qr->buildImageUrl($order['order_code'], (int) $order['total_amount']) : null,
            'account' => $qr->accountInfo(),
        ]);
    }
}
