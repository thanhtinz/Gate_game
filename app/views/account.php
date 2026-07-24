<div class="account-grid">
  <div class="card">
    <h2><?= icon('user') ?> <?= e($me['username']) ?></h2>
    <ul class="info-list">
      <li><span>Số dư xu</span><b class="accent"><?= number_vn($me['xu']) ?> xu</b></li>
      <li><span>Tổng đã nạp</span><b><?= number_vn($me['tong_nap']) ?> đ</b></li>
      <li><span>Email</span><b><?= e($me['email'] ?: '—') ?>
        <?php if (EmailVerify::isVerified($me)): ?>
          <span class="badge badge-completed"><?= icon('check') ?> Đã xác minh</span>
        <?php else: ?>
          <span class="badge badge-pending">Chưa xác minh</span>
        <?php endif; ?>
      </b></li>
      <li><span>Ngày tạo</span><b><?= date('d/m/Y', strtotime($me['created_at'])) ?></b></li>
    </ul>
    <div class="btn-row">
      <a class="btn btn-primary" href="<?= url('/nap-xu') ?>"><?= icon('wallet') ?> Nạp xu</a>
      <a class="btn btn-outline" href="<?= url('/doi-xu') ?>"><?= icon('exchange') ?> Đổi xu</a>
    </div>
    <?php if (!EmailVerify::isVerified($me)): ?>
      <div class="alert alert-info" style="margin-top:14px">
        <?= icon('mail') ?> Tài khoản chưa xác minh email<?= EmailVerify::required() ? ' — cần xác minh để vào game và nạp/đổi xu' : '' ?>.
        <form method="post" action="<?= url('/gui-lai-xac-minh') ?>" style="margin-top:8px">
          <?= Csrf::field() ?>
          <button class="btn btn-sm btn-primary" type="submit">Gửi lại mail xác minh</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2><?= icon('key') ?> Đổi mật khẩu</h2>
    <p class="muted small">Mật khẩu mới sẽ được đồng bộ xuống tất cả game.</p>
    <form method="post" action="<?= url('/doi-mat-khau') ?>">
      <?= Csrf::field() ?>
      <label>Mật khẩu hiện tại</label>
      <input type="password" name="old_password" required>
      <label>Mật khẩu mới</label>
      <input type="password" name="new_password" required minlength="6" maxlength="32">
      <label>Nhập lại mật khẩu mới</label>
      <input type="password" name="new_password2" required minlength="6" maxlength="32">
      <button class="btn btn-primary" type="submit">Đổi mật khẩu</button>
    </form>
  </div>
</div>

<section class="section card">
  <h2 class="section-title"><?= icon('receipt') ?> Lịch sử nạp xu</h2>
  <div class="table-wrap">
    <table class="data-table">
      <tr><th>Mã đơn</th><th>Gói</th><th>Số tiền</th><th>Xu nhận</th><th>Trạng thái</th><th>Thời gian</th></tr>
      <?php if (!$orders): ?><tr><td colspan="6" class="muted">Chưa có giao dịch.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="<?= url('/nap-xu/don/' . $o['code']) ?>"><?= e($o['code']) ?></a></td>
        <td><?= e($o['package_name'] ?: '—') ?></td>
        <td><?= number_vn($o['amount_vnd']) ?> đ</td>
        <td><?= number_vn($o['xu'] + $o['bonus_xu']) ?></td>
        <td><span class="badge badge-<?= $o['status'] ?>"><?= ['pending' => 'Chờ thanh toán', 'completed' => 'Thành công', 'cancelled' => 'Đã huỷ'][$o['status']] ?></span></td>
        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>

<section class="section card">
  <h2 class="section-title"><?= icon('exchange') ?> Lịch sử quy đổi</h2>
  <div class="table-wrap">
    <table class="data-table">
      <tr><th>Game</th><th>Server</th><th>Nhân vật</th><th>Xu trừ</th><th>Nhận</th><th>Trạng thái</th><th>Thời gian</th></tr>
      <?php if (!$exchanges): ?><tr><td colspan="7" class="muted">Chưa có giao dịch.</td></tr><?php endif; ?>
      <?php foreach ($exchanges as $x): ?>
      <tr>
        <td><?= e($x['game_name']) ?></td>
        <td><?= e($x['server_name'] ?: '—') ?></td>
        <td><?= e($x['character_name']) ?></td>
        <td><?= number_vn($x['xu_cost']) ?></td>
        <td><?= number_vn($x['currency_amount']) ?> <?= e($x['currency_key']) ?></td>
        <td><span class="badge badge-<?= $x['status'] ?>"><?= $x['status'] === 'completed' ? 'Thành công' : 'Thất bại' ?></span></td>
        <td><?= date('d/m/Y H:i', strtotime($x['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>

<section class="section card">
  <h2 class="section-title"><?= icon('gift') ?> Giftcode đã nhập</h2>
  <div class="table-wrap">
    <table class="data-table">
      <tr><th>Mã</th><th>Game</th><th>Nhân vật</th><th>Thời gian</th></tr>
      <?php if (!$giftlogs): ?><tr><td colspan="4" class="muted">Chưa nhập giftcode nào.</td></tr><?php endif; ?>
      <?php foreach ($giftlogs as $l): ?>
      <tr>
        <td><b><?= e($l['code']) ?></b></td>
        <td><?= e($l['game_name']) ?></td>
        <td><?= e($l['character_name']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>
