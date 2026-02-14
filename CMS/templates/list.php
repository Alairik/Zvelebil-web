<?php if (isset($category)): ?>
    <h1 style="margin-bottom: 1.5rem;">Kategorie: <?= h($category['name']) ?></h1>
<?php elseif (isset($tag)): ?>
    <h1 style="margin-bottom: 1.5rem;">Tag: <?= h($tag['name']) ?></h1>
<?php endif; ?>

<?php if (empty($articles)): ?>
    <div class="article-card">
        <div class="article-card-body" style="text-align: center; padding: 3rem;">
            <p>Zatím zde nejsou žádné články.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
    <article class="article-card">
        <?php if (!empty($article['featured_image'])): ?>
            <a href="<?= SITE_URL ?>/clanek/<?= h($article['slug']) ?>">
                <img src="<?= UPLOADS_URL . '/' . h($article['featured_image']) ?>" alt="<?= h($article['title']) ?>" class="article-card-image">
            </a>
        <?php endif; ?>
        <div class="article-card-body">
            <h2><a href="<?= SITE_URL ?>/clanek/<?= h($article['slug']) ?>"><?= h($article['title']) ?></a></h2>
            <div class="article-meta">
                <span><?= format_date($article['created_at']) ?></span>
                <span><?= h($article['author_name']) ?></span>
                <?php if ($article['category_name']): ?>
                    <span><a href="<?= SITE_URL ?>/kategorie/<?= h($article['slug']) ?>"><?= h($article['category_name']) ?></a></span>
                <?php endif; ?>
            </div>
            <p class="article-excerpt"><?= h($article['excerpt'] ?: excerpt($article['content'])) ?></p>
            <p style="margin-top: .8rem;"><a href="<?= SITE_URL ?>/clanek/<?= h($article['slug']) ?>">Číst dále &rarr;</a></p>
        </div>
    </article>
    <?php endforeach; ?>

    <?php if ($pag['total_pages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
            <?php if ($i === $pag['current_page']): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/stranka/<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
