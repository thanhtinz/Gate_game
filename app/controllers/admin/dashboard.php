<?php
/** Tổng quan quản trị */

function admin_dashboard(): void
{
    $stats = [
        'users'          => (int)(DB::one('SELECT COUNT(*) c FROM users')['c'] ?? 0),
        'xu_total'       => (int)(DB::one('SELECT COALESCE(SUM(xu),0) s FROM users')['s'] ?? 0),
        'revenue_today'  => (int)(DB::one("SELECT COALESCE(SUM(amount_vnd),0) s FROM orders WHERE status='completed' AND DATE(completed_at) = CURDATE()")['s'] ?? 0),
        'revenue_total'  => (int)(DB::one("SELECT COALESCE(SUM(amount_vnd),0) s FROM orders WHERE status='completed'")['s'] ?? 0),
        'orders_pending' => (int)(DB::one("SELECT COUNT(*) c FROM orders WHERE status='pending'")['c'] ?? 0),
        'games'          => (int)(DB::one('SELECT COUNT(*) c FROM games')['c'] ?? 0),
        'servers'        => (int)(DB::one('SELECT COUNT(*) c FROM game_servers')['c'] ?? 0),
        'news'           => (int)(DB::one('SELECT COUNT(*) c FROM news')['c'] ?? 0),
        'giftcodes'      => (int)(DB::one('SELECT COUNT(*) c FROM giftcodes')['c'] ?? 0),
    ];

    $latest_orders = DB::all(
        "SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.status = 'completed' ORDER BY o.completed_at DESC LIMIT 10"
    );

    $latest_exchanges = DB::all(
        'SELECT ex.*, u.username, g.name AS game_name, s.name AS server_name
         FROM exchanges ex
         JOIN users u ON u.id = ex.user_id
         LEFT JOIN games g ON g.id = ex.game_id
         LEFT JOIN game_servers s ON s.id = ex.server_id
         ORDER BY ex.created_at DESC LIMIT 10'
    );

    admin_view('dashboard', [
        'title' => 'Tổng quan',
        'stats' => $stats,
        'latest_orders' => $latest_orders,
        'latest_exchanges' => $latest_exchanges,
    ]);
}
