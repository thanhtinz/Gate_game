<?php
/**
 * Mã hoá/giải mã mật khẩu game bằng app_key (AES-256-CBC).
 * Cần lưu được mật khẩu gốc vì DB game (NRO plaintext, Avatar MD5)
 * yêu cầu tự tạo tài khoản game với đúng mật khẩu người dùng.
 */
class Crypto
{
    private static function key(): string
    {
        return hash('sha256', (string)config('app_key'), true);
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipher);
    }

    public static function decrypt(string $enc): ?string
    {
        $raw = base64_decode($enc, true);
        if ($raw === false || strlen($raw) < 17) {
            return null;
        }
        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv);
        return $plain === false ? null : $plain;
    }
}
