<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\BannerRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

final class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        $products = new ProductRepository();

        return $this->render('client.home', [
            'title' => 'Trang chủ',
            'banners' => (new BannerRepository())->active(),
            'categories' => (new CategoryRepository())->activeWithCounts(),
            'featured' => $products->featured(4),
            'newest' => $products->newest(4),
            'bestSelling' => $products->bestSelling(4),
            'topRated' => $products->topRated(4),
        ]);
    }
}
