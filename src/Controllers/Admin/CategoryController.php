<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class CategoryController extends AbstractResourceController
{
    protected function config(): array
    {
        return [
            'title' => 'Danh mục',
            'route' => 'categories',
            'table' => 'categories',
            'search' => ['name'],
            'slug_from' => 'name',
            'product_fk' => 'category_id',
            'block_delete_when_used' => true,
            'columns' => [
                ['key' => 'name', 'label' => 'Tên danh mục', 'type' => 'text'],
                ['key' => 'product_count', 'label' => 'Số sản phẩm', 'type' => 'number'],
                ['key' => 'is_active', 'label' => 'Hiển thị', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Tên danh mục', 'type' => 'text', 'rules' => 'required|max:120'],
                ['name' => 'is_active', 'label' => 'Hiển thị trên website', 'type' => 'checkbox'],
            ],
        ];
    }
}
