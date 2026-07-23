<div class="card">
  <p class="muted">Tổng: <?= number_vn($total) ?> lượt quy đổi</p>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Game</th><th>Server</th><th>Nhân vật</th><th>Xu</th><th>Nhận</th><th>Trạng thái</th><th>Ghi chú</th><th>Thời gian</th></tr>
      </thead>
      <tbody>
      <?php if (!$exchanges): ?>
        <tr><td colspan="10" class="empty">Chưa có lượt quy đổi nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($exchanges as $ex): ?>
        <tr>
          <td>#<?= (int)$ex['id'] ?></td>
          <td><strong><?= e($ex['username']) ?></strong></td>
          <td><?= e($ex['game_name'] ?? '?') ?></td>
          <td><?= e($ex['server_name'] ?? '?') ?></td>
          <td><?= e($ex['character_name']) ?> <small class="muted">(#<?= e($ex['character_id']) ?>)</small></td>
          <td><?= number_vn($ex['xu_cost']) ?></td>
          <td><?= number_vn($ex['currency_amount']) ?> <code><?= e($ex['currency_key']) ?></code></td>
          <td>
            <?php if ($ex['status'] === 'completed'): ?>
              <span class="badge badge-success">Thành công</span>
            <?php else: ?>
              <span class="badge badge-danger">Thất bại</span>
            <?php endif; ?>
          </td>
          <td><?= e($ex['message'] ?? '') ?></td>
          <td><?= e($ex['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= url('/admin/exchanges?page=' . $i) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
