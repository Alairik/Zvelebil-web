<?php
/**
 * Setup Portfolio Table & Import Initial Data
 * Run once at: https://zvelebil.online/admin/setup-portfolio.php
 * Then DELETE this file!
 */

require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$db = db_connect();

echo "<h2>Setup Portfolio</h2>";

// ============================
// 1. Create portfolio table
// ============================
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS portfolio (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            client VARCHAR(255) NULL,
            project_type ENUM('web', 'logo', 'marketing', 'eshop') NULL,
            project_url VARCHAR(500) NULL,
            github_url VARCHAR(500) NULL,
            thumbnail VARCHAR(255) NULL,
            status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ Tabulka 'portfolio' vytvořena (nebo již existuje)<br><br>";
} catch (PDOException $e) {
    echo "❌ Chyba při vytváření tabulky: " . $e->getMessage() . "<br>";
    exit;
}

// ============================
// 2. Import portfolio items
// ============================
$projects = [
    [
        'title' => 'Krejčovství Verde Design',
        'slug' => 'krejcovstvi-verde-design',
        'description' => 'Webová prezentace pro krejčovství specializující se na zakázkové šití a úpravy oděvů. Projekt připravený k nasazení.',
        'client' => 'Verde Design',
        'project_type' => 'web',
        'project_url' => null,
        'github_url' => 'https://github.com/Alairik/verde-design',
        'status' => 'published',
        'sort_order' => 1,
    ],
    [
        'title' => 'Akrasia',
        'slug' => 'akrasia',
        'description' => 'Osobní projekt ve vývoji. Moderní webová aplikace s dynamickým obsahem.',
        'client' => 'Vlastní projekt',
        'project_type' => 'web',
        'project_url' => 'https://akrasia.zvelebil.online',
        'github_url' => null,
        'status' => 'published',
        'sort_order' => 2,
    ],
    [
        'title' => 'Machanka lesní školka',
        'slug' => 'machanka-lesni-skolka',
        'description' => 'Tvorba loga a vizuálních guidelines pro lesní školku. Přátelský a přírodní design pro rodiče s malými dětmi.',
        'client' => 'Machanka lesní školka',
        'project_type' => 'logo',
        'project_url' => null,
        'github_url' => null,
        'status' => 'published',
        'sort_order' => 3,
    ],
    [
        'title' => 'Společně v Jehnicích',
        'slug' => 'spolecne-v-jehnicich',
        'description' => 'Marketingová konzultace pro komunitní projekt. Strategie komunikace a online prezentace.',
        'client' => 'Společně v Jehnicích',
        'project_type' => 'marketing',
        'project_url' => null,
        'github_url' => 'https://github.com/Alairik/spolecne-v-jehnicich',
        'status' => 'published',
        'sort_order' => 4,
    ],
];

echo "<strong>Importuji projekty...</strong><br><br>";

foreach ($projects as $project) {
    // Check if exists
    $stmt = $db->prepare('SELECT id FROM portfolio WHERE slug = ?');
    $stmt->execute([$project['slug']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        echo "⚠️ '{$project['title']}' již existuje (ID: {$existingId}). Přeskakuji.<br>";
        continue;
    }

    // Insert
    $stmt = $db->prepare('
        INSERT INTO portfolio (title, slug, description, client, project_type, project_url, github_url, thumbnail, status, sort_order, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $stmt->execute([
        $project['title'],
        $project['slug'],
        $project['description'],
        $project['client'],
        $project['project_type'],
        $project['project_url'],
        $project['github_url'],
        null, // thumbnail - upload via admin
        $project['status'],
        $project['sort_order'],
    ]);

    $id = $db->lastInsertId();
    echo "✅ '{$project['title']}' importován (ID: {$id})<br>";
}

echo "<br><strong>Import dokončen!</strong><br><br>";
echo "<a href='" . ADMIN_URL . "/portfolio.php'>Přejít do administrace portfolia</a><br>";
echo "<a href='/' target='_blank'>Zobrazit web</a><br>";
echo "<br>⚠️ <strong>Smažte tento soubor po importu!</strong>";
