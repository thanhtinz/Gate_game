<div class="order-box card" data-order-code="<?= e($order['code']) ?>" data-order-status="<?= e($order['status']) ?>">
  <h1>Đơn nạp <?= e($order['code']) ?></h1>

  <?php if ($order['status'] === 'completed'): ?>
    <div class="alert alert-success"><?= icon('check-circle') ?> Đơn đã thanh toán thành công! Xu đã được cộng vào tài khoản.</div>
    <a class="btn btn-primary" href="<?= url('/doi-xu') ?>"><?= icon('exchange') ?> Đổi xu ra tiền tệ game</a>
  <?php elseif ($order['status'] === 'cancelled'): ?>
    <div class="alert alert-error">Đơn đã bị huỷ. Vui lòng tạo đơn mới.</div>
    <a class="btn btn-primary" href="<?= url('/nap-xu') ?>">Tạo đơn mới</a>
  <?php else: ?>
    <div class="order-flex">
      <div class="qr-side">
        <img class="qr-img" src="<?= e($qrUrl) ?>" alt="QR thanh toán">
        <p class="muted small center">Quét mã QR bằng app ngân hàng</p>
      </div>
      <div class="order-info">
        <ul class="info-list">
          <li><span>Ngân hàng</span><b><?= e($bank) ?></b></li>
          <li><span>Số tài khoản</span><b><?= e($account) ?></b></li>
          <?php if ($accountName): ?><li><span>Chủ tài khoản</span><b><?= e($accountName) ?></b></li><?php endif; ?>
          <li><span>Số tiền</span><b class="accent"><?= number_vn($order['amount_vnd']) ?> đ</b></li>
          <li><span>Nội dung CK</span><b class="accent"><?= e($order['code']) ?></b></li>
          <li><span>Xu nhận</span><b><?= number_vn($order['xu'] + $order['bonus_xu']) ?> xu</b></li>
        </ul>
        <div class="alert alert-info">
          <?= icon('warning') ?> Chuyển khoản <b>đúng số tiền</b> và <b>đúng nội dung "<?= e($order['code']) ?>"</b>.
          Xu sẽ tự động cộng sau 5–30 giây kể từ khi tiền vào.
        </div>
        <p id="orderPolling" class="muted center"><?= icon('clock') ?> Đang chờ thanh toán...</p>
      </div>
    </div>
  <?php endif; ?>
</div>
