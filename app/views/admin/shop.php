<?php $isEdit = !empty($edit); ?>
<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa sản phẩm: ' . e($edit['name']) : 'Thêm sản phẩm mới' ?></h2>
  <form method="post" action="<?= url('/admin/shop') ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Game *</label>
        <select name="game_id" id="shop-game" required>
          <option value="">— Chọn game —</option>
          <?php foreach ($games as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($edit['game_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>ID vật phẩm trong game *</label>
        <input type="number" name="item_id" id="shop-item-id" required min="0" value="<?= $isEdit ? (int)$edit['item_id'] : '' ?>" placeholder="VD: 12">
        <small class="help-text" id="shop-item-name">Nhập ID rồi bấm <b>Kiểm tra tên</b> để xác nhận đúng vật phẩm.</small>
        <button type="button" class="btn btn-sm" id="shop-lookup-btn" style="margin-top:6px">Kiểm tra tên</button>
      </div>
    </div>

    <div class="form-group">
      <label>Tên hiển thị</label>
      <input type="text" name="name" id="shop-name" value="<?= e($edit['name'] ?? '') ?>" placeholder="Để trống sẽ tự lấy theo tên vật phẩm trong game">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Số lượng giao mỗi lần *</label>
        <input type="number" name="item_quantity" required min="1" value="<?= (int)($edit['item_quantity'] ?? 1) ?>">
      </div>
      <div class="form-group">
        <label>Giá (xu) *</label>
        <input type="number" name="xu_cost" required min="1" value="<?= (int)($edit['xu_cost'] ?? '') ?: '' ?>">
      </div>
      <div class="form-group">
        <label>Tồn kho</label>
        <input type="number" name="stock" min="-1" value="<?= $isEdit ? (int)$edit['stock'] : '' ?>" placeholder="Trống = vô hạn">
        <small class="help-text">Để trống hoặc -1 = không giới hạn.</small>
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <input type="text" name="description" value="<?= e($edit['description'] ?? '') ?>" placeholder="Không bắt buộc">
    </div>

    <div class="form-group">
      <label>Ảnh sản phẩm</label>
      <?php if ($isEdit && !empty($edit['image'])): ?>
        <div class="admin-thumb"><img src="<?= e(url($edit['image'])) ?>" alt="" style="max-height:64px"></div>
      <?php endif; ?>
      <input type="file" name="image_file" accept="image/*">
      <small class="help-text">Hoặc dán URL:</small>
      <input type="text" name="image_url" value="" placeholder="https://...">
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
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Thêm sản phẩm' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/shop') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách sản phẩm</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Game</th><th>Sản phẩm</th><th>Item</th><th>SL</th><th>Xu</th><th>Kho</th><th>TT</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$products): ?>
        <tr><td colspan="9" class="empty">Chưa có sản phẩm nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= e($p['game_name']) ?></td>
          <td>
            <?php if (!empty($p['image'])): ?><img src="<?= e(url($p['image'])) ?>" alt="" style="height:22px;vertical-align:middle;margin-right:6px"><?php endif; ?>
            <strong><?= e($p['name']) ?></strong>
          </td>
          <td><code>#<?= (int)$p['item_id'] ?></code></td>
          <td><?= number_vn($p['item_quantity']) ?></td>
          <td><?= number_vn($p['xu_cost']) ?></td>
          <td><?= (int)$p['stock'] < 0 ? '∞' : number_vn($p['stock']) ?></td>
          <td><?= (int)$p['status'] === 1 ? '<span class="badge badge-success">Hiện</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/shop?edit=' . (int)$p['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/shop') ?>" onsubmit="return confirm('Xoá sản phẩm này?')">
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

<script>
(function () {
  var BASE = <?= json_encode(rtrim(config('base_path', ''), '/'), JSON_UNESCAPED_SLASHES) ?>;
  var btn = document.getElementById('shop-lookup-btn');
  var out = document.getElementById('shop-item-name');
  var nameInput = document.getElementById('shop-name');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var gid = document.getElementById('shop-game').value;
    var iid = document.getElementById('shop-item-id').value;
    if (!gid || iid === '') { out.textContent = 'Chọn game và nhập ID vật phẩm trước.'; return; }
    out.textContent = 'Đang tra...';
    fetch(BASE + '/admin/shop/item-lookup?game_id=' + encodeURIComponent(gid) + '&item_id=' + encodeURIComponent(iid), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          out.innerHTML = 'Vật phẩm: <b>' + (res.name || '').replace(/[<>&]/g, '') + '</b>';
          if (nameInput && !nameInput.value) nameInput.value = res.name;
        } else {
          out.textContent = res.message || 'Không tìm thấy.';
        }
      })
      .catch(function () { out.textContent = 'Lỗi kết nối.'; });
  });
})();
</script>
