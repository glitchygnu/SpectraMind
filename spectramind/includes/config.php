<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'secure_chat');

// Security configuration
define('PEPPER', 'x1z2y3a4b5c6d7e8f9g0'); // Secret pepper for hashing
define('SESSION_EXPIRE', 86400); // 24 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes in seconds

// Application settings
define('SITE_NAME', 'SecureChat');
define('BASE_URL', 'http://localhost/chat-platform/');
define('UPLOAD_DIR', 'assets/uploads/');

// Timezone
date_default_timezone_set('UTC');

// Start session
session_start();

// Include other required files
require_once 'db.php';
require_once 'functions.php';
require_once 'auth.php';
?>
