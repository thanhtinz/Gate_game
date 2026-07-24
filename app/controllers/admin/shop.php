<?php
/** Quản lý webshop: sản phẩm bán bằng xu, giao vật phẩm vào nhân vật */

/** Tra tên vật phẩm trong DB game (dùng server đầu tiên của game) */
function admin_shop_lookup_item_name(array $game, int $itemId): ?string
{
    $server = DB::one(
        'SELECT * FROM game_servers WHERE game_id = ? AND status = 1 ORDER BY sort_order, id LIMIT 1',
        [(int)$game['id']]
    );
    if (!$server) {
        return null;
    }
    try {
        $adapter = AdapterRegistry::forGame($game['adapter']);
        $gameDb = GameDB::forServer($server);
        return $adapter->getItemName($gameDb, $itemId);
    } catch (Throwable $e) {
        return null;
    }
}

function admin_shop(): void
{
    $games = DB::all('SELECT * FROM games ORDER BY sort_order, id');
    $products = DB::all(
        'SELECT p.*, g.name AS game_name FROM shop_products p
         JOIN games g ON g.id = p.game_id ORDER BY g.sort_order, p.sort_order, p.id'
    );

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM shop_products WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('shop', [
        'title' => 'Webshop',
        'games' => $games,
        'products' => $products,
        'edit' => $edit,
    ]);
}

/** AJAX: tra tên vật phẩm theo game_id + item_id để admin xem trước */
function admin_shop_item_lookup(): void
{
    Auth::requireAdmin();
    $gameId = (int)get('game_id');
    $itemId = (int)get('item_id');
    $game = DB::one('SELECT * FROM games WHERE id = ?', [$gameId]);
    if (!$game || $itemId < 0) {
        json_out(['success' => false, 'message' => 'Thiếu thông tin.']);
    }
    $name = admin_shop_lookup_item_name($game, $itemId);
    if ($name === null) {
        json_out(['success' => false, 'message' => 'Không tìm thấy vật phẩm id ' . $itemId . ' (hoặc chưa cấu hình server game).']);
    }
    json_out(['success' => true, 'name' => $name]);
}

function admin_shop_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM shop_products WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá sản phẩm.');
        redirect('/admin/shop');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $gameId = (int)post('game_id');
        $itemId = (int)post('item_id');
        $itemQty = max(1, (int)post('item_quantity', 1));
        $xuCost = (int)post('xu_cost');
        $name = trim((string)post('name'));

        $game = DB::one('SELECT * FROM games WHERE id = ?', [$gameId]);
        if (!$game) {
            flash_set('error', 'Vui lòng chọn game hợp lệ.');
            redirect('/admin/shop' . ($id ? '?edit=' . $id : ''));
        }
        if ($itemId <= 0 || $xuCost <= 0) {
            flash_set('error', 'Vui lòng nhập ID vật phẩm và giá xu hợp lệ (> 0).');
            redirect('/admin/shop' . ($id ? '?edit=' . $id : ''));
        }

        // Tên trống -> tự lấy theo tên vật phẩm trong game
        if ($name === '') {
            $name = admin_shop_lookup_item_name($game, $itemId) ?? ('Vật phẩm #' . $itemId);
        }

        // Ảnh: upload mới hoặc giữ ảnh cũ
        $image = handle_upload('image_file', 'uploads');
        if ($image === null) {
            $image = trim((string)post('image_url'));
            if ($image === '' && $id) {
                $old = DB::one('SELECT image FROM shop_products WHERE id = ?', [$id]);
                $image = $old['image'] ?? '';
            }
        }

        // Stock: rỗng = vô hạn (-1)
        $stockRaw = trim((string)post('stock'));
        $stock = $stockRaw === '' ? -1 : max(-1, (int)$stockRaw);

        $data = [
            'game_id' => $gameId,
            'name' => $name,
            'item_id' => $itemId,
            'item_quantity' => $itemQty,
            'xu_cost' => $xuCost,
            'image' => $image !== '' ? $image : null,
            'description' => trim((string)post('description')) ?: null,
            'stock' => $stock,
            'status' => (int)post('status', 1),
            'sort_order' => (int)post('sort_order', 0),
        ];

        if ($id > 0) {
            DB::update('shop_products', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật sản phẩm.');
        } else {
            DB::insert('shop_products', $data);
            flash_set('success', 'Đã thêm sản phẩm mới.');
        }
    }

    redirect('/admin/shop');
}
