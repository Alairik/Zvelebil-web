<?php
/**
 * Article functions
 */

function articles_list(int $limit, int $offset, ?string $status = null, ?int $categoryId = null): array {
    $db = db_connect();
    $where = [];
    $params = [];

    if ($status !== null) {
        $where[] = 'a.status = ?';
        $params[] = $status;
    }
    if ($categoryId !== null) {
        $where[] = 'a.category_id = ?';
        $params[] = $categoryId;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT a.*, u.username AS author_name, c.name AS category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            {$whereSQL}
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function articles_count(?string $status = null, ?int $categoryId = null): int {
    $db = db_connect();
    $where = [];
    $params = [];

    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }
    if ($categoryId !== null) {
        $where[] = 'category_id = ?';
        $params[] = $categoryId;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmt = $db->prepare("SELECT COUNT(*) FROM articles {$whereSQL}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function article_get(int $id): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT a.*, u.username AS author_name, c.name AS category_name
                           FROM articles a
                           LEFT JOIN users u ON a.author_id = u.id
                           LEFT JOIN categories c ON a.category_id = c.id
                           WHERE a.id = ?');
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    return $article ?: null;
}

function article_get_by_slug(string $slug): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT a.*, u.username AS author_name, c.name AS category_name
                           FROM articles a
                           LEFT JOIN users u ON a.author_id = u.id
                           LEFT JOIN categories c ON a.category_id = c.id
                           WHERE a.slug = ? AND a.status = ?');
    $stmt->execute([$slug, 'published']);
    $article = $stmt->fetch();
    return $article ?: null;
}

function article_save(array $data): int {
    $db = db_connect();

    if (!empty($data['id'])) {
        $stmt = $db->prepare('UPDATE articles SET
            title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?,
            category_id = ?, status = ?, updated_at = NOW()
            WHERE id = ?');
        $stmt->execute([
            $data['title'], $data['slug'], $data['content'], $data['excerpt'],
            $data['featured_image'], $data['category_id'] ?: null,
            $data['status'], $data['id']
        ]);
        $id = $data['id'];
    } else {
        $stmt = $db->prepare('INSERT INTO articles
            (title, slug, content, excerpt, featured_image, category_id, author_id, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $data['title'], $data['slug'], $data['content'], $data['excerpt'],
            $data['featured_image'], $data['category_id'] ?: null,
            $data['author_id'], $data['status']
        ]);
        $id = (int) $db->lastInsertId();
    }

    // Sync tags
    if (isset($data['tags'])) {
        article_sync_tags($id, $data['tags']);
    }

    return $id;
}

function article_delete(int $id): void {
    $db = db_connect();
    $db->prepare('DELETE FROM article_tags WHERE article_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
}

function article_get_tags(int $articleId): array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT t.* FROM tags t
                           JOIN article_tags at2 ON t.id = at2.tag_id
                           WHERE at2.article_id = ?
                           ORDER BY t.name');
    $stmt->execute([$articleId]);
    return $stmt->fetchAll();
}

function article_sync_tags(int $articleId, array $tagIds): void {
    $db = db_connect();
    $db->prepare('DELETE FROM article_tags WHERE article_id = ?')->execute([$articleId]);

    if (!empty($tagIds)) {
        $stmt = $db->prepare('INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)');
        foreach ($tagIds as $tagId) {
            $stmt->execute([$articleId, (int) $tagId]);
        }
    }
}
