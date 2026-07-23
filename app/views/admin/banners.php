<?php $isEdit = !empty($edit); ?>
<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa banner' : 'Thêm banner mới' ?></h2>
  <form method="post" action="<?= url('/admin/banners') ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-group">
      <label>Tiêu đề</label>
      <input type="text" name="title" maxlength="100" value="<?= e($edit['title'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Ảnh banner <?= $isEdit ? '(để trống = giữ ảnh cũ)' : '*' ?></label>
      <input type="file" name="image_file" accept="image/*">
      <label class="sub-label">hoặc URL ảnh</label>
      <input type="text" name="image_url" placeholder="/uploads/... hoặc https://...">
      <?php if (!empty($edit['image'])): ?><img class="img-preview" src="<?= e($edit['image']) ?>" alt=""><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Link khi bấm vào</label>
      <input type="text" name="link" maxlength="255" value="<?= e($edit['link'] ?? '') ?>" placeholder="https://... hoặc /game/ngoc-rong">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hiển thị</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Thêm banner' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/banners') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách banner</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Ảnh</th><th>Tiêu đề</th><th>Link</th><th>Thứ tự</th><th>Trạng thái</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$banners): ?>
        <tr><td colspan="7" class="empty">Chưa có banner nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($banners as $b): ?>
        <tr>
          <td><?= (int)$b['id'] ?></td>
          <td><img class="table-thumb" src="<?= e($b['image']) ?>" alt=""></td>
          <td><?= e($b['title'] ?? '') ?></td>
          <td><small><?= e($b['link'] ?? '') ?></small></td>
          <td><?= (int)$b['sort_order'] ?></td>
          <td><?= (int)$b['status'] === 1 ? '<span class="badge badge-success">Hiển thị</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/banners?edit=' . (int)$b['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/banners') ?>" onsubmit="return confirm('Xoá banner này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
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
