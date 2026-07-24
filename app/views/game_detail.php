<div class="game-hero">
  <?php if ($game['banner']): ?>
    <img class="game-hero-banner" src="<?= e(url($game['banner'])) ?>" alt="<?= e($game['name']) ?>">
  <?php endif; ?>
  <div class="game-hero-info">
    <?php if ($game['thumbnail']): ?><img class="game-hero-thumb" src="<?= e(url($game['thumbnail'])) ?>" alt=""><?php endif; ?>
    <div>
      <h1><?= e($game['name']) ?></h1>
      <p class="muted"><?= e($game['short_desc']) ?></p>
      <div class="btn-row">
        <?php foreach ($downloads as $d): ?>
          <a class="btn btn-primary btn-download" href="<?= e($d['url'] ?? '#') ?>" target="_blank" rel="noopener"><?= download_icon($d['label'] ?? '') ?> <?= e($d['label'] ?? 'Tải game') ?></a>
        <?php endforeach; ?>
        <a class="btn btn-outline" href="<?= url('/nap-xu') ?>"><?= icon('wallet') ?> Nạp xu</a>
        <a class="btn btn-outline" href="<?= url('/doi-xu') ?>"><?= icon('exchange') ?> Đổi xu</a>
        <a class="btn btn-outline" href="<?= url('/bxh?game_id=' . $game['id']) ?>"><?= icon('trophy') ?> Xếp hạng</a>
      </div>
    </div>
  </div>
</div>

<?php if ($servers): ?>
<section class="section">
  <h2 class="section-title"><?= icon('server') ?> Máy chủ</h2>
  <div class="server-chips">
    <?php foreach ($servers as $s): ?>
      <span class="chip"><?= icon('dot') ?> <?= e($s['name']) ?><?= $s['note'] ? ' — ' . e($s['note']) : '' ?></span>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section card">
  <h2 class="section-title">Giới thiệu</h2>
  <div class="content-html"><?= $game['description'] /* HTML do admin nhập */ ?></div>
</section>

<?php if ($news): ?>
<section class="section">
  <h2 class="section-title"><?= icon('news') ?> Tin tức & sự kiện của game</h2>
  <div class="news-list">
    <?php foreach ($news as $n): ?>
      <a class="news-item" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
        <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php endif; ?>
        <div>
          <h4><?= $n['type'] === 'event' ? icon('event') : icon('news') ?> <?= e($n['title']) ?></h4>
          <span class="muted"><?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
