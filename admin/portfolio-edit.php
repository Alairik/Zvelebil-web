<?php
$pageTitle = 'Úprava portfolia';

require_once __DIR__ . '/lib/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/portfolio.php';
auth_start_session();
auth_require();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = $id ? portfolio_get($id) : null;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Neplatný bezpečnostní token.');
        redirect(ADMIN_URL . '/portfolio-edit.php' . ($id ? "?id={$id}" : ''));
    }

    $data = [
        'id' => $id,
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'client' => trim($_POST['client'] ?? ''),
        'project_type' => trim($_POST['project_type'] ?? ''),
        'project_url' => trim($_POST['project_url'] ?? ''),
        'github_url' => trim($_POST['github_url'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'thumbnail' => $item['thumbnail'] ?? null,
    ];

    if (empty($data['slug'])) {
        $data['slug'] = slug($data['title']);
    }

    // Handle image upload
    if (!empty($_FILES['thumbnail']['name'])) {
        $uploaded = upload_image($_FILES['thumbnail']);
        if ($uploaded) {
            $data['thumbnail'] = $uploaded;
        } else {
            flash_set('error', 'Obrázek se nepodařilo nahrát.');
        }
    }

    // Remove image
    if (isset($_POST['remove_image'])) {
        $data['thumbnail'] = null;
    }

    if (empty($data['title'])) {
        flash_set('error', 'Název projektu je povinný.');
    } else {
        portfolio_save($data);
        flash_set('success', $id ? 'Položka byla upravena.' : 'Položka byla vytvořena.');
        redirect(ADMIN_URL . '/portfolio.php');
    }
}

if ($id && !$item) {
    flash_set('error', 'Položka nebyla nalezena.');
    redirect(ADMIN_URL . '/portfolio.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<h1><?= $id ? 'Upravit položku portfolia' : 'Nová položka portfolia' ?></h1>

<form method="post" enctype="multipart/form-data" class="card">
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="title">Název projektu</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= h($item['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">URL slug</label>
            <input type="text" id="slug" name="slug" class="form-control" value="<?= h($item['slug'] ?? '') ?>">
            <div class="form-hint">Ponechte prázdné pro automatické vygenerování</div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="client">Klient</label>
            <input type="text" id="client" name="client" class="form-control" value="<?= h($item['client'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="project_type">Typ projektu</label>
            <select id="project_type" name="project_type" class="form-control">
                <option value="">— Vyberte —</option>
                <option value="web" <?= ($item['project_type'] ?? '') === 'web' ? 'selected' : '' ?>>Web</option>
                <option value="logo" <?= ($item['project_type'] ?? '') === 'logo' ? 'selected' : '' ?>>Logo & Branding</option>
                <option value="marketing" <?= ($item['project_type'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                <option value="eshop" <?= ($item['project_type'] ?? '') === 'eshop' ? 'selected' : '' ?>>E-shop</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Popis</label>
        <textarea id="description" name="description" class="form-control" rows="4"><?= h($item['description'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="project_url">URL projektu</label>
            <input type="url" id="project_url" name="project_url" class="form-control" value="<?= h($item['project_url'] ?? '') ?>" placeholder="https://...">
            <div class="form-hint">Odkaz na živý web (pokud existuje)</div>
        </div>
        <div class="form-group">
            <label for="github_url">GitHub URL</label>
            <input type="url" id="github_url" name="github_url" class="form-control" value="<?= h($item['github_url'] ?? '') ?>" placeholder="https://github.com/...">
            <div class="form-hint">Odkaz na repozitář (volitelné)</div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="status">Stav</label>
            <select id="status" name="status" class="form-control">
                <option value="draft" <?= ($item['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Koncept</option>
                <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publikovaný</option>
            </select>
        </div>
        <div class="form-group">
            <label for="sort_order">Pořadí</label>
            <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?= h($item['sort_order'] ?? 0) ?>" min="0">
            <div class="form-hint">Nižší číslo = zobrazí se dříve</div>
        </div>
    </div>

    <div class="form-group">
        <label for="thumbnail">Náhledový obrázek</label>
        <?php if (!empty($item['thumbnail'])): ?>
            <div class="current-image">
                <img src="<?= UPLOADS_URL . '/' . h($item['thumbnail']) ?>" alt="">
                <label><input type="checkbox" name="remove_image" value="1"> Odstranit obrázek</label>
            </div>
        <?php endif; ?>
        <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
        <img id="image-preview" class="image-preview" style="display:none" alt="Náhled">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Uložit</button>
        <a href="<?= ADMIN_URL ?>/portfolio.php" class="btn btn-secondary">Zpět na seznam</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
