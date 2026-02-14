<?php
/**
 * Public JSON API for articles
 *
 * GET /cms/api/articles.php              — list published articles
 * GET /cms/api/articles.php?slug=xyz     — single article by slug
 * GET /cms/api/articles.php?limit=5      — limit results
 * GET /cms/api/articles.php?offset=10    — pagination offset
 * GET /cms/api/articles.php?category=abc — filter by category slug
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/articles.php';
require_once INCLUDES_PATH . '/categories.php';

// Single article by slug
if (!empty($_GET['slug'])) {
    $article = article_get_by_slug($_GET['slug']);
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'Article not found']);
        exit;
    }
    $article['tags'] = article_get_tags($article['id']);
    $article['url'] = SITE_URL . '/clanek/' . $article['slug'];
    echo json_encode($article, JSON_UNESCAPED_UNICODE);
    exit;
}

// List published articles
$limit = max(1, min(100, (int) ($_GET['limit'] ?? ARTICLES_PER_PAGE)));
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$categoryId = null;

if (!empty($_GET['category'])) {
    $db = db_connect();
    $stmt = $db->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$_GET['category']]);
    $categoryId = $stmt->fetchColumn() ?: null;
}

$total = articles_count('published', $categoryId);
$articles = articles_list($limit, $offset, 'published', $categoryId);

foreach ($articles as &$a) {
    $a['url'] = SITE_URL . '/clanek/' . $a['slug'];
    if (!empty($a['featured_image'])) {
        $a['featured_image_url'] = UPLOADS_URL . '/' . $a['featured_image'];
    }
}
unset($a);

echo json_encode([
    'total' => $total,
    'limit' => $limit,
    'offset' => $offset,
    'articles' => $articles,
], JSON_UNESCAPED_UNICODE);
