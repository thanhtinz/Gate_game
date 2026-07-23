</main>
<footer class="site-footer">
  <div class="container">
    <p>© <?= date('Y') ?> <?= e(Settings::get('site_name', 'Gate Game')) ?>. <?= e(Settings::get('site_desc')) ?></p>
  </div>
</footer>
<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
