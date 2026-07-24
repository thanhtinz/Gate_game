<h1 class="page-heading"><?= icon('exchange') ?> Đổi xu ra tiền tệ game</h1>
<?php if ($me): ?>
  <p class="muted">Số dư của bạn: <b class="accent"><?= number_vn($me['xu']) ?> xu</b> — <a href="<?= url('/nap-xu') ?>">Nạp thêm</a></p>
<?php else: ?>
  <div class="alert alert-info">Vui lòng <a href="<?= url('/dang-nhap') ?>">đăng nhập</a> để quy đổi.</div>
<?php endif; ?>

<form method="post" action="<?= url('/doi-xu') ?>" class="card cascade-form" id="exchangeForm">
  <?= Csrf::field() ?>
  <div class="cascade-row">
    <div class="field">
      <label>1. Chọn game</label>
      <select name="game_id" id="selGame" required>
        <option value="">— Chọn game —</option>
        <?php foreach ($games as $g): ?><option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>2. Chọn server</label>
      <select name="server_id" id="selServer" required disabled><option value="">— Chọn game trước —</option></select>
    </div>
    <div class="field">
      <label>3. Chọn nhân vật</label>
      <select name="character_id" id="selChar" required disabled><option value="">— Chọn server trước —</option></select>
    </div>
  </div>

  <div id="packageArea" class="hidden">
    <label>4. Chọn gói quy đổi</label>
    <div class="package-grid" id="exchangePackages"></div>
  </div>

  <button class="btn btn-primary btn-lg" type="submit" <?= $me ? '' : 'disabled' ?>>Xác nhận quy đổi</button>
  <p class="muted small"><?= icon('warning') ?> Với tiền tệ cộng trực tiếp vào nhân vật, hãy <b>thoát game</b> trước khi quy đổi để không bị server game ghi đè.</p>
</form>

<script>
window.EXCHANGE_PACKAGES = <?= json_encode($packagesByGame, JSON_UNESCAPED_UNICODE) ?>;
window.CURRENCY_LABELS = <?= json_encode($currencyLabels, JSON_UNESCAPED_UNICODE) ?>;
</script>
