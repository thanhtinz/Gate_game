<?php

/** API AJAX cho các bước chọn game -> server -> nhân vật */

function api_servers(): void
{
    $gameId = (int)get('game_id');
    $servers = DB::all(
        'SELECT id, name, note FROM game_servers WHERE game_id = ? AND status = 1 ORDER BY sort_order',
        [$gameId]
    );
    json_out(['success' => true, 'servers' => $servers]);
}

function api_characters(): void
{
    $me = Auth::user();
    if (!$me) {
        json_out(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
    }
    $serverId = (int)get('server_id');
    $server = DB::one(
        'SELECT s.*, g.adapter FROM game_servers s JOIN games g ON g.id = s.game_id
         WHERE s.id = ? AND s.status = 1 AND g.status = 1',
        [$serverId]
    );
    if (!$server) {
        json_out(['success' => false, 'message' => 'Server không tồn tại.'], 404);
    }
    try {
        $adapter = AdapterRegistry::forGame($server['adapter']);
        $pdo = GameDB::forServer($server);
        // Tự tạo "vỏ" tài khoản trong DB game nếu chưa có (mật khẩu ngẫu nhiên —
        // không dùng để đăng nhập vì game xác thực qua API auth tập trung)
        if (!$adapter->accountExists($pdo, $me['username'])) {
            $adapter->ensureAccount($pdo, $me['username'], bin2hex(random_bytes(16)));
        }
        $chars = $adapter->getCharacters($pdo, $me['username']);
        json_out(['success' => true, 'characters' => $chars]);
    } catch (Throwable $e) {
        json_out(['success' => false, 'message' => 'Không kết nối được server game.'], 500);
    }
}

/** Thông tin giftcode để hiển thị icon vật phẩm ngay dưới ô nhập */
function api_giftcode_info(): void
{
    $me = Auth::user();
    $gameId = (int)get('game_id');
    $code = strtoupper(trim((string)get('code')));
    if (!$gameId || $code === '') {
        json_out(['success' => false, 'message' => 'Thiếu thông tin.']);
    }
    $gc = DB::one('SELECT * FROM giftcodes WHERE game_id = ? AND UPPER(code) = ? AND status = 1', [$gameId, $code]);
    if (!$gc) {
        json_out(['success' => false, 'message' => 'Giftcode không tồn tại.']);
    }
    if ($gc['expires_at'] && strtotime($gc['expires_at']) < time()) {
        json_out(['success' => false, 'message' => 'Giftcode đã hết hạn.']);
    }
    if ((int)$gc['max_uses'] > 0 && (int)$gc['used_count'] >= (int)$gc['max_uses']) {
        json_out(['success' => false, 'message' => 'Giftcode đã hết lượt sử dụng.']);
    }
    // Nhập rồi -> ẩn với user đó
    if ($me && DB::one('SELECT id FROM giftcode_logs WHERE giftcode_id = ? AND user_id = ?', [$gc['id'], $me['id']])) {
        json_out(['success' => false, 'used' => true, 'message' => 'Bạn đã nhập giftcode này rồi.']);
    }
    json_out([
        'success' => true,
        'description' => $gc['description'],
        'items' => json_decode((string)$gc['items'], true) ?: [],
    ]);
}

function api_order_status(): void
{
    $me = Auth::user();
    if (!$me) {
        json_out(['success' => false], 401);
    }
    $code = trim((string)get('code'));
    $order = DB::one('SELECT status FROM orders WHERE code = ? AND user_id = ?', [$code, $me['id']]);
    if (!$order) {
        json_out(['success' => false], 404);
    }
    json_out(['success' => true, 'status' => $order['status']]);
}
