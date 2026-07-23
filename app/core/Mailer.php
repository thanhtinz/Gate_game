<?php
/**
 * SMTP client tối giản (AUTH LOGIN, hỗ trợ ssl/tls/none) — không cần thư viện ngoài.
 * Cấu hình trong admin: smtp_host, smtp_port, smtp_encryption, smtp_user, smtp_pass,
 * smtp_from, smtp_from_name. Dùng được với Gmail (App Password), Zoho, SendGrid SMTP...
 */
class Mailer
{
    /** Gửi mail HTML. Trả về [ok, message] */
    public static function send(string $to, string $subject, string $html): array
    {
        $host = Settings::get('smtp_host');
        $port = (int)Settings::get('smtp_port', '587');
        $enc = Settings::get('smtp_encryption', 'tls'); // tls (STARTTLS) | ssl | none
        $user = Settings::get('smtp_user');
        $pass = Settings::get('smtp_pass');
        $from = Settings::get('smtp_from') ?: $user;
        $fromName = Settings::get('smtp_from_name', 'Gate Game');

        if ($host === '' || $from === '') {
            return [false, 'Chưa cấu hình SMTP trong admin.'];
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Email người nhận không hợp lệ.'];
        }

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 10);
        if (!$fp) {
            return [false, "Không kết nối được SMTP: $errstr"];
        }
        stream_set_timeout($fp, 10);

        try {
            self::expect($fp, '220');
            self::cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), '250');

            if ($enc === 'tls') {
                self::cmd($fp, 'STARTTLS', '220');
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Bật TLS thất bại');
                }
                self::cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), '250');
            }

            if ($user !== '') {
                self::cmd($fp, 'AUTH LOGIN', '334');
                self::cmd($fp, base64_encode($user), '334');
                self::cmd($fp, base64_encode($pass), '235');
            }

            self::cmd($fp, 'MAIL FROM:<' . $from . '>', '250');
            self::cmd($fp, 'RCPT TO:<' . $to . '>', '250');
            self::cmd($fp, 'DATA', '354');

            $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $headers = [
                'From: ' . $encodedFromName . ' <' . $from . '>',
                'To: <' . $to . '>',
                'Subject: ' . $encodedSubject,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
                'Date: ' . date('r'),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>',
            ];
            $body = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n.";
            self::cmd($fp, $body, '250');
            self::cmd($fp, 'QUIT', '221');
            return [true, 'Đã gửi mail.'];
        } catch (Throwable $e) {
            return [false, 'Lỗi SMTP: ' . $e->getMessage()];
        } finally {
            @fclose($fp);
        }
    }

    private static function cmd($fp, string $cmd, string $expectCode): void
    {
        fwrite($fp, $cmd . "\r\n");
        self::expect($fp, $expectCode);
    }

    private static function expect($fp, string $code): void
    {
        $line = '';
        do {
            $line = fgets($fp, 1024);
            if ($line === false) {
                throw new RuntimeException('Mất kết nối SMTP');
            }
        } while (isset($line[3]) && $line[3] === '-'); // bỏ qua dòng multi-line 250-...
        if (strncmp($line, $code, strlen($code)) !== 0) {
            throw new RuntimeException(trim($line));
        }
    }
}
