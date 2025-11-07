<?php
/**
 * Configuration File for SoulBalance Blog
 * Initializes database constants and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables (optional - for production use)
// Uncomment and modify for .env file support
/*
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
*/

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'soulbalance_blog');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application Configuration
define('APP_NAME', 'SoulBalance');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('SESSION_LIFETIME', 3600); // 1 hour
define('TIMEZONE', 'UTC');

// Security
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'your-secret-key-change-in-production');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
