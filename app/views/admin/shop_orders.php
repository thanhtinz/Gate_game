<div class="card">
  <p class="muted">Tổng: <?= number_vn($total) ?> lượt mua</p>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Game</th><th>Server</th><th>Nhân vật</th><th>Sản phẩm</th><th>SL</th><th>Xu</th><th>Trạng thái</th><th>Ghi chú</th><th>Thời gian</th></tr>
      </thead>
      <tbody>
      <?php if (!$orders): ?>
        <tr><td colspan="11" class="empty">Chưa có lượt mua nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int)$o['id'] ?></td>
          <td><strong><?= e($o['username']) ?></strong></td>
          <td><?= e($o['game_name'] ?? '?') ?></td>
          <td><?= e($o['server_name'] ?? '?') ?></td>
          <td><?= e($o['character_name']) ?> <small class="muted">(#<?= e($o['character_id']) ?>)</small></td>
          <td><?= e($o['product_name']) ?> <code>#<?= (int)$o['item_id'] ?></code></td>
          <td><?= number_vn($o['item_quantity']) ?></td>
          <td><?= number_vn($o['xu_cost']) ?></td>
          <td>
            <?php if ($o['status'] === 'completed'): ?>
              <span class="badge badge-success">Thành công</span>
            <?php else: ?>
              <span class="badge badge-danger">Thất bại</span>
            <?php endif; ?>
          </td>
          <td><?= e($o['message'] ?? '') ?></td>
          <td><?= e($o['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= url('/admin/shop-orders?page=' . $i) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
