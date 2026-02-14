<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/articles.php';
require_once INCLUDES_PATH . '/categories.php';

auth_start_session();

$currentUser = auth_user();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Administrace') ?> — <?= h(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/style.css">
</head>
<body>
<?php if ($currentUser): ?>
<div class="admin-layout">
    <nav class="sidebar">
        <div class="sidebar-brand">
            <a href="<?= ADMIN_URL ?>/"><?= h(SITE_NAME) ?></a>
        </div>
        <ul class="sidebar-nav">
            <li class="<?= $currentPage === 'index' ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>/">Dashboard</a>
            </li>
            <li class="<?= in_array($currentPage, ['articles', 'article-edit']) ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>/articles.php">Články</a>
            </li>
            <li class="<?= $currentPage === 'categories' ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>/categories.php">Kategorie</a>
            </li>
            <li class="<?= $currentPage === 'tags' ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>/tags.php">Tagy</a>
            </li>
            <?php if (auth_is_admin()): ?>
            <li class="<?= $currentPage === 'users' ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>/users.php">Uživatelé</a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <span><?= h($currentUser['username']) ?> (<?= h($currentUser['role']) ?>)</span>
            <a href="<?= ADMIN_URL ?>/logout.php">Odhlásit</a>
            <a href="<?= SITE_URL ?>/" target="_blank">Web</a>
        </div>
    </nav>
    <main class="content">
        <?php foreach (flash_get() as $flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endforeach; ?>
<?php endif; ?>
