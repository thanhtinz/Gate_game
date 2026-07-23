<div class="auth-box card">
  <h1>Đăng ký</h1>
  <p class="muted">Tài khoản đăng ký trên web dùng để đăng nhập <b>tất cả game</b> trên cổng.</p>
  <form method="post" action="<?= url('/dang-ky') ?>">
    <?= Csrf::field() ?>
    <label>Tên tài khoản (4-20 ký tự: chữ, số, gạch dưới)</label>
    <input type="text" name="username" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+" autofocus>
    <label>Mật khẩu (6-32 ký tự)</label>
    <input type="password" name="password" required minlength="6" maxlength="32">
    <label>Nhập lại mật khẩu</label>
    <input type="password" name="password2" required minlength="6" maxlength="32">
    <label>Email (không bắt buộc — dùng khôi phục tài khoản)</label>
    <input type="email" name="email" maxlength="100">
    <button class="btn btn-primary btn-block" type="submit">Đăng ký</button>
  </form>
  <p class="muted center">Đã có tài khoản? <a href="<?= url('/dang-nhap') ?>">Đăng nhập</a></p>
</div>
