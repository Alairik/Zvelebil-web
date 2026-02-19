<?php
require_once __DIR__ . '/lib/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/helpers.php';

auth_start_session();

if (auth_check()) {
    redirect(ADMIN_URL . '/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Brute force ochrana - max 5 pokusů za 15 minut
    $now = time();
    $_SESSION['login_attempts'] = array_filter(
        $_SESSION['login_attempts'] ?? [],
        fn($t) => $now - $t < 900
    );

    if (count($_SESSION['login_attempts']) >= 5) {
        $error = 'Příliš mnoho neúspěšných pokusů. Zkuste to za 15 minut.';
    } elseif (!csrf_verify()) {
        $error = 'Neplatný bezpečnostní token. Zkuste to znovu.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (auth_login($username, $password)) {
            // Reset pokusů po úspěšném přihlášení
            unset($_SESSION['login_attempts']);
            redirect(ADMIN_URL . '/');
        } else {
            $_SESSION['login_attempts'][] = $now;
            $error = 'Nesprávné uživatelské jméno nebo heslo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení — <?= h(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/style.css">
</head>
<body class="login-page">
<div class="login-box">
    <h1><?= h(SITE_NAME) ?></h1>
    <h2>Přihlášení</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Uživatelské jméno</label>
            <input type="text" id="username" name="username" value="<?= h($_POST['username'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Heslo</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Přihlásit se</button>
    </form>
</div>
</body>
</html>
