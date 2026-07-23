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

    flash_set('success', 'Đã lưu cấu hình.');
    redirect('/admin/settings');
}
