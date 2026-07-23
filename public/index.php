<?php
/** Front controller — mọi request đi qua đây */
require dirname(__DIR__) . '/app/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$base = rtrim(config('base_path', ''), '/');
if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)) ?: '/';
}
$uri = '/' . trim($uri, '/');

require APP_DIR . '/routes.php';

dispatch($uri, $_SERVER['REQUEST_METHOD']);
