<?php
/** Chống CSRF cho form POST */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function check(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$token)) {
            http_response_code(419);
            exit('Phiên làm việc hết hạn, vui lòng tải lại trang.');
        }
    }
}
