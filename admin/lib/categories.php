<?php
/**
 * Category & Tag functions
 */

function categories_list(): array {
    $db = db_connect();
    $stmt = $db->query('SELECT c.*, COUNT(a.id) AS article_count
                         FROM categories c
                         LEFT JOIN articles a ON c.id = a.category_id
                         GROUP BY c.id
                         ORDER BY c.name');
    return $stmt->fetchAll();
}

function category_get(int $id): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    return $cat ?: null;
}

function category_save(array $data): int {
    $db = db_connect();
    if (!empty($data['id'])) {
        $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['slug'], $data['id']]);
        return $data['id'];
    } else {
        $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['slug']]);
        return (int) $db->lastInsertId();
    }
}

function category_delete(int $id): void {
    $db = db_connect();
    // Set articles to uncategorized
    $db->prepare('UPDATE articles SET category_id = NULL WHERE category_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
}

// Tags

function tags_list(): array {
    $db = db_connect();
    $stmt = $db->query('SELECT t.*, COUNT(at2.article_id) AS article_count
                         FROM tags t
                         LEFT JOIN article_tags at2 ON t.id = at2.tag_id
                         GROUP BY t.id
                         ORDER BY t.name');
    return $stmt->fetchAll();
}

function tag_get(int $id): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT * FROM tags WHERE id = ?');
    $stmt->execute([$id]);
    $tag = $stmt->fetch();
    return $tag ?: null;
}

function tag_save(array $data): int {
    $db = db_connect();
    if (!empty($data['id'])) {
        $stmt = $db->prepare('UPDATE tags SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['slug'], $data['id']]);
        return $data['id'];
    } else {
        $stmt = $db->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['slug']]);
        return (int) $db->lastInsertId();
    }
}

function tag_delete(int $id): void {
    $db = db_connect();
    $db->prepare('DELETE FROM article_tags WHERE tag_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM tags WHERE id = ?')->execute([$id]);
}
