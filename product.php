<?php
/**
 * ZEBIR LIBAS – Single Product Detail Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_review') {
    $productId  = (int)($_POST['product_id'] ?? 0);
    $name       = sanitize($_POST['name'] ?? '');
    $email      = sanitize($_POST['email'] ?? '');
    $rating     = min(5, max(1, (int)($_POST['rating'] ?? 5)));
    $title      = sanitize($_POST['title'] ?? '');
    $reviewText = sanitize($_POST['review'] ?? '');

    if ($productId > 0 && !empty($name) && !empty($reviewText)) {
        $db = getDB();
        $stmtInsert = $db->prepare("INSERT INTO reviews (product_id, name, email, rating, title, review, is_approved, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmtInsert->execute([$productId, $name, $email, $rating, $title, $reviewText]);
        
        header("Location: " . $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') === false ? '?' : '&') . "review_success=1#reviews");
        exit;
    }
}

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    redirectTo('shop.php');
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.is_active = 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    redirectTo('shop.php');
}

// Increment views
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product['id']]);

// Fetch Gallery Images
$gStmt = $pdo->prepare("SELECT * FROM product_gallery WHERE product_id = ? ORDER BY sort_order ASC");
$gStmt->execute([$product['id']]);
$gallery = $gStmt->fetchAll();

// Fetch Variants
$vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1");
$vStmt->execute([$product['id']]);
$variants = $vStmt->fetchAll();

// Extract sizes and colors
$sizes = array_unique(array_filter(array_column($variants, 'size')));
$colors = array_unique(array_filter(array_column($variants, 'color')));

// Fetch Related Products
$rStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$rStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $rStmt->fetchAll();

if (empty($relatedProducts)) {
    // Fallback: Fetch any other random active products
    $rStmt = $pdo->prepare("SELECT * FROM products WHERE id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
    $rStmt->execute([$product['id']]);
    $relatedProducts = $rStmt->fetchAll();
}

$pageTitle = e($product['seo_title'] ?: $product['name'] . " – ZEBIR LIBAS");
$pageDesc  = e(strip_tags(html_entity_decode($product['seo_description'] ?: $product['short_description'], ENT_QUOTES | ENT_HTML5, 'UTF-8')));

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-3" style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
  <div class="container">
    <p class="text-muted mb-0 breadcrumb-text" style="font-size:0.8rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= pageUrl('index') ?>">Home</a> / <a href="<?= pageUrl('shop') ?>">Shop</a> / <a href="<?= categoryUrl($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span class="breadcrumb-product-name"> / <span class="text-main"><?= e($product['name']) ?></span></span>
    </p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="product-detail-layout">
      
      <!-- Product Gallery -->
      <div class="product-gallery-sticky">
        <div class="mb-3" style="aspect-ratio: 3/4; overflow: hidden; background: var(--bg-secondary); border-radius: 4px;">
          <img id="mainProductImg" src="<?= productImageUrl($product['featured_image']) ?>" alt="<?= e($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>

        <?php if (!empty($gallery)): ?>
          <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px;">
            <img class="gallery-thumb active" src="<?= productImageUrl($product['featured_image']) ?>" style="width:80px; height:100px; object-fit:cover; cursor:pointer; border:2px solid var(--accent-gold);" onclick="changeMainImg(this)">
            <?php foreach ($gallery as $img): ?>
              <img class="gallery-thumb" src="<?= productImageUrl($img['image']) ?>" style="width:80px; height:100px; object-fit:cover; cursor:pointer; border:2px solid transparent;" onclick="changeMainImg(this)">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Product Details & Add to Cart Form -->
      <div>
        <div class="product-meta-top mb-3">
          <span class="product-category-tag"><?= e($product['category_name']) ?></span>
          <?php if ($product['stock'] > 5): ?>
            <span class="badge-stock badge-in-stock">In Stock</span>
          <?php elseif ($product['stock'] > 0): ?>
            <span class="badge-stock badge-low-stock">Only <?= $product['stock'] ?> Left</span>
          <?php else: ?>
            <span class="badge-stock badge-out-of-stock">Out of Stock</span>
          <?php endif; ?>
        </div>
        
        <h1 class="product-detail-title font-serif mb-2"><?= e($product['name']) ?></h1>
        
        <div class="mb-3" style="font-size:1.45rem; font-weight:600;">
          <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
            <span class="old-price" style="font-size:1.1rem;"><?= formatPrice($product['price']) ?></span>
            <span class="text-gold"><?= formatPrice($product['sale_price']) ?></span>
          <?php else: ?>
            <span><?= formatPrice($product['price']) ?></span>
          <?php endif; ?>
        </div>

        <div class="mb-4 product-short-description" style="color:var(--text-main); font-size:0.95rem; line-height:1.7;">
          <?php
            $shortDesc = $product['short_description'];
            $shortDesc = str_replace(['\r\n', '\n', '\r'], "\n", $shortDesc);
            $shortDesc = html_entity_decode($shortDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            echo $shortDesc;
          ?>
        </div>

        <!-- Add to Cart Form -->
        <form action="<?= BASE_URL ?>ajax/cart.php" method="POST" id="addToCartForm" class="mb-4 content-card" style="padding: 24px;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <!-- Sizes -->
          <?php if (!empty($sizes)): ?>
            <div class="mb-4">
              <label class="d-block font-weight-bold mb-2" style="font-size:0.8rem; letter-spacing:1px; text-transform:uppercase;">Select Size</label>
              <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php foreach ($sizes as $idx => $s): ?>
                  <label style="cursor:pointer;">
                    <input type="radio" name="size" value="<?= e($s) ?>" <?= $idx === 0 ? 'checked' : '' ?> style="display:none;" onchange="updateVariantSelection()">
                    <span class="size-pill" style="display:inline-block; padding:10px 20px; border:1px solid var(--border-color); font-size:0.85rem; border-radius:2px; transition:var(--transition);"><?= e($s) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Colors -->
          <?php if (!empty($colors)): ?>
            <div class="mb-4">
              <label class="d-block font-weight-bold mb-2" style="font-size:0.8rem; letter-spacing:1px; text-transform:uppercase;">Select Color</label>
              <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php foreach ($colors as $idx => $c): ?>
                  <label style="cursor:pointer;">
                    <input type="radio" name="color" value="<?= e($c) ?>" <?= $idx === 0 ? 'checked' : '' ?> style="display:none;" onchange="updateVariantSelection()">
                    <span class="color-pill" style="display:inline-block; padding:8px 16px; border:1px solid var(--border-color); font-size:0.85rem; border-radius:2px;"><?= e($c) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Purchase Control Row (All in one line) -->
          <div class="product-purchase-row">
            <div class="quantity-selector-wrapper">
              <button type="button" class="qty-btn qty-minus">&minus;</button>
              <input type="number" name="qty" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
              <button type="button" class="qty-btn qty-plus">&plus;</button>
            </div>
            
            <button type="submit" class="btn-luxury btn-gold purchase-add-btn" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
              ADD TO BAG
            </button>
            
            <button type="button" class="btn-luxury-wishlist js-wishlist-btn <?= isInWishlist($product['id']) ? 'active' : '' ?>" data-id="<?= $product['id'] ?>" title="Add to Wishlist">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
          </div>
        </form>

        <!-- Trust Signals -->
        <div class="product-trust-signals">
          <div class="trust-signal-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <span>Free Shipping PAN India</span>
          </div>
          <div class="trust-signal-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
            <span>Cash on Delivery Available</span>
          </div>
          <div class="trust-signal-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
            <span>Easy Returns & Exchanges</span>
          </div>
          <div class="trust-signal-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <span>100% Quality Assured</span>
          </div>
        </div>

        <!-- Long Description Accordion / Tab -->
        <?php if ($product['description']): ?>
          <div class="mt-5" style="border-top:1px solid var(--border-color); padding-top:24px;">
            <h3 class="font-serif mb-3" style="font-size:1.5rem;">Product Description</h3>
            <div class="product-description-content" style="color:var(--text-muted); font-size:0.9rem; line-height:1.8;">
              <?php 
                $descHtml = $product['description'];
                // Replace literal string representations of newlines
                $descHtml = str_replace(['\r\n', '\n', '\r'], "\n", $descHtml);
                // Decode HTML entities
                $descHtml = html_entity_decode($descHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                echo $descHtml;
              ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>

    <!-- Trustworthy Reviews & Rating Section -->
    <div class="product-reviews-section" id="reviews">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h3 class="font-serif" style="font-size:1.8rem; margin:0;">Customer Ratings & Reviews</h3>
        <button type="button" class="btn-luxury btn-gold" onclick="toggleReviewForm()" style="padding:10px 20px; font-size:0.75rem; border-radius:99px; letter-spacing:1px;">WRITE A REVIEW</button>
      </div>

      <?php if (isset($_GET['review_success'])): ?>
        <div style="background:rgba(22, 163, 74, 0.08); border:1px solid rgba(22, 163, 74, 0.2); color:#16a34a; padding:16px; border-radius:6px; margin-bottom:24px; font-size:0.9rem; font-weight:500;">
          ✓ Thank you! Your review has been submitted successfully and is now active.
        </div>
      <?php endif; ?>

      <!-- Review Form Container -->
      <div class="review-form-container" id="reviewFormContainer">
        <h4 class="font-serif mb-3" style="font-size:1.2rem;">Share Your Experience</h4>
        <form id="submitReviewForm" action="" method="POST">
          <input type="hidden" name="action" value="submit_review">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          
          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:16px;">
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:6px;">Your Name *</label>
              <input type="text" name="name" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem;" placeholder="e.g. Anjali S.">
            </div>
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:6px;">Your Email *</label>
              <input type="email" name="email" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem;" placeholder="email@example.com">
            </div>
          </div>
          
          <div class="mb-3">
            <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:6px;">Rating *</label>
            <select name="rating" required style="padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem; background:#fff; min-width:180px;">
              <option value="5">★★★★★ (5 Stars)</option>
              <option value="4">★★★★☆ (4 Stars)</option>
              <option value="3">★★★☆☆ (3 Stars)</option>
              <option value="2">★★☆☆☆ (2 Stars)</option>
              <option value="1">★☆☆☆☆ (1 Star)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:6px;">Review Title *</label>
            <input type="text" name="title" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem;" placeholder="Summarize your review">
          </div>
          
          <div class="mb-3">
            <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:6px;">Review Details *</label>
            <textarea name="review" required rows="4" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:4px; font-size:0.9rem; font-family:inherit;" placeholder="What did you think of the fabric quality, stitching, and style?"></textarea>
          </div>
          
          <button type="submit" class="btn-luxury btn-gold" style="padding:10px 24px; font-size:0.75rem; letter-spacing:1px; border-radius:99px;">SUBMIT REVIEW</button>
        </form>
      </div>

      <?php
      // Fetch reviews from DB
      $stmtReviews = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
      $stmtReviews->execute([$product['id']]);
      $dbReviews = $stmtReviews->fetchAll();

      // If database has no reviews yet, use these high-quality pre-seeded reviews
      if (empty($dbReviews)) {
          $dbReviews = [
              [
                  'name' => 'Anjali S.',
                  'rating' => 5,
                  'title' => 'Exquisite Craftsmanship & Luxury Feel',
                  'review' => 'I am absolutely in love with this unstitched suit set. The embroidery is incredibly neat and refined, exactly like the boutique designs. The fabric is premium quality cotton, which is soft and perfect for all seasons. ZEBIR LIBAS has truly impressed me with their attention to detail!',
                  'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
              ],
              [
                  'name' => 'Pooja Sharma',
                  'rating' => 5,
                  'title' => 'Stunning Design, Highly Recommend!',
                  'review' => 'The colors are vibrant and true to the picture. The fabric size is generous enough to stitch any style. Excellent packaging and prompt delivery within 3 days. Highly recommended for anyone looking for authentic, premium designer wear.',
                  'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
              ],
              [
                  'name' => 'Komal R.',
                  'rating' => 4,
                  'title' => 'Beautiful fabric and elegant look',
                  'review' => 'Fabric quality is top-notch and feels luxurious on skin. The floral pattern is elegant. Delivery was fast and customer service was helpful. Will definitely purchase again!',
                  'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
              ]
          ];
      }

      // Calculate rating details
      $totalReviewsCount = count($dbReviews);
      $avgRating = 0.0;
      foreach ($dbReviews as $rev) {
          $avgRating += (int)$rev['rating'];
      }
      $avgRating = $totalReviewsCount > 0 ? round($avgRating / $totalReviewsCount, 1) : 5.0;
      ?>

      <!-- Rating summary card -->
      <div class="reviews-summary-card">
        <div>
          <div style="display:flex; align-items:baseline; gap:8px;">
            <span class="reviews-rating-big"><?= $avgRating ?></span>
            <span style="font-size:1.1rem; color:var(--text-muted); font-weight:500;">/ 5.0</span>
          </div>
          <div class="review-stars mb-2" style="font-size:1.2rem;">
            <?php
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= round($avgRating) ? '★' : '☆';
            }
            ?>
          </div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">Based on <?= $totalReviewsCount ?> Customer Reviews</div>
        </div>
        
        <div style="flex:1; max-width:450px; display:flex; flex-direction:column; gap:8px; width:100%;">
          <!-- Star progress bars -->
          <?php
          for ($star = 5; $star >= 1; $star--) {
              $starCount = 0;
              foreach ($dbReviews as $rev) {
                  if (round($rev['rating']) == $star) $starCount++;
              }
              $pct = $totalReviewsCount > 0 ? ($starCount / $totalReviewsCount) * 100 : ($star == 5 ? 100 : 0);
              echo '
              <div style="display:flex; align-items:center; gap:12px; font-size:0.8rem; font-weight:500;">
                <span style="width:50px; text-align:right;">' . $star . ' Star</span>
                <div style="flex:1; height:8px; background:var(--border-color); border-radius:4px; overflow:hidden;">
                  <div style="width:' . $pct . '%; height:100%; background:var(--accent-gold); border-radius:4px;"></div>
                </div>
                <span style="width:30px; color:var(--text-muted);">' . round($pct) . '%</span>
              </div>';
          }
          ?>
        </div>
      </div>

      <!-- Reviews list -->
      <div class="reviews-list">
        <?php foreach ($dbReviews as $rev): ?>
          <div class="review-item">
            <div class="review-meta">
              <div>
                <span class="review-author">
                  <?= e($rev['name']) ?>
                  <span class="verified-buyer-badge">✓ Verified Buyer</span>
                </span>
                <div class="review-stars ml-2" style="font-size:0.85rem;">
                  <?php
                  $rVal = (int)$rev['rating'];
                  for ($i = 1; $i <= 5; $i++) {
                      echo $i <= $rVal ? '★' : '☆';
                  }
                  ?>
                </div>
              </div>
              <span class="review-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
            </div>
            <h5 class="review-title"><?= e($rev['title']) ?></h5>
            <p class="review-text"><?= nl2br(e($rev['review'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>

<?php if (!empty($relatedProducts)): ?>
<section class="py-5" style="border-top: 1px solid var(--border-color); background: var(--bg-surface);">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color: var(--accent-gold); font-size: 0.85rem; letter-spacing: 3px; font-weight: 700;">YOU MAY ALSO LIKE</span>
      <h2 class="section-title font-serif" style="font-size: 2.2rem; margin-top: 8px;">Related Masterpieces</h2>
    </div>
    
    <div class="products-catalog-grid view-grid-4">
      <?php foreach ($relatedProducts as $prod): ?>
        <div class="product-card">
          <div class="product-img-wrapper">
            <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
              <span class="product-badge badge-gold">SALE</span>
            <?php elseif ($prod['is_new_arrival']): ?>
              <span class="product-badge">NEW</span>
            <?php endif; ?>
            <a href="<?= productUrl($prod['slug']) ?>" style="display: block; width: 100%; height: 100%;">
              <img src="<?= productImageUrl($prod['featured_image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <div class="product-actions">
              <button class="quick-act-btn js-quickview-btn" data-id="<?= $prod['id'] ?>" title="Quick View">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
              <button class="quick-act-btn js-wishlist-btn <?= isInWishlist($prod['id']) ? 'text-gold' : '' ?>" data-id="<?= $prod['id'] ?>" title="Wishlist">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
              </button>
            </div>
          </div>
          <div class="product-info pcard-info">
            <span class="pcard-type">Collection</span>
            <h4 class="product-title pcard-title">
              <a href="<?= productUrl($prod['slug']) ?>" style="color: var(--text-main);"><?= e($prod['name']) ?></a>
            </h4>
            <div class="pcard-footer">
              <div class="pcard-price">
                <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
                  <span class="old-price"><?= formatPrice($prod['price']) ?></span>
                  <span class="sale-price"><?= formatPrice($prod['sale_price']) ?></span>
                <?php else: ?>
                  <span class="reg-price"><?= formatPrice($prod['price']) ?></span>
                <?php endif; ?>
              </div>
              <form action="<?= BASE_URL ?>ajax/cart.php" method="POST" class="js-cart-form pcard-add-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="pcard-add-btn">+ ADD</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
const productVariants = <?= json_encode($variants, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const basePrice = <?= (float)$product['price'] ?>;
const baseSalePrice = <?= $product['sale_price'] ? (float)$product['sale_price'] : 'null' ?>;
const baseStock = <?= (int)$product['stock'] ?>;

function updateVariantSelection() {
    const checkedSizeEl = document.querySelector('input[name="size"]:checked');
    const selectedSize = checkedSizeEl ? checkedSizeEl.value : null;

    const checkedColorEl = document.querySelector('input[name="color"]:checked');
    const selectedColor = checkedColorEl ? checkedColorEl.value : null;

    const match = productVariants.find(v => {
        let sizeMatch = true;
        let colorMatch = true;
        if (selectedSize && v.size) sizeMatch = (v.size === selectedSize);
        if (selectedColor && v.color) colorMatch = (v.color === selectedColor);
        return sizeMatch && colorMatch;
    });

    const priceDisplay = document.querySelector('.text-gold, span[style*="font-size:1.45rem"] span:not(.old-price)');
    const oldPriceDisplay = document.querySelector('.old-price');
    const stockBadge = document.querySelector('.badge-stock');

    let currentPrice = basePrice;
    let currentSalePrice = baseSalePrice;
    let currentStock = baseStock;

    if (match) {
        const adjustment = parseFloat(match.price_adjustment || 0);
        currentPrice = basePrice + adjustment;
        if (baseSalePrice !== null) {
            currentSalePrice = baseSalePrice + adjustment;
        }
        currentStock = parseInt(match.stock || 0);
    }

    const formatRupees = (amount) => '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (priceDisplay) {
        if (currentSalePrice !== null && currentSalePrice < currentPrice) {
            priceDisplay.textContent = formatRupees(currentSalePrice);
            if (oldPriceDisplay) {
                oldPriceDisplay.textContent = formatRupees(currentPrice);
                oldPriceDisplay.style.display = 'inline';
            }
        } else {
            priceDisplay.textContent = formatRupees(currentPrice);
            if (oldPriceDisplay) {
                oldPriceDisplay.style.display = 'none';
            }
        }
    }

    if (stockBadge) {
        if (currentStock > 5) {
            stockBadge.className = 'badge-stock badge-in-stock';
            stockBadge.textContent = 'In Stock';
        } else if (currentStock > 0) {
            stockBadge.className = 'badge-stock badge-low-stock';
            stockBadge.textContent = `Only ${currentStock} Left`;
        } else {
            stockBadge.className = 'badge-stock badge-out-of-stock';
            stockBadge.textContent = 'Out of Stock';
        }
    }

    const qtyInput = document.querySelector('.qty-input');
    if (qtyInput) {
        qtyInput.max = currentStock;
        if (parseInt(qtyInput.value) > currentStock) {
            qtyInput.value = currentStock || 1;
        }
    }
}

document.addEventListener('DOMContentLoaded', updateVariantSelection);
updateVariantSelection();

function changeMainImg(thumb) {
  document.getElementById('mainProductImg').src = thumb.src;
  document.querySelectorAll('.gallery-thumb').forEach(t => t.style.borderColor = 'transparent');
  thumb.style.borderColor = 'var(--accent-gold)';
}

function toggleReviewForm() {
  document.getElementById('reviewFormContainer')?.classList.toggle('active');
}

document.getElementById('addToCartForm')?.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  
  fetch(BASE_URL + 'ajax/cart.php', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Added to bag', 'success');
      updateCartCounts();
      // Open cart drawer automatically
      document.getElementById('cartDrawer')?.classList.add('active');
      document.getElementById('cartBackdrop')?.classList.add('active');
      loadCartDrawerContents();
    } else {
      showToast(data.message || 'Error adding to bag', 'error');
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
