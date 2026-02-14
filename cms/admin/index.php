<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
auth_require();

$totalArticles = articles_count();
$publishedArticles = articles_count('published');
$draftArticles = articles_count('draft');
$categories = categories_list();
$recentArticles = articles_list(5, 0);
?>

<h1>Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $totalArticles ?></div>
        <div class="stat-label">Články celkem</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $publishedArticles ?></div>
        <div class="stat-label">Publikované</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $draftArticles ?></div>
        <div class="stat-label">Koncepty</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($categories) ?></div>
        <div class="stat-label">Kategorie</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Poslední články</h2>
        <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-primary btn-sm">Nový článek</a>
    </div>
    <?php if (empty($recentArticles)): ?>
        <p class="empty-state">Zatím žádné články. <a href="<?= ADMIN_URL ?>/article-edit.php">Vytvořte první!</a></p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Titulek</th>
                    <th>Stav</th>
                    <th>Autor</th>
                    <th>Vytvořeno</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentArticles as $article): ?>
                <tr>
                    <td><a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>"><?= h($article['title']) ?></a></td>
                    <td><span class="badge badge-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>"><?= $article['status'] === 'published' ? 'Publikován' : 'Koncept' ?></span></td>
                    <td><?= h($article['author_name']) ?></td>
                    <td><?= format_date($article['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
