<?php

function auth_login_form(): void
{
    if (Auth::check()) {
        redirect('/');
    }
    view('auth/login', ['title' => 'Đăng nhập']);
}

function auth_login(): void
{
    Csrf::check();
    $username = trim((string)post('username'));
    $password = (string)post('password');
    if (Auth::attempt($username, $password)) {
        flash_set('success', 'Đăng nhập thành công!');
        redirect('/');
    }
    flash_set('error', 'Sai tên tài khoản hoặc mật khẩu, hoặc tài khoản bị khoá.');
    redirect('/dang-nhap');
}

function auth_register_form(): void
{
    if (Auth::check()) {
        redirect('/');
    }
    view('auth/register', ['title' => 'Đăng ký']);
}

function auth_register(): void
{
    Csrf::check();
    $username = strtolower(trim((string)post('username')));
    $password = (string)post('password');
    $password2 = (string)post('password2');
    $email = trim((string)post('email'));

    if ($password !== $password2) {
        flash_set('error', 'Mật khẩu nhập lại không khớp.');
        redirect('/dang-ky');
    }

    [$ok, $msg, $warnings] = Auth::register($username, $password, $email);
    if (!$ok) {
        flash_set('error', $msg);
        redirect('/dang-ky');
    }
    $extra = $warnings ? ' Lưu ý: ' . implode(' | ', $warnings) : ' Tài khoản dùng chung cho tất cả game trên cổng.';
    flash_set('success', $msg . $extra);
    redirect('/');
}

function auth_logout(): void
{
    Auth::logout();
    redirect('/');
}
