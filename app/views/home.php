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
  <a href="<?= url('/nap-xu') ?>" class="qa-item"><span class="qa-icon"><?= icon('wallet') ?></span><b>Nạp xu</b></a>
  <a href="<?= url('/doi-xu') ?>" class="qa-item"><span class="qa-icon"><?= icon('exchange') ?></span><b>Đổi xu</b></a>
  <a href="<?= url('/giftcode') ?>" class="qa-item"><span class="qa-icon"><?= icon('gift') ?></span><b>Giftcode</b></a>
  <a href="<?= url('/bxh') ?>" class="qa-item"><span class="qa-icon"><?= icon('trophy') ?></span><b>Xếp hạng</b></a>
</div>

<section class="section">
  <h2 class="section-title"><?= icon('gamepad') ?> Danh sách game</h2>
  <div class="game-list">
    <?php if (!$games): ?><p class="muted">Chưa có game nào.</p><?php endif; ?>
    <?php foreach ($games as $g): ?>
      <a class="game-row" href="<?= url('/game/' . $g['slug']) ?>">
        <span class="game-icon">
          <?php if ($g['thumbnail']): ?>
            <img src="<?= e(url($g['thumbnail'])) ?>" alt="<?= e($g['name']) ?>">
          <?php else: ?>
            <?= icon('gamepad', 'ic-lg') ?>
          <?php endif; ?>
        </span>
        <span class="game-row-info">
          <b><?= e($g['name']) ?></b>
          <span class="game-row-desc"><?= e($g['short_desc']) ?></span>
        </span>
        <span class="btn btn-sm btn-primary game-row-btn"><?= icon('download') ?> Tải</span>
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
