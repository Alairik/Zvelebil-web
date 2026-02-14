<?php
/**
 * CMS Installation Script
 * Run this once to set up the database, then DELETE this file!
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminEmail = trim($_POST['admin_email'] ?? '');

    if (strlen($adminUser) < 3) $errors[] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
    if (strlen($adminPass) < 8) $errors[] = 'Heslo musí mít alespoň 8 znaků.';
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Zadejte platný e-mail.';

    if (empty($errors)) {
        try {
            $db = db_connect();

            // Create tables
            $db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
                    active TINYINT(1) NOT NULL DEFAULT 1,
                    last_login DATETIME NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS tags (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS articles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL UNIQUE,
                    content LONGTEXT NOT NULL,
                    excerpt TEXT NULL,
                    featured_image VARCHAR(255) NULL,
                    category_id INT NULL,
                    author_id INT NOT NULL,
                    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                    FOREIGN KEY (author_id) REFERENCES users(id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS article_tags (
                    article_id INT NOT NULL,
                    tag_id INT NOT NULL,
                    PRIMARY KEY (article_id, tag_id),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Create admin user
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$adminUser, $adminEmail, $hash, 'admin']);

            // Create default category
            $db->exec("INSERT INTO categories (name, slug) VALUES ('Obecné', 'obecne')");

            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Chyba databáze: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalace CMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); max-width: 500px; width: 100%; }
        h1 { margin-bottom: 1.5rem; color: #1a1a2e; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .3rem; font-weight: 600; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: .6rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        button { background: #4361ee; color: #fff; border: none; padding: .7rem 2rem; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #3a56d4; }
        .error { background: #fee; border: 1px solid #fcc; color: #c33; padding: .8rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { background: #efe; border: 1px solid #cfc; color: #363; padding: .8rem; border-radius: 4px; margin-bottom: 1rem; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: .8rem; border-radius: 4px; margin-top: 1rem; font-size: .9rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>Instalace CMS</h1>

    <?php if ($success): ?>
        <div class="success">
            <strong>Instalace proběhla úspěšně!</strong><br>
            <a href="admin/">Přejít do administrace</a>
        </div>
        <div class="warning">
            <strong>Důležité:</strong> Smažte soubor <code>install.php</code> z webu!
        </div>
    <?php else: ?>
        <?php if ($errors): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p style="margin-bottom:1rem;">Před instalací upravte <code>includes/config.php</code> s údaji k databázi.</p>

        <form method="post">
            <div class="form-group">
                <label>Uživatelské jméno (admin)</label>
                <input type="text" name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?>" required>
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Heslo (min. 8 znaků)</label>
                <input type="password" name="admin_pass" required minlength="8">
            </div>
            <button type="submit">Nainstalovat</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
