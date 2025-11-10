<?php
/**
 * Application Configuration
 */

// Database Configuration
define('DB_HOST', 'sql310.infinityfree.com'); // Replace with your actual host
define('DB_USER', 'if0_40368929'); // Replace with your actual username
define('DB_PASS', 'NnACtKpS3O'); // Replace with your actual password
define('DB_NAME', 'if0_40368929_soulbalance'); // Replace with your actual database name

// Site Configuration
define('SITE_URL', 'https://wellness-blog.infinityfree.me');
define('SITE_NAME', 'SoulBalance');

// Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS only

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production
ini_set('log_errors', 1);
?>