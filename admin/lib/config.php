<?php
/**
 * CMS Configuration
 */

// Database – přihlašovací údaje (DB_HOST, DB_NAME, DB_USER, DB_PASS) jsou v
// config.local.php, který není v gitu (viz config.local.example.php)
$localConfig = __DIR__ . '/config.local.php';
if (!is_file($localConfig)) {
    http_response_code(503);
    error_log('CMS: chybí ' . $localConfig);
    exit('Služba je dočasně nedostupná.');
}
require_once $localConfig;
define('DB_CHARSET', 'utf8mb4');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/lib');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// URL - adjust to your domain
define('SITE_URL', 'https://zvelebil.online/admin');
define('ADMIN_URL', SITE_URL);
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
