<?php
/**
 * N.A Fresh Fruits & Coconuts - Application Router
 * Used with PHP built-in web server: php -S 0.0.0.0:3000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// 1. If static file exists, serve directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeMap = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf'
    ];

    if (isset($mimeMap[$ext])) {
        header('Content-Type: ' . $mimeMap[$ext]);
        readfile($file);
        return true;
    }
    return false;
}

// 2. Direct exact php file matches
if (file_exists(__DIR__ . $uri . '.php')) {
    require __DIR__ . $uri . '.php';
    return true;
}

// 3. Routing Table for clean URLs
$routes = [
    '/' => 'index.php',
    '/menu' => 'products.php',
    '/products' => 'products.php',
    '/product' => 'product-detail.php',
    '/cart' => 'cart.php',
    '/checkout' => 'checkout.php',
    '/order' => 'order-success.php',
    '/order-success' => 'order-success.php',
    '/invoice' => 'invoice.php',
    '/account' => 'account.php',
    '/wishlist' => 'wishlist.php',
    '/login' => 'login.php',
    '/register' => 'register.php',
    '/logout' => 'logout.php',
    '/forgot-password' => 'forgot-password.php',
    '/installer' => 'installer.php',
    '/admin' => 'admin/index.php',
    '/admin/' => 'admin/index.php',
    '/admin/login' => 'admin/login.php',
    '/admin/logout' => 'admin/logout.php',
    '/admin/products' => 'admin/products.php',
    '/admin/orders' => 'admin/orders.php',
    '/admin/delivery-areas' => 'admin/delivery-areas.php',
    '/admin/settings' => 'admin/settings.php',
    '/admin/coupons' => 'admin/coupons.php',
    '/admin/reviews' => 'admin/reviews.php',
    '/admin/gallery' => 'admin/gallery.php',
    '/admin/push' => 'admin/push.php'
];

if (isset($routes[$uri])) {
    require __DIR__ . '/' . $routes[$uri];
    return true;
}

// 4. Default fallback: If directory with index.php exists
if (is_dir($file) && file_exists($file . '/index.php')) {
    require $file . '/index.php';
    return true;
}

// 5. If nothing matched, send to index.php
require __DIR__ . '/index.php';
return true;
