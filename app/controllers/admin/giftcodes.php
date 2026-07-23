<?php
/** Quản lý giftcode */

/** Map {game_id: {currency_key: label}} theo adapter của từng game */
function admin_giftcode_currency_map(array $games): array
{
    $map = [];
    foreach ($games as $g) {
        try {
            $map[(int)$g['id']] = AdapterRegistry::forGame($g['adapter'])->currencies();
        } catch (Throwable $e) {
            $map[(int)$g['id']] = [];
        }
    }
    return $map;
}

/** Upload 1 ảnh trong mảng file input name="xxx[]" tại vị trí $index, trả về path hoặc null */
function admin_giftcode_upload_row(string $field, int $index): ?string
{
    if (empty($_FILES[$field]['tmp_name'][$index]) || ($_FILES[$field]['error'][$index] ?? 1) !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo((string)$_FILES[$field]['name'][$index], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return null;
    }
    if (@getimagesize($_FILES[$field]['tmp_name'][$index]) === false) {
        return null;
    }
    $dir = BASE_DIR . '/public/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES[$field]['tmp_name'][$index], $dir . '/' . $name)) {
        return '/uploads/' . $name;
    }
    return null;
}

function admin_giftcodes(): void
{
    $games = DB::all('SELECT * FROM games ORDER BY sort_order, id');
    $giftcodes = DB::all(
        'SELECT gc.*, g.name AS game_name FROM giftcodes gc
         JOIN games g ON g.id = gc.game_id ORDER BY gc.id DESC'
    );

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM giftcodes WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('giftcodes', [
        'title' => 'Giftcode',
        'games' => $games,
        'giftcodes' => $giftcodes,
        'edit' => $edit,
        'currency_map' => admin_giftcode_currency_map($games),
    ]);
}

function admin_giftcodes_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM giftcodes WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá giftcode.');
        redirect('/admin/giftcodes');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $gameId = (int)post('game_id');
        $code = strtoupper(trim((string)post('code')));

        $game = DB::one('SELECT * FROM games WHERE id = ?', [$gameId]);
        if (!$game) {
            flash_set('error', 'Vui lòng chọn game hợp lệ.');
            redirect('/admin/giftcodes' . ($id ? '?edit=' . $id : ''));
        }
        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
            flash_set('error', 'Mã giftcode 3-50 ký tự, chỉ gồm chữ, số, gạch ngang/dưới.');
            redirect('/admin/giftcodes' . ($id ? '?edit=' . $id : ''));
        }
        if (DB::one('SELECT id FROM giftcodes WHERE game_id = ? AND code = ? AND id <> ?', [$gameId, $code, $id])) {
            flash_set('error', "Mã \"$code\" đã tồn tại cho game này.");
            redirect('/admin/giftcodes' . ($id ? '?edit=' . $id : ''));
        }

        // Vật phẩm hiển thị: icon (upload thắng URL) + name + qty
        $itemNames = (array)post('item_name', []);
        $itemIcons = (array)post('item_icon', []);
        $itemQtys = (array)post('item_qty', []);
        $items = [];
        foreach ($itemNames as $i => $n) {
            $n = trim((string)$n);
            if ($n === '') {
                continue;
            }
            $icon = admin_giftcode_upload_row('item_icon_file', (int)$i) ?? trim((string)($itemIcons[$i] ?? ''));
            $items[] = [
                'icon' => $icon,
                'name' => $n,
                'qty' => max(1, (int)($itemQtys[$i] ?? 1)),
            ];
        }

        // Phần thưởng thực nhận: currency_key + amount (validate theo adapter)
        try {
            $currencies = AdapterRegistry::forGame($game['adapter'])->currencies();
        } catch (Throwable $e) {
            $currencies = [];
        }
        $rewardKeys = (array)post('reward_key', []);
        $rewardAmounts = (array)post('reward_amount', []);
        $rewards = [];
        foreach ($rewardKeys as $i => $k) {
            $k = (string)$k;
            $amount = (int)($rewardAmounts[$i] ?? 0);
            if ($k === '' || $amount <= 0) {
                continue;
            }
            if (!array_key_exists($k, $currencies)) {
                flash_set('error', "Loại tiền tệ \"$k\" không hợp lệ với game đã chọn.");
                redirect('/admin/giftcodes' . ($id ? '?edit=' . $id : ''));
            }
            $rewards[] = ['currency_key' => $k, 'amount' => $amount];
        }

        $expiresAt = trim((string)post('expires_at'));
        $expiresAt = $expiresAt !== '' ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null;

        $data = [
            'game_id' => $gameId,
            'code' => $code,
            'description' => trim((string)post('description')) ?: null,
            'items' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'rewards' => json_encode($rewards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'max_uses' => max(0, (int)post('max_uses', 0)),
            'status' => (int)post('status', 1),
            'expires_at' => $expiresAt,
        ];

        if ($id > 0) {
            DB::update('giftcodes', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật giftcode.');
        } else {
            DB::insert('giftcodes', $data);
            flash_set('success', 'Đã tạo giftcode mới.');
        }
    }

    redirect('/admin/giftcodes');
}
