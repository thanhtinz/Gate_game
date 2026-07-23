<?php
/** Quản lý game */

function admin_games(): void
{
    $games = DB::all(
        'SELECT g.*, (SELECT COUNT(*) FROM game_servers s WHERE s.game_id = g.id) AS server_count
         FROM games g ORDER BY g.sort_order, g.id'
    );

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM games WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('games', [
        'title' => 'Quản lý game',
        'games' => $games,
        'edit' => $edit,
        'adapters' => AdapterRegistry::available(),
    ]);
}

function admin_games_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        $id = (int)post('id');
        $count = (int)(DB::one('SELECT COUNT(*) c FROM game_servers WHERE game_id = ?', [$id])['c'] ?? 0);
        if ($count > 0) {
            flash_set('error', 'Không thể xoá: game vẫn còn ' . $count . ' server. Hãy xoá server trước.');
        } else {
            DB::query('DELETE FROM games WHERE id = ?', [$id]);
            flash_set('success', 'Đã xoá game.');
        }
        redirect('/admin/games');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $name = trim((string)post('name'));
        if ($name === '') {
            flash_set('error', 'Tên game không được để trống.');
            redirect('/admin/games' . ($id ? '?edit=' . $id : ''));
        }

        $slug = trim((string)post('slug'));
        if ($slug === '') {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        $adapter = (string)post('adapter');
        if (!array_key_exists($adapter, AdapterRegistry::available())) {
            flash_set('error', 'Adapter không hợp lệ.');
            redirect('/admin/games' . ($id ? '?edit=' . $id : ''));
        }

        // Trùng slug?
        $dup = DB::one('SELECT id FROM games WHERE slug = ? AND id <> ?', [$slug, $id]);
        if ($dup) {
            flash_set('error', 'Slug "' . $slug . '" đã được dùng cho game khác.');
            redirect('/admin/games' . ($id ? '?edit=' . $id : ''));
        }

        // Ảnh: upload thắng, nếu không có upload thì dùng URL nhập tay
        $thumbnail = handle_upload('thumbnail_file', 'uploads') ?? trim((string)post('thumbnail_url'));
        $banner = handle_upload('banner_file', 'uploads') ?? trim((string)post('banner_url'));

        // Link tải: mảng label[] + url[] -> JSON
        $labels = (array)post('download_label', []);
        $urls = (array)post('download_url', []);
        $links = [];
        foreach ($labels as $i => $label) {
            $label = trim((string)$label);
            $u = trim((string)($urls[$i] ?? ''));
            if ($label !== '' && $u !== '') {
                $links[] = ['label' => $label, 'url' => $u];
            }
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'adapter' => $adapter,
            'short_desc' => trim((string)post('short_desc')) ?: null,
            'description' => (string)post('description'),
            'thumbnail' => $thumbnail ?: null,
            'banner' => $banner ?: null,
            'download_links' => json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => (int)post('status', 1),
            'sort_order' => (int)post('sort_order', 0),
        ];

        if ($id > 0) {
            DB::update('games', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật game.');
        } else {
            DB::insert('games', $data);
            flash_set('success', 'Đã thêm game mới.');
        }
    }

    redirect('/admin/games');
}
