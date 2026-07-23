<div class="card">
  <form method="get" action="<?= url('/admin/orders') ?>" class="filter-form">
    <label>Trạng thái:</label>
    <select name="status" onchange="this.form.submit()">
      <option value="">— Tất cả —</option>
      <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ thanh toán</option>
      <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
      <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã huỷ</option>
    </select>
    <span class="muted">Tổng: <?= number_vn($total) ?> đơn</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Mã đơn</th><th>Số tiền</th><th>Xu</th><th>Bonus</th><th>Phương thức</th><th>SePay TX</th><th>Trạng thái</th><th>Tạo lúc</th><th>Hoàn thành</th></tr>
      </thead>
      <tbody>
      <?php if (!$orders): ?>
        <tr><td colspan="11" class="empty">Không có đơn nạp nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int)$o['id'] ?></td>
          <td><strong><?= e($o['username']) ?></strong></td>
          <td><code><?= e($o['code']) ?></code></td>
          <td><?= number_vn($o['amount_vnd']) ?>đ</td>
          <td><?= number_vn($o['xu']) ?></td>
          <td><?= number_vn($o['bonus_xu']) ?></td>
          <td><?= e($o['method']) ?></td>
          <td><?= e($o['sepay_tx_id'] ?? '') ?></td>
          <td>
            <?php if ($o['status'] === 'completed'): ?>
              <span class="badge badge-success">Hoàn thành</span>
            <?php elseif ($o['status'] === 'pending'): ?>
              <span class="badge badge-warning">Chờ thanh toán</span>
            <?php else: ?>
              <span class="badge badge-danger">Đã huỷ</span>
            <?php endif; ?>
          </td>
          <td><?= e($o['created_at']) ?></td>
          <td><?= e($o['completed_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <?php $link = '/admin/orders?page=' . $i . ($status !== '' ? '&status=' . urlencode($status) : ''); ?>
      <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= url($link) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
