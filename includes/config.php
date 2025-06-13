<?php
// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.gc_maxlifetime', 7200);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'php_login');
define('DB_USER', 'root');
define('DB_PASS', '');

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');

// Application Settings
define('APP_NAME', 'ระบบเลือกตั้งออนไลน์');
define('APP_URL', 'http://localhost/php-login-main291');
define('APP_DEBUG', true);
define('APP_TIMEZONE', 'Asia/Bangkok');

// Security
define('CSRF_LIFETIME', 7200);
define('SESSION_LIFETIME', 7200);
define('PASSWORD_HASH_COST', 12);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', __DIR__ . '/../uploads');

// Initialize settings
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Create upload directory if not exists
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
} 