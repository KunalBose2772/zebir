<?php
/**
 * ZEBIR LIBAS – Premium Fashion Storefront Homepage
 */
$pageTitle = "ZEBIR LIBAS – Unveil Timeless Elegance";
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

// Fetch Featured Products for "Enhance Your Look"
$featuredProducts = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY is_featured DESC, id DESC LIMIT 8")->fetchAll();
if (empty($featuredProducts)) {
    $featuredProducts = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 8")->fetchAll();
}

// Fetch Categories for navigation list
$homeCategories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$categoryBySlug = [];
foreach ($homeCategories as $category) {
    $categoryBySlug[$category['slug']] = $category;
}

$selectedCategoryIds = array_values(array_filter(array_map('intval', getSettingArray('home_category_ids', []))));
$displayCategories = [];
if (!empty($selectedCategoryIds)) {
    foreach ($selectedCategoryIds as $categoryId) {
        foreach ($homeCategories as $category) {
            if ($category['id'] === $categoryId) {
                $displayCategories[] = $category;
                break;
            }
        }
    }
}

$defaultCategories = getHomepageCategoryDefaults();
$existingSlugs = array_column($displayCategories, 'slug');
foreach ($defaultCategories as $defaultCategory) {
    if (count($displayCategories) >= 6) {
        break;
    }
    if (in_array($defaultCategory['slug'], $existingSlugs, true)) {
        continue;
    }
    if (isset($categoryBySlug[$defaultCategory['slug']])) {
        $displayCategories[] = $categoryBySlug[$defaultCategory['slug']];
    } else {
        $displayCategories[] = $defaultCategory;
    }
    $existingSlugs[] = $defaultCategory['slug'];
}

$displayCategories = array_slice($displayCategories, 0, 6);
$heroMode = getSetting('home_hero_mode', 'content');
$heroSlides = getSettingArray('home_hero_config', []);
if (empty($heroSlides)) {
    $heroSlides = [
        ['image' => 'HERO_03_WEDDING_COLLECTION_DESKTOP.webp', 'title' => 'Bridal Collection', 'subtitle' => '', 'description' => 'Discover couture-inspired suits designed for unforgettable celebrations and graceful presence.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
        ['image' => 'HERO_01_LUXURY_COLLECTION_DESKTOP.webp', 'title' => 'Signature Couture', 'subtitle' => '', 'description' => 'Modern luxury crafted in every detail.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
        ['image' => 'HERO_02_FESTIVE_COLLECTION_DESKTO.webp', 'title' => 'Festive Edit', 'subtitle' => '', 'description' => 'Find richly textured suits and festive pieces made to shine through every occasion.', 'button_text' => 'Shop Now', 'button_link' => 'shop', 'link' => ''],
    ];
}
?>

<!-- Full-viewport Hero Slider -->
<section class="hero-slider swiper position-relative <?= $heroMode === 'image' ? 'hero-image-only' : '' ?>" aria-label="Featured collections">
  <div class="swiper-wrapper">
    <?php foreach ($heroSlides as $slide):
      $slideImage = $slide['image'] ? bannerImageUrl($slide['image']) : assetUrl('images/banner-placeholder.jpg');
      $mobileImage = $slide['image'] ? bannerMobileImageUrl($slide['image']) : $slideImage;
      $slideLink = filter_var(trim($slide['link'] ?? ''), FILTER_SANITIZE_URL);
      $hasContent = $heroMode === 'content' && (!empty($slide['title']) || !empty($slide['description']) || !empty($slide['button_text']));
    ?>
      <div class="swiper-slide">
        <?php if ($slideLink): ?>
          <a href="<?= e($slideLink) ?>" class="hero-slide-link" <?= strpos($slideLink, 'http') === 0 ? 'target="_blank" rel="noopener"' : '' ?>></a>
        <?php endif; ?>
        <picture>
          <source media="(max-width: 768px)" srcset="<?= e($mobileImage) ?>">
          <img src="<?= e($slideImage) ?>" alt="<?= e($slide['title'] ?: 'Featured slide') ?>" loading="eager">
        </picture>
        <div class="hero-overlay"></div>
        <?php if ($hasContent): ?>
          <div class="container hero-content">
            <div class="hero-copy">
              <?php if (!empty($slide['subtitle'])): ?><span class="hero-eyebrow"><?= e($slide['subtitle']) ?></span><?php endif; ?>
              <?php if (!empty($slide['title'])): ?><h1><?= e($slide['title']) ?></h1><?php endif; ?>
              <?php if (!empty($slide['description'])): ?><p><?= e($slide['description']) ?></p><?php endif; ?>
              <?php if (!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
                <a href="<?= e($slide['button_link']) ?>" class="btn-luxury btn-gold"><?= e($slide['button_text']) ?></a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="swiper-pagination"></div>
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
</section>

<!-- Sub-Navigation Category Marquee Section -->
<div class="category-marquee-container">
  <div class="category-marquee-label">Browse:</div>
  <div style="overflow: hidden; width: 100%; display: flex; mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);">
    <div class="category-marquee-track">
      <!-- Set 1 -->
      <a href="<?= pageUrl('shop') ?>" class="category-marquee-item">All Collections</a>
      <?php foreach ($displayCategories as $c): ?>
        <a href="<?= categoryUrl($c['slug']) ?>" class="category-marquee-item"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
      <!-- Set 2 (Duplicate for loop) -->
      <a href="<?= pageUrl('shop') ?>" class="category-marquee-item">All Collections</a>
      <?php foreach ($displayCategories as $c): ?>
        <a href="<?= categoryUrl($c['slug']) ?>" class="category-marquee-item"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Category Grid – Shop by Category -->
<section class="cat-grid-section">
  <!-- Section Heading -->
  <div class="cat-grid-heading">
    <span class="section-subtitle">THE EDIT</span>
    <h2 class="section-title font-serif">Shop by Category</h2>
  </div>
  <div class="category-swiper swiper" style="padding-bottom: 30px;">
    <div class="swiper-wrapper">
      <?php
      if (empty($displayCategories)) {
          $displayCategories = [
              ['slug' => 'pakistani-lawn-suits', 'name' => 'Pakistani Lawn', 'image' => 'CAT_PAKISTANI_LAWN.webp'],
              ['slug' => 'masleen-suits', 'name' => 'Masleen Suits', 'image' => 'CAT_MASLEEN.webp'],
              ['slug' => 'cotton-suits', 'name' => 'Cotton Suits', 'image' => 'CAT_COTTON_SUITS.webp'],
              ['slug' => 'festive-collection', 'name' => 'Festive', 'image' => 'CAT_FESTIVE.webp'],
              ['slug' => 'lawn-cotton-suits', 'name' => 'Lawn Cotton', 'image' => 'CAT_LAWN_COTTON_SUITS.webp'],
              ['slug' => 'silk-collection', 'name' => 'Silk', 'image' => 'CAT_SILK.webp'],
          ];
      }
      foreach ($displayCategories as $cat):
        $catImage = !empty($cat['image']) ? categoryImageUrl($cat['image']) : assetUrl('images/placeholder.jpg');
        $catImage = str_ireplace('.png', '.webp', $catImage);
        $catName = e($cat['name'] ?? $cat['label'] ?? 'Category');
        $catLink = categoryUrl($cat['slug'] ?? '');
      ?>
        <div class="swiper-slide">
          <a href="<?= e($catLink) ?>" class="cat-grid-item">
            <img src="<?= e($catImage) ?>" alt="<?= $catName ?>" loading="lazy">
            <span class="cat-grid-pill"><?= $catName ?></span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <!-- Swiper Pagination -->
    <div class="category-pagination swiper-pagination"></div>
  </div>
</section>

<!-- "Enhance Your Look" – Featured Products Section -->
<section class="py-5" style="background-color: var(--bg-surface);">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color: var(--accent-gold); font-size: 0.85rem; letter-spacing: 3px; font-weight: 700;">ENHANCE YOUR LOOK</span>
      <h2 class="section-title font-serif" style="font-size: 2.5rem; margin-top: 8px;">Featured Handcrafted Designer Sets</h2>
    </div>

    <div class="products-catalog-grid view-grid-4">
      <?php if (!empty($featuredProducts)): ?>
        <?php foreach ($featuredProducts as $prod): ?>
          <div class="product-card" style="border-radius: 4px; overflow: hidden; border: 1px solid var(--border-light); background: var(--bg-primary); transition: var(--transition);">
            <div class="product-img-wrapper" style="aspect-ratio: 3/4; overflow: hidden; position: relative;">
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
              <span class="pcard-type">Unstitched 3-Piece Set</span>
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
      <?php else: ?>
        <p class="text-center text-muted" style="grid-column: 1/-1;">No products found. Please run import or add products via admin panel.</p>
      <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
      <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold">EXPLORE CATALOGUE</a>
    </div>
  </div>
</section>

<!-- "Where Every Outfit Feels Made Just for You" – Luxury Feature Grid -->
<section class="py-5" style="background-color: #000000; color: #fff; border-top: 1px solid rgba(200, 150, 12, 0.25); border-bottom: 1px solid rgba(200, 150, 12, 0.25);">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color: var(--accent-gold); font-size: 0.85rem; letter-spacing: 3px; font-weight: 700;">WHY ZEBIR LIBAS</span>
      <h2 class="section-title font-serif" style="font-size: 2.5rem; margin-top: 8px; color: #fff;">Where Every Outfit Feels Made Just for You</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 28px;">
      <!-- Feature 1: Tailored -->
      <div style="background-color: rgba(255,255,255,0.03); padding: 32px; border: 1px solid rgba(200, 150, 12, 0.15); border-radius: 4px; text-align: center;">
        <div style="color: var(--accent-gold); margin-bottom: 20px; display:flex; justify-content:center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6c0-1.1.9-2 2-2h.5L9 9H6l-1.5 1.5a2 2 0 0 0 0 2.83L12 21l1.5-1.5L9.17 15l1.5-1.5H15l1.5-1.5L13.17 9H15l3.5-5H19a2 2 0 0 1 2 2v.5"/></svg>
        </div>
        <h4 class="font-serif mb-2" style="font-size: 1.25rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px;">Tailored for Perfection</h4>
        <p style="font-size: 0.85rem; color: #bcaea0; line-height: 1.6;">Generous 2.5-meter fabric cut allows customization to your exact measurements and silhouette.</p>
      </div>

      <!-- Feature 2: Luxury Fabrics -->
      <div style="background-color: rgba(255,255,255,0.03); padding: 32px; border: 1px solid rgba(200, 150, 12, 0.15); border-radius: 4px; text-align: center;">
        <div style="color: var(--accent-gold); margin-bottom: 20px; display:flex; justify-content:center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
        </div>
        <h4 class="font-serif mb-2" style="font-size: 1.25rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px;">Luxury Fabrics</h4>
        <p style="font-size: 0.85rem; color: #bcaea0; line-height: 1.6;">Handpicked rich materials like premium Jaam Cotton Satin, Masleen, and pure organza weaves.</p>
      </div>

      <!-- Feature 3: Comfort & Value -->
      <div style="background-color: rgba(255,255,255,0.03); padding: 32px; border: 1px solid rgba(200, 150, 12, 0.15); border-radius: 4px; text-align: center;">
        <div style="color: var(--accent-gold); margin-bottom: 20px; display:flex; justify-content:center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <h4 class="font-serif mb-2" style="font-size: 1.25rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px;">Comfort & Value</h4>
        <p style="font-size: 0.85rem; color: #bcaea0; line-height: 1.6;">Luxury feel at accessible price points, so you never have to choose between elegance and cost.</p>
      </div>

      <!-- Feature 4: Secured Payment -->
      <div style="background-color: rgba(255,255,255,0.03); padding: 32px; border: 1px solid rgba(200, 150, 12, 0.15); border-radius: 4px; text-align: center;">
        <div style="color: var(--accent-gold); margin-bottom: 20px; display:flex; justify-content:center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h4 class="font-serif mb-2" style="font-size: 1.25rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px;">Secured Payment</h4>
        <p style="font-size: 0.85rem; color: #bcaea0; line-height: 1.6;">Safe & verified checkout options including Instant UPI transfer and Cash on Delivery.</p>
      </div>

      <!-- Feature 5: Empowering Women -->
      <div style="background-color: rgba(255,255,255,0.03); padding: 32px; border: 1px solid rgba(200, 150, 12, 0.15); border-radius: 4px; text-align: center;">
        <div style="color: var(--accent-gold); margin-bottom: 20px; display:flex; justify-content:center;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M12 14v6M9 19h6"/><path d="M6 21c0-3.31 2.69-6 6-6s6 2.69 6 6"/></svg>
        </div>
        <h4 class="font-serif mb-2" style="font-size: 1.25rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px;">Empowering Women</h4>
        <p style="font-size: 0.85rem; color: #bcaea0; line-height: 1.6;">Promoting traditional artisan communities, bringing beautiful heritage art to modern lives.</p>
      </div>
    </div>
  </div>
</section>

<!-- Split Editorial Banner Showcase (Zebir Style Highlights) -->
<section class="py-5" style="background-color: var(--bg-secondary);">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
      
      <!-- Card 1: Deals -->
      <div class="position-relative overflow-hidden" style="border-radius: 4px; aspect-ratio: 16/10; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <img src="<?= assetUrl('images/COLLECTION_03_NEW_ARRIVALS.webp') ?>" alt="Deals" style="width:100%; height:100%; object-fit:cover;">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 40%, rgba(0,0,0,0.7) 100%);"></div>
        <div style="position: absolute; bottom: 24px; left: 24px; right: 24px; color: #fff;">
          <h3 class="font-serif" style="font-size: 1.6rem; margin-bottom: 8px;">End Your Week in Style with Zebir’s Great Deals!</h3>
          <a href="<?= pageUrl('shop') ?>?sort=featured" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; color: var(--accent-gold); text-transform: uppercase;">Explore Deals →</a>
        </div>
      </div>

      <!-- Card 2: Designs -->
      <div class="position-relative overflow-hidden" style="border-radius: 4px; aspect-ratio: 16/10; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <img src="<?= assetUrl('images/COLLECTION_02_FESTIVE.webp') ?>" alt="Fine Designs" style="width:100%; height:100%; object-fit:cover;">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 40%, rgba(0,0,0,0.7) 100%);"></div>
        <div style="position: absolute; bottom: 24px; left: 24px; right: 24px; color: #fff;">
          <h3 class="font-serif" style="font-size: 1.6rem; margin-bottom: 8px;">Crafting the Finest Elegant Designs</h3>
          <a href="about.php" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; color: var(--accent-gold); text-transform: uppercase;">Our Heritage Story →</a>
        </div>
      </div>

      <!-- Card 3: Designed by You -->
      <div class="position-relative overflow-hidden" style="border-radius: 4px; aspect-ratio: 16/10; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <img src="<?= assetUrl('images/COLLECTION_01_WEDDING.webp') ?>" alt="Bespoke Couture" style="width:100%; height:100%; object-fit:cover;">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 40%, rgba(0,0,0,0.7) 100%);"></div>
        <div style="position: absolute; bottom: 24px; left: 24px; right: 24px; color: #fff;">
          <h3 class="font-serif" style="font-size: 1.6rem; margin-bottom: 8px;">Designed by You, Crafted by Zebir</h3>
          <a href="<?= pageUrl('shop') ?>" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; color: var(--accent-gold); text-transform: uppercase;">Discover Collection →</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- As Seen On Instagram Reels (Dynamic Swiper Slider) -->
<?php
$instagramReels = $pdo->query("SELECT * FROM instagram_gallery WHERE is_active = 1 ORDER BY sort_order ASC, id DESC")->fetchAll();
if (empty($instagramReels)) {
    // Elegant fallback curated reels with their actual shortcodes that represent beautiful fashion aesthetics
    $instagramReels = [
        ['id' => 9991, 'image' => '', 'url' => 'https://www.instagram.com/reel/C8P_m-sIs3X/'],
        ['id' => 9992, 'image' => '', 'url' => 'https://www.instagram.com/reel/C8PvJ4xouEw/'],
        ['id' => 9993, 'image' => '', 'url' => 'https://www.instagram.com/reel/C8P0Y6co1k2/'],
        ['id' => 9994, 'image' => '', 'url' => 'https://www.instagram.com/reel/C7z12K-I1mO/'],
    ];
}

if (!function_exists('getInstaShortcode')) {
    function getInstaShortcode($url) {
        if (preg_match('/(?:\/p\/|\/reel\/|\/tv\/)([A-Za-z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
?>
<section class="py-5" style="background-color: var(--bg-primary); border-top: 1px solid var(--border-color); overflow:hidden;">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color: var(--accent-gold); font-size: 0.85rem; letter-spacing: 3px; font-weight: 700;">FOLLOW US @ZEBIRFROMRANCHI</span>
      <h2 class="section-title font-serif" style="font-size: 2.5rem; margin-top: 8px;">As Seen On Reels</h2>
    </div>

    <!-- Swiper Container for Reels -->
    <div class="swiper reels-slider" style="padding: 10px 4px 40px 4px;">
      <div class="swiper-wrapper">
        <?php foreach ($instagramReels as $reel): 
          $shortcode = getInstaShortcode($reel['url']);
          if (empty($shortcode)) continue;
        ?>
          <div class="swiper-slide">
            <div class="reel-card-wrapper" style="position:relative; width:100%; border-radius:12px; overflow:hidden; background:#000; box-shadow:var(--shadow-md); aspect-ratio: 9/16; border: 1px solid rgba(200, 150, 12, 0.15);">
              
              <?php if (!empty($reel['image'])): ?>
                <!-- Cover Image Thumbnail Option (Optimized Performance) -->
                <img src="<?= UPLOAD_URL . 'instagram/' . e($reel['image']) ?>" alt="Zebir Reel Cover" style="width:100%; height:100%; object-fit:cover;">
                <div class="reel-play-overlay" onclick="loadLiveReel(this, '<?= e($shortcode) ?>')" style="position:absolute; inset:0; background:rgba(0,0,0,0.35); display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; transition:all 0.3s ease;">
                  <div style="width:64px; height:64px; border-radius:50%; background:rgba(255,255,255,0.9); display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(0,0,0,0.3); transition:transform 0.3s ease;" class="play-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24" width="24" height="24" style="margin-left:4px; color:#151515;"><path d="M8 5v14l11-7z"/></svg>
                  </div>
                  <span style="color:#fff; font-size:0.75rem; font-weight:700; letter-spacing:1px; margin-top:14px; text-transform:uppercase;">Watch Reel</span>
                </div>
              <?php else: ?>
                <!-- Directly embed the live Instagram Player Iframe with autoplay -->
                <iframe
                  src="https://www.instagram.com/reel/<?= e($shortcode) ?>/embed/?autoplay=1&muted=1"
                  style="width:100%; height:100%; border:none; margin:0;"
                  allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                  allowtransparency="true"
                  scrolling="no"
                  frameborder="0"
                  loading="lazy"
                ></iframe>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <!-- Pagination & Nav Buttons -->
      <div class="swiper-pagination reels-pagination" style="bottom: 0;"></div>
    </div>
  </div>
</section>

<script>
  function loadLiveReel(element, shortcode) {
    const wrapper = element.parentElement;
    wrapper.innerHTML = `<iframe src="https://www.instagram.com/reel/${shortcode}/embed/?autoplay=1&muted=1" style="width:100%; height:100%; border:none; margin:0;" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowtransparency="true" scrolling="no" frameborder="0"></iframe>`;
  }
</script>

<!-- ── Testimonials Marquee ──────────────────────────────────── -->
<section class="testimonial-marquee-section">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color:var(--accent-gold);">CLIENT TESTIMONIALS</span>
      <h2 class="section-title font-serif" style="color:#fff;">What Our Private Circle Says</h2>
    </div>
  </div>

  <!-- Marquee Track -->
  <div class="tmarquee-outer">
    <div class="tmarquee-track">
      <?php
      $reviews = [
        ['name'=>'Aarushi Mehta',   'loc'=>'Mumbai',    'text'=>'The fabric quality of Zebir is absolutely top-notch! The fit is perfect, colors are exactly as shown, and it feels so luxurious.'],
        ['name'=>'Sneha Sivaji',    'loc'=>'Bangalore', 'text'=>'Beautiful handwork designs! Got so many compliments on my Eid suit set. Incredible value for money.'],
        ['name'=>'Priya Sharma',    'loc'=>'Delhi',     'text'=>'Super comfortable daily wear suits. Incredible styling and very fast shipping! Extremely happy with the purchase.'],
        ['name'=>'Fatima Ansari',   'loc'=>'Hyderabad', 'text'=>'Ordered the masleen suit set and I am completely in love. The embroidery detail is stunning and fabric is premium.'],
        ['name'=>'Kavya Reddy',     'loc'=>'Chennai',   'text'=>'Zebir\'s collection is unmatched. Bought for a family function and received nothing but compliments all evening!'],
        ['name'=>'Ritu Agarwal',    'loc'=>'Jaipur',    'text'=>'Amazing quality and packaging! The dupatta is so beautiful, it\'s beyond my expectations. Will definitely order again.'],
      ];
      // Duplicate for seamless loop
      $allReviews = array_merge($reviews, $reviews);
      foreach ($allReviews as $r): ?>
        <div class="tmarquee-card">
          <!-- Google logo row -->
          <div class="tmarquee-top">
            <div class="tmarquee-avatar"><?= strtoupper(substr($r['name'],0,1)) ?></div>
            <div>
              <div class="tmarquee-name"><?= htmlspecialchars($r['name']) ?></div>
              <div class="tmarquee-loc"><?= htmlspecialchars($r['loc']) ?></div>
            </div>
            <!-- Google G icon -->
            <svg class="tmarquee-g" viewBox="0 0 48 48" width="28" height="28"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
          </div>
          <!-- Stars -->
          <div class="tmarquee-stars">
            <?php for($i=0;$i<5;$i++): ?><svg width="15" height="15" viewBox="0 0 24 24" fill="#F9A800"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg><?php endfor; ?>
          </div>
          <p class="tmarquee-text">"<?= htmlspecialchars($r['text']) ?>"</p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FAQ Section ─────────────────────────────────────────── -->
<section class="faq-section">
  <div class="faq-bg-glow"></div>
  <div class="container faq-container">
    <div class="section-header text-center mb-5">
      <span class="section-subtitle" style="color:rgba(0,0,0,0.55);">HAVE QUESTIONS?</span>
      <h2 class="section-title font-serif" style="color:#1a1100;">Frequently Asked Questions</h2>
    </div>
    <div class="faq-list">
      <?php
      $faqs = [
        ['q'=>'What fabric quality does Zebir use?',          'a'=>'Zebir uses only premium fabrics including Masleen, Jaam Cotton Satin, pure organza, and lawn to ensure the finest feel and durability.'],
        ['q'=>'How long does delivery take?',                 'a'=>'Orders are dispatched within 1–2 business days. Standard delivery takes 4–7 business days across India.'],
        ['q'=>'Do you offer Cash on Delivery?',              'a'=>'Yes! We offer both UPI and Cash on Delivery (COD) options for all orders across India.'],
        ['q'=>'Can I customize or get stitched outfits?',    'a'=>'Our suits are unstitched 3-piece sets, giving you the flexibility to tailor them to your exact measurements and personal style.'],
        ['q'=>'What is the return or exchange policy?',      'a'=>'We accept exchange requests within 7 days of delivery for manufacturing defects. Please contact us via WhatsApp for assistance.'],
        ['q'=>'How do I track my order?',                    'a'=>'Once shipped, you\'ll receive a tracking link via SMS/email. You can also check your order status from the My Orders section in your account.'],
      ];
      foreach ($faqs as $i => $faq): ?>
        <div class="faq-item" id="faq-<?= $i ?>">
          <button class="faq-question" onclick="toggleFaq(<?= $i ?>)" aria-expanded="false">
            <span><?= htmlspecialchars($faq['q']) ?></span>
            <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer" id="faq-ans-<?= $i ?>">
            <p><?= htmlspecialchars($faq['a']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA Section ────────────────────────────────────────── -->
<section class="py-5" style="background: #000000; color: #fff; text-align: center; border-top: 1px solid rgba(200, 150, 12, 0.2);">
  <div class="container" style="max-width: 800px;">
    <h3 class="font-serif mb-3" style="font-size: 2.2rem; color: var(--accent-gold);">Do You Still Have Questions?</h3>
    <p style="color: #c9bfb4; font-size: 1.05rem; line-height: 1.6; margin-bottom: 28px;">
      Our customer concierge team is available 24/7 to guide you with sizing, custom fits, and shipping inquiries.
    </p>
    <a href="contact.php" class="btn-luxury btn-gold" style="padding: 14px 42px;">GET IN TOUCH</a>
  </div>
</section>
<!-- Swiper Initialization + FAQ JS -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    new Swiper(".hero-slider", {
      loop: true,
      autoHeight: true,
      autoplay: { delay: 6000, disableOnInteraction: false },
      pagination: { el: ".swiper-pagination", clickable: true },
      navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
      effect: "fade",
      fadeEffect: { crossFade: true }
    });
    new Swiper(".reels-slider", {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: ".reels-pagination", clickable: true },
      breakpoints: { 576:{slidesPerView:2}, 992:{slidesPerView:3}, 1200:{slidesPerView:4} }
    });
    new Swiper(".category-swiper", {
      slidesPerView: 1.8,
      spaceBetween: 12,
      loop: true,
      autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: ".category-pagination", clickable: true },
      breakpoints: {
        480: { slidesPerView: 2.4, spaceBetween: 14 },
        768: { slidesPerView: 3.5, spaceBetween: 16 },
        1024: { slidesPerView: 5, spaceBetween: 20 }
      }
    });
  });

  function toggleFaq(idx) {
    const btn = document.querySelector('#faq-' + idx + ' .faq-question');
    const ans = document.getElementById('faq-ans-' + idx);
    const isOpen = btn.getAttribute('aria-expanded') === 'true';
    // Close all
    document.querySelectorAll('.faq-question').forEach(b => b.setAttribute('aria-expanded','false'));
    document.querySelectorAll('.faq-answer').forEach(a => { a.style.maxHeight = null; });
    // Open clicked if it was closed
    if (!isOpen) {
      btn.setAttribute('aria-expanded','true');
      ans.style.maxHeight = ans.scrollHeight + 'px';
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
