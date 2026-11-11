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
            'search' => ['name', 'slug'],
            'slug_from' => 'name',
            'columns' => [
                ['key' => 'image_path', 'label' => 'Ảnh', 'type' => 'image'],
                ['key' => 'name', 'label' => 'Tên', 'type' => 'text'],
                ['key' => 'slug', 'label' => 'Slug', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'Hiển thị', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Tên danh mục', 'type' => 'text', 'rules' => 'required|max:120'],
                ['name' => 'slug', 'label' => 'Slug (để trống để tự tạo)', 'type' => 'text', 'rules' => 'max:140'],
                ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea', 'rules' => 'max:2000'],
                ['name' => 'image_path', 'label' => 'Hình ảnh', 'type' => 'image'],
                ['name' => 'is_active', 'label' => 'Hiển thị', 'type' => 'checkbox'],
            ],
        ];
    }
}
