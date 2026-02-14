<?php
$pageTitle = 'Články';
require_once __DIR__ . '/includes/header.php';
auth_require();

// Handle delete
if (isset($_GET['delete']) && csrf_verify()) {
    $id = (int) $_GET['delete'];
    article_delete($id);
    flash_set('success', 'Článek byl smazán.');
    redirect(ADMIN_URL . '/articles.php');
}

// Filters
$status = $_GET['status'] ?? null;
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$page = max(1, (int) ($_GET['page'] ?? 1));

$total = articles_count($status, $categoryId);
$pag = paginate($total, ARTICLES_PER_PAGE, $page);
$articles = articles_list($pag['per_page'], $pag['offset'], $status, $categoryId);
$allCategories = categories_list();
?>

<h1>Články</h1>

<div class="toolbar">
    <div class="toolbar-filters">
        <select onchange="window.location.href='?status='+this.value+'&category=<?= $categoryId ?>'">
            <option value="">Všechny stavy</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publikované</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Koncepty</option>
        </select>
        <select onchange="window.location.href='?status=<?= h($status ?? '') ?>&category='+this.value">
            <option value="">Všechny kategorie</option>
            <?php foreach ($allCategories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-primary">Nový článek</a>
</div>

<div class="card">
    <?php if (empty($articles)): ?>
        <p class="empty-state">Žádné články k zobrazení.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Titulek</th>
                    <th>Kategorie</th>
                    <th>Stav</th>
                    <th>Autor</th>
                    <th>Datum</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td><a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>"><?= h($article['title']) ?></a></td>
                    <td><?= h($article['category_name'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>"><?= $article['status'] === 'published' ? 'Publikován' : 'Koncept' ?></span></td>
                    <td><?= h($article['author_name']) ?></td>
                    <td><?= format_date($article['created_at']) ?></td>
                    <td>
                        <a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-secondary">Upravit</a>
                        <a href="<?= ADMIN_URL ?>/articles.php?delete=<?= $article['id'] ?>&csrf_token=<?= h(csrf_token()) ?>"
                           class="btn btn-sm btn-danger"
                           data-confirm="Opravdu smazat článek '<?= h($article['title']) ?>'?">Smazat</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pag['total_pages'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
                <?php if ($i === $pag['current_page']): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&status=<?= h($status ?? '') ?>&category=<?= $categoryId ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
