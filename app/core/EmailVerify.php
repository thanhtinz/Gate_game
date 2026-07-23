<?php
/** Xác minh email tài khoản (thay cơ chế "kích hoạt tài khoản" cũ của từng game) */
class EmailVerify
{
    /** Có bắt buộc xác minh email không (bật/tắt trong admin) */
    public static function required(): bool
    {
        return Settings::get('email_verify_required') === '1';
    }

    public static function isVerified(array $user): bool
    {
        return (int)($user['email_verified'] ?? 0) === 1;
    }

    /** Chặn hành động nhạy cảm (nạp/đổi/giftcode) khi bắt buộc xác minh mà chưa xác minh */
    public static function guard(array $user): void
    {
        if (self::required() && !self::isVerified($user)) {
            flash_set('error', 'Bạn cần xác minh email trước khi sử dụng chức năng này. Kiểm tra hộp thư hoặc gửi lại mail xác minh trong trang Tài khoản.');
            redirect('/tai-khoan');
        }
    }

    /**
     * Gửi (hoặc gửi lại) mail xác minh. Rate-limit 60 giây.
     * Trả về [ok, message]
     */
    public static function sendMail(array $user): array
    {
        if (empty($user['email'])) {
            return [false, 'Tài khoản chưa có email. Vui lòng liên hệ Admin.'];
        }
        if (self::isVerified($user)) {
            return [false, 'Email đã được xác minh rồi.'];
        }
        if (!empty($user['verify_sent_at']) && time() - strtotime($user['verify_sent_at']) < 60) {
            return [false, 'Vui lòng đợi 1 phút rồi gửi lại.'];
        }

        $token = bin2hex(random_bytes(32));
        DB::update('users', [
            'verify_token' => hash('sha256', $token),
            'verify_expires' => date('Y-m-d H:i:s', time() + 86400),
            'verify_sent_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$user['id']]);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $link = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('/xac-minh-email?token=' . $token);
        $siteName = Settings::get('site_name', 'Gate Game');

        $html = '
        <div style="max-width:520px;margin:0 auto;font-family:Arial,sans-serif;background:#131a2c;color:#e8edf7;border-radius:12px;padding:28px">
          <h2 style="margin-top:0">🎮 ' . e($siteName) . '</h2>
          <p>Xin chào <b>' . e($user['username']) . '</b>,</p>
          <p>Bấm nút bên dưới để xác minh email và kích hoạt tài khoản (dùng chung cho website và tất cả game trên cổng):</p>
          <p style="text-align:center;margin:26px 0">
            <a href="' . e($link) . '" style="background:#4f8cff;color:#fff;padding:12px 26px;border-radius:8px;text-decoration:none;font-weight:bold">Xác minh email</a>
          </p>
          <p style="font-size:13px;color:#8b96b0">Hoặc mở link: <a href="' . e($link) . '" style="color:#4f8cff">' . e($link) . '</a><br>
          Link có hiệu lực 24 giờ. Nếu bạn không đăng ký tài khoản này, hãy bỏ qua email.</p>
        </div>';

        [$ok, $msg] = Mailer::send($user['email'], "[$siteName] Xác minh email tài khoản " . $user['username'], $html);
        return [$ok, $ok ? 'Đã gửi mail xác minh tới ' . $user['email'] . '. Kiểm tra cả mục Spam.' : $msg];
    }

    /** Xử lý link trong mail. Trả về [ok, message] */
    public static function confirm(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return [false, 'Link xác minh không hợp lệ.'];
        }
        $u = DB::one('SELECT * FROM users WHERE verify_token = ?', [hash('sha256', $token)]);
        if (!$u) {
            return [false, 'Link xác minh không hợp lệ hoặc đã dùng.'];
        }
        if (self::isVerified($u)) {
            return [true, 'Email đã được xác minh trước đó.'];
        }
        if (empty($u['verify_expires']) || strtotime($u['verify_expires']) < time()) {
            return [false, 'Link xác minh đã hết hạn. Vui lòng gửi lại mail xác minh.'];
        }
        DB::update('users', [
            'email_verified' => 1,
            'verify_token' => null,
            'verify_expires' => null,
        ], 'id = ?', [$u['id']]);
        return [true, 'Xác minh email thành công! Tài khoản đã kích hoạt cho web và tất cả game.'];
    }
}
