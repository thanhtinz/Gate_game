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

<section class="section">
  <h2 class="section-title">🎮 Danh sách game</h2>
  <div class="game-grid">
    <?php if (!$games): ?><p class="muted">Chưa có game nào.</p><?php endif; ?>
    <?php foreach ($games as $g): ?>
      <a class="game-card" href="<?= url('/game/' . $g['slug']) ?>">
        <div class="game-thumb">
          <?php if ($g['thumbnail']): ?>
            <img src="<?= e(url($g['thumbnail'])) ?>" alt="<?= e($g['name']) ?>">
          <?php else: ?>
            <div class="thumb-placeholder">🎮</div>
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
    <h2 class="section-title">📰 Tin tức <a class="more" href="<?= url('/tin-tuc?type=news') ?>">Xem tất cả →</a></h2>
    <div class="news-list">
      <?php if (!$news): ?><p class="muted">Chưa có tin tức.</p><?php endif; ?>
      <?php foreach ($news as $n): ?>
        <a class="news-item" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
          <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php endif; ?>
          <div>
            <h4><?= $n['pinned'] ? '📌 ' : '' ?><?= e($n['title']) ?></h4>
            <span class="muted"><?= e($n['game_name'] ?: 'Chung') ?> · <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section">
    <h2 class="section-title">🎉 Sự kiện <a class="more" href="<?= url('/tin-tuc?type=event') ?>">Xem tất cả →</a></h2>
    <div class="news-list">
      <?php if (!$events): ?><p class="muted">Chưa có sự kiện.</p><?php endif; ?>
      <?php foreach ($events as $n): ?>
        <a class="news-item" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
          <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php endif; ?>
          <div>
            <h4><?= $n['pinned'] ? '📌 ' : '' ?><?= e($n['title']) ?></h4>
            <span class="muted"><?= e($n['game_name'] ?: 'Chung') ?> · <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
