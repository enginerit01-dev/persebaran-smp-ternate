<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

if ($path === '/') {
    $path = '/index.php';
}

$root = dirname(__DIR__);
$target = realpath($root . '/' . ltrim($path, '/'));

if (!$target || !str_starts_with($target, $root) || !is_file($target)) {
    http_response_code(404);
    echo '404 Not Found';
    exit();
}

if (pathinfo($target, PATHINFO_EXTENSION) !== 'php') {
    return false;
}

chdir($root);
require $target;
