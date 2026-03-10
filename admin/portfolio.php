<?php
$pageTitle = 'Portfolio';

require_once __DIR__ . '/lib/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/portfolio.php';
auth_start_session();
auth_require();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && csrf_verify()) {
    $id = (int) $_POST['delete'];
    portfolio_delete($id);
    flash_set('success', 'Položka portfolia byla smazána.');
    redirect(ADMIN_URL . '/portfolio.php');
}

require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? null;
$page = max(1, (int) ($_GET['page'] ?? 1));

$total = portfolio_count($status);
$pag = paginate($total, 10, $page);
$items = portfolio_list($pag['per_page'], $pag['offset'], $status);
?>

<h1>Portfolio</h1>

<div class="toolbar">
    <div class="toolbar-filters">
        <select onchange="window.location.href='?status='+this.value">
            <option value="">Všechny stavy</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publikované</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Koncepty</option>
        </select>
    </div>
    <a href="<?= ADMIN_URL ?>/portfolio-edit.php" class="btn btn-primary">Nová položka</a>
</div>

<div class="card">
    <?php if (empty($items)): ?>
        <p class="empty-state">Žádné položky portfolia k zobrazení.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Projekt</th>
                    <th>Klient</th>
                    <th>Typ</th>
                    <th>Stav</th>
                    <th>Pořadí</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><a href="<?= ADMIN_URL ?>/portfolio-edit.php?id=<?= $item['id'] ?>"><?= h($item['title']) ?></a></td>
                    <td><?= h($item['client'] ?? '—') ?></td>
                    <td><?= h($item['project_type'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $item['status'] === 'published' ? 'success' : 'warning' ?>"><?= $item['status'] === 'published' ? 'Publikován' : 'Koncept' ?></span></td>
                    <td><?= $item['sort_order'] ?></td>
                    <td>
                        <a href="<?= ADMIN_URL ?>/portfolio-edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-secondary">Upravit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Opravdu smazat \'<?= h($item['title']) ?>\'?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Smazat</button>
                        </form>
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
                    <a href="?page=<?= $i ?>&status=<?= h($status ?? '') ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
