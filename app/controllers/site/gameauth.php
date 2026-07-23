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
function game_auth_verify(): void
{
    $key = Settings::get('central_auth_key');
    $given = $_SERVER['HTTP_X_AUTH_KEY'] ?? '';
    if ($key === '' || !hash_equals($key, $given)) {
        json_out(['success' => false, 'code' => 'unauthorized'], 401);
    }

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
