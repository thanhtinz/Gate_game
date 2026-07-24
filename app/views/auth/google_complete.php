<div class="auth-wrap">
  <div class="auth-panel single">
    <div class="auth-main">
      <h1>Hoàn tất đăng ký</h1>
      <p class="muted">
        Đã kết nối Google: <b class="accent"><?= e($pending['email']) ?></b>
        <span class="badge badge-completed"><?= icon('check') ?> Email đã xác minh</span>
      </p>
      <p class="muted small">Chọn <b>tên tài khoản</b> và <b>mật khẩu</b> — đây là thông tin bạn dùng để đăng nhập <b>trong game</b>.</p>

      <form method="post" action="<?= url('/hoan-tat-dang-ky') ?>">
        <?= Csrf::field() ?>
        <div class="field-icon">
          <span class="fi"><?= icon('user') ?></span>
          <input type="text" name="username" placeholder="Tên tài khoản (4-20 ký tự: a-z, 0-9, _)" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+" autofocus>
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('lock') ?></span>
          <input type="password" name="password" id="pw1" placeholder="Mật khẩu vào game (6-32 ký tự)" required minlength="6" maxlength="32">
          <button type="button" class="pw-toggle" data-target="pw1" aria-label="Hiện mật khẩu"><?= icon('eye') ?></button>
        </div>
        <div class="field-icon">
          <span class="fi"><?= icon('lock') ?></span>
          <input type="password" name="password2" id="pw2" placeholder="Nhập lại mật khẩu" required minlength="6" maxlength="32">
          <button type="button" class="pw-toggle" data-target="pw2" aria-label="Hiện mật khẩu"><?= icon('eye') ?></button>
        </div>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Hoàn tất</button>
      </form>
    </div>
  </div>
</div>
