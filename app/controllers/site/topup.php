<?php

function topup_index(): void
{
    $me = Auth::user();
    $packages = DB::all('SELECT * FROM coin_packages WHERE status = 1 ORDER BY sort_order');
    $pending = $me
        ? DB::all("SELECT * FROM orders WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 5", [$me['id']])
        : [];
    view('topup', [
        'title' => 'Nạp xu',
        'packages' => $packages,
        'pending' => $pending,
        'sepayEnabled' => Settings::get('sepay_enabled') === '1' && Settings::get('sepay_account') !== '',
    ]);
}

function topup_create(): void
{
    Csrf::check();
    $me = Auth::requireLogin();
    if (Settings::get('sepay_enabled') !== '1') {
        flash_set('error', 'Phương thức nạp đang tạm đóng.');
        redirect('/nap-xu');
    }
    $pkg = DB::one('SELECT * FROM coin_packages WHERE id = ? AND status = 1', [(int)post('package_id')]);
    if (!$pkg) {
        flash_set('error', 'Gói nạp không hợp lệ.');
        redirect('/nap-xu');
    }
    // Huỷ các đơn pending quá 24h của user
    DB::query(
        "UPDATE orders SET status = 'cancelled' WHERE user_id = ? AND status = 'pending' AND created_at < NOW() - INTERVAL 1 DAY",
        [$me['id']]
    );
    $orderId = DB::insert('orders', [
        'user_id' => $me['id'],
        'package_id' => $pkg['id'],
        'code' => 'TMP',
        'amount_vnd' => $pkg['price_vnd'],
        'xu' => $pkg['xu'],
        'bonus_xu' => $pkg['bonus_xu'],
        'method' => 'sepay',
    ]);
    $code = strtoupper(Settings::get('sepay_prefix', 'GATE')) . $orderId;
    DB::update('orders', ['code' => $code], 'id = ?', [$orderId]);
    redirect('/nap-xu/don/' . $code);
}

function topup_order(string $code): void
{
    $me = Auth::requireLogin();
    $order = DB::one('SELECT * FROM orders WHERE code = ? AND user_id = ?', [$code, $me['id']]);
    if (!$order) {
        http_response_code(404);
        view('errors/404', ['title' => 'Không tìm thấy đơn nạp']);
    }
    $qrUrl = sprintf(
        'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=%d&des=%s',
        urlencode(Settings::get('sepay_account')),
        urlencode(Settings::get('sepay_bank')),
        (int)$order['amount_vnd'],
        urlencode($order['code'])
    );
    view('topup_order', [
        'title' => 'Thanh toán đơn ' . $order['code'],
        'order' => $order,
        'qrUrl' => $qrUrl,
        'bank' => Settings::get('sepay_bank'),
        'account' => Settings::get('sepay_account'),
        'accountName' => Settings::get('sepay_account_name'),
    ]);
}
