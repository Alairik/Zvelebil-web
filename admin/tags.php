<?php
$pageTitle = 'Tagy';
require_once __DIR__ . '/includes/header.php';
auth_require();

// Handle delete
if (isset($_GET['delete']) && csrf_verify()) {
    tag_delete((int) $_GET['delete']);
    flash_set('success', 'Tag byl smazán.');
    redirect(ADMIN_URL . '/tags.php');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Neplatný bezpečnostní token.');
    } else {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            flash_set('error', 'Název tagu je povinný.');
        } else {
            tag_save([
                'id' => (int) ($_POST['id'] ?? 0),
                'name' => $name,
                'slug' => slug($name),
            ]);
            flash_set('success', 'Tag byl uložen.');
            redirect(ADMIN_URL . '/tags.php');
        }
    }
}

$editTag = isset($_GET['edit']) ? tag_get((int) $_GET['edit']) : null;
$tags = tags_list();
?>

<h1>Tagy</h1>

<div class="card">
    <h2><?= $editTag ? 'Upravit tag' : 'Nový tag' ?></h2>
    <form method="post" style="display: flex; gap: .5rem; align-items: end; flex-wrap: wrap;">
        <?= csrf_field() ?>
        <?php if ($editTag): ?>
            <input type="hidden" name="id" value="<?= $editTag['id'] ?>">
        <?php endif; ?>
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="name">Název</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= h($editTag['name'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editTag ? 'Upravit' : 'Přidat' ?></button>
        <?php if ($editTag): ?>
            <a href="<?= ADMIN_URL ?>/tags.php" class="btn btn-secondary">Zrušit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <?php if (empty($tags)): ?>
        <p class="empty-state">Žádné tagy.</p>
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
                <?php foreach ($tags as $tag): ?>
                <tr>
                    <td><?= h($tag['name']) ?></td>
                    <td><code><?= h($tag['slug']) ?></code></td>
                    <td><?= $tag['article_count'] ?></td>
                    <td>
                        <a href="?edit=<?= $tag['id'] ?>" class="btn btn-sm btn-secondary">Upravit</a>
                        <a href="?delete=<?= $tag['id'] ?>&csrf_token=<?= h(csrf_token()) ?>"
                           class="btn btn-sm btn-danger"
                           data-confirm="Smazat tag '<?= h($tag['name']) ?>'?">Smazat</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
