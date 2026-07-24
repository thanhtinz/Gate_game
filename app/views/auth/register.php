<div class="auth-wrap">
  <div class="auth-panel">
    <div class="auth-side">
      <div class="auth-side-inner">
        <span class="auth-side-icon"><?= icon('user', 'ic-xl') ?></span>
        <h2>Tạo tài khoản</h2>
        <p>Đăng ký 1 lần trên web, dùng để đăng nhập <b>tất cả game</b> trên cổng.</p>
        <ul class="auth-perks">
          <li><?= icon('mail') ?> Xác minh email — bảo vệ tài khoản</li>
          <li><?= icon('key') ?> Đổi mật khẩu tại 1 nơi, hiệu lực mọi game</li>
          <li><?= icon('gift') ?> Nhận quà tân thủ qua giftcode</li>
        </ul>
      </div>
    </div>
    <div class="auth-main">
      <h1>Đăng ký</h1>
      <p class="muted">Điền thông tin bên dưới, sau đó xác minh email để kích hoạt.</p>

      <?php if (Settings::get('google_enabled') === '1'): ?>
        <a class="btn btn-google btn-block" href="<?= url('/auth/google') ?>">
          <?= icon('google') ?>
          Đăng ký nhanh với Google
        </a>
        <div class="auth-divider"><span>hoặc</span></div>
      <?php endif; ?>

      <form method="post" action="<?= url('/dang-ky') ?>">
        <?= Csrf::field() ?>
        <div class="field-icon">
          <span class="fi"><?= icon('user') ?></span>
          <input type="text" name="username" placeholder="Tên tài khoản (4-20 ký tự: a-z, 0-9, _)" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+" autofocus>
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('mail') ?></span>
          <input type="email" name="email" placeholder="Email (nhận mail xác minh)" required maxlength="100">
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('lock') ?></span>
          <input type="password" name="password" id="pw1" placeholder="Mật khẩu (6-32 ký tự)" required minlength="6" maxlength="32">
          <button type="button" class="pw-toggle" data-target="pw1" aria-label="Hiện mật khẩu"><?= icon('eye') ?></button>
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('lock') ?></span>
          <input type="password" name="password2" id="pw2" placeholder="Nhập lại mật khẩu" required minlength="6" maxlength="32">
          <button type="button" class="pw-toggle" data-target="pw2" aria-label="Hiện mật khẩu"><?= icon('eye') ?></button>
        </div>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Tạo tài khoản</button>
      </form>
      <p class="muted center auth-alt">Đã có tài khoản? <a href="<?= url('/dang-nhap') ?>"><b>Đăng nhập</b></a></p>
    </div>
  </div>
</div>
