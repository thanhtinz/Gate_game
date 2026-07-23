<form method="post" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>

  <div class="card">
    <h2 class="card-title">Thông tin website</h2>

    <div class="form-row">
      <div class="form-group">
        <label>Tên website</label>
        <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Mô tả (SEO)</label>
        <input type="text" name="site_desc" value="<?= e($settings['site_desc'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Từ khoá (SEO)</label>
      <input type="text" name="site_keywords" value="<?= e($settings['site_keywords'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Logo (upload)</label>
      <input type="file" name="site_logo_file" accept="image/*">
      <label class="sub-label">hoặc URL logo (để trống cả 2 = giữ nguyên)</label>
      <input type="text" name="site_logo_url" placeholder="/uploads/... hoặc https://...">
      <?php if (!empty($settings['site_logo'])): ?><img class="img-preview" src="<?= e($settings['site_logo']) ?>" alt="Logo hiện tại"><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2 class="card-title">Thanh toán SePay</h2>

    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="sepay_enabled" value="1" <?= ($settings['sepay_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
        Bật nạp xu tự động qua SePay
      </label>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Ngân hàng</label>
        <input type="text" name="sepay_bank" value="<?= e($settings['sepay_bank'] ?? '') ?>" placeholder="MBBank">
      </div>
      <div class="form-group">
        <label>Số tài khoản</label>
        <input type="text" name="sepay_account" value="<?= e($settings['sepay_account'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Chủ tài khoản</label>
        <input type="text" name="sepay_account_name" value="<?= e($settings['sepay_account_name'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tiền tố mã đơn</label>
        <input type="text" name="sepay_prefix" value="<?= e($settings['sepay_prefix'] ?? 'GATE') ?>" placeholder="GATE">
      </div>
      <div class="form-group">
        <label>API Key (xác thực webhook)</label>
        <input type="text" name="sepay_api_key" value="<?= e($settings['sepay_api_key'] ?? '') ?>" autocomplete="off">
      </div>
    </div>

    <div class="alert alert-info">
      <strong>Webhook URL:</strong>
      <code><?= e((isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']) : '') . url('/api/sepay-webhook')) ?></code><br>
      Vào SePay Dashboard → Webhooks → thêm URL trên và điền API Key trùng với ô "API Key" ở đây để hệ thống tự cộng xu khi nhận được tiền.
    </div>
  </div>

  <div class="card">
    <h2 class="card-title">🔐 Auth tập trung cho game server</h2>
    <div class="form-group">
      <label>Central Auth Key (game server gọi API xác thực bằng key này)</label>
      <input type="text" name="central_auth_key" value="<?= e($settings['central_auth_key'] ?? '') ?>" autocomplete="off" placeholder="Chuỗi ngẫu nhiên 32+ ký tự">
    </div>
    <div class="alert alert-info">
      <strong>API URL:</strong>
      <code><?= e((isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']) : '') . url('/api/game-auth/verify')) ?></code><br>
      Điền URL này + key trên vào <code>Config.properties</code> (NRO) và <code>config.properties</code> (Avatar) của từng game server
      (<code>auth.enabled / auth.url / auth.key</code>). Để trống key = tắt API (game server tự fallback về đăng nhập cũ).
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
  </div>
</form>
