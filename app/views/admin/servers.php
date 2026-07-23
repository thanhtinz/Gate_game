<?php $isEdit = !empty($edit); ?>
<div class="card">
  <form method="get" action="<?= url('/admin/servers') ?>" class="filter-form">
    <label>Lọc theo game:</label>
    <select name="game_id" onchange="this.form.submit()">
      <option value="0">— Tất cả game —</option>
      <?php foreach ($games as $g): ?>
        <option value="<?= (int)$g['id'] ?>" <?= $game_id === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="grid-2col">
<div class="card">
  <h2 class="card-title"><?= $isEdit ? 'Sửa server: ' . e($edit['name']) : 'Thêm server mới' ?></h2>
  <form method="post" action="<?= url('/admin/servers') ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$edit['id'] : 0 ?>">
    <input type="hidden" name="game_id_filter" value="<?= (int)$game_id ?>">
    <?php if ($isEdit): ?><input type="hidden" name="keep_pass" value="1"><?php endif; ?>

    <div class="form-row">
      <div class="form-group">
        <label>Game *</label>
        <select name="game_id" required>
          <option value="">— Chọn game —</option>
          <?php foreach ($games as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($edit['game_id'] ?? $game_id) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tên server *</label>
        <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Server 1">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>DB Host</label>
        <input type="text" name="db_host" value="<?= e($edit['db_host'] ?? '127.0.0.1') ?>">
      </div>
      <div class="form-group">
        <label>DB Port</label>
        <input type="number" name="db_port" value="<?= (int)($edit['db_port'] ?? 3306) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>DB Name *</label>
        <input type="text" name="db_name" required value="<?= e($edit['db_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>DB User</label>
        <input type="text" name="db_user" value="<?= e($edit['db_user'] ?? 'root') ?>">
      </div>
      <div class="form-group">
        <label>DB Pass <?= $isEdit ? '(để trống = giữ nguyên)' : '' ?></label>
        <input type="password" name="db_pass" value="" autocomplete="new-password">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <option value="1" <?= (int)($edit['status'] ?? 1) === 1 ? 'selected' : '' ?>>Hoạt động</option>
          <option value="0" <?= (int)($edit['status'] ?? 1) === 0 ? 'selected' : '' ?>>Bảo trì / Ẩn</option>
        </select>
      </div>
      <div class="form-group">
        <label>Thứ tự</label>
        <input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Ghi chú</label>
      <input type="text" name="note" maxlength="255" value="<?= e($edit['note'] ?? '') ?>">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Thêm server' ?></button>
      <?php if ($isEdit): ?><a class="btn" href="<?= url('/admin/servers' . ($game_id ? '?game_id=' . $game_id : '')) ?>">Huỷ</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Danh sách server</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Game</th><th>Server</th><th>Database</th><th>Trạng thái</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (!$servers): ?>
        <tr><td colspan="6" class="empty">Chưa có server nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($servers as $s): ?>
        <tr>
          <td><?= (int)$s['id'] ?></td>
          <td><?= e($s['game_name']) ?></td>
          <td>
            <strong><?= e($s['name']) ?></strong>
            <?php if (!empty($s['note'])): ?><br><small class="muted"><?= e($s['note']) ?></small><?php endif; ?>
          </td>
          <td><code><?= e($s['db_user']) ?>@<?= e($s['db_host']) ?>:<?= (int)$s['db_port'] ?>/<?= e($s['db_name']) ?></code></td>
          <td><?= (int)$s['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge">Bảo trì</span>' ?></td>
          <td class="actions">
            <form method="post" action="<?= url('/admin/servers') ?>">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="test">
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <input type="hidden" name="game_id_filter" value="<?= (int)$game_id ?>">
              <button type="submit" class="btn btn-sm btn-success">Kiểm tra DB</button>
            </form>
            <a class="btn btn-sm" href="<?= url('/admin/servers?edit=' . (int)$s['id'] . ($game_id ? '&game_id=' . $game_id : '')) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/servers') ?>" onsubmit="return confirm('Xoá server này?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <input type="hidden" name="game_id_filter" value="<?= (int)$game_id ?>">
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
