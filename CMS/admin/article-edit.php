<?php
$pageTitle = 'Úprava článku';
require_once __DIR__ . '/includes/header.php';
auth_require();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = $id ? article_get($id) : null;
$allCategories = categories_list();
$allTags = tags_list();
$articleTags = $id ? array_column(article_get_tags($id), 'id') : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Neplatný bezpečnostní token.');
        redirect(ADMIN_URL . '/article-edit.php' . ($id ? "?id={$id}" : ''));
    }

    $data = [
        'id' => $id,
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null,
        'status' => $_POST['status'] ?? 'draft',
        'author_id' => $article ? $article['author_id'] : $currentUser['id'],
        'tags' => $_POST['tags'] ?? [],
        'featured_image' => $article['featured_image'] ?? null,
    ];

    if (empty($data['slug'])) {
        $data['slug'] = slug($data['title']);
    }

    // Handle image upload
    if (!empty($_FILES['featured_image']['name'])) {
        $uploaded = upload_image($_FILES['featured_image']);
        if ($uploaded) {
            $data['featured_image'] = $uploaded;
        } else {
            flash_set('error', 'Obrázek se nepodařilo nahrát. Povolené formáty: JPG, PNG, GIF, WebP. Max 5 MB.');
        }
    }

    // Remove image
    if (isset($_POST['remove_image'])) {
        $data['featured_image'] = null;
    }

    if (empty($data['title'])) {
        flash_set('error', 'Titulek je povinný.');
    } else {
        $savedId = article_save($data);
        flash_set('success', $id ? 'Článek byl upraven.' : 'Článek byl vytvořen.');
        redirect(ADMIN_URL . '/article-edit.php?id=' . $savedId);
    }
}

// Reload after potential save
if ($id && !$article) {
    flash_set('error', 'Článek nebyl nalezen.');
    redirect(ADMIN_URL . '/articles.php');
}
?>

<h1><?= $id ? 'Upravit článek' : 'Nový článek' ?></h1>

<form method="post" enctype="multipart/form-data" class="card">
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="title">Titulek</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= h($article['title'] ?? $_POST['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">URL slug</label>
            <input type="text" id="slug" name="slug" class="form-control" value="<?= h($article['slug'] ?? $_POST['slug'] ?? '') ?>">
            <div class="form-hint">Ponechte prázdné pro automatické vygenerování</div>
        </div>
    </div>

    <div class="form-group">
        <label for="content">Obsah</label>
        <div class="editor-tabs">
            <button type="button" class="editor-tab active" data-tab="write">Psát</button>
            <button type="button" class="editor-tab" data-tab="preview">Náhled</button>
        </div>
        <div id="pane-write" class="editor-pane active">
            <textarea id="content" name="content" class="form-control" style="min-height: 400px; border-radius: 0 0 4px 4px;"><?= h($article['content'] ?? $_POST['content'] ?? '') ?></textarea>
        </div>
        <div id="pane-preview" class="editor-pane">
            <div id="markdown-preview" class="markdown-preview"></div>
        </div>
        <div class="form-hint">Lze použít Markdown nebo HTML.</div>
    </div>

    <div class="form-group">
        <label for="excerpt">Výtah (volitelný)</label>
        <textarea id="excerpt" name="excerpt" class="form-control" rows="3"><?= h($article['excerpt'] ?? $_POST['excerpt'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="category_id">Kategorie</label>
            <select id="category_id" name="category_id" class="form-control">
                <option value="">— Bez kategorie —</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($article['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Stav</label>
            <select id="status" name="status" class="form-control">
                <option value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Koncept</option>
                <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publikovaný</option>
            </select>
        </div>
    </div>

    <?php if (!empty($allTags)): ?>
    <div class="form-group">
        <label>Tagy</label>
        <div class="checkbox-group">
            <?php foreach ($allTags as $tag): ?>
                <label>
                    <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $articleTags) ? 'checked' : '' ?>>
                    <span><?= h($tag['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="featured_image">Hlavní obrázek</label>
        <?php if (!empty($article['featured_image'])): ?>
            <div class="current-image">
                <img src="<?= UPLOADS_URL . '/' . h($article['featured_image']) ?>" alt="">
                <label><input type="checkbox" name="remove_image" value="1"> Odstranit obrázek</label>
            </div>
        <?php endif; ?>
        <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
        <img id="image-preview" class="image-preview" style="display:none" alt="Náhled">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Uložit</button>
        <a href="<?= ADMIN_URL ?>/articles.php" class="btn btn-secondary">Zpět na seznam</a>
        <?php if ($id && ($article['status'] ?? '') === 'published'): ?>
            <a href="<?= SITE_URL ?>/clanek/<?= h($article['slug']) ?>" class="btn btn-success" target="_blank">Zobrazit na webu</a>
        <?php endif; ?>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
