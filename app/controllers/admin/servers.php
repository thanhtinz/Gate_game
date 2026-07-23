<?php
/** Quản lý server game (mỗi server = 1 DB MySQL riêng) */

function admin_servers(): void
{
    $gameId = (int)get('game_id', 0);
    $games = DB::all('SELECT * FROM games ORDER BY sort_order, id');

    $params = [];
    $where = '';
    if ($gameId > 0) {
        $where = 'WHERE s.game_id = ?';
        $params[] = $gameId;
    }
    $servers = DB::all(
        "SELECT s.*, g.name AS game_name, g.adapter
         FROM game_servers s JOIN games g ON g.id = s.game_id
         $where ORDER BY g.sort_order, s.sort_order, s.id",
        $params
    );

    $edit = null;
    if (get('edit') !== '') {
        $edit = DB::one('SELECT * FROM game_servers WHERE id = ?', [(int)get('edit')]);
    }

    admin_view('servers', [
        'title' => 'Server game',
        'games' => $games,
        'servers' => $servers,
        'edit' => $edit,
        'game_id' => $gameId,
    ]);
}

function admin_servers_save(): void
{
    Csrf::check();
    $action = post('action');
    $back = '/admin/servers' . ((int)post('game_id_filter') > 0 ? '?game_id=' . (int)post('game_id_filter') : '');

    if ($action === 'delete') {
        DB::query('DELETE FROM game_servers WHERE id = ?', [(int)post('id')]);
        flash_set('success', 'Đã xoá server.');
        redirect($back);
    }

    if ($action === 'test') {
        $srv = DB::one(
            'SELECT s.*, g.adapter, g.name AS game_name FROM game_servers s JOIN games g ON g.id = s.game_id WHERE s.id = ?',
            [(int)post('id')]
        );
        if (!$srv) {
            flash_set('error', 'Server không tồn tại.');
            redirect($back);
        }
        [$ok, $msg] = GameDB::test($srv);
        if ($ok) {
            try {
                $adapter = AdapterRegistry::forGame($srv['adapter']);
                [$ok2, $msg2] = $adapter->testConnection(GameDB::forServer($srv));
                if ($ok2) {
                    flash_set('success', "[{$srv['game_name']} - {$srv['name']}] Kết nối OK. " . $msg2);
                } else {
                    flash_set('error', "[{$srv['game_name']} - {$srv['name']}] Kết nối DB được nhưng sai schema: " . $msg2);
                }
            } catch (Throwable $e) {
                flash_set('error', "[{$srv['game_name']} - {$srv['name']}] Lỗi adapter: " . $e->getMessage());
            }
        } else {
            flash_set('error', "[{$srv['game_name']} - {$srv['name']}] " . $msg);
        }
        redirect($back);
    }

    if ($action === 'save') {
        $id = (int)post('id');
        $gameId = (int)post('game_id');
        $name = trim((string)post('name'));
        $dbName = trim((string)post('db_name'));

        if ($gameId <= 0 || !DB::one('SELECT id FROM games WHERE id = ?', [$gameId])) {
            flash_set('error', 'Vui lòng chọn game hợp lệ.');
            redirect($back);
        }
        if ($name === '' || $dbName === '') {
            flash_set('error', 'Tên server và tên database không được để trống.');
            redirect($back);
        }

        $data = [
            'game_id' => $gameId,
            'name' => $name,
            'db_host' => trim((string)post('db_host')) ?: '127.0.0.1',
            'db_port' => (int)post('db_port', 3306) ?: 3306,
            'db_name' => $dbName,
            'db_user' => trim((string)post('db_user')) ?: 'root',
            'db_pass' => (string)post('db_pass'),
            'status' => (int)post('status', 1),
            'sort_order' => (int)post('sort_order', 0),
            'note' => trim((string)post('note')) ?: null,
        ];

        if ($id > 0) {
            // Giữ mật khẩu cũ nếu để trống ô mật khẩu khi sửa
            if ($data['db_pass'] === '' && post('keep_pass') === '1') {
                unset($data['db_pass']);
            }
            DB::update('game_servers', $data, 'id = ?', [$id]);
            flash_set('success', 'Đã cập nhật server.');
        } else {
            DB::insert('game_servers', $data);
            flash_set('success', 'Đã thêm server mới.');
        }
    }

    redirect($back);
}
