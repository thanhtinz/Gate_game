<?php
$me = Auth::requireAdmin();
$flash = flash_get();
$active = fn(string $p) => str_starts_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), rtrim(config('base_path',''),'/') . $p) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Admin') ?> - Quản trị</title>
<link rel="stylesheet" href="<?= url('/assets/css/admin.css') ?>">
</head>
<body class="admin">
<aside class="sidebar">
  <div class="sidebar-logo"><?= icon('settings') ?> Quản trị</div>
  <nav>
    <a class="<?= $active('/admin') && trim($_SERVER['REQUEST_URI'], '/') === trim(url('/admin'), '/') ? 'active' : '' ?>" href="<?= url('/admin') ?>"><?= icon('chart') ?>Tổng quan</a>
    <a class="<?= $active('/admin/games') ?>" href="<?= url('/admin/games') ?>"><?= icon('gamepad') ?>Game</a>
    <a class="<?= $active('/admin/servers') ?>" href="<?= url('/admin/servers') ?>"><?= icon('server') ?>Server game</a>
    <a class="<?= $active('/admin/coin-packages') ?>" href="<?= url('/admin/coin-packages') ?>"><?= icon('wallet') ?>Gói nạp xu</a>
    <a class="<?= $active('/admin/exchange-packages') ?>" href="<?= url('/admin/exchange-packages') ?>"><?= icon('exchange') ?>Gói quy đổi</a>
    <a class="<?= $active('/admin/orders') ?>" href="<?= url('/admin/orders') ?>"><?= icon('receipt') ?>Đơn nạp</a>
    <a class="<?= $active('/admin/exchanges') ?>" href="<?= url('/admin/exchanges') ?>"><?= icon('scroll') ?>Lịch sử quy đổi</a>
    <a class="<?= $active('/admin/users') ?>" href="<?= url('/admin/users') ?>"><?= icon('users') ?>Người dùng</a>
    <a class="<?= $active('/admin/news') ?>" href="<?= url('/admin/news') ?>"><?= icon('news') ?>Tin tức / Sự kiện</a>
    <a class="<?= $active('/admin/banners') ?>" href="<?= url('/admin/banners') ?>"><?= icon('image') ?>Banner</a>
    <a class="<?= $active('/admin/giftcodes') ?>" href="<?= url('/admin/giftcodes') ?>"><?= icon('gift') ?>Giftcode</a>
    <a class="<?= $active('/admin/settings') ?>" href="<?= url('/admin/settings') ?>"><?= icon('settings') ?>Cấu hình</a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= url('/') ?>"><?= icon('arrow-left') ?> Về trang chủ</a>
  </div>
</aside>
<main class="admin-main">
<h1 class="page-title"><?= e($title ?? 'Admin') ?></h1>
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
