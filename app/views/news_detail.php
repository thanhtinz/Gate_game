<article class="card article">
  <h1><?= e($item['title']) ?></h1>
  <p class="muted">
    <?= $item['type'] === 'event' ? '🎉 Sự kiện' : '📰 Tin tức' ?>
    · <?= e($item['game_name'] ?: 'Chung') ?>
    · <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
  </p>
  <?php if ($item['thumbnail']): ?><img class="article-thumb" src="<?= e(url($item['thumbnail'])) ?>" alt=""><?php endif; ?>
  <div class="content-html"><?= $item['content'] /* HTML do admin nhập */ ?></div>
</article>

<?php if ($related): ?>
<section class="section">
  <h2 class="section-title">Bài viết liên quan</h2>
  <ul class="related-list">
    <?php foreach ($related as $r): ?>
      <li><a href="<?= url('/tin-tuc/' . $r['slug']) ?>"><?= e($r['title']) ?></a> <span class="muted small"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>
