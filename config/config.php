<?php
/**
 * Global Application Configuration
 * ZEBIR LIBAS – Premium Fashion Ecommerce
 */

// Environment check (live vs local)
$isLocal = true;
if (isset($_SERVER['HTTP_HOST'])) {
    $host = strtolower($_SERVER['HTTP_HOST']);
    if ($host !== 'localhost' && $host !== '127.0.0.1' && strpos($host, '192.168.') !== 0) {
        $isLocal = false;
    }
}
define('IS_LOCAL', $isLocal);

// Base URL and Path
if (IS_LOCAL) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    define('BASE_URL', $protocol . $host . '/zebirl/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    // Ensure host ends with trailing slash
    $detectedHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'zebirlibas.com';
    define('BASE_URL', $protocol . $detectedHost . '/');
}
define('BASE_PATH', dirname(__DIR__) . '/');

// App Info
define('APP_NAME', 'ZEBIR LIBAS');
define('APP_VERSION', '1.0.0');

// Session prefix
define('SESSION_PREFIX', 'zbl_');

// Upload directories
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('UPLOAD_PRODUCTS', BASE_PATH . 'uploads/products/');
define('UPLOAD_BANNERS', BASE_PATH . 'uploads/banners/');
define('UPLOAD_QR', BASE_PATH . 'uploads/qr/');
define('UPLOAD_PAYMENTS', BASE_PATH . 'uploads/payments/');
define('UPLOAD_LOGO', BASE_PATH . 'uploads/logo/');

// Upload URL paths
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('UPLOAD_PRODUCTS_URL', BASE_URL . 'uploads/products/');
define('UPLOAD_BANNERS_URL', BASE_URL . 'uploads/banners/');

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Image sizes (width x height)
define('IMG_PRODUCT_THUMB', [400, 500]);
define('IMG_PRODUCT_LARGE', [800, 1000]);
define('IMG_BANNER', [1920, 800]);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PAYMENT_SETTINGS_PASSWORD', 'ZebirPaySecure2026!');
define('ADMIN_EMAIL', 'zebirlibas@gmail.com');

// Error reporting (set to 0 in production)
if (IS_LOCAL) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
