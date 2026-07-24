<?php $isEdit = !empty($edit); ?>
<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa gói: ' . e($edit['name']) : 'Thêm gói quy đổi mới' ?></h2>
  <form method="post" action="<?= url('/admin/exchange-packages') ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Game *</label>
        <select name="game_id" id="ex-game" required>
          <option value="">— Chọn game —</option>
          <?php foreach ($games as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($edit['game_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Loại tiền tệ *</label>
        <select name="currency_key" id="ex-currency" required>
          <option value="">— Chọn game trước —</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Tên gói *</label>
      <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Gói 10.000 vàng">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Giá (xu) *</label>
        <input type="number" name="xu_cost" required min="1" value="<?= (int)($edit['xu_cost'] ?? '') ?: '' ?>">
      </div>
      <div class="form-group">
        <label>Lượng tiền tệ nhận *</label>
        <input type="number" name="currency_amount" required min="1" value="<?= (int)($edit['currency_amount'] ?? '') ?: '' ?>">
      </div>
      <div class="form-group">
        <label>Thưởng thêm</label>
        <input type="number" name="bonus_amount" min="0" value="<?= (int)($edit['bonus_amount'] ?? 0) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Cộng tổng nạp in-game (VND)</label>
      <input type="number" name="add_tongnap" min="0" value="<?= (int)($edit['add_tongnap'] ?? 0) ?>">
      <small class="help-text">Cộng vào tổng nạp trong game (VND) để mở mốc thưởng nạp. Để 0 nếu không dùng.</small>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
        </select>
      </div>
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Thêm gói' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/exchange-packages') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách gói quy đổi</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Game</th><th>Tên gói</th><th>Xu</th><th>Nhận</th><th>Bonus</th><th>+Tổng nạp</th><th>Trạng thái</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$packages): ?>
        <tr><td colspan="9" class="empty">Chưa có gói quy đổi nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($packages as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= e($p['game_name']) ?></td>
          <td><strong><?= e($p['name']) ?></strong></td>
          <td><?= number_vn($p['xu_cost']) ?></td>
          <td><?= number_vn($p['currency_amount']) ?> <code><?= e($p['currency_key']) ?></code></td>
          <td><?= number_vn($p['bonus_amount']) ?></td>
          <td><?= number_vn($p['add_tongnap']) ?>đ</td>
          <td><?= (int)$p['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/exchange-packages?edit=' . (int)$p['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/exchange-packages') ?>" onsubmit="return confirm('Xoá gói này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<div class="card">
  <h2 class="card-title">Icon tiền tệ game</h2>
  <p class="help-text" style="margin-bottom:14px">Upload ảnh icon cho từng loại tiền tệ (jpg/png/webp/gif). Icon hiện ở trang Đổi xu; chưa upload thì dùng ảnh mặc định.</p>

  <?php foreach ($games as $g): $gid = (int)$g['id']; $currencies = $icon_info[$gid] ?? []; if (!$currencies) continue; ?>
    <div class="icon-game-block">
      <h3 class="icon-game-title"><?= e($g['name']) ?> <span class="muted">(<?= e($g['adapter']) ?>)</span></h3>
      <div class="currency-icon-grid">
        <?php foreach ($currencies as $ckey => $c): ?>
          <div class="currency-icon-item">
            <img src="<?= e($c['url']) ?>" alt="" onerror="this.src='<?= url('/assets/currency/default.png') ?>'">
            <div class="currency-icon-meta">
              <b><?= e($c['label']) ?> <code><?= e($ckey) ?></code></b>
              <span><?= $c['has'] ? 'Đã upload icon' : 'Đang dùng ảnh mặc định' ?></span>
            </div>
            <form method="post" action="<?= url('/admin/exchange-packages') ?>" enctype="multipart/form-data" class="currency-icon-form">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="save_currency_icon">
              <input type="hidden" name="game_id" value="<?= $gid ?>">
              <input type="hidden" name="currency_key" value="<?= e($ckey) ?>">
              <input type="file" name="icon_file" accept="image/*">
              <button type="submit" class="btn btn-sm btn-primary">Upload</button>
              <?php if ($c['has']): ?>
                <button type="submit" name="remove" value="1" class="btn btn-sm btn-danger" onclick="return confirm('Xoá icon này?')">Xoá</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
var CURRENCY_MAP = <?= json_encode($currency_map, JSON_UNESCAPED_UNICODE) ?>;
var SELECTED_CURRENCY = <?= json_encode($edit['currency_key'] ?? '') ?>;

function fillCurrencies() {
  var gameSel = document.getElementById('ex-game');
  var curSel = document.getElementById('ex-currency');
  var map = CURRENCY_MAP[gameSel.value] || {};
  curSel.innerHTML = '';
  var keys = Object.keys(map);
  if (!keys.length) {
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = '— Chọn game trước —';
    curSel.appendChild(opt);
    return;
  }
  keys.forEach(function (k) {
    var opt = document.createElement('option');
    opt.value = k;
    opt.textContent = map[k] + ' (' + k + ')';
    if (k === SELECTED_CURRENCY) opt.selected = true;
    curSel.appendChild(opt);
  });
}
document.getElementById('ex-game').addEventListener('change', function () {
  SELECTED_CURRENCY = '';
  fillCurrencies();
});
fillCurrencies();
</script>
