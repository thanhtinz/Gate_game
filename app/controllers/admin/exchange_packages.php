<?php
/** Quản lý gói quy đổi xu -> tiền tệ in-game */

/** Map {game_id: {currency_key: label}} theo adapter của từng game */
function admin_exchange_currency_map(array $games): array
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

function admin_exchange_packages(): void
{
    $games = DB::all('SELECT * FROM games ORDER BY sort_order, id');
    $packages = DB::all(
        'SELECT p.*, g.name AS game_name FROM exchange_packages p
         JOIN games g ON g.id = p.game_id ORDER BY g.sort_order, p.sort_order, p.id'
    );

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM exchange_packages WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('exchange_packages', [
        'title' => 'Gói quy đổi',
        'games' => $games,
        'packages' => $packages,
        'edit' => $edit,
        'currency_map' => admin_exchange_currency_map($games),
    ]);
}

function admin_exchange_packages_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM exchange_packages WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá gói quy đổi.');
        redirect('/admin/exchange-packages');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $gameId = (int)post('game_id');
        $name = trim((string)post('name'));
        $xuCost = (int)post('xu_cost');
        $currencyKey = (string)post('currency_key');
        $currencyAmount = (int)post('currency_amount');

        $game = DB::one('SELECT * FROM games WHERE id = ?', [$gameId]);
        if (!$game) {
            flash_set('error', 'Vui lòng chọn game hợp lệ.');
            redirect('/admin/exchange-packages' . ($id ? '?edit=' . $id : ''));
        }

        // Kiểm tra currency_key thuộc adapter của game
        try {
            $currencies = AdapterRegistry::forGame($game['adapter'])->currencies();
        } catch (Throwable $e) {
            $currencies = [];
        }
        if (!array_key_exists($currencyKey, $currencies)) {
            flash_set('error', 'Loại tiền tệ không hợp lệ với game đã chọn.');
            redirect('/admin/exchange-packages' . ($id ? '?edit=' . $id : ''));
        }

        if ($name === '' || $xuCost <= 0 || $currencyAmount <= 0) {
            flash_set('error', 'Vui lòng nhập tên gói, số xu và lượng tiền tệ hợp lệ (> 0).');
            redirect('/admin/exchange-packages' . ($id ? '?edit=' . $id : ''));
        }

        $data = [
            'game_id' => $gameId,
            'name' => $name,
            'xu_cost' => $xuCost,
            'currency_key' => $currencyKey,
            'currency_amount' => $currencyAmount,
            'bonus_amount' => max(0, (int)post('bonus_amount', 0)),
            'add_tongnap' => max(0, (int)post('add_tongnap', 0)),
            'status' => (int)post('status', 1),
            'sort_order' => (int)post('sort_order', 0),
        ];

        if ($id > 0) {
            DB::update('exchange_packages', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật gói quy đổi.');
        } else {
            DB::insert('exchange_packages', $data);
            flash_set('success', 'Đã thêm gói quy đổi mới.');
        }
    }

    redirect('/admin/exchange-packages');
}
