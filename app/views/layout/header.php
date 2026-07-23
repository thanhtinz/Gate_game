<?php
$siteName = Settings::get('site_name', 'Gate Game');
$me = Auth::user();
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(($title ?? '') ? $title . ' - ' . $siteName : $siteName) ?></title>
<meta name="description" content="<?= e($metaDesc ?? Settings::get('site_desc')) ?>">
<meta name="keywords" content="<?= e(Settings::get('site_keywords')) ?>">
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= url('/') ?>" class="logo">
      <?php if (Settings::get('site_logo')): ?>
        <img src="<?= e(url(Settings::get('site_logo'))) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?>
        <span class="logo-text">🎮 <?= e($siteName) ?></span>
      <?php endif; ?>
    </a>
    <nav class="main-nav" id="mainNav">
      <a href="<?= url('/') ?>">Trang chủ</a>
      <a href="<?= url('/tin-tuc') ?>">Tin tức</a>
      <a href="<?= url('/nap-xu') ?>">Nạp xu</a>
      <a href="<?= url('/doi-xu') ?>">Đổi xu</a>
      <a href="<?= url('/giftcode') ?>">Giftcode</a>
      <a href="<?= url('/bxh') ?>">Xếp hạng</a>
    </nav>
    <div class="header-user">
      <?php if ($me): ?>
        <span class="user-xu">💰 <?= number_vn($me['xu']) ?> xu</span>
        <a href="<?= url('/tai-khoan') ?>" class="btn btn-sm"><?= e($me['username']) ?></a>
        <?php if ((int)$me['role'] === 1): ?><a href="<?= url('/admin') ?>" class="btn btn-sm btn-warning">Admin</a><?php endif; ?>
        <a href="<?= url('/dang-xuat') ?>" class="btn btn-sm btn-outline">Thoát</a>
      <?php else: ?>
        <a href="<?= url('/dang-nhap') ?>" class="btn btn-sm btn-outline">Đăng nhập</a>
        <a href="<?= url('/dang-ky') ?>" class="btn btn-sm btn-primary">Đăng ký</a>
      <?php endif; ?>
    </div>
    <button class="nav-toggle" onclick="document.getElementById('mainNav').classList.toggle('open')">☰</button>
  </div>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
