<article class="article-full">
    <h1><?= h($article['title']) ?></h1>
    <div class="article-meta">
        <span><?= format_date($article['created_at']) ?></span>
        <span><?= h($article['author_name']) ?></span>
        <?php if ($article['category_name']): ?>
            <span>Kategorie: <a href="<?= SITE_URL ?>/kategorie/<?= h($article['slug']) ?>"><?= h($article['category_name']) ?></a></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($article['featured_image'])): ?>
        <img src="<?= UPLOADS_URL . '/' . h($article['featured_image']) ?>" alt="<?= h($article['title']) ?>" class="featured-img">
    <?php endif; ?>

    <div class="article-content">
        <?= $article['content'] ?>
    </div>

    <?php if (!empty($articleTags)): ?>
    <div class="article-tags">
        <?php foreach ($articleTags as $t): ?>
            <a href="<?= SITE_URL ?>/tag/<?= h($t['slug']) ?>" class="tag-badge"><?= h($t['name']) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</article>
