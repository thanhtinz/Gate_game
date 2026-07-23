<?php
/** Quản lý banner trang chủ */

function admin_banners(): void
{
    $banners = DB::all('SELECT * FROM banners ORDER BY sort_order, id');

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM banners WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('banners', [
        'title' => 'Banner trang chủ',
        'banners' => $banners,
        'edit' => $edit,
    ]);
}

function admin_banners_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM banners WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá banner.');
        redirect('/admin/banners');
    }

    if ($action === 'save') {
        $id = (int)post('id');

        $image = handle_upload('image_file', 'uploads') ?? trim((string)post('image_url'));
        if ($image === '' || $image === null) {
            // Khi sửa: giữ ảnh cũ nếu không upload / không nhập URL
            $old = $id > 0 ? DB::one('SELECT image FROM banners WHERE id = ?', [$id]) : null;
            $image = $old['image'] ?? '';
        }
        if ($image === '') {
            flash_set('error', 'Banner bắt buộc phải có ảnh (upload hoặc nhập URL).');
            redirect('/admin/banners' . ($id ? '?edit=' . $id : ''));
        }

        $data = [
            'title' => trim((string)post('title')) ?: null,
            'image' => $image,
            'link' => trim((string)post('link')) ?: null,
            'sort_order' => (int)post('sort_order', 0),
            'status' => (int)post('status', 1),
        ];

        if ($id > 0) {
            DB::update('banners', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật banner.');
        } else {
            DB::insert('banners', $data);
            flash_set('success', 'Đã thêm banner mới.');
        }
    }

    redirect('/admin/banners');
}
