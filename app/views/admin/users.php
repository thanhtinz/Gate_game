<div class="card">
  <form method="get" action="<?= url('/admin/users') ?>" class="filter-form">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên tài khoản...">
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    <?php if ($q !== ''): ?><a class="btn" href="<?= url('/admin/users') ?>">Xoá lọc</a><?php endif; ?>
    <span class="muted">Tổng: <?= number_vn($total) ?> tài khoản</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Email</th><th>Xu</th><th>Tổng nạp</th><th>Quyền</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
      <?php if (!$users): ?>
        <tr><td colspan="9" class="empty">Không tìm thấy người dùng nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><strong><?= e($u['username']) ?></strong></td>
          <td><?= e($u['email'] ?? '') ?>
            <?php if ((int)($u['email_verified'] ?? 0) === 1): ?>
              <span class="badge badge-success" title="Đã xác minh email"><?= icon('check') ?></span>
            <?php else: ?>
              <form method="post" action="<?= url('/admin/users') ?>" style="display:inline">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="verify_email">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <?php if (isset($q) && $q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                <?php if (isset($page)): ?><input type="hidden" name="page" value="<?= (int)$page ?>"><?php endif; ?>
                <button type="submit" class="btn btn-sm" title="Xác minh email thủ công">Duyệt mail</button>
              </form>
            <?php endif; ?>
          </td>
          <td><?= number_vn($u['xu']) ?></td>
          <td><?= number_vn($u['tong_nap']) ?>đ</td>
          <td><?= (int)$u['role'] === 1 ? '<span class="badge badge-primary">Admin</span>' : '<span class="badge">User</span>' ?></td>
          <td><?= (int)$u['status'] === 1 ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Đã khoá</span>' ?></td>
          <td><?= e($u['created_at']) ?></td>
          <td class="actions">
            <form method="post" action="<?= url('/admin/users') ?>" onsubmit="return confirm('<?= (int)$u['status'] === 1 ? 'Khoá' : 'Mở khoá' ?> tài khoản <?= e($u['username']) ?>?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="toggle_status">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="q" value="<?= e($q) ?>">
              <input type="hidden" name="page" value="<?= (int)$page ?>">
              <button type="submit" class="btn btn-sm <?= (int)$u['status'] === 1 ? 'btn-danger' : 'btn-success' ?>"><?= (int)$u['status'] === 1 ? 'Khoá' : 'Mở khoá' ?></button>
            </form>
            <form method="post" action="<?= url('/admin/users') ?>" onsubmit="return confirm('Thay đổi quyền của <?= e($u['username']) ?>?')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="toggle_role">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="q" value="<?= e($q) ?>">
              <input type="hidden" name="page" value="<?= (int)$page ?>">
              <button type="submit" class="btn btn-sm"><?= (int)$u['role'] === 1 ? 'Gỡ admin' : 'Cấp admin' ?></button>
            </form>
            <button type="button" class="btn btn-sm" onclick="toggleRow('xu-<?= (int)$u['id'] ?>')">± Xu</button>
            <button type="button" class="btn btn-sm" onclick="toggleRow('pw-<?= (int)$u['id'] ?>')">Đổi MK</button>
          </td>
        </tr>
        <tr id="xu-<?= (int)$u['id'] ?>" class="inline-form-row" style="display:none">
          <td colspan="9">
            <form method="post" action="<?= url('/admin/users') ?>" class="inline-form">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="adjust_xu">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="q" value="<?= e($q) ?>">
              <input type="hidden" name="page" value="<?= (int)$page ?>">
              <label>Điều chỉnh xu cho <strong><?= e($u['username']) ?></strong> (số dương = cộng, số âm = trừ):</label>
              <input type="number" name="amount" required placeholder="VD: 5000 hoặc -3000">
              <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
            </form>
          </td>
        </tr>
        <tr id="pw-<?= (int)$u['id'] ?>" class="inline-form-row" style="display:none">
          <td colspan="9">
            <form method="post" action="<?= url('/admin/users') ?>" class="inline-form" onsubmit="return confirm('Đặt lại mật khẩu cho <?= e($u['username']) ?>? Mật khẩu sẽ được đồng bộ xuống DB các game.')">
              <?= Csrf::field() ?>
              <input type="hidden" name="action" value="reset_password">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="q" value="<?= e($q) ?>">
              <input type="hidden" name="page" value="<?= (int)$page ?>">
              <label>Mật khẩu mới cho <strong><?= e($u['username']) ?></strong> (6-32 ký tự):</label>
              <input type="text" name="new_password" required minlength="6" maxlength="32" autocomplete="off">
              <button type="submit" class="btn btn-sm btn-primary">Đặt lại mật khẩu</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <?php $link = '/admin/users?page=' . $i . ($q !== '' ? '&q=' . urlencode($q) : ''); ?>
      <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= url($link) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleRow(id) {
  var el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
