<?php
/**
 * ZEBIR LIBAS – WooCommerce Compatible Product CSV Exporter
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

// Fetch all active/inactive products from DB
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();

// Set HTTP headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=products-export-' . date('Y-m-d-H-i-s') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Define columns (WooCommerce-compatible headers mapped in import.php)
$headers = [
    'sku',
    'name',
    'short description',
    'description',
    'regular price',
    'sale price',
    'stock',
    'categories',
    'images',
    'is featured?'
];
fputcsv($output, $headers);

foreach ($products as $p) {
    fputcsv($output, [
        $p['sku'],
        $p['name'],
        $p['short_description'],
        $p['description'],
        $p['price'],
        $p['sale_price'] !== null ? $p['sale_price'] : '',
        $p['stock'],
        $p['category_name'] ?: '',
        $p['featured_image'] ?: '',
        $p['is_featured'] ? '1' : '0'
    ]);
}

fclose($output);
exit;
