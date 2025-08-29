<?php
// router.php - Place in the project root

$publicDir = __DIR__ . '/build';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files directly from the 'build' directory if they exist
$requestedFile = $publicDir . $uri;
if (file_exists($requestedFile) && !is_dir($requestedFile)) {
    // Let the built-in server handle the file
    return false;
}

// For API calls, route them to the correct script inside 'build/api'
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/build' . $uri;
    exit;
}

// For all other requests, serve the main index.html to let React handle routing
require_once $publicDir . '/index.html';