<?php

/**
 * API xác thực tập trung cho GAME SERVER (server-to-server).
 *
 * Game server gọi khi người chơi đăng nhập:
 *   POST /api/game-auth/verify
 *   Header:  X-Auth-Key: <central_auth_key trong admin>
 *   Body:    username=...&password=...
 *
 * Trả về JSON:
 *   { success: true,  code: "ok", user_id, username }
 *   { success: false, code: "not_found" }      -> game server fallback tài khoản cũ trong DB game
 *   { success: false, code: "wrong_password" } -> báo sai mật khẩu, KHÔNG fallback
 *   { success: false, code: "locked" }         -> tài khoản bị khoá trên web
 *   { success: false, code: "unverified" }     -> chưa xác minh email (khi admin bật bắt buộc)
 */
function game_auth_check_key(): void
{
    $key = Settings::get('central_auth_key');
    $given = $_SERVER['HTTP_X_AUTH_KEY'] ?? '';
    if ($key === '' || !hash_equals($key, $given)) {
        json_out(['success' => false, 'code' => 'unauthorized'], 401);
    }
}

function game_auth_verify(): void
{
    game_auth_check_key();

    $username = strtolower(trim((string)post('username')));
    $password = (string)post('password');
    if ($username === '' || $password === '') {
        json_out(['success' => false, 'code' => 'invalid_request'], 400);
    }

    $u = DB::one('SELECT id, username, password, status, email_verified FROM users WHERE username = ?', [$username]);
    if (!$u) {
        json_out(['success' => false, 'code' => 'not_found']);
    }
    if (!password_verify($password, $u['password'])) {
        json_out(['success' => false, 'code' => 'wrong_password']);
    }
    if ((int)$u['status'] !== 1) {
        json_out(['success' => false, 'code' => 'locked']);
    }
    if (EmailVerify::required() && (int)$u['email_verified'] !== 1) {
        json_out(['success' => false, 'code' => 'unverified']);
    }
    json_out([
        'success' => true,
        'code' => 'ok',
        'user_id' => (int)$u['id'],
        'username' => $u['username'],
    ]);
}

/**
 * Đăng ký tài khoản từ TRONG GAME (game server gọi hộ người chơi):
 *   POST /api/game-auth/register
 *   Header: X-Auth-Key | Body: username, password, email
 * Cổng tạo tài khoản + gửi mail xác minh như đăng ký trên web.
 * Trả về: { success, code: ok|error, message (tiếng Việt để game hiển thị) }
 */
function game_auth_register(): void
{
    game_auth_check_key();

    $username = strtolower(trim((string)post('username')));
    $password = (string)post('password');
    $email = trim((string)post('email'));

    [$ok, $msg, $uid] = Auth::createAccount($username, $password, $email);
    if (!$ok) {
        json_out(['success' => false, 'code' => 'error', 'message' => $msg]);
    }

    $newUser = DB::one('SELECT * FROM users WHERE id = ?', [$uid]);
    [$mailOk, $mailMsg] = EmailVerify::sendMail($newUser);

    $needVerify = EmailVerify::required();
    $message = 'Đăng ký thành công!';
    if ($mailOk) {
        $message .= $needVerify
            ? ' Kiểm tra email ' . $email . ' để xác minh rồi đăng nhập.'
            : ' Đã gửi mail xác minh tới ' . $email . '.';
    } elseif ($needVerify) {
        $message .= ' Chưa gửi được mail xác minh, vào website để gửi lại.';
    }
    json_out(['success' => true, 'code' => 'ok', 'message' => $message, 'user_id' => (int)$uid]);
}

/**
 * Đổi mật khẩu từ TRONG GAME:
 *   POST /api/game-auth/change-password
 *   Header: X-Auth-Key | Body: username, old_password, new_password
 * Trả về: { success, code: ok|not_found|wrong_password|error, message }
 */
function game_auth_change_password(): void
{
    game_auth_check_key();

    $username = strtolower(trim((string)post('username')));
    $old = (string)post('old_password');
    $new = (string)post('new_password');

    $u = DB::one('SELECT * FROM users WHERE username = ?', [$username]);
    if (!$u) {
        json_out(['success' => false, 'code' => 'not_found', 'message' => 'Tài khoản không có trên cổng.']);
    }
    if (!password_verify($old, $u['password'])) {
        json_out(['success' => false, 'code' => 'wrong_password', 'message' => 'Mật khẩu hiện tại không đúng.']);
    }
    [$ok, $msg] = Auth::changePassword((int)$u['id'], $new);
    json_out(['success' => $ok, 'code' => $ok ? 'ok' : 'error', 'message' => $msg]);
}
