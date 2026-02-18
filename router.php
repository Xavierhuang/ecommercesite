<?php
// Router for PHP built-in server (so OpenCart SEO-style URLs work)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . urldecode($uri);

if ($uri !== '/' && $uri !== '' && is_file($path) && !preg_match('/\.php$/', $path)) {
    return false;
}

// Direct index.php request: pass through (route= in query string is used)
if ($uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

if (strpos($uri, '/admin') === 0) {
    // Keep route from query string if present; only derive from path for clean URLs like /admin/
    if (empty($_GET['route'])) {
        $admin_route = trim(substr(trim($uri, '/'), 6), '/');
        if ($admin_route === '' || $admin_route === 'index.php') {
            $admin_route = 'common/dashboard';
        }
        $_GET['_route_'] = $admin_route;
        $_GET['route'] = $admin_route;
    } else {
        $_GET['_route_'] = $_GET['route'];
    }
    require __DIR__ . '/admin/index.php';
    return true;
}

// Clean URL for catalog: set _route_ only when non-empty (else default common/home is used)
$route = trim($uri, '/');
if ($route !== '') {
    $_GET['_route_'] = $route;
}
require __DIR__ . '/index.php';
return true;
