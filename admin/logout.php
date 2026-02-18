<?php
require_once __DIR__ . '/lib/config.php';
require_once INCLUDES_PATH . '/auth.php';

auth_start_session();
auth_logout();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
