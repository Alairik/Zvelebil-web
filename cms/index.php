<?php
/**
 * Public frontend router
 */
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/articles.php';
require_once INCLUDES_PATH . '/categories.php';

$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'article':
        $slug = $_GET['slug'] ?? '';
        $article = article_get_by_slug($slug);
        if (!$article) {
            http_response_code(404);
            $pageTitle = 'Stránka nenalezena';
            $template = '404';
        } else {
            $pageTitle = $article['title'];
            $articleTags = article_get_tags($article['id']);
            $template = 'article';
        }
        break;

    case 'category':
        $slug = $_GET['slug'] ?? '';
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        $category = $stmt->fetch();
        if (!$category) {
            http_response_code(404);
            $pageTitle = 'Kategorie nenalezena';
            $template = '404';
        } else {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $total = articles_count('published', $category['id']);
            $pag = paginate($total, ARTICLES_PER_PAGE, $page);
            $articles = articles_list($pag['per_page'], $pag['offset'], 'published', $category['id']);
            $pageTitle = 'Kategorie: ' . $category['name'];
            $template = 'list';
        }
        break;

    case 'tag':
        $slug = $_GET['slug'] ?? '';
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM tags WHERE slug = ?');
        $stmt->execute([$slug]);
        $tag = $stmt->fetch();
        if (!$tag) {
            http_response_code(404);
            $pageTitle = 'Tag nenalezen';
            $template = '404';
        } else {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $stmt = $db->prepare('SELECT COUNT(*) FROM article_tags at2 JOIN articles a ON a.id = at2.article_id WHERE at2.tag_id = ? AND a.status = ?');
            $stmt->execute([$tag['id'], 'published']);
            $total = (int) $stmt->fetchColumn();
            $pag = paginate($total, ARTICLES_PER_PAGE, $page);

            $stmt = $db->prepare('SELECT a.*, u.username AS author_name, c.name AS category_name
                FROM articles a
                JOIN article_tags at2 ON a.id = at2.article_id
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE at2.tag_id = ? AND a.status = ?
                ORDER BY a.created_at DESC LIMIT ? OFFSET ?');
            $stmt->execute([$tag['id'], 'published', $pag['per_page'], $pag['offset']]);
            $articles = $stmt->fetchAll();
            $pageTitle = 'Tag: ' . $tag['name'];
            $template = 'list';
        }
        break;

    default: // home
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total = articles_count('published');
        $pag = paginate($total, ARTICLES_PER_PAGE, $page);
        $articles = articles_list($pag['per_page'], $pag['offset'], 'published');
        $pageTitle = SITE_NAME;
        $template = 'list';
        break;
}

// Get categories for sidebar
$allCategories = categories_list();
$allTags = tags_list();

// Render
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/templates/' . $template . '.php';
require_once __DIR__ . '/templates/footer.php';
