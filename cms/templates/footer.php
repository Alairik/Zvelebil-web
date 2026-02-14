</div><!-- /.main-content -->

<aside class="sidebar">
    <div class="widget">
        <h3>Kategorie</h3>
        <ul>
            <?php foreach ($allCategories as $cat): ?>
                <li><a href="<?= SITE_URL ?>/kategorie/<?= h($cat['slug']) ?>"><?= h($cat['name']) ?> (<?= $cat['article_count'] ?>)</a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if (!empty($allTags)): ?>
    <div class="widget">
        <h3>Tagy</h3>
        <div class="tag-cloud">
            <?php foreach ($allTags as $t): ?>
                <a href="<?= SITE_URL ?>/tag/<?= h($t['slug']) ?>" class="tag-badge"><?= h($t['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</aside>

</div><!-- /.main-layout -->
</div><!-- /.container -->

<footer class="site-footer">
    <div class="container">
        &copy; <?= date('Y') ?> <?= h(SITE_NAME) ?>
    </div>
</footer>

</body>
</html>
