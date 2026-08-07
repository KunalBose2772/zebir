<?php
/**
 * AJAX Live Search Handler
 * ZEBIR LIBAS
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$query = sanitize($_GET['q'] ?? '');
if (strlen($query) < 2) exit;

$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.name, p.slug, p.featured_image, p.price, p.sale_price, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.is_active = 1 AND (p.name LIKE ? OR p.tags LIKE ?) 
                       LIMIT 5");
$stmt->execute(["%$query%", "%$query%"]);
$products = $stmt->fetchAll();

if (empty($products)) {
    echo '<div style="padding:16px; text-align:center; color:var(--text-muted); font-size:0.85rem;">No matching products found.</div>';
    exit;
}

echo '<div style="display:flex; flex-direction:column; background: #fff;">';
foreach ($products as $p) {
    $price = $p['sale_price'] ? formatPrice($p['sale_price']) : formatPrice($p['price']);
    echo '
    <a href="' . productUrl($p['slug']) . '" style="display:flex; gap:12px; align-items:center; padding:12px 16px; border-bottom:1px solid var(--border-light); text-decoration:none; color:#151515; transition: background 0.2s;" onmouseover="this.style.background=\'#faf7f2\'" onmouseout="this.style.background=\'none\'">
        <img src="' . productImageUrl($p['featured_image']) . '" alt="' . e($p['name']) . '" style="width:40px; height:50px; object-fit:cover; border-radius:2px; flex-shrink:0;">
        <div style="flex:1; min-width:0;">
            <div style="font-size:0.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#151515;">' . e($p['name']) . '</div>
            <div style="font-size:0.75rem; color:var(--accent-gold); font-weight:700; margin-top:2px;">' . $price . '</div>
        </div>
    </a>';
}
echo '</div>';
