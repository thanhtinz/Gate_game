<h1 class="page-heading">💰 Nạp xu</h1>
<p class="muted">Nạp xu trên web qua chuyển khoản (SePay), sau đó vào mục <a href="<?= url('/doi-xu') ?>">Đổi xu</a> để quy đổi ra tiền tệ trong game.</p>

<?php if (!$sepayEnabled): ?>
  <div class="alert alert-error">Phương thức nạp đang tạm đóng, vui lòng quay lại sau.</div>
<?php endif; ?>

<?php if ($pending): ?>
<div class="card">
  <h3>Đơn đang chờ thanh toán</h3>
  <ul class="related-list">
    <?php foreach ($pending as $o): ?>
      <li><a href="<?= url('/nap-xu/don/' . $o['code']) ?>">Đơn <?= e($o['code']) ?> — <?= number_vn($o['amount_vnd']) ?> đ</a> <span class="muted small"><?= date('d/m H:i', strtotime($o['created_at'])) ?></span></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="package-grid">
  <?php foreach ($packages as $p): ?>
  <form method="post" action="<?= url('/nap-xu') ?>" class="package-card">
    <?= Csrf::field() ?>
    <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
    <h3><?= e($p['name']) ?></h3>
    <div class="package-xu"><?= number_vn($p['xu']) ?> xu</div>
    <?php if ($p['bonus_xu'] > 0): ?><div class="package-bonus">+ <?= number_vn($p['bonus_xu']) ?> xu tặng</div><?php endif; ?>
    <div class="package-price"><?= number_vn($p['price_vnd']) ?> đ</div>
    <button class="btn btn-primary btn-block" type="submit" <?= $sepayEnabled ? '' : 'disabled' ?>>Nạp ngay</button>
  </form>
  <?php endforeach; ?>
</div>
