<?php

function exchange_index(): void
{
    $me = Auth::user();
    $games = DB::all('SELECT id, slug, name, adapter FROM games WHERE status = 1 ORDER BY sort_order');
    $packagesByGame = [];
    foreach (DB::all('SELECT * FROM exchange_packages WHERE status = 1 ORDER BY game_id, sort_order') as $p) {
        $packagesByGame[$p['game_id']][] = $p;
    }
    $currencyLabels = [];
    foreach ($games as $g) {
        try {
            $currencyLabels[$g['id']] = AdapterRegistry::forGame($g['adapter'])->currencies();
        } catch (Throwable $e) {
            $currencyLabels[$g['id']] = [];
        }
    }
    view('exchange', [
        'title' => 'Đổi xu lấy tiền tệ game',
        'me' => $me,
        'games' => $games,
        'packagesByGame' => $packagesByGame,
        'currencyLabels' => $currencyLabels,
    ]);
}

function exchange_submit(): void
{
    Csrf::check();
    $me = Auth::requireLogin();

    $gameId = (int)post('game_id');
    $serverId = (int)post('server_id');
    $packageId = (int)post('package_id');
    $characterId = trim((string)post('character_id'));

    $game = DB::one('SELECT * FROM games WHERE id = ? AND status = 1', [$gameId]);
    $server = DB::one('SELECT * FROM game_servers WHERE id = ? AND game_id = ? AND status = 1', [$serverId, $gameId]);
    $pkg = DB::one('SELECT * FROM exchange_packages WHERE id = ? AND game_id = ? AND status = 1', [$packageId, $gameId]);

    if (!$game || !$server || !$pkg || $characterId === '') {
        flash_set('error', 'Vui lòng chọn đầy đủ game, server, nhân vật và gói quy đổi.');
        redirect('/doi-xu');
    }

    try {
        $adapter = AdapterRegistry::forGame($game['adapter']);
        $gameDb = GameDB::forServer($server);
    } catch (Throwable $e) {
        flash_set('error', 'Không kết nối được server game, vui lòng thử lại sau.');
        redirect('/doi-xu');
    }

    // Nhân vật phải thuộc tài khoản của user
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
        redirect('/doi-xu');
    }

    $totalAmount = (int)$pkg['currency_amount'] + (int)$pkg['bonus_amount'];

    // Trừ xu web (khoá dòng chống trừ 2 lần)
    $pdo = DB::pdo();
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT xu FROM users WHERE id = ? FOR UPDATE');
        $st->execute([$me['id']]);
        $xu = (int)$st->fetchColumn();
        if ($xu < (int)$pkg['xu_cost']) {
            $pdo->rollBack();
            flash_set('error', 'Số dư xu không đủ. Cần ' . number_vn($pkg['xu_cost']) . ' xu, bạn có ' . number_vn($xu) . ' xu.');
            redirect('/doi-xu');
        }
        $pdo->prepare('UPDATE users SET xu = xu - ? WHERE id = ?')->execute([(int)$pkg['xu_cost'], $me['id']]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash_set('error', 'Lỗi hệ thống, vui lòng thử lại.');
        redirect('/doi-xu');
    }

    // Cộng tiền tệ trong game
    [$ok, $msg] = $adapter->creditCurrency($gameDb, $characterId, $pkg['currency_key'], $totalAmount, (int)$pkg['add_tongnap']);

    if (!$ok) {
        // Hoàn xu nếu cộng game thất bại
        DB::query('UPDATE users SET xu = xu + ? WHERE id = ?', [(int)$pkg['xu_cost'], $me['id']]);
    }

    DB::insert('exchanges', [
        'user_id' => $me['id'],
        'game_id' => $gameId,
        'server_id' => $serverId,
        'package_id' => $packageId,
        'character_id' => $characterId,
        'character_name' => $char['name'],
        'xu_cost' => $pkg['xu_cost'],
        'currency_key' => $pkg['currency_key'],
        'currency_amount' => $totalAmount,
        'status' => $ok ? 'completed' : 'failed',
        'message' => mb_substr($msg, 0, 255),
    ]);

    flash_set($ok ? 'success' : 'error', $ok ? "Quy đổi thành công cho nhân vật {$char['name']}: $msg" : "Quy đổi thất bại (đã hoàn xu): $msg");
    redirect('/doi-xu');
}
