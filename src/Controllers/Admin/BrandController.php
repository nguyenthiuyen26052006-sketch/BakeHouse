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
            'search' => ['name', 'slug'],
            'slug_from' => 'name',
            'columns' => [
                ['key' => 'name', 'label' => 'Tên', 'type' => 'text'],
                ['key' => 'slug', 'label' => 'Slug', 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'Ngày tạo', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Tên thương hiệu', 'type' => 'text', 'rules' => 'required|max:120'],
                ['name' => 'slug', 'label' => 'Slug (để trống để tự tạo)', 'type' => 'text', 'rules' => 'max:140'],
            ],
        ];
    }
}
