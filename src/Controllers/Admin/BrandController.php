<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class BrandController extends AbstractResourceController
{
    protected function config(): array
    {
        return [
            'title' => 'Thương hiệu',
            'route' => 'brands',
            'table' => 'brands',
            'search' => ['name'],
            'slug_from' => 'name',
            'product_fk' => 'brand_id',
            'columns' => [
                ['key' => 'name', 'label' => 'Tên thương hiệu', 'type' => 'text'],
                ['key' => 'product_count', 'label' => 'Số sản phẩm', 'type' => 'number'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Tên thương hiệu', 'type' => 'text', 'rules' => 'required|max:120'],
            ],
        ];
    }
}
