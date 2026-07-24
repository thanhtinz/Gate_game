<h1 class="page-heading"><?= icon('cart') ?> Webshop</h1>
<?php if ($me): ?>
  <p class="muted">Số dư của bạn: <b class="accent"><?= number_vn($me['xu']) ?> xu</b> — <a href="<?= url('/nap-xu') ?>">Nạp thêm</a></p>
<?php else: ?>
  <div class="alert alert-info">Vui lòng <a href="<?= url('/dang-nhap') ?>">đăng nhập</a> để mua vật phẩm.</div>
<?php endif; ?>

<?php if (!$products): ?>
  <div class="card"><p class="empty">Chưa có sản phẩm nào. Vui lòng quay lại sau.</p></div>
<?php else: ?>

<?php if (count($games) > 1): ?>
<div class="shop-filter" id="shopFilter">
  <button type="button" class="chip active" data-game="">Tất cả</button>
  <?php foreach ($games as $g): ?>
    <button type="button" class="chip" data-game="<?= (int)$g['id'] ?>"><?= e($g['name']) ?></button>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="post" action="<?= url('/shop') ?>" class="card cascade-form" id="shopForm">
  <?= Csrf::field() ?>
  <input type="hidden" name="product_id" id="shopProductId" value="">

  <div class="shop-grid" id="shopGrid">
    <?php foreach ($products as $p): ?>
      <div class="shop-card" data-product="<?= (int)$p['id'] ?>" data-game="<?= (int)$p['game_id'] ?>" tabindex="0" role="button">
        <div class="shop-card-img">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= e(url($p['image'])) ?>" alt="<?= e($p['name']) ?>">
          <?php else: ?>
            <span class="shop-card-noimg"><?= icon('cart', 'ic-lg') ?></span>
          <?php endif; ?>
          <?php if ((int)$p['item_quantity'] > 1): ?><span class="shop-card-qty">x<?= number_vn($p['item_quantity']) ?></span><?php endif; ?>
        </div>
        <div class="shop-card-body">
          <span class="shop-card-game"><?= e($p['game_name']) ?></span>
          <h3 class="shop-card-name"><?= e($p['name']) ?></h3>
          <?php if (!empty($p['description'])): ?><p class="shop-card-desc"><?= e($p['description']) ?></p><?php endif; ?>
          <div class="shop-card-foot">
            <span class="shop-card-price"><?= icon('coin') ?> <?= number_vn($p['xu_cost']) ?> xu</span>
            <?php if ((int)$p['stock'] >= 0): ?><span class="shop-card-stock">Còn <?= number_vn($p['stock']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="shopBuyPanel" class="shop-buy-panel hidden">
    <h2 class="shop-buy-title">Nhận vật phẩm: <span id="shopBuyName" class="accent"></span></h2>
    <div class="cascade-row">
      <div class="field">
        <label>Server</label>
        <select id="shopServer" name="server_id" required disabled><option value="">— Chọn sản phẩm trước —</option></select>
      </div>
      <div class="field">
        <label>Nhân vật</label>
        <select id="shopChar" name="character_id" required disabled><option value="">— Chọn server trước —</option></select>
      </div>
    </div>
    <p class="help-text">Lưu ý: nhân vật phải <b>offline</b> để nhận vật phẩm. Vật phẩm được gửi vào ô trống trong túi đồ/rương.</p>
    <button class="btn btn-primary btn-lg" type="submit" <?= $me ? '' : 'disabled' ?>>Xác nhận mua bằng xu</button>
  </div>
</form>

<script>
window.BASE_URL = <?= json_encode(rtrim(config('base_path', ''), '/'), JSON_UNESCAPED_SLASHES) ?>;
window.SHOP_PRODUCTS = <?= json_encode(array_map(fn($p) => ['id' => (int)$p['id'], 'name' => $p['name']], $products), JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php endif; ?>
