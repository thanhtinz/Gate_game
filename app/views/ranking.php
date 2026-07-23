<h1 class="page-heading">🏆 Bảng xếp hạng</h1>

<form method="get" action="<?= url('/bxh') ?>" class="filter-bar">
  <select name="game_id" onchange="this.form.server_id.value='';this.form.submit()">
    <?php foreach ($games as $g): ?>
      <option value="<?= $g['id'] ?>"<?= $gameId === (int)$g['id'] ? ' selected' : '' ?>><?= e($g['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="server_id" onchange="this.form.submit()">
    <?php foreach ($servers as $s): ?>
      <option value="<?= $s['id'] ?>"<?= $serverId === (int)$s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <noscript><button class="btn btn-sm" type="submit">Xem</button></noscript>
</form>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php elseif ($ranking && $ranking['rows']): ?>
<div class="card table-wrap">
  <table class="data-table ranking-table">
    <tr>
      <th>#</th>
      <?php foreach ($ranking['columns'] as $c): ?><th><?= e($c) ?></th><?php endforeach; ?>
    </tr>
    <?php foreach ($ranking['rows'] as $i => $row): ?>
    <tr class="<?= $i < 3 ? 'top-' . ($i + 1) : '' ?>">
      <td><?= $i < 3 ? ['🥇', '🥈', '🥉'][$i] : $i + 1 ?></td>
      <?php foreach ($row as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php else: ?>
  <p class="muted">Chưa có dữ liệu xếp hạng.</p>
<?php endif; ?>
