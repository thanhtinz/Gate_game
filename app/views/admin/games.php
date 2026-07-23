<?php $isEdit = !empty($edit); ?>
<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa game: ' . e($edit['name']) : 'Thêm game mới' ?></h2>
  <form method="post" action="<?= url('/admin/games') ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Tên game *</label>
        <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Slug (để trống sẽ tự tạo từ tên)</label>
        <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Adapter *</label>
        <select name="adapter" required>
          <?php foreach ($adapters as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= ($edit['adapter'] ?? '') === $k ? 'selected' : '' ?>><?= e($label) ?> (<?= e($k) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Ẩn</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <input type="text" name="short_desc" maxlength="255" value="<?= e($edit['short_desc'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Mô tả chi tiết (HTML)</label>
      <textarea name="description" rows="8"><?= e($edit['description'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Thumbnail (upload)</label>
        <input type="file" name="thumbnail_file" accept="image/*">
        <label class="sub-label">hoặc URL ảnh</label>
        <input type="text" name="thumbnail_url" value="<?= e($edit['thumbnail'] ?? '') ?>" placeholder="/uploads/... hoặc https://...">
        <?php if (!empty($edit['thumbnail'])): ?><img class="img-preview" src="<?= e($edit['thumbnail']) ?>" alt=""><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Banner (upload)</label>
        <input type="file" name="banner_file" accept="image/*">
        <label class="sub-label">hoặc URL ảnh</label>
        <input type="text" name="banner_url" value="<?= e($edit['banner'] ?? '') ?>" placeholder="/uploads/... hoặc https://...">
        <?php if (!empty($edit['banner'])): ?><img class="img-preview" src="<?= e($edit['banner']) ?>" alt=""><?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Link tải game</label>
      <div id="download-rows">
        <?php
        $links = json_decode($edit['download_links'] ?? '[]', true) ?: [];
        foreach ($links as $lk): ?>
        <div class="dyn-row">
          <input type="text" name="download_label[]" placeholder="Nhãn (VD: Android)" value="<?= e($lk['label'] ?? '') ?>">
          <input type="text" name="download_url[]" placeholder="URL tải" value="<?= e($lk['url'] ?? '') ?>">
          <button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">Xoá</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-sm" id="add-download">+ Thêm link tải</button>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Thêm game' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/games') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách game</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Game</th><th>Adapter</th><th>Server</th><th>Trạng thái</th><th>Thứ tự</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$games): ?>
        <tr><td colspan="7" class="empty">Chưa có game nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($games as $g): ?>
        <tr>
          <td><?= (int)$g['id'] ?></td>
          <td>
            <strong><?= e($g['name']) ?></strong><br>
            <small class="muted"><?= e($g['slug']) ?></small>
          </td>
          <td><code><?= e($g['adapter']) ?></code></td>
          <td><?= (int)$g['server_count'] ?></td>
          <td><?= (int)$g['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td><?= (int)$g['sort_order'] ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/games?edit=' . (int)$g['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/games') ?>" onsubmit="return confirm('Xoá game này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
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
document.getElementById('add-download').addEventListener('click', function () {
  var row = document.createElement('div');
  row.className = 'dyn-row';
  row.innerHTML = '<input type="text" name="download_label[]" placeholder="Nhãn (VD: Android)">' +
    '<input type="text" name="download_url[]" placeholder="URL tải">' +
    '<button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">Xoá</button>';
  document.getElementById('download-rows').appendChild(row);
});
</script>
