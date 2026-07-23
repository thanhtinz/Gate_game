<?php
/**
 * Bootstrap: nạp config, session, autoload core + adapters.
 */
define('BASE_DIR', dirname(__DIR__));
define('APP_DIR', __DIR__);

$configFile = BASE_DIR . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Chưa có file config.php — hãy copy config.example.php thành config.php và cấu hình DB.');
}
$GLOBALS['config'] = require $configFile;

if ($GLOBALS['config']['debug'] ?? false) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

spl_autoload_register(function ($class) {
    foreach (['/core/', '/adapters/'] as $dir) {
        $file = APP_DIR . $dir . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require APP_DIR . '/core/helpers.php';
