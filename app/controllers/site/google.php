<?php

/**
 * Đăng nhập / đăng ký bằng Google (OAuth 2.0).
 *
 * Bật trong Admin → Cấu hình: google_enabled + Client ID + Client Secret.
 * Redirect URI khai báo trên Google Cloud Console: https://domain/auth/google/callback
 *
 * Vì game đăng nhập bằng username + mật khẩu (qua auth tập trung), tài khoản
 * Google mới sẽ đi qua bước "hoàn tất đăng ký" để chọn username + mật khẩu game.
 * Email từ Google được coi là đã xác minh.
 */

function google_enabled(): bool
{
    return Settings::get('google_enabled') === '1'
        && Settings::get('google_client_id') !== ''
        && Settings::get('google_client_secret') !== '';
}

function google_redirect_uri(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('/auth/google/callback');
}

/** Bước 1: chuyển hướng sang Google */
function google_start(): void
{
    if (!google_enabled()) {
        flash_set('error', 'Đăng nhập Google chưa được bật.');
        redirect('/dang-nhap');
    }
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_state'] = $state;
    $params = http_build_query([
        'client_id' => Settings::get('google_client_id'),
        'redirect_uri' => google_redirect_uri(),
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'prompt' => 'select_account',
    ]);
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    exit;
}

/** Gọi HTTP ra Google (tôn trọng biến môi trường HTTPS_PROXY nếu có) */
function google_http(string $url, ?array $postFields = null, array $headers = []): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($postFields !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    }
    $proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy');
    if ($proxy) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        $ca = getenv('CURL_CA_BUNDLE');
        if ($ca) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }
    }
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res === false) {
        return null;
    }
    $data = json_decode($res, true);
    return is_array($data) ? $data : null;
}

/** Bước 2: Google gọi về */
function google_callback(): void
{
    if (!google_enabled()) {
        redirect('/dang-nhap');
    }
    $state = (string)get('state');
    if (empty($_SESSION['google_state']) || !hash_equals($_SESSION['google_state'], $state)) {
        flash_set('error', 'Phiên đăng nhập Google không hợp lệ, thử lại.');
        redirect('/dang-nhap');
    }
    unset($_SESSION['google_state']);

    $code = (string)get('code');
    if ($code === '') {
        flash_set('error', 'Bạn đã huỷ đăng nhập Google.');
        redirect('/dang-nhap');
    }

    $token = google_http('https://oauth2.googleapis.com/token', [
        'code' => $code,
        'client_id' => Settings::get('google_client_id'),
        'client_secret' => Settings::get('google_client_secret'),
        'redirect_uri' => google_redirect_uri(),
        'grant_type' => 'authorization_code',
    ]);
    if (!$token || empty($token['access_token'])) {
        flash_set('error', 'Không lấy được thông tin từ Google, thử lại sau.');
        redirect('/dang-nhap');
    }

    $info = google_http('https://www.googleapis.com/oauth2/v3/userinfo', null, [
        'Authorization: Bearer ' . $token['access_token'],
    ]);
    if (!$info || empty($info['sub']) || empty($info['email'])) {
        flash_set('error', 'Không đọc được tài khoản Google, thử lại sau.');
        redirect('/dang-nhap');
    }

    $sub = (string)$info['sub'];
    $email = strtolower((string)$info['email']);

    // 1) Đã liên kết Google -> đăng nhập luôn
    $u = DB::one('SELECT * FROM users WHERE google_id = ?', [$sub]);
    if ($u) {
        google_login_user($u);
    }

    // 2) Email trùng tài khoản có sẵn -> liên kết + đăng nhập
    $u = DB::one('SELECT * FROM users WHERE email = ?', [$email]);
    if ($u) {
        DB::update('users', ['google_id' => $sub, 'email_verified' => 1], 'id = ?', [$u['id']]);
        google_login_user($u);
    }

    // 3) Tài khoản mới -> hoàn tất đăng ký (chọn username + mật khẩu để vào game)
    $_SESSION['google_pending'] = [
        'sub' => $sub,
        'email' => $email,
        'name' => (string)($info['name'] ?? ''),
    ];
    redirect('/hoan-tat-dang-ky');
}

function google_login_user(array $u): void
{
    if ((int)$u['status'] !== 1) {
        flash_set('error', 'Tài khoản đã bị khoá.');
        redirect('/dang-nhap');
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    DB::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$u['id']]);
    flash_set('success', 'Đăng nhập Google thành công!');
    redirect('/');
}

/** Bước 3 (tài khoản mới): chọn username + mật khẩu game */
function google_complete_form(): void
{
    if (empty($_SESSION['google_pending'])) {
        redirect('/dang-nhap');
    }
    view('auth/google_complete', [
        'title' => 'Hoàn tất đăng ký',
        'pending' => $_SESSION['google_pending'],
    ]);
}

function google_complete_submit(): void
{
    Csrf::check();
    if (empty($_SESSION['google_pending'])) {
        redirect('/dang-nhap');
    }
    $pending = $_SESSION['google_pending'];
    $username = strtolower(trim((string)post('username')));
    $password = (string)post('password');
    $password2 = (string)post('password2');

    if ($password !== $password2) {
        flash_set('error', 'Mật khẩu nhập lại không khớp.');
        redirect('/hoan-tat-dang-ky');
    }

    // Email Google đã được Google xác minh -> kích hoạt luôn, không cần gửi mail
    [$ok, $msg, $uid] = Auth::createAccount($username, $password, $pending['email'], [
        'google_id' => $pending['sub'],
        'email_verified' => 1,
    ]);
    if (!$ok) {
        flash_set('error', $msg);
        redirect('/hoan-tat-dang-ky');
    }
    unset($_SESSION['google_pending']);
    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
    flash_set('success', 'Đăng ký bằng Google thành công! Dùng username "' . $username . '" và mật khẩu vừa đặt để đăng nhập trong game.');
    redirect('/');
}
