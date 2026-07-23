<?php
/** Đăng nhập/đăng ký cổng game + đồng bộ tài khoản xuống DB các game */
class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['uid'])) {
            return null;
        }
        static $user = null;
        if ($user === null) {
            $user = DB::one('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]);
            if ($user && (int)$user['status'] !== 1) {
                self::logout();
                $user = null;
            }
        }
        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): array
    {
        $u = self::user();
        if (!$u) {
            flash_set('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/dang-nhap');
        }
        return $u;
    }

    public static function requireAdmin(): array
    {
        $u = self::user();
        if (!$u || (int)$u['role'] !== 1) {
            http_response_code(403);
            exit('Không có quyền truy cập.');
        }
        return $u;
    }

    public static function attempt(string $username, string $password): bool
    {
        $u = DB::one('SELECT * FROM users WHERE username = ?', [$username]);
        if (!$u || !password_verify($password, $u['password'])) {
            return false;
        }
        if ((int)$u['status'] !== 1) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        DB::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$u['id']]);
        // Cập nhật secret nếu chưa có (tài khoản tạo trước khi có tính năng sync)
        if (empty($u['game_secret'])) {
            DB::update('users', ['game_secret' => Crypto::encrypt($password)], 'id = ?', [$u['id']]);
        }
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['uid']);
        session_regenerate_id(true);
    }

    /**
     * Đăng ký: tạo user cổng + tạo tài khoản game trên tất cả server đang hoạt động.
     * Trả về [ok, message, warnings[]]
     */
    public static function register(string $username, string $password, string $email = '', string $phone = ''): array
    {
        if (!preg_match('/^[a-z0-9_]{4,20}$/i', $username)) {
            return [false, 'Tên tài khoản 4-20 ký tự, chỉ gồm chữ, số, gạch dưới.', []];
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return [false, 'Mật khẩu phải từ 6 đến 32 ký tự.', []];
        }
        if (DB::one('SELECT id FROM users WHERE username = ?', [$username])) {
            return [false, 'Tên tài khoản đã tồn tại.', []];
        }

        // Kiểm tra trùng username trong DB các game (tài khoản in-game có sẵn của người khác)
        foreach (self::activeServers() as $srv) {
            try {
                $adapter = AdapterRegistry::forGame($srv['adapter']);
                $pdo = GameDB::forServer($srv);
                if ($adapter->accountExists($pdo, $username)) {
                    return [false, "Tên tài khoản đã tồn tại trong game {$srv['game_name']} ({$srv['name']}). Vui lòng chọn tên khác.", []];
                }
            } catch (Throwable $e) {
                // server game offline: bỏ qua, sẽ tự tạo khi truy cập sau
            }
        }

        $uid = DB::insert('users', [
            'username' => $username,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'game_secret' => Crypto::encrypt($password),
        ]);

        $warnings = self::provisionGameAccounts($uid);
        $_SESSION['uid'] = $uid;
        session_regenerate_id(true);
        return [true, 'Đăng ký thành công!', $warnings];
    }

    /** Tạo tài khoản game trên mọi server đang hoạt động (bỏ qua server lỗi) */
    public static function provisionGameAccounts(int $userId): array
    {
        $u = DB::one('SELECT * FROM users WHERE id = ?', [$userId]);
        if (!$u || empty($u['game_secret'])) {
            return ['Không có mật khẩu game để đồng bộ.'];
        }
        $plain = Crypto::decrypt($u['game_secret']);
        if ($plain === null) {
            return ['Không giải mã được mật khẩu game (app_key thay đổi?).'];
        }
        $warnings = [];
        foreach (self::activeServers() as $srv) {
            try {
                $adapter = AdapterRegistry::forGame($srv['adapter']);
                $pdo = GameDB::forServer($srv);
                [$ok, $msg] = $adapter->ensureAccount($pdo, $u['username'], $plain);
                if (!$ok) {
                    $warnings[] = "{$srv['game_name']} - {$srv['name']}: $msg";
                }
            } catch (Throwable $e) {
                $warnings[] = "{$srv['game_name']} - {$srv['name']}: không kết nối được server.";
            }
        }
        return $warnings;
    }

    /** Đổi mật khẩu web + đồng bộ xuống DB tất cả game */
    public static function changePassword(int $userId, string $newPassword): array
    {
        $u = DB::one('SELECT * FROM users WHERE id = ?', [$userId]);
        if (!$u) {
            return [false, 'Tài khoản không tồn tại.', []];
        }
        if (strlen($newPassword) < 6 || strlen($newPassword) > 32) {
            return [false, 'Mật khẩu phải từ 6 đến 32 ký tự.', []];
        }
        DB::update('users', [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'game_secret' => Crypto::encrypt($newPassword),
        ], 'id = ?', [$userId]);

        $warnings = [];
        foreach (self::activeServers() as $srv) {
            try {
                $adapter = AdapterRegistry::forGame($srv['adapter']);
                $pdo = GameDB::forServer($srv);
                if ($adapter->accountExists($pdo, $u['username'])) {
                    $adapter->syncPassword($pdo, $u['username'], $newPassword);
                } else {
                    $adapter->ensureAccount($pdo, $u['username'], $newPassword);
                }
            } catch (Throwable $e) {
                $warnings[] = "{$srv['game_name']} - {$srv['name']}: chưa đồng bộ được (server lỗi).";
            }
        }
        return [true, 'Đổi mật khẩu thành công.', $warnings];
    }

    /** Danh sách server đang hoạt động của các game đang hoạt động */
    public static function activeServers(): array
    {
        return DB::all(
            'SELECT s.*, g.adapter, g.name AS game_name, g.slug AS game_slug
             FROM game_servers s JOIN games g ON g.id = s.game_id
             WHERE s.status = 1 AND g.status = 1
             ORDER BY g.sort_order, s.sort_order'
        );
    }
}
