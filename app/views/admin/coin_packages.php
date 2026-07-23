<?php $isEdit = !empty($edit); ?>
<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa gói: ' . e($edit['name']) : 'Thêm gói nạp mới' ?></h2>
  <form method="post" action="<?= url('/admin/coin-packages') ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-group">
      <label>Tên gói *</label>
      <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Gói 50K">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Giá (VND) *</label>
        <input type="number" name="price_vnd" required min="1000" value="<?= (int)($edit['price_vnd'] ?? '') ?: '' ?>">
      </div>
      <div class="form-group">
        <label>Xu nhận được *</label>
        <input type="number" name="xu" required min="1" value="<?= (int)($edit['xu'] ?? '') ?: '' ?>">
      </div>
      <div class="form-group">
        <label>Xu thưởng thêm</label>
        <input type="number" name="bonus_xu" min="0" value="<?= (int)($edit['bonus_xu'] ?? 0) ?>">
      </div>
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
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/coin-packages') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách gói nạp</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tên gói</th><th>Giá VND</th><th>Xu</th><th>Bonus</th><th>Trạng thái</th><th>Thứ tự</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$packages): ?>
        <tr><td colspan="8" class="empty">Chưa có gói nạp nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($packages as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><strong><?= e($p['name']) ?></strong></td>
          <td><?= number_vn($p['price_vnd']) ?>đ</td>
          <td><?= number_vn($p['xu']) ?></td>
          <td><?= number_vn($p['bonus_xu']) ?></td>
          <td><?= (int)$p['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td><?= (int)$p['sort_order'] ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/coin-packages?edit=' . (int)$p['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/coin-packages') ?>" onsubmit="return confirm('Xoá gói này?')">
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
