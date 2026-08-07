<?php
/**
 * AJAX Quick View Modal Render
 * ZEBIR LIBAS
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('<p class="p-4 text-center">Product not found.</p>');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) exit('<p class="p-4 text-center">Product not found.</p>');

// Fetch variants
$vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1");
$vStmt->execute([$id]);
$variants = $vStmt->fetchAll();
$sizes  = array_unique(array_filter(array_column($variants, 'size')));
$colors = array_unique(array_filter(array_column($variants, 'color')));
?>
<style>
  .quickview-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    padding: 20px;
  }
  .quickview-img-wrap {
    aspect-ratio: 3/4;
    overflow: hidden;
    background: var(--bg-secondary);
    border-radius: 4px;
    max-height: 50vh;
  }
  @media(min-width: 768px) {
    .quickview-layout {
      grid-template-columns: 1fr 1fr;
      gap: 32px;
      padding: 32px;
    }
    .quickview-img-wrap {
      max-height: none;
    }
  }
</style>
<div class="quickview-layout">
  <div class="quickview-img-wrap">
    <img src="<?= productImageUrl($product['featured_image']) ?>" alt="<?= e($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
  </div>
  
  <div>
    <span class="text-gold" style="font-size:0.75rem; letter-spacing:2px; text-transform:uppercase; font-weight:600;"><?= e($product['category_name']) ?></span>
    <h2 class="font-serif mb-2" style="font-size: 1.5rem; line-height: 1.3;"><?= e($product['name']) ?></h2>
    
    <div class="mb-3" style="font-size:1.25rem; font-weight:600;">
      <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
        <span class="old-price" style="font-size:0.9rem;"><?= formatPrice($product['price']) ?></span>
        <span class="text-gold"><?= formatPrice($product['sale_price']) ?></span>
      <?php else: ?>
        <span><?= formatPrice($product['price']) ?></span>
      <?php endif; ?>
    </div>

    <p style="color:var(--text-muted); font-size:0.875rem; line-height:1.6; margin-bottom:20px;">
      <?php
      // Decode entities multiple times in case of double-encoding, then strip tags
      $decoded = html_entity_decode($product['short_description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      echo truncate(e(strip_tags($decoded)), 150);
      ?>
    </p>

    <a href="<?= productUrl($product['slug']) ?>" class="btn-luxury btn-gold btn-full text-center">VIEW FULL DETAILS</a>
  </div>
</div>

