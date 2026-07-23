<?php
/**
 * Cấu hình cổng game — copy thành config.php và sửa lại.
 */
return [
    // DB của cổng game (không phải DB game)
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'gate_portal',
        'user' => 'root',
        'pass' => '',
    ],

    // Khoá bí mật của ứng dụng: dùng mã hoá mật khẩu game + CSRF.
    // Sinh chuỗi ngẫu nhiên dài 32+ ký tự, KHÔNG dùng giá trị mặc định.
    'app_key' => 'CHANGE_ME_TO_A_RANDOM_32_CHAR_STRING',

    // Đường dẫn gốc nếu web đặt trong thư mục con, vd '/portal'. Để '' nếu ở gốc domain.
    'base_path' => '',

    // Bật hiển thị lỗi khi dev
    'debug' => false,
];
