<?php
/** Cấu hình website */

function admin_settings(): void
{
    admin_view('settings', [
        'title' => 'Cấu hình website',
        'settings' => Settings::all(),
    ]);
}

function admin_settings_save(): void
{
    Csrf::check();

    // Logo: upload thắng, sau đó tới URL nhập tay, nếu cả 2 trống thì giữ nguyên
    $logo = handle_upload('site_logo_file', 'uploads') ?? trim((string)post('site_logo_url'));
    if ($logo !== '' && $logo !== null) {
        Settings::set('site_logo', $logo);
    }

    Settings::set('site_name', trim((string)post('site_name')));
    Settings::set('site_desc', trim((string)post('site_desc')));
    Settings::set('site_keywords', trim((string)post('site_keywords')));
    Settings::set('sepay_enabled', post('sepay_enabled') === '1' ? '1' : '0');
    Settings::set('sepay_bank', trim((string)post('sepay_bank')));
    Settings::set('sepay_account', trim((string)post('sepay_account')));
    Settings::set('sepay_account_name', trim((string)post('sepay_account_name')));
    Settings::set('sepay_prefix', strtoupper(trim((string)post('sepay_prefix'))));
    Settings::set('sepay_api_key', trim((string)post('sepay_api_key')));
    Settings::set('central_auth_key', trim((string)post('central_auth_key')));
    Settings::set('google_enabled', post('google_enabled') === '1' ? '1' : '0');
    Settings::set('google_client_id', trim((string)post('google_client_id')));
    if (trim((string)post('google_client_secret')) !== '') { // để trống = giữ secret cũ
        Settings::set('google_client_secret', trim((string)post('google_client_secret')));
    }
    Settings::set('email_verify_required', post('email_verify_required') === '1' ? '1' : '0');
    Settings::set('smtp_host', trim((string)post('smtp_host')));
    Settings::set('smtp_port', trim((string)post('smtp_port')) ?: '587');
    Settings::set('smtp_encryption', in_array(post('smtp_encryption'), ['tls', 'ssl', 'none']) ? post('smtp_encryption') : 'tls');
    Settings::set('smtp_user', trim((string)post('smtp_user')));
    if (trim((string)post('smtp_pass')) !== '') { // để trống = giữ mật khẩu cũ
        Settings::set('smtp_pass', trim((string)post('smtp_pass')));
    }
    Settings::set('smtp_from', trim((string)post('smtp_from')));
    Settings::set('smtp_from_name', trim((string)post('smtp_from_name')));

    // Gửi mail test nếu có yêu cầu
    if (trim((string)post('smtp_test_to')) !== '') {
        [$ok, $msg] = Mailer::send(trim((string)post('smtp_test_to')), 'Test SMTP - ' . Settings::get('site_name'), '<p>Cấu hình SMTP hoạt động!</p>');
        flash_set($ok ? 'success' : 'error', 'Đã lưu cấu hình. Mail test: ' . $msg);
        redirect('/admin/settings');
    }

    flash_set('success', 'Đã lưu cấu hình.');
    redirect('/admin/settings');
}
