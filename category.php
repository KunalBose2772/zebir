<?php
/**
 * ZEBIR LIBAS – Category Details & Products Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    redirectTo('shop.php');
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    redirectTo('shop.php');
}

$pageTitle = e($category['seo_title'] ?: $category['name'] . " – ZEBIR LIBAS");
$pageDesc  = e($category['seo_description'] ?: $category['description']);

// Redirect to clean category URL
redirectTo(categoryUrl($category['slug']));
