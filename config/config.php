<?php
/**
 * Configuration File for SoulBalance Blog
 * Initializes database constants and session management
 */

// Session cookie hardening lives in .htaccess (php_flag) since every page
// calls session_start() before this file loads, so ini_set() here is too late.

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables from .env
// (putenv()/getenv() are avoided here since some hosts, e.g. InfinityFree,
// disable putenv() for security - so values are read straight from the
// parsed array instead of round-tripping through the process environment.)
$env = [];
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env') ?: [];
}

function env($key, $default = null) {
    global $env;
    return $env[$key] ?? $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'soulbalance_blog'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// Application Configuration
define('APP_NAME', 'SoulBalance');
define('APP_URL', env('APP_URL', 'http://localhost'));
define('SESSION_LIFETIME', 3600); // 1 hour
define('TIMEZONE', 'UTC');

// Security
define('SECRET_KEY', env('SECRET_KEY', 'your-secret-key-change-in-production'));

// Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (hidden from visitors in production)
error_reporting(E_ALL);
ini_set('display_errors', env('APP_ENV') === 'production' ? 0 : 1);

// CORS Headers (optional - for API)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
