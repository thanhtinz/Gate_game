<div class="filter-bar">
  <div class="tabs">
    <a class="tab<?= $type === '' ? ' active' : '' ?>" href="<?= url('/tin-tuc') ?>">Tất cả</a>
    <a class="tab<?= $type === 'news' ? ' active' : '' ?>" href="<?= url('/tin-tuc?type=news') ?>">📰 Tin tức</a>
    <a class="tab<?= $type === 'event' ? ' active' : '' ?>" href="<?= url('/tin-tuc?type=event') ?>">🎉 Sự kiện</a>
  </div>
  <form method="get" action="<?= url('/tin-tuc') ?>">
    <?php if ($type): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
    <select name="game_id" onchange="this.form.submit()">
      <option value="">— Tất cả game —</option>
      <?php foreach ($games as $g): ?>
        <option value="<?= $g['id'] ?>"<?= $gameId === (int)$g['id'] ? ' selected' : '' ?>><?= e($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="news-grid">
  <?php if (!$items): ?><p class="muted">Chưa có bài viết nào.</p><?php endif; ?>
  <?php foreach ($items as $n): ?>
    <a class="news-card" href="<?= url('/tin-tuc/' . $n['slug']) ?>">
      <div class="news-card-thumb">
        <?php if ($n['thumbnail']): ?><img src="<?= e(url($n['thumbnail'])) ?>" alt=""><?php else: ?><div class="thumb-placeholder">📰</div><?php endif; ?>
        <span class="badge badge-<?= $n['type'] ?>"><?= $n['type'] === 'event' ? 'Sự kiện' : 'Tin tức' ?></span>
      </div>
      <div class="news-card-body">
        <h3><?= $n['pinned'] ? '📌 ' : '' ?><?= e($n['title']) ?></h3>
        <p class="muted"><?= e(mb_substr((string)$n['summary'], 0, 120)) ?></p>
        <span class="muted small"><?= e($n['game_name'] ?: 'Chung') ?> · <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
<div class="pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="page<?= $i === $page ? ' active' : '' ?>" href="<?= url('/tin-tuc?page=' . $i . ($type ? '&type=' . $type : '') . ($gameId ? '&game_id=' . $gameId : '')) ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
