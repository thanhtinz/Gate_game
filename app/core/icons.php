<?php
/**
 * Icon SVG inline (line style, theo currentColor) — thay cho emoji.
 * Dùng: icon('wallet'), icon('wallet', 'ic-lg'), icon('star', '', ['aria-hidden'=>'true'])
 */
function icon(string $name, string $class = '', int $size = 20): string
{
    static $paths = null;
    if ($paths === null) {
        $paths = [
            // Tiền tệ / nạp
            'wallet' => '<path d="M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1"/><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 1-2-2Z"/><circle cx="16" cy="13" r="1.4"/>',
            'exchange' => '<path d="M4 8h13l-3-3"/><path d="M20 16H7l3 3"/>',
            'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M12 8v13"/><path d="M12 8S10.5 4 8.5 4A2.5 2.5 0 0 0 8.5 9H12Z"/><path d="M12 8s1.5-4 3.5-4a2.5 2.5 0 0 1 0 5H12Z"/>',
            'trophy' => '<path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 0 1-10 0Z"/><path d="M7 6H4v2a3 3 0 0 0 3 3"/><path d="M17 6h3v2a3 3 0 0 1-3 3"/>',
            'coin' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v8"/><path d="M9.5 10.5a2.5 2 0 0 1 5 0c0 1.5-2.5 1.5-2.5 3"/>',
            // Nav / hệ thống
            'home' => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10"/>',
            'news' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
            'event' => '<path d="M12 3l2.5 5 5.5.8-4 3.9 1 5.5-5-2.7-5 2.7 1-5.5-4-3.9 5.5-.8Z"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M16 5a3.5 3.5 0 0 1 0 7"/><path d="M17 21a7 7 0 0 0-3-5.7"/>',
            'lock' => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'key' => '<circle cx="8" cy="15" r="4"/><path d="M11 13l9-9"/><path d="M17 7l2 2"/><path d="M14 10l2 2"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'eye-off' => '<path d="M9.9 5.2A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.3 4.1M6.3 6.3A17 17 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 4-.8"/><path d="M9.5 9.5a3 3 0 0 0 4.2 4.2"/><path d="m3 3 18 18"/>',
            'gamepad' => '<rect x="2" y="7" width="20" height="11" rx="4"/><path d="M7 11v3M5.5 12.5h3"/><circle cx="16" cy="11.5" r="1"/><circle cx="18.5" cy="14" r="1"/>',
            'server' => '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>',
            'receipt' => '<path d="M5 3v18l2-1 2 1 2-1 2 1 2-1 2 1V3l-2 1-2-1-2 1-2-1-2 1Z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
            'scroll' => '<path d="M6 4h11a2 2 0 0 1 2 2v11a3 3 0 0 0 3 3H8a2 2 0 0 1-2-2V4Z"/><path d="M6 4a2 2 0 0 0-2 2v2h4"/><path d="M9 9h7M9 13h7"/>',
            'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m4 18 5-5 4 4 3-3 4 4"/>',
            'chart' => '<path d="M4 20V4"/><path d="M4 20h16"/><rect x="7" y="11" width="3" height="6"/><rect x="12" y="7" width="3" height="10"/><rect x="17" y="13" width="3" height="4"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1"/>',
            'shield-lock' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6Z"/><rect x="9.5" y="11" width="5" height="4" rx="1"/><path d="M10.5 11v-1a1.5 1.5 0 0 1 3 0v1"/>',
            'google' => '__GOOGLE__',
            // Trạng thái / mũi tên
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
            'warning' => '<path d="M12 4 2.5 20h19L12 4Z"/><path d="M12 10v4M12 17.5h.01"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'pin' => '<path d="M12 21s6-5.7 6-10a6 6 0 1 0-12 0c0 4.3 6 10 6 10Z"/><circle cx="12" cy="11" r="2.2"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
            'arrow-left' => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
            'download' => '<path d="M12 3v12"/><path d="m7 11 5 5 5-5"/><path d="M4 20h16"/>',
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/>',
            'moon' => '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/>',
            'dot' => '<circle cx="12" cy="12" r="5" fill="currentColor" stroke="none"/>',
            // Store (nút tải)
            'android' => '<path d="M6 10a1 1 0 0 1 1 1v5a1 1 0 0 1-2 0v-5a1 1 0 0 1 1-1Z" fill="currentColor" stroke="none"/><path d="M18 10a1 1 0 0 1 1 1v5a1 1 0 0 1-2 0v-5a1 1 0 0 1 1-1Z" fill="currentColor" stroke="none"/><path d="M7.5 10.5v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-7Z" fill="currentColor" stroke="none"/><path d="M8 10a4 4 0 0 1 8 0Z" fill="currentColor" stroke="none"/><path d="m8.5 6.5-1-1.6M15.5 6.5l1-1.6" stroke-width="1.4"/><circle cx="10" cy="8" r=".6" fill="#0d1220" stroke="none"/><circle cx="14" cy="8" r=".6" fill="#0d1220" stroke="none"/>',
            'windows' => '<path d="M3 5.5 11 4.4v7.1H3Z" fill="currentColor" stroke="none"/><path d="M11 4.3 21 3v8.5H11Z" fill="currentColor" stroke="none"/><path d="M3 12.5h8v7.1L3 18.5Z" fill="currentColor" stroke="none"/><path d="M11 12.5h10V21l-10-1.3Z" fill="currentColor" stroke="none"/>',
            'apple' => '<path d="M16 13c0-2.5 2-3.5 2-3.5-1-1.5-2.6-1.6-3.2-1.6-1.4-.1-2.7.8-3.4.8-.7 0-1.8-.8-3-.8-1.5 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.2 9.3.8 1.1 1.7 2.3 2.9 2.3 1.2 0 1.6-.7 3-.7 1.4 0 1.8.7 3 .7 1.2 0 2-1.1 2.8-2.2a9 9 0 0 0 1.3-2.6S16 15.5 16 13Z"/><path d="M13.5 5.5a3 3 0 0 0 .8-2.5 3.2 3.2 0 0 0-2.1 1.1 3 3 0 0 0-.8 2.4 2.7 2.7 0 0 0 2.1-1Z"/>',
        ];
    }
    $body = $paths[$name] ?? '';
    if ($body === '__GOOGLE__') {
        // Google giữ màu gốc (multicolor) — không theo currentColor
        return '<svg class="ic ' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 40.4 44 35 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>';
    }
    return '<svg class="ic ' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

/** Đoán icon store phù hợp từ nhãn nút tải (Android/iOS/Windows/PC/Java...) */
function download_icon(string $label): string
{
    $l = mb_strtolower($label);
    if (str_contains($l, 'android') || str_contains($l, 'apk')) {
        return icon('android');
    }
    if (str_contains($l, 'ios') || str_contains($l, 'iphone') || str_contains($l, 'apple') || str_contains($l, 'app store')) {
        return icon('apple');
    }
    if (str_contains($l, 'window') || str_contains($l, 'pc') || str_contains($l, 'máy tính') || str_contains($l, '.exe')) {
        return icon('windows');
    }
    return icon('download');
}
