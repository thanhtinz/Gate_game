<div class="auth-wrap">
  <div class="auth-panel">
    <div class="auth-side">
      <div class="auth-side-inner">
        <span class="auth-side-icon">🎮</span>
        <h2><?= e(Settings::get('site_name', 'Gate Game')) ?></h2>
        <p>Một tài khoản — chơi <b>tất cả game</b> trên cổng.<br>Nạp xu, quy đổi, giftcode, xếp hạng tại một nơi duy nhất.</p>
        <ul class="auth-perks">
          <li>✅ Đăng nhập mọi game bằng 1 tài khoản</li>
          <li>💰 Ví xu dùng chung, nạp qua QR ngân hàng</li>
          <li>🎁 Giftcode &amp; sự kiện độc quyền web</li>
        </ul>
      </div>
    </div>
    <div class="auth-main">
      <h1>Đăng nhập</h1>
      <p class="muted">Chào mừng quay lại! Đăng nhập để tiếp tục.</p>

      <?php if (Settings::get('google_enabled') === '1'): ?>
        <a class="btn btn-google btn-block" href="<?= url('/auth/google') ?>">
          <svg class="gicon" viewBox="0 0 48 48" width="18" height="18"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 40.4 44 35 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
          Đăng nhập với Google
        </a>
        <div class="auth-divider"><span>hoặc</span></div>
      <?php endif; ?>

      <form method="post" action="<?= url('/dang-nhap') ?>">
        <?= Csrf::field() ?>
        <div class="field-icon">
          <span class="fi">👤</span>
          <input type="text" name="username" placeholder="Tên tài khoản" required maxlength="20" autofocus>
        </div>
        <div class="field-icon">
          <span class="fi">🔒</span>
          <input type="password" name="password" id="pw1" placeholder="Mật khẩu" required>
          <button type="button" class="pw-toggle" data-target="pw1">👁</button>
        </div>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Đăng nhập</button>
      </form>
      <p class="muted center auth-alt">Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>"><b>Đăng ký ngay</b></a></p>
    </div>
  </div>
</div>
