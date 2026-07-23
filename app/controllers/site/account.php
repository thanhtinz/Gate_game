<?php

function account_index(): void
{
    $me = Auth::requireLogin();
    $orders = DB::all(
        'SELECT o.*, p.name AS package_name FROM orders o LEFT JOIN coin_packages p ON p.id = o.package_id
         WHERE o.user_id = ? ORDER BY o.id DESC LIMIT 20',
        [$me['id']]
    );
    $exchanges = DB::all(
        'SELECT x.*, g.name AS game_name, s.name AS server_name FROM exchanges x
         JOIN games g ON g.id = x.game_id LEFT JOIN game_servers s ON s.id = x.server_id
         WHERE x.user_id = ? ORDER BY x.id DESC LIMIT 20',
        [$me['id']]
    );
    $giftlogs = DB::all(
        'SELECT l.*, gc.code, g.name AS game_name FROM giftcode_logs l
         JOIN giftcodes gc ON gc.id = l.giftcode_id JOIN games g ON g.id = l.game_id
         WHERE l.user_id = ? ORDER BY l.id DESC LIMIT 20',
        [$me['id']]
    );
    view('account', [
        'title' => 'Tài khoản',
        'me' => $me,
        'orders' => $orders,
        'exchanges' => $exchanges,
        'giftlogs' => $giftlogs,
    ]);
}

function account_change_password(): void
{
    Csrf::check();
    $me = Auth::requireLogin();
    $old = (string)post('old_password');
    $new = (string)post('new_password');
    $new2 = (string)post('new_password2');

    if (!password_verify($old, $me['password'])) {
        flash_set('error', 'Mật khẩu hiện tại không đúng.');
        redirect('/tai-khoan');
    }
    if ($new !== $new2) {
        flash_set('error', 'Mật khẩu mới nhập lại không khớp.');
        redirect('/tai-khoan');
    }
    [$ok, $msg, $warnings] = Auth::changePassword((int)$me['id'], $new);
    flash_set($ok ? 'success' : 'error', $msg . ($warnings ? ' (' . implode(' | ', $warnings) . ')' : ''));
    redirect('/tai-khoan');
}
