<?php
$pageTitle = 'Kategorie';
require_once __DIR__ . '/includes/header.php';
auth_require();

// Handle delete
if (isset($_GET['delete']) && csrf_verify()) {
    category_delete((int) $_GET['delete']);
    flash_set('success', 'Kategorie byla smazána.');
    redirect(ADMIN_URL . '/categories.php');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Neplatný bezpečnostní token.');
    } else {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            flash_set('error', 'Název kategorie je povinný.');
        } else {
            category_save([
                'id' => (int) ($_POST['id'] ?? 0),
                'name' => $name,
                'slug' => slug($name),
            ]);
            flash_set('success', 'Kategorie byla uložena.');
            redirect(ADMIN_URL . '/categories.php');
        }
    }
}

$editCat = isset($_GET['edit']) ? category_get((int) $_GET['edit']) : null;
$categories = categories_list();
?>

<h1>Kategorie</h1>

<div class="card">
    <h2><?= $editCat ? 'Upravit kategorii' : 'Nová kategorie' ?></h2>
    <form method="post" style="display: flex; gap: .5rem; align-items: end; flex-wrap: wrap;">
        <?= csrf_field() ?>
        <?php if ($editCat): ?>
            <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
        <?php endif; ?>
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="name">Název</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= h($editCat['name'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editCat ? 'Upravit' : 'Přidat' ?></button>
        <?php if ($editCat): ?>
            <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-secondary">Zrušit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <?php if (empty($categories)): ?>
        <p class="empty-state">Žádné kategorie.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Slug</th>
                    <th>Články</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= h($cat['name']) ?></td>
                    <td><code><?= h($cat['slug']) ?></code></td>
                    <td><?= $cat['article_count'] ?></td>
                    <td>
                        <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary">Upravit</a>
                        <a href="?delete=<?= $cat['id'] ?>&csrf_token=<?= h(csrf_token()) ?>"
                           class="btn btn-sm btn-danger"
                           data-confirm="Smazat kategorii '<?= h($cat['name']) ?>'? Články budou bez kategorie.">Smazat</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
