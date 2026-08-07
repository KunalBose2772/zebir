<?php
/**
 * ZEBIR LIBAS – Wishlist Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "My Wishlist – ZEBIR LIBAS";
$pdo = getDB();

$customerId = $_SESSION['customer_id'] ?? null;
$sessionId  = session_id();

if ($customerId) {
    $stmt = $pdo->prepare("SELECT p.*, w.id as wishlist_id FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.customer_id = ? AND p.is_active = 1");
    $stmt->execute([$customerId]);
} else {
    $stmt = $pdo->prepare("SELECT p.*, w.id as wishlist_id FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.session_id = ? AND p.is_active = 1");
    $stmt->execute([$sessionId]);
}

$wishlistItems = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4">
  <div class="container text-center">
    <h1 class="font-serif display-4">Wishlist</h1>
    <p class="text-muted mt-3" style="max-width: 720px; margin: 0 auto;">Save your favorite pieces and return whenever you’re ready to shop.</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php if (!empty($wishlistItems)): ?>
      <div class="products-catalog-grid view-grid-4">
        <?php foreach ($wishlistItems as $prod): ?>
          <div class="product-card">
            <div class="product-img-wrapper">
              <img src="<?= productImageUrl($prod['featured_image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
              <div class="product-actions">
                <button class="quick-act-btn js-wishlist-btn text-gold" data-id="<?= $prod['id'] ?>" title="Remove Wishlist">
                  ♥
                </button>
              </div>
            </div>
            <div class="product-info">
              <h4 class="product-title"><a href="<?= productUrl($prod['slug']) ?>"><?= e($prod['name']) ?></a></h4>
              <div class="product-price mb-3">
                <span><?= formatPrice($prod['sale_price'] ?: $prod['price']) ?></span>
              </div>
              <a href="<?= productUrl($prod['slug']) ?>" class="btn-luxury-outline btn-sm btn-full text-center">VIEW PRODUCT</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <h2 class="font-serif mb-3">Your wishlist is empty</h2>
        <p class="text-muted mb-4">Save your favorite editorial pieces for later.</p>
        <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold">EXPLORE CATALOGUE</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
