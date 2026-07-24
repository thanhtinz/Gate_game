<?php if ($banners): ?>
<div class="banner-slider" id="bannerSlider">
  <?php foreach ($banners as $i => $b): ?>
    <a class="banner-slide<?= $i === 0 ? ' active' : '' ?>" href="<?= e($b['link'] ?: '#') ?>">
      <img src="<?= e(url($b['image'])) ?>" alt="<?= e($b['title']) ?>">
    </a>
  <?php endforeach; ?>
  <?php if (count($banners) > 1): ?>
    <div class="banner-dots">
      <?php foreach ($banners as $i => $b): ?><span class="dot<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>"></span><?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="quick-actions">
  <a href="<?= url('/nap-xu') ?>" class="qa-item"><span class="qa-icon"><?= icon('wallet') ?></span><b>Nạp xu</b><span class="muted small">QR ngân hàng, tự động 24/7</span></a>
  <a href="<?= url('/doi-xu') ?>" class="qa-item"><span class="qa-icon"><?= icon('exchange') ?></span><b>Đổi xu</b><span class="muted small">Quy đổi ra tiền tệ game</span></a>
  <a href="<?= url('/giftcode') ?>" class="qa-item"><span class="qa-icon"><?= icon('gift') ?></span><b>Giftcode</b><span class="muted small">Nhập code nhận quà</span></a>
  <a href="<?= url('/bxh') ?>" class="qa-item"><span class="qa-icon"><?= icon('trophy') ?></span><b>Xếp hạng</b><span class="muted small">Đua top mỗi server</span></a>
</div>

<section class="section">
  <h2 class="section-title"><?= icon('gamepad') ?> Danh sách game</h2>
  <div class="game-grid">
    <?php if (!$games): ?><p class="muted">Chưa có game nào.</p><?php endif; ?>
    <?php foreach ($games as $g): ?>
      <a class="game-card" href="<?= url('/game/' . $g['slug']) ?>">
        <div class="game-thumb">
          <?php if ($g['thumbnail']): ?>
            <img src="<?= e(url($g['thumbnail'])) ?>" alt="<?= e($g['name']) ?>">
          <?php else: ?>
            <div class="thumb-placeholder"><?= icon('gamepad', 'ic-lg') ?></div>
          <?php endif; ?>
        </div>
        <div class="game-card-body">
          <h3><?= e($g['name']) ?></h3>
          <p><?= e($g['short_desc']) ?></p>
          <span class="btn btn-sm btn-primary">Chi tiết & Tải game</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<div class="two-cols">
  <section class="section">
    <h2 class="section-title"><?= icon('news') ?> Tin tức <a class="more" href="<?= url('/tin-tuc?type=news') ?>">Xem tất cả <?= icon('arrow-right') ?></a></h2>
    <div class="news-list">
      <?php if (!$news): ?><p class="muted">Chưa có tin tức.</p><?php endif; ?>
      <?php foreach ($news as $n): ?>
        <a class="news-item" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
          <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php endif; ?>
          <div>
            <h4><?php if ($n['pinned']): ?><?= icon('pin') ?> <?php endif; ?><?= e($n['title']) ?></h4>
            <span class="muted"><?= e($n['game_name'] ?: 'Chung') ?> · <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section">
    <h2 class="section-title"><?= icon('event') ?> Sự kiện <a class="more" href="<?= url('/tin-tuc?type=event') ?>">Xem tất cả <?= icon('arrow-right') ?></a></h2>
    <div class="news-list">
      <?php if (!$events): ?><p class="muted">Chưa có sự kiện.</p><?php endif; ?>
      <?php foreach ($events as $n): ?>
        <a class="news-item" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
          <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php endif; ?>
          <div>
            <h4><?php if ($n['pinned']): ?><?= icon('pin') ?> <?php endif; ?><?= e($n['title']) ?></h4>
            <span class="muted"><?= e($n['game_name'] ?: 'Chung') ?> · <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
