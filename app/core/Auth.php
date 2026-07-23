<?php
/**
 * Auth tập trung: tài khoản CHỈ nằm ở DB cổng (bcrypt).
 * Game server xác thực qua API /api/game-auth/verify — web không còn
 * ghi/đồng bộ mật khẩu xuống DB game. Dòng account trong DB game chỉ là
 * "vỏ chứa dữ liệu nhân vật", được game server hoặc cổng tự tạo khi cần.
 */
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
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['uid']);
        session_regenerate_id(true);
    }

    /**
     * Tạo tài khoản cổng (không đăng nhập session) — dùng chung cho web,
     * API đăng ký từ game và Google.
     * Trả về [ok, message, uid|null]
     */
    public static function createAccount(string $username, string $password, string $email, array $extra = []): array
    {
        $username = strtolower(trim($username));
        $email = trim($email);
        if (!preg_match('/^[a-z0-9_]{4,20}$/i', $username)) {
            return [false, 'Tên tài khoản 4-20 ký tự, chỉ gồm chữ, số, gạch dưới.', null];
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return [false, 'Mật khẩu phải từ 6 đến 32 ký tự.', null];
        }
        if (DB::one('SELECT id FROM users WHERE username = ?', [$username])) {
            return [false, 'Tên tài khoản đã tồn tại.', null];
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Vui lòng nhập email hợp lệ (dùng để xác minh và khôi phục tài khoản).', null];
        }
        if (DB::one('SELECT id FROM users WHERE email = ?', [$email])) {
            return [false, 'Email này đã được dùng cho tài khoản khác.', null];
        }

        // Chặn trùng với tài khoản in-game có sẵn (tài khoản cũ của người khác
        // vẫn đăng nhập game qua chế độ fallback — không cho chiếm username đó)
        foreach (self::activeServers() as $srv) {
            try {
                $adapter = AdapterRegistry::forGame($srv['adapter']);
                $pdo = GameDB::forServer($srv);
                if ($adapter->accountExists($pdo, $username)) {
                    return [false, "Tên tài khoản đã tồn tại trong game {$srv['game_name']} ({$srv['name']}). Vui lòng chọn tên khác.", null];
                }
            } catch (Throwable $e) {
                // server game offline: bỏ qua kiểm tra
            }
        }

        $uid = DB::insert('users', array_merge([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ], $extra));
        return [true, 'Tạo tài khoản thành công.', $uid];
    }

    /**
     * Đăng ký trên web: tạo tài khoản + đăng nhập session + gửi mail xác minh.
     * Trả về [ok, message, warnings[]]
     */
    public static function register(string $username, string $password, string $email = '', string $phone = ''): array
    {
        [$ok, $msg, $uid] = self::createAccount($username, $password, $email, $phone !== '' ? ['phone' => $phone] : []);
        if (!$ok) {
            return [false, $msg, []];
        }

        $_SESSION['uid'] = $uid;
        session_regenerate_id(true);

        // Gửi mail xác minh (thay cơ chế kích hoạt tài khoản cũ của game)
        $warnings = [];
        $newUser = DB::one('SELECT * FROM users WHERE id = ?', [$uid]);
        [$mailOk, $mailMsg] = EmailVerify::sendMail($newUser);
        if (!$mailOk) {
            $warnings[] = 'Chưa gửi được mail xác minh: ' . $mailMsg;
        }
        return [true, 'Đăng ký thành công!' . ($mailOk ? ' Vui lòng kiểm tra email để xác minh tài khoản.' : ''), $warnings];
    }

    /** Đổi mật khẩu web — game xác thực qua API nên không cần đồng bộ gì thêm */
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
        ], 'id = ?', [$userId]);
        return [true, 'Đổi mật khẩu thành công. Mật khẩu mới dùng cho cả web và các game.', []];
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
