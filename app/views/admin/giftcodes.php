<?php
$isEdit = !empty($edit);
$editItems = $isEdit ? (json_decode($edit['items'] ?? '[]', true) ?: []) : [];
$editRewards = $isEdit ? (json_decode($edit['rewards'] ?? '[]', true) ?: []) : [];
?>
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa giftcode: ' . e($edit['code']) : 'Tạo giftcode mới' ?></h2>
  <form method="post" action="<?= url('/admin/giftcodes') ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Game *</label>
        <select name="game_id" id="gc-game" required>
          <option value="">— Chọn game —</option>
          <?php foreach ($games as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($edit['game_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Mã code * (3-50 ký tự: A-Z, 0-9, - _)</label>
        <input type="text" name="code" required value="<?= e($edit['code'] ?? '') ?>" placeholder="TET2026" style="text-transform:uppercase">
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <input type="text" name="description" maxlength="255" value="<?= e($edit['description'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Vật phẩm hiển thị (icon + tên + số lượng — chỉ để trưng bày)</label>
      <div id="item-rows">
        <?php foreach ($editItems as $it): ?>
        <div class="dyn-row">
          <input type="text" name="item_icon[]" placeholder="URL icon" value="<?= e($it['icon'] ?? '') ?>">
          <input type="file" name="item_icon_file[]" accept="image/*">
          <input type="text" name="item_name[]" placeholder="Tên vật phẩm" value="<?= e($it['name'] ?? '') ?>">
          <input type="number" name="item_qty[]" placeholder="SL" min="1" value="<?= (int)($it['qty'] ?? 1) ?>" class="input-sm">
          <button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">Xoá</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-sm" id="add-item">+ Thêm vật phẩm</button>
    </div>

    <div class="form-group">
      <label>Phần thưởng thực nhận (tiền tệ theo adapter của game)</label>
      <div id="reward-rows">
        <?php foreach ($editRewards as $rw): ?>
        <div class="dyn-row">
          <select name="reward_key[]" class="gc-currency" data-selected="<?= e($rw['currency_key'] ?? '') ?>"></select>
          <input type="number" name="reward_amount[]" placeholder="Số lượng" min="1" value="<?= (int)($rw['amount'] ?? 0) ?>">
          <button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">Xoá</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-sm" id="add-reward">+ Thêm phần thưởng</button>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Số lượt dùng tối đa (0 = không giới hạn)</label>
        <input type="number" name="max_uses" min="0" value="<?= (int)($edit['max_uses'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label>Hạn sử dụng (để trống = vô thời hạn)</label>
        <input type="datetime-local" name="expires_at" value="<?= !empty($edit['expires_at']) ? e(date('Y-m-d\TH:i', strtotime($edit['expires_at']))) : '' ?>">
      </div>
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Tắt</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo giftcode' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/giftcodes') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách giftcode</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Game</th><th>Code</th><th>Mô tả</th><th>Phần thưởng</th><th>Đã dùng</th><th>Hạn</th><th>Trạng thái</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$giftcodes): ?>
        <tr><td colspan="9" class="empty">Chưa có giftcode nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($giftcodes as $gc): ?>
        <?php $rws = json_decode($gc['rewards'] ?? '[]', true) ?: []; ?>
        <tr>
          <td><?= (int)$gc['id'] ?></td>
          <td><?= e($gc['game_name']) ?></td>
          <td><code><?= e($gc['code']) ?></code></td>
          <td><small><?= e($gc['description'] ?? '') ?></small></td>
          <td>
            <?php foreach ($rws as $rw): ?>
              <small><?= number_vn($rw['amount'] ?? 0) ?> <?= e($rw['currency_key'] ?? '') ?></small><br>
            <?php endforeach; ?>
          </td>
          <td><?= number_vn($gc['used_count']) ?> / <?= (int)$gc['max_uses'] === 0 ? '∞' : number_vn($gc['max_uses']) ?></td>
          <td><?= !empty($gc['expires_at']) ? e($gc['expires_at']) : 'Vô thời hạn' ?></td>
          <td><?= (int)$gc['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge">Tắt</span>' ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/giftcodes?edit=' . (int)$gc['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/giftcodes') ?>" onsubmit="return confirm('Xoá giftcode này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$gc['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var CURRENCY_MAP = <?= json_encode($currency_map, JSON_UNESCAPED_UNICODE) ?>;

function currentCurrencies() {
  return CURRENCY_MAP[document.getElementById('gc-game').value] || {};
}

function fillCurrencySelect(sel, selected) {
  var map = currentCurrencies();
  sel.innerHTML = '';
  var keys = Object.keys(map);
  if (!keys.length) {
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = '— Chọn game trước —';
    sel.appendChild(opt);
    return;
  }
  keys.forEach(function (k) {
    var opt = document.createElement('option');
    opt.value = k;
    opt.textContent = map[k] + ' (' + k + ')';
    if (k === selected) opt.selected = true;
    sel.appendChild(opt);
  });
}

function refreshAllCurrencySelects() {
  document.querySelectorAll('.gc-currency').forEach(function (sel) {
    fillCurrencySelect(sel, sel.dataset.selected || sel.value);
  });
}

document.getElementById('gc-game').addEventListener('change', function () {
  document.querySelectorAll('.gc-currency').forEach(function (sel) { sel.dataset.selected = ''; });
  refreshAllCurrencySelects();
});

document.getElementById('add-item').addEventListener('click', function () {
  var row = document.createElement('div');
  row.className = 'dyn-row';
  row.innerHTML = '<input type="text" name="item_icon[]" placeholder="URL icon">' +
    '<input type="file" name="item_icon_file[]" accept="image/*">' +
    '<input type="text" name="item_name[]" placeholder="Tên vật phẩm">' +
    '<input type="number" name="item_qty[]" placeholder="SL" min="1" value="1" class="input-sm">' +
    '<button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">Xoá</button>';
  document.getElementById('item-rows').appendChild(row);
});

document.getElementById('add-reward').addEventListener('click', function () {
  var row = document.createElement('div');
  row.className = 'dyn-row';
  var sel = document.createElement('select');
  sel.name = 'reward_key[]';
  sel.className = 'gc-currency';
  fillCurrencySelect(sel, '');
  row.appendChild(sel);
  var amount = document.createElement('input');
  amount.type = 'number';
  amount.name = 'reward_amount[]';
  amount.placeholder = 'Số lượng';
  amount.min = '1';
  row.appendChild(amount);
  var del = document.createElement('button');
  del.type = 'button';
  del.className = 'btn btn-sm btn-danger';
  del.textContent = 'Xoá';
  del.onclick = function () { row.remove(); };
  row.appendChild(del);
  document.getElementById('reward-rows').appendChild(row);
});

refreshAllCurrencySelects();
</script>
