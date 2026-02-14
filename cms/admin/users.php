<?php
$pageTitle = 'Uživatelé';
require_once __DIR__ . '/includes/header.php';
auth_require_admin();

$db = db_connect();

// Handle delete
if (isset($_GET['delete']) && csrf_verify()) {
    $deleteId = (int) $_GET['delete'];
    if ($deleteId === $currentUser['id']) {
        flash_set('error', 'Nemůžete smazat sami sebe.');
    } else {
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$deleteId]);
        flash_set('success', 'Uživatel byl smazán.');
    }
    redirect(ADMIN_URL . '/users.php');
}

// Handle toggle active
if (isset($_GET['toggle']) && csrf_verify()) {
    $toggleId = (int) $_GET['toggle'];
    if ($toggleId !== $currentUser['id']) {
        $db->prepare('UPDATE users SET active = NOT active WHERE id = ?')->execute([$toggleId]);
        flash_set('success', 'Stav uživatele byl změněn.');
    }
    redirect(ADMIN_URL . '/users.php');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Neplatný bezpečnostní token.');
    } else {
        $editId = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'editor';

        $errors = [];
        if (strlen($username) < 3) $errors[] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Zadejte platný e-mail.';
        if (!$editId && strlen($password) < 8) $errors[] = 'Heslo musí mít alespoň 8 znaků.';
        if (!in_array($role, ['admin', 'editor'])) $errors[] = 'Neplatná role.';

        if (empty($errors)) {
            if ($editId) {
                $sql = 'UPDATE users SET username = ?, email = ?, role = ?';
                $params = [$username, $email, $role];
                if ($password) {
                    $sql .= ', password_hash = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id = ?';
                $params[] = $editId;
                $db->prepare($sql)->execute($params);
                flash_set('success', 'Uživatel byl upraven.');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)')
                   ->execute([$username, $email, $hash, $role]);
                flash_set('success', 'Uživatel byl vytvořen.');
            }
            redirect(ADMIN_URL . '/users.php');
        } else {
            foreach ($errors as $err) flash_set('error', $err);
        }
    }
}

$editUser = isset($_GET['edit']) ? $db->prepare('SELECT * FROM users WHERE id = ?')->execute([(int)$_GET['edit']]) : null;
if ($editUser) {
    $editUser = $db->prepare('SELECT * FROM users WHERE id = ?');
    $editUser->execute([(int)$_GET['edit']]);
    $editUser = $editUser->fetch();
}

$users = $db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>

<h1>Uživatelé</h1>

<div class="card">
    <h2><?= $editUser ? 'Upravit uživatele' : 'Nový uživatel' ?></h2>
    <form method="post">
        <?= csrf_field() ?>
        <?php if ($editUser): ?>
            <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label for="username">Uživatelské jméno</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= h($editUser['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= h($editUser['email'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Heslo<?= $editUser ? ' (ponechte prázdné pro zachování)' : '' ?></label>
                <input type="password" id="password" name="password" class="form-control" <?= $editUser ? '' : 'required minlength="8"' ?>>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="editor" <?= ($editUser['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrátor</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editUser ? 'Upravit' : 'Vytvořit' ?></button>
            <?php if ($editUser): ?>
                <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-secondary">Zrušit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Uživatel</th>
                <th>E-mail</th>
                <th>Role</th>
                <th>Aktivní</th>
                <th>Poslední přihlášení</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= h($user['username']) ?></td>
                <td><?= h($user['email']) ?></td>
                <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'info' : 'success' ?>"><?= h($user['role']) ?></span></td>
                <td><?= $user['active'] ? 'Ano' : 'Ne' ?></td>
                <td><?= $user['last_login'] ? format_date($user['last_login']) : '—' ?></td>
                <td>
                    <a href="?edit=<?= $user['id'] ?>" class="btn btn-sm btn-secondary">Upravit</a>
                    <?php if ($user['id'] !== $currentUser['id']): ?>
                        <a href="?toggle=<?= $user['id'] ?>&csrf_token=<?= h(csrf_token()) ?>" class="btn btn-sm btn-<?= $user['active'] ? 'warning' : 'success' ?>"><?= $user['active'] ? 'Deaktivovat' : 'Aktivovat' ?></a>
                        <a href="?delete=<?= $user['id'] ?>&csrf_token=<?= h(csrf_token()) ?>"
                           class="btn btn-sm btn-danger"
                           data-confirm="Opravdu smazat uživatele '<?= h($user['username']) ?>'?">Smazat</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
