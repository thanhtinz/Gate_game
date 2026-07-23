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
  <div class="sidebar-logo">⚙️ Quản trị</div>
  <nav>
    <a class="<?= $active('/admin') && trim($_SERVER['REQUEST_URI'], '/') === trim(url('/admin'), '/') ? 'active' : '' ?>" href="<?= url('/admin') ?>">📊 Tổng quan</a>
    <a class="<?= $active('/admin/games') ?>" href="<?= url('/admin/games') ?>">🎮 Game</a>
    <a class="<?= $active('/admin/servers') ?>" href="<?= url('/admin/servers') ?>">🖥️ Server game</a>
    <a class="<?= $active('/admin/coin-packages') ?>" href="<?= url('/admin/coin-packages') ?>">💰 Gói nạp xu</a>
    <a class="<?= $active('/admin/exchange-packages') ?>" href="<?= url('/admin/exchange-packages') ?>">🔄 Gói quy đổi</a>
    <a class="<?= $active('/admin/orders') ?>" href="<?= url('/admin/orders') ?>">🧾 Đơn nạp</a>
    <a class="<?= $active('/admin/exchanges') ?>" href="<?= url('/admin/exchanges') ?>">📜 Lịch sử quy đổi</a>
    <a class="<?= $active('/admin/users') ?>" href="<?= url('/admin/users') ?>">👥 Người dùng</a>
    <a class="<?= $active('/admin/news') ?>" href="<?= url('/admin/news') ?>">📰 Tin tức / Sự kiện</a>
    <a class="<?= $active('/admin/banners') ?>" href="<?= url('/admin/banners') ?>">🖼️ Banner</a>
    <a class="<?= $active('/admin/giftcodes') ?>" href="<?= url('/admin/giftcodes') ?>">🎁 Giftcode</a>
    <a class="<?= $active('/admin/settings') ?>" href="<?= url('/admin/settings') ?>">⚙️ Cấu hình</a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= url('/') ?>">← Về trang chủ</a>
  </div>
</aside>
<main class="admin-main">
<h1 class="page-title"><?= e($title ?? 'Admin') ?></h1>
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
