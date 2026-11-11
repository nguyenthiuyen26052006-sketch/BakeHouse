<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class BannerController extends AbstractResourceController
{
    protected function config(): array
    {
        return [
            'title' => 'Banner',
            'route' => 'banners',
            'table' => 'banners',
            'search' => ['title', 'link_url'],
            'columns' => [
                ['key' => 'image_path', 'label' => 'Ảnh', 'type' => 'image'],
                ['key' => 'title', 'label' => 'Tiêu đề', 'type' => 'text'],
                ['key' => 'link_url', 'label' => 'Liên kết', 'type' => 'text'],
                ['key' => 'is_active', 'label' => 'Hiển thị', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Tiêu đề', 'type' => 'text', 'rules' => 'required|max:190'],
                ['name' => 'image_path', 'label' => 'Hình ảnh', 'type' => 'image', 'required_on_create' => true],
                ['name' => 'link_url', 'label' => 'Liên kết (vd: /products)', 'type' => 'text', 'rules' => 'max:255', 'format' => 'url'],
                ['name' => 'is_active', 'label' => 'Hiển thị', 'type' => 'checkbox'],
            ],
        ];
    }
}
