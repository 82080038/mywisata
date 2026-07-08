<?php

/**
 * PHPUnit Bootstrap File
 *
 * Bootstrap file for PHPUnit tests.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load environment variables for testing
if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
}

use Dotenv\Dotenv;

// Load .env.test if it exists, otherwise load .env
if (file_exists(APP_ROOT . '/.env.test')) {
    $dotenv = Dotenv::createImmutable(APP_ROOT, '.env.test');
    $dotenv->load();
} elseif (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

// Set test environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';

// Load core classes
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/core/Controller.php';
require_once APP_ROOT . '/app/core/View.php';
require_once APP_ROOT . '/app/helpers/Session.php';
require_once APP_ROOT . '/app/helpers/Validator.php';
require_once APP_ROOT . '/app/helpers/Logger.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
