<?php
/**
 * Public JSON API for portfolio
 *
 * GET /admin/api/portfolio.php          — list published portfolio items
 * GET /admin/api/portfolio.php?slug=xyz — single item by slug
 * GET /admin/api/portfolio.php?limit=6  — limit results
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . '/lib/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/portfolio.php';

// Single item by slug
if (!empty($_GET['slug'])) {
    $item = portfolio_get_by_slug($_GET['slug']);
    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Portfolio item not found']);
        exit;
    }
    if (!empty($item['thumbnail'])) {
        $item['thumbnail_url'] = UPLOADS_URL . '/' . $item['thumbnail'];
    }
    echo json_encode($item, JSON_UNESCAPED_UNICODE);
    exit;
}

// List published portfolio items
$limit = max(1, min(50, (int) ($_GET['limit'] ?? 20)));
$offset = max(0, (int) ($_GET['offset'] ?? 0));

$total = portfolio_count('published');
$items = portfolio_list($limit, $offset, 'published');

foreach ($items as &$item) {
    if (!empty($item['thumbnail'])) {
        $item['thumbnail_url'] = UPLOADS_URL . '/' . $item['thumbnail'];
    }
}
unset($item);

echo json_encode([
    'total' => $total,
    'limit' => $limit,
    'offset' => $offset,
    'items' => $items,
], JSON_UNESCAPED_UNICODE);
