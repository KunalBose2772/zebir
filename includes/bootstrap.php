<?php
/**
 * Core Bootstrap – loaded by every page
 * ZEBIR LIBAS
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';

// Start output buffering and session
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_name('zebirl_session');
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => !IS_LOCAL,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Generate CSRF token
if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Load site settings into global
function getSetting(string $key, string $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        $pdo = getDB();
        $rows = $pdo->query("SELECT `key`, `value` FROM settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
    }
    return (isset($settings[$key]) && $settings[$key] !== '') ? $settings[$key] : $default;
}

// Theme helper
function getTheme(): string {
    return getSetting('theme', 'light');
}
