<?php

/**
 * Phục vụ icon vật phẩm đọc trực tiếp từ thư mục icon của game server
 * (NRO: data/icon/x1/<icon_id>.png). Đường dẫn khai báo trong admin
 * (setting game_icon_path theo từng game). Có cache + fallback.
 */
function game_icon_serve(string $gameId, string $iconId): void
{
    $gameId = (int)$gameId;
    $iconId = (int)$iconId; // chỉ nhận số -> chống path traversal
    $dir = CurrencyIcon::iconPath($gameId);

    $file = $dir !== '' ? $dir . '/' . $iconId . '.png' : '';
    if ($file === '' || !is_file($file)) {
        // fallback ảnh mặc định
        $file = BASE_DIR . '/public/assets/currency/default.png';
        if (!is_file($file)) {
            http_response_code(404);
            exit;
        }
    }

    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
