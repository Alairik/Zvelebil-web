<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . '/auth.php';

auth_start_session();
auth_logout();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
