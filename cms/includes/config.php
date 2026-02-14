<?php
/**
 * CMS Configuration
 */

// Database
define('DB_HOST', 'md395.wedos.net');
define('DB_NAME', 'd391762_cmstst');
define('DB_USER', 'a391762_cmstst');
define('DB_PASS', 'Jvyz23:kpgn4uYW');
define('DB_CHARSET', 'utf8mb4');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ADMIN_PATH', BASE_PATH . '/admin');

// URL - adjust to your domain
define('SITE_URL', 'https://zvelebil.online/cms');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Site
define('SITE_NAME', 'Můj web');
define('ARTICLES_PER_PAGE', 10);

// Session
define('SESSION_LIFETIME', 3600); // 1 hour

// Timezone
date_default_timezone_set('Europe/Prague');

// Error reporting (turn off in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);
