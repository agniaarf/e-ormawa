<?php
declare(strict_types=1);

session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL - auto detect (always project root)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Project root filesystem path (config.php is in includes/)
$project_fs = str_replace('\\', '/', dirname(__DIR__));
$doc_root   = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$base_path  = rtrim(substr($project_fs, strlen($doc_root)), '/');

define('BASE_URL', $protocol . $host . $base_path);
define('BASE_PATH', dirname(__DIR__));

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'orbita');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App settings
define('APP_NAME', 'ORBITA');
define('APP_VERSION', '1.0.0');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200); // 2 hours

// Upload limits
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
