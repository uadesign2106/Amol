<?php $cfg = app_config(); ?>
</main>
<a class="float-whatsapp" href="https://wa.me/<?= e($cfg['admin_whatsapp']) ?>?text=Hello%20Green%20Plus" target="_blank" rel="noopener">WhatsApp</a>
<footer class="site-footer">
    <div class="container">
        <p>© <?= date('Y') ?> Green Plus | <?= e($cfg['domain']) ?></p>
    </div>
</footer>
<script src="<?= site_url('assets/js/main.js') ?>"></script>
</body>
</html>
