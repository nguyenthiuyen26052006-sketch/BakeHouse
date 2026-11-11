<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\CartService;
use App\Services\OrderService;

final class CartController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->render('client.cart.index', [
            'title' => 'Giỏ hàng',
            'cart' => (new CartService())->summary((int) Auth::id()),
            'shippingFee' => (new OrderService())->getShippingFee(),
        ]);
    }
}
