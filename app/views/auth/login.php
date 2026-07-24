<div class="auth-wrap">
  <div class="auth-panel">
    <div class="auth-side">
      <div class="auth-side-inner">
        <span class="auth-side-icon"><?= icon('gamepad', 'ic-xl') ?></span>
        <h2><?= e(Settings::get('site_name', 'Gate Game')) ?></h2>
        <p>Một tài khoản — chơi <b>tất cả game</b> trên cổng.<br>Nạp xu, quy đổi, giftcode, xếp hạng tại một nơi duy nhất.</p>
        <ul class="auth-perks">
          <li><?= icon('check-circle') ?> Đăng nhập mọi game bằng 1 tài khoản</li>
          <li><?= icon('wallet') ?> Ví xu dùng chung, nạp qua QR ngân hàng</li>
          <li><?= icon('gift') ?> Giftcode &amp; sự kiện độc quyền web</li>
        </ul>
      </div>
    </div>
    <div class="auth-main">
      <h1>Đăng nhập</h1>
      <p class="muted">Chào mừng quay lại! Đăng nhập để tiếp tục.</p>

      <?php if (Settings::get('google_enabled') === '1'): ?>
        <a class="btn btn-google btn-block" href="<?= url('/auth/google') ?>">
          <?= icon('google') ?>
          Đăng nhập với Google
        </a>
        <div class="auth-divider"><span>hoặc</span></div>
      <?php endif; ?>

      <form method="post" action="<?= url('/dang-nhap') ?>">
        <?= Csrf::field() ?>
        <div class="field-icon">
          <span class="fi"><?= icon('user') ?></span>
          <input type="text" name="username" placeholder="Tên tài khoản" required maxlength="20" autofocus>
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('lock') ?></span>
          <input type="password" name="password" id="pw1" placeholder="Mật khẩu" required>
          <button type="button" class="pw-toggle" data-target="pw1" aria-label="Hiện mật khẩu"><?= icon('eye') ?></button>
        </div>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Đăng nhập</button>
      </form>
      <p class="muted center auth-alt">Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>"><b>Đăng ký ngay</b></a></p>
    </div>
  </div>
</div>
