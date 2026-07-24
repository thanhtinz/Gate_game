<?php
/** Webshop: mua vật phẩm bằng xu, giao thẳng vào nhân vật trong game */

function shop_index(): void
{
    $me = Auth::user();
    $games = DB::all('SELECT id, slug, name, adapter FROM games WHERE status = 1 ORDER BY sort_order');
    $gameById = [];
    foreach ($games as $g) {
        $gameById[(int)$g['id']] = $g;
    }
    // Chỉ hiện sản phẩm còn hàng (stock = -1 vô hạn, hoặc > 0)
    $products = DB::all(
        'SELECT p.*, g.name AS game_name, g.slug AS game_slug
         FROM shop_products p JOIN games g ON g.id = p.game_id
         WHERE p.status = 1 AND g.status = 1 AND (p.stock < 0 OR p.stock > 0)
         ORDER BY g.sort_order, p.sort_order, p.id'
    );
    view('shop', [
        'title' => 'Webshop — mua vật phẩm bằng xu',
        'me' => $me,
        'games' => $games,
        'gameById' => $gameById,
        'products' => $products,
    ]);
}

function shop_submit(): void
{
    Csrf::check();
    $me = Auth::requireLogin();
    EmailVerify::guard($me);

    $productId = (int)post('product_id');
    $serverId = (int)post('server_id');
    $characterId = trim((string)post('character_id'));

    $product = DB::one('SELECT * FROM shop_products WHERE id = ? AND status = 1', [$productId]);
    if (!$product) {
        flash_set('error', 'Sản phẩm không tồn tại hoặc đã ngừng bán.');
        redirect('/shop');
    }
    $gameId = (int)$product['game_id'];
    $game = DB::one('SELECT * FROM games WHERE id = ? AND status = 1', [$gameId]);
    $server = DB::one('SELECT * FROM game_servers WHERE id = ? AND game_id = ? AND status = 1', [$serverId, $gameId]);

    if (!$game || !$server || $characterId === '') {
        flash_set('error', 'Vui lòng chọn đầy đủ server và nhân vật.');
        redirect('/shop');
    }

    try {
        $adapter = AdapterRegistry::forGame($game['adapter']);
        $gameDb = GameDB::forServer($server);
    } catch (Throwable $e) {
        flash_set('error', 'Không kết nối được server game, vui lòng thử lại sau.');
        redirect('/shop');
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
        redirect('/shop');
    }

    $xuCost = (int)$product['xu_cost'];

    // Trừ xu web + trừ tồn kho trong 1 transaction (khoá dòng chống mua trùng)
    $pdo = DB::pdo();
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT xu FROM users WHERE id = ? FOR UPDATE');
        $st->execute([$me['id']]);
        $xu = (int)$st->fetchColumn();
        if ($xu < $xuCost) {
            $pdo->rollBack();
            flash_set('error', 'Số dư xu không đủ. Cần ' . number_vn($xuCost) . ' xu, bạn có ' . number_vn($xu) . ' xu.');
            redirect('/shop');
        }
        // Tồn kho có giới hạn: khoá dòng sản phẩm, kiểm tra & trừ
        $ps = $pdo->prepare('SELECT stock FROM shop_products WHERE id = ? FOR UPDATE');
        $ps->execute([$productId]);
        $stock = (int)$ps->fetchColumn();
        if ($stock === 0) {
            $pdo->rollBack();
            flash_set('error', 'Sản phẩm đã hết hàng.');
            redirect('/shop');
        }
        if ($stock > 0) {
            $pdo->prepare('UPDATE shop_products SET stock = stock - 1 WHERE id = ?')->execute([$productId]);
        }
        $pdo->prepare('UPDATE users SET xu = xu - ? WHERE id = ?')->execute([$xuCost, $me['id']]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash_set('error', 'Lỗi hệ thống, vui lòng thử lại.');
        redirect('/shop');
    }

    // Giao vật phẩm vào nhân vật trong game
    [$ok, $msg] = $adapter->giveItem($gameDb, $characterId, (int)$product['item_id'], (int)$product['item_quantity']);

    if (!$ok) {
        // Hoàn xu + hoàn tồn kho nếu giao thất bại
        DB::query('UPDATE users SET xu = xu + ? WHERE id = ?', [$xuCost, $me['id']]);
        if ((int)$product['stock'] > 0) {
            DB::query('UPDATE shop_products SET stock = stock + 1 WHERE id = ?', [$productId]);
        }
    }

    DB::insert('shop_orders', [
        'user_id' => $me['id'],
        'game_id' => $gameId,
        'server_id' => $serverId,
        'product_id' => $productId,
        'product_name' => $product['name'],
        'item_id' => (int)$product['item_id'],
        'item_quantity' => (int)$product['item_quantity'],
        'character_id' => $characterId,
        'character_name' => $char['name'],
        'xu_cost' => $xuCost,
        'status' => $ok ? 'completed' : 'failed',
        'message' => mb_substr($msg, 0, 255),
    ]);

    flash_set(
        $ok ? 'success' : 'error',
        $ok
            ? "Mua thành công! Đã gửi {$product['name']} x" . number_vn($product['item_quantity']) . " cho nhân vật {$char['name']}: $msg"
            : "Mua thất bại (đã hoàn xu): $msg"
    );
    redirect('/shop');
}
