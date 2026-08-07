<?php
/**
 * Newsletter AJAX subscription
 * ZEBIR LIBAS
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$email = sanitize($_POST['email'] ?? '');
if (!isValidEmail($email)) {
    setFlash('error', 'Please enter a valid email address.');
    redirectTo($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$pdo = getDB();
try {
    $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1");
    $stmt->execute([$email]);
    setFlash('success', 'Thank you for joining our private list.');
} catch (Exception $e) {
    setFlash('error', 'Subscription failed.');
}

redirectTo($_SERVER['HTTP_REFERER'] ?? 'index.php');
