<div class="auth-box card">
  <h1>Đăng nhập</h1>
  <p class="muted">Một tài khoản — chơi tất cả game trên cổng.</p>
  <form method="post" action="<?= url('/dang-nhap') ?>">
    <?= Csrf::field() ?>
    <label>Tên tài khoản</label>
    <input type="text" name="username" required maxlength="20" autofocus>
    <label>Mật khẩu</label>
    <input type="password" name="password" required>
    <button class="btn btn-primary btn-block" type="submit">Đăng nhập</button>
  </form>
  <p class="muted center">Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>">Đăng ký ngay</a></p>
</div>
