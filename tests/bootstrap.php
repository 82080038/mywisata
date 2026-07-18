<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Sets up the testing environment for PHPUnit tests.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load configuration
require_once APP_ROOT . '/app/config/config.php';

// Load helpers
require_once APP_ROOT . '/app/helpers/Validator.php';
require_once APP_ROOT . '/app/helpers/Session.php';
require_once APP_ROOT . '/app/helpers/Logger.php';
require_once APP_ROOT . '/app/helpers/Security.php';

// Load core classes (if they exist)
$coreFiles = [
    'Database.php',
    'Router.php',
    'Controller.php',
    'View.php'
];

foreach ($coreFiles as $file) {
    $filePath = APP_ROOT . '/app/core/' . $file;
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

// Set testing environment (if not already defined)
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Configure test database
define('TEST_DB_HOST', 'localhost');
define('TEST_DB_NAME', 'mywisata_test');
define('TEST_DB_USER', 'root');
define('TEST_DB_PASS', 'root');

// Autoload test classes
spl_autoload_register(function ($class) {
    $prefix = 'Tests\\';
    $base_dir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);
