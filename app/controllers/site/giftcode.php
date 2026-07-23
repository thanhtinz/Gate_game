<?php

function giftcode_index(): void
{
    $me = Auth::user();
    $games = DB::all('SELECT id, slug, name FROM games WHERE status = 1 ORDER BY sort_order');
    view('giftcode', [
        'title' => 'Nhập Giftcode',
        'me' => $me,
        'games' => $games,
    ]);
}

function giftcode_submit(): void
{
    Csrf::check();
    $me = Auth::requireLogin();
    EmailVerify::guard($me);

    $gameId = (int)post('game_id');
    $serverId = (int)post('server_id');
    $characterId = trim((string)post('character_id'));
    $code = strtoupper(trim((string)post('code')));

    $game = DB::one('SELECT * FROM games WHERE id = ? AND status = 1', [$gameId]);
    $server = DB::one('SELECT * FROM game_servers WHERE id = ? AND game_id = ? AND status = 1', [$serverId, $gameId]);
    if (!$game || !$server || $characterId === '' || $code === '') {
        flash_set('error', 'Vui lòng chọn game, server, nhân vật và nhập mã giftcode.');
        redirect('/giftcode');
    }

    $gc = DB::one('SELECT * FROM giftcodes WHERE game_id = ? AND UPPER(code) = ? AND status = 1', [$gameId, $code]);
    if (!$gc) {
        flash_set('error', 'Giftcode không tồn tại hoặc đã bị khoá.');
        redirect('/giftcode');
    }
    if ($gc['expires_at'] && strtotime($gc['expires_at']) < time()) {
        flash_set('error', 'Giftcode đã hết hạn.');
        redirect('/giftcode');
    }
    if ((int)$gc['max_uses'] > 0 && (int)$gc['used_count'] >= (int)$gc['max_uses']) {
        flash_set('error', 'Giftcode đã hết lượt sử dụng.');
        redirect('/giftcode');
    }
    if (DB::one('SELECT id FROM giftcode_logs WHERE giftcode_id = ? AND user_id = ?', [$gc['id'], $me['id']])) {
        flash_set('error', 'Bạn đã nhập giftcode này rồi.');
        redirect('/giftcode');
    }

    try {
        $adapter = AdapterRegistry::forGame($game['adapter']);
        $gameDb = GameDB::forServer($server);
    } catch (Throwable $e) {
        flash_set('error', 'Không kết nối được server game, vui lòng thử lại sau.');
        redirect('/giftcode');
    }

    $chars = $adapter->getCharacters($gameDb, $me['username']);
    $char = null;
    foreach ($chars as $c) {
        if ((string)$c['id'] === $characterId) {
            $char = $c;
            break;
        }
    }
    if (!$char) {
        flash_set('error', 'Nhân vật không thuộc tài khoản của bạn trên server này.');
        redirect('/giftcode');
    }

    // Ghi lượt dùng trước (UNIQUE giftcode_id+user_id chống nhập song song)
    try {
        DB::insert('giftcode_logs', [
            'giftcode_id' => $gc['id'],
            'user_id' => $me['id'],
            'game_id' => $gameId,
            'server_id' => $serverId,
            'character_id' => $characterId,
            'character_name' => $char['name'],
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Bạn đã nhập giftcode này rồi.');
        redirect('/giftcode');
    }
    DB::query('UPDATE giftcodes SET used_count = used_count + 1 WHERE id = ?', [$gc['id']]);

    // Trao thưởng tiền tệ (nếu giftcode có cấu hình rewards)
    $rewards = json_decode((string)$gc['rewards'], true) ?: [];
    $messages = [];
    $failed = false;
    foreach ($rewards as $r) {
        $key = (string)($r['currency_key'] ?? '');
        $amount = (int)($r['amount'] ?? 0);
        if ($key === '' || $amount <= 0) {
            continue;
        }
        [$ok, $msg] = $adapter->creditCurrency($gameDb, $characterId, $key, $amount);
        $messages[] = $msg;
        if (!$ok) {
            $failed = true;
        }
    }

    if ($failed) {
        // Gỡ lượt dùng để user nhập lại (vd: đang online)
        DB::query('DELETE FROM giftcode_logs WHERE giftcode_id = ? AND user_id = ?', [$gc['id'], $me['id']]);
        DB::query('UPDATE giftcodes SET used_count = used_count - 1 WHERE id = ? AND used_count > 0', [$gc['id']]);
        flash_set('error', 'Chưa nhận được quà: ' . implode(' | ', $messages));
        redirect('/giftcode');
    }

    flash_set('success', 'Nhập giftcode thành công cho nhân vật ' . $char['name'] . '!' . ($messages ? ' ' . implode(' | ', $messages) : ''));
    redirect('/giftcode');
}
