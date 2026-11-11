<?php

declare(strict_types=1);

return [
    'app' => [
        'tagline' => 'Nguyên vật liệu & dụng cụ làm bánh',
    ],
    'validation' => [
        'required' => ':attribute không được để trống.',
        'email' => ':attribute không đúng định dạng email.',
        'phone' => ':attribute không đúng định dạng số điện thoại.',
        'min' => ':attribute phải có ít nhất :arg ký tự.',
        'max' => ':attribute không được vượt quá :arg ký tự.',
        'numeric' => ':attribute phải là số.',
        'integer' => ':attribute phải là số nguyên.',
        'in' => ':attribute không hợp lệ.',
        'confirmed' => 'Xác nhận :attribute không khớp.',
        'password' => ':attribute phải từ 8 ký tự, gồm chữ hoa, chữ thường và số.',
        'identity' => 'Vui lòng nhập email hoặc số điện thoại hợp lệ.',
        'attributes' => [
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'identity' => 'Email hoặc số điện thoại',
            'password' => 'Mật khẩu',
            'password_confirmation' => 'Xác nhận mật khẩu',
            'full_name' => 'Họ tên',
            'name' => 'Tên',
            'title' => 'Tiêu đề',
            'slug' => 'Slug',
            'message' => 'Nội dung',
            'recipient_name' => 'Tên người nhận',
            'shipping_phone' => 'Số điện thoại nhận hàng',
            'shipping_address' => 'Địa chỉ giao hàng',
            'payment_method' => 'Phương thức thanh toán',
            'price' => 'Giá',
            'stock_quantity' => 'Tồn kho',
            'category_id' => 'Danh mục',
            'unit' => 'Đơn vị',
            'link_url' => 'Liên kết',
            'rating' => 'Số sao',
        ],
    ],
];
