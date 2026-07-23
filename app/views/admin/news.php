<?php $isEdit = !empty($edit); ?>
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa bài: ' . e($edit['title']) : 'Đăng bài mới' ?></h2>
  <form method="post" action="<?= url('/admin/news') ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Loại bài</label>
        <select name="type">
          <option value="news" <?= ($edit['type'] ?? 'news') === 'news' ? 'selected' : '' ?>>Tin tức</option>
          <option value="event" <?= ($edit['type'] ?? '') === 'event' ? 'selected' : '' ?>>Sự kiện</option>
        </select>
      </div>
      <div class="form-group">
        <label>Game</label>
        <select name="game_id">
          <option value="0">— Tin chung của cổng —</option>
          <?php foreach ($games as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($edit['game_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tiêu đề *</label>
        <input type="text" name="title" required maxlength="200" value="<?= e($edit['title'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Slug (để trống sẽ tự tạo)</label>
        <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Thumbnail (upload)</label>
      <input type="file" name="thumbnail_file" accept="image/*">
      <label class="sub-label">hoặc URL ảnh</label>
      <input type="text" name="thumbnail_url" value="<?= e($edit['thumbnail'] ?? '') ?>" placeholder="/uploads/... hoặc https://...">
      <?php if (!empty($edit['thumbnail'])): ?><img class="img-preview" src="<?= e($edit['thumbnail']) ?>" alt=""><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Tóm tắt</label>
      <textarea name="summary" rows="2" maxlength="500"><?= e($edit['summary'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Nội dung (HTML)</label>
      <textarea name="content" rows="12"><?= e($edit['content'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="checkbox-label"><input type="checkbox" name="pinned" value="1" <?= (int)($edit['pinned'] ?? 0) === 1 ? 'checked' : '' ?>> Ghim bài viết</label>
      </div>
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hiển thị</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Nháp / Ẩn</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Đăng bài' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/news') ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách bài viết</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tiêu đề</th><th>Loại</th><th>Game</th><th>Ghim</th><th>Trạng thái</th><th>Ngày tạo</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$items): ?>
        <tr><td colspan="8" class="empty">Chưa có bài viết nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($items as $n): ?>
        <tr>
          <td><?= (int)$n['id'] ?></td>
          <td>
            <strong><?= e($n['title']) ?></strong><br>
            <small class="muted"><?= e($n['slug']) ?></small>
          </td>
          <td><?= $n['type'] === 'event' ? '<span class="badge badge-warning">Sự kiện</span>' : '<span class="badge badge-primary">Tin tức</span>' ?></td>
          <td><?= e($n['game_name'] ?? 'Tin chung') ?></td>
          <td><?= (int)$n['pinned'] === 1 ? '📌' : '' ?></td>
          <td><?= (int)$n['status'] === 1 ? '<span class="badge badge-success">Hiển thị</span>' : '<span class="badge">Ẩn</span>' ?></td>
          <td><?= e($n['created_at']) ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= url('/admin/news?edit=' . (int)$n['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/news') ?>" onsubmit="return confirm('Xoá bài viết này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
