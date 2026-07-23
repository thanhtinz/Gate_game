<?php
/**
 * Router cho PHP built-in server (dev):
 *   php -S 0.0.0.0:8080 -t public router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $path;
if ($path !== '/' && is_file($file)) {
    return false; // file tĩnh
}
require __DIR__ . '/public/index.php';
