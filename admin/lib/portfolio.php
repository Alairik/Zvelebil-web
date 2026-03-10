<?php
/**
 * Portfolio functions
 */

function portfolio_list(int $limit = 100, int $offset = 0, ?string $status = null): array {
    $db = db_connect();
    $where = [];
    $params = [];

    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT * FROM portfolio {$whereSQL} ORDER BY sort_order ASC, created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function portfolio_count(?string $status = null): int {
    $db = db_connect();
    $where = [];
    $params = [];

    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmt = $db->prepare("SELECT COUNT(*) FROM portfolio {$whereSQL}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function portfolio_get(int $id): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT * FROM portfolio WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    return $item ?: null;
}

function portfolio_get_by_slug(string $slug): ?array {
    $db = db_connect();
    $stmt = $db->prepare('SELECT * FROM portfolio WHERE slug = ? AND status = ?');
    $stmt->execute([$slug, 'published']);
    $item = $stmt->fetch();
    return $item ?: null;
}

function portfolio_save(array $data): int {
    $db = db_connect();

    if (!empty($data['id'])) {
        $stmt = $db->prepare('UPDATE portfolio SET
            title = ?, slug = ?, description = ?, client = ?,
            project_type = ?, project_url = ?, github_url = ?,
            thumbnail = ?, status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?');
        $stmt->execute([
            $data['title'], $data['slug'], $data['description'], $data['client'],
            $data['project_type'], $data['project_url'] ?: null, $data['github_url'] ?: null,
            $data['thumbnail'], $data['status'], $data['sort_order'] ?? 0, $data['id']
        ]);
        return $data['id'];
    } else {
        $stmt = $db->prepare('INSERT INTO portfolio
            (title, slug, description, client, project_type, project_url, github_url, thumbnail, status, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $data['title'], $data['slug'], $data['description'], $data['client'],
            $data['project_type'], $data['project_url'] ?: null, $data['github_url'] ?: null,
            $data['thumbnail'], $data['status'], $data['sort_order'] ?? 0
        ]);
        return (int) $db->lastInsertId();
    }
}

function portfolio_delete(int $id): void {
    $db = db_connect();
    $db->prepare('DELETE FROM portfolio WHERE id = ?')->execute([$id]);
}

function portfolio_update_order(array $order): void {
    $db = db_connect();
    $stmt = $db->prepare('UPDATE portfolio SET sort_order = ? WHERE id = ?');
    foreach ($order as $position => $id) {
        $stmt->execute([$position, $id]);
    }
}
