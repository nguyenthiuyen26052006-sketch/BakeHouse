<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class TagController extends AbstractResourceController
{
    protected function config(): array
    {
        return [
            'title' => 'Tag',
            'route' => 'tags',
            'table' => 'tags',
            'search' => ['name', 'slug'],
            'slug_from' => 'name',
            'columns' => [
                ['key' => 'name', 'label' => 'Tên', 'type' => 'text'],
                ['key' => 'slug', 'label' => 'Slug', 'type' => 'text'],
                ['key' => 'created_at', 'label' => 'Ngày tạo', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Tên tag', 'type' => 'text', 'rules' => 'required|max:80'],
                ['name' => 'slug', 'label' => 'Slug (để trống để tự tạo)', 'type' => 'text', 'rules' => 'max:100'],
            ],
        ];
    }
}
