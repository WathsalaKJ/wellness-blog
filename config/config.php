<?php
/**
 * Configuration File for SoulBalance Blog
 * Loads environment variables from .env file
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('Error: .env file not found. Please create .env file from .env.example');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (strpos(trim($line), '#') === 0 || trim($line) === '') {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}


// Load .env file from parent directory
loadEnv(__DIR__ . '/../.env');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'sql310.infinityfree.com');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_40368929_soulbalance');
define('DB_USER', getenv('DB_USER') ?: 'if0_40368929');
define('DB_PASS', getenv('DB_PASS') ?: 'NnACtKpS3O');

// Application Configuration
define('APP_NAME', 'SoulBalance');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN));

define('SITE_URL', 'https://wellness-blog.infinityfree.me');
define('SITE_NAME', 'SoulBalance');

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
// Session Configuration
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('TIMEZONE', getenv('TIMEZONE') ?: 'Asia/Colombo');

// Security
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'change-this-in-production');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', getenv('MAX_UPLOAD_SIZE') ?: 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', getenv('ALLOWED_IMAGE_TYPES') ?: 'jpg,jpeg,png,gif,webp');

// Email Configuration (Optional)
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@soulbalance.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'SoulBalance');

// Set timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting based on environment
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

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