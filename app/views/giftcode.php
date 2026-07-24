<h1 class="page-heading"><?= icon('gift') ?> Nhập Giftcode</h1>
<?php if (!$me): ?>
  <div class="alert alert-info">Vui lòng <a href="<?= url('/dang-nhap') ?>">đăng nhập</a> để nhập giftcode.</div>
<?php endif; ?>

<form method="post" action="<?= url('/giftcode') ?>" class="card cascade-form" id="giftcodeForm">
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

  <div class="field">
    <label>4. Nhập mã giftcode</label>
    <input type="text" name="code" id="giftcodeInput" placeholder="VD: TANTHU2026" autocomplete="off" required>
  </div>

  <!-- Thông tin quà hiện ngay dưới ô nhập -->
  <div id="giftcodePreview" class="giftcode-preview hidden"></div>

  <button class="btn btn-primary btn-lg" type="submit" <?= $me ? '' : 'disabled' ?>>Nhận quà</button>
</form>
