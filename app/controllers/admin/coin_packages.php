<?php
/** Quản lý gói nạp xu */

function admin_coin_packages(): void
{
    $packages = DB::all('SELECT * FROM coin_packages ORDER BY sort_order, id');

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM coin_packages WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('coin_packages', [
        'title' => 'Gói nạp xu',
        'packages' => $packages,
        'edit' => $edit,
    ]);
}

function admin_coin_packages_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM coin_packages WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá gói nạp.');
        redirect('/admin/coin-packages');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $name = trim((string)post('name'));
        $price = (int)post('price_vnd');
        $xu = (int)post('xu');

        if ($name === '' || $price <= 0 || $xu <= 0) {
            flash_set('error', 'Vui lòng nhập tên gói, giá VND và số xu hợp lệ (> 0).');
            redirect('/admin/coin-packages' . ($id ? '?edit=' . $id : ''));
        }

        $data = [
            'name' => $name,
            'price_vnd' => $price,
            'xu' => $xu,
            'bonus_xu' => max(0, (int)post('bonus_xu', 0)),
            'status' => (int)post('status', 1),
            'sort_order' => (int)post('sort_order', 0),
        ];

        if ($id > 0) {
            DB::update('coin_packages', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật gói nạp.');
        } else {
            DB::insert('coin_packages', $data);
            flash_set('success', 'Đã thêm gói nạp mới.');
        }
    }

    redirect('/admin/coin-packages');
}
