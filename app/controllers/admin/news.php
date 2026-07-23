<?php
/** Quản lý tin tức / sự kiện */

function admin_news(): void
{
    $items = DB::all(
        'SELECT n.*, g.name AS game_name FROM news n
         LEFT JOIN games g ON g.id = n.game_id
         ORDER BY n.pinned DESC, n.id DESC'
    );
    $games = DB::all('SELECT id, name FROM games ORDER BY sort_order, id');

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM news WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('news', [
        'title' => 'Tin tức / Sự kiện',
        'items' => $items,
        'games' => $games,
        'edit' => $edit,
    ]);
}

function admin_news_save(): void
{
    Csrf::check();
    $action = post('action');

    if ($action === 'delete') {
        DB::query('DELETE FROM news WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá bài viết.');
        redirect('/admin/news');
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $title = trim((string)post('title'));
        if ($title === '') {
            flash_set('error', 'Tiêu đề không được để trống.');
            redirect('/admin/news' . ($id ? '?edit=' . $id : ''));
        }

        $slug = trim((string)post('slug'));
        $slug = $slug === '' ? slugify($title) : slugify($slug);

        // Trùng slug -> gắn hậu tố
        if (DB::one('SELECT id FROM news WHERE slug = ? AND id <> ?', [$slug, $id])) {
            $slug .= '-' . time();
        }

        $type = post('type') === 'event' ? 'event' : 'news';
        $gameId = (int)post('game_id') ?: null;
        if ($gameId !== null && !DB::one('SELECT id FROM games WHERE id = ?', [$gameId])) {
            $gameId = null;
        }

        $thumbnail = handle_upload('thumbnail_file', 'uploads') ?? trim((string)post('thumbnail_url'));

        $data = [
            'game_id' => $gameId,
            'type' => $type,
            'title' => $title,
            'slug' => $slug,
            'thumbnail' => $thumbnail ?: null,
            'summary' => trim((string)post('summary')) ?: null,
            'content' => (string)post('content'),
            'pinned' => (int)(post('pinned') === '1'),
            'status' => (int)post('status', 1),
        ];

        if ($id > 0) {
            DB::update('news', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật bài viết.');
        } else {
            DB::insert('news', $data);
            flash_set('success', 'Đã đăng bài viết mới.');
        }
    }

    redirect('/admin/news');
}
