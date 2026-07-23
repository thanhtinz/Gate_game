<?php
/** Đơn nạp xu & lịch sử quy đổi (chỉ xem) */

function admin_orders(): void
{
    $status = (string)get('status');
    $page = max(1, (int)get('page', 1));
    $perPage = 50;

    $where = '';
    $params = [];
    if (in_array($status, ['pending', 'completed', 'cancelled'], true)) {
        $where = 'WHERE o.status = ?';
        $params[] = $status;
    }

    $total = (int)(DB::one("SELECT COUNT(*) c FROM orders o $where", $params)['c'] ?? 0);
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;

    $orders = DB::all(
        "SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id
         $where ORDER BY o.id DESC LIMIT $perPage OFFSET $offset",
        $params
    );

    admin_view('orders', [
        'title' => 'Đơn nạp xu',
        'orders' => $orders,
        'status' => $status,
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
    ]);
}

function admin_exchanges(): void
{
    $page = max(1, (int)get('page', 1));
    $perPage = 50;

    $total = (int)(DB::one('SELECT COUNT(*) c FROM exchanges')['c'] ?? 0);
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;

    $exchanges = DB::all(
        "SELECT ex.*, u.username, g.name AS game_name, s.name AS server_name
         FROM exchanges ex
         JOIN users u ON u.id = ex.user_id
         LEFT JOIN games g ON g.id = ex.game_id
         LEFT JOIN game_servers s ON s.id = ex.server_id
         ORDER BY ex.id DESC LIMIT $perPage OFFSET $offset"
    );

    admin_view('exchanges', [
        'title' => 'Lịch sử quy đổi',
        'exchanges' => $exchanges,
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
    ]);
}
