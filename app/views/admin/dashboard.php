<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Người dùng</div>
    <div class="stat-value"><?= number_vn($stats['users']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Xu đang lưu hành</div>
    <div class="stat-value"><?= number_vn($stats['xu_total']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Doanh thu hôm nay</div>
    <div class="stat-value"><?= number_vn($stats['revenue_today']) ?>đ</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Tổng doanh thu</div>
    <div class="stat-value"><?= number_vn($stats['revenue_total']) ?>đ</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Đơn chờ thanh toán</div>
    <div class="stat-value"><?= number_vn($stats['orders_pending']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Game / Server</div>
    <div class="stat-value"><?= number_vn($stats['games']) ?> / <?= number_vn($stats['servers']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Tin tức</div>
    <div class="stat-value"><?= number_vn($stats['news']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Giftcode</div>
    <div class="stat-value"><?= number_vn($stats['giftcodes']) ?></div>
  </div>
</div>

<div class="card">
  <h2 class="card-title">10 đơn nạp thành công gần nhất</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Mã đơn</th><th>Số tiền</th><th>Xu</th><th>Bonus</th><th>Hoàn thành lúc</th></tr>
      </thead>
      <tbody>
      <?php if (!$latest_orders): ?>
        <tr><td colspan="7" class="empty">Chưa có đơn nạp nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($latest_orders as $o): ?>
        <tr>
          <td>#<?= (int)$o['id'] ?></td>
          <td><?= e($o['username']) ?></td>
          <td><code><?= e($o['code']) ?></code></td>
          <td><?= number_vn($o['amount_vnd']) ?>đ</td>
          <td><?= number_vn($o['xu']) ?></td>
          <td><?= number_vn($o['bonus_xu']) ?></td>
          <td><?= e($o['completed_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2 class="card-title">10 lượt quy đổi gần nhất</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Tài khoản</th><th>Game / Server</th><th>Nhân vật</th><th>Xu</th><th>Nhận</th><th>Trạng thái</th><th>Thời gian</th></tr>
      </thead>
      <tbody>
      <?php if (!$latest_exchanges): ?>
        <tr><td colspan="8" class="empty">Chưa có lượt quy đổi nào.</td></tr>
      <?php endif; ?>
      <?php foreach ($latest_exchanges as $ex): ?>
        <tr>
          <td>#<?= (int)$ex['id'] ?></td>
          <td><?= e($ex['username']) ?></td>
          <td><?= e($ex['game_name'] ?? '?') ?> / <?= e($ex['server_name'] ?? '?') ?></td>
          <td><?= e($ex['character_name']) ?></td>
          <td><?= number_vn($ex['xu_cost']) ?></td>
          <td><?= number_vn($ex['currency_amount']) ?> <?= e($ex['currency_key']) ?></td>
          <td>
            <?php if ($ex['status'] === 'completed'): ?>
              <span class="badge badge-success">Thành công</span>
            <?php else: ?>
              <span class="badge badge-danger">Thất bại</span>
            <?php endif; ?>
          </td>
          <td><?= e($ex['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
