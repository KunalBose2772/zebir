<?php
/**
 * ZEBIR LIBAS – Shop Catalog Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Shop All Products – ZEBIR LIBAS";
$pdo = getDB();

// Helper for removing a specific filter query param
function removeFilterUrl(string $filterKey): string {
    $params = $_GET;
    if ($filterKey === 'price') {
        unset($params['min_price'], $params['max_price']);
    } else {
        unset($params[$filterKey]);
    }
    unset($params['page']); // Reset page when filter changes

    if (isset($params['category']) && $params['category'] !== '') {
        $cat = $params['category'];
        unset($params['category']);
        $qs = !empty($params) ? '?' . http_build_query($params) : '';
        return categoryUrl($cat) . $qs;
    }

    $qs = !empty($params) ? '?' . http_build_query($params) : '';
    return pageUrl('shop') . $qs;
}

// Filters & Query Parameters
$catSlug  = sanitize($_GET['category'] ?? '');
$sort     = sanitize($_GET['sort'] ?? 'newest');
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 0);
$search   = sanitize($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$inStock  = isset($_GET['in_stock']) && $_GET['in_stock'] == '1';
$onSale   = isset($_GET['on_sale']) && $_GET['on_sale'] == '1';

// Base WHERE condition
$where = ["p.is_active = 1"];
$params = [];

if ($catSlug) {
    $where[] = "c.slug = ?";
    $params[] = $catSlug;
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($minPrice > 0) {
    $where[] = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
}

// Fetch absolute maximum price to scale placeholders and range limits
$maxDbPrice = (float)$pdo->query("SELECT MAX(COALESCE(sale_price, price)) FROM products WHERE is_active = 1")->fetchColumn();
if ($maxDbPrice <= 0) {
    $maxDbPrice = 9999.00;
}

if ($maxPrice > 0 && $maxPrice < $maxDbPrice) {
    $where[] = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
}

if ($inStock) {
    $where[] = "p.stock > 0";
}

if ($onSale) {
    $where[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price AND p.sale_price > 0";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// Sorting logic
$orderSql = match($sort) {
    'price_low'  => "ORDER BY COALESCE(p.sale_price, p.price) ASC",
    'price_high' => "ORDER BY COALESCE(p.sale_price, p.price) DESC",
    'featured'   => "ORDER BY p.is_featured DESC, p.id DESC",
    default      => "ORDER BY p.id DESC",
};

// Count total items
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalItems = (int)$stmt->fetchColumn();

// Pagination setup
$pagination = paginate($totalItems, ITEMS_PER_PAGE, $page, 'shop.php');

// Fetch products
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereSql $orderSql 
        LIMIT " . ITEMS_PER_PAGE . " OFFSET " . $pagination['offset'];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for sidebar filter
$allCategories = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4 page-breadcrumbs-header">
  <div class="container text-center">
    <h1 class="font-serif display-4">Catalog</h1>
    <p class="text-muted mt-3" style="font-size:0.85rem; letter-spacing:1px; text-transform:uppercase;">
      <a href="<?= BASE_URL ?>">Home</a> / <span class="text-main">Shop</span>
    </p>
  </div>
</div>

<div class="py-5">
  <div class="container">
    
    <!-- Top Filter & Sort Bar -->
    <div class="catalog-top-bar">
      <div class="top-bar-left">
        <button type="button" class="btn-toggle-sidebar js-toggle-sidebar d-lg-none" title="Toggle Sidebar">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          <span class="d-none d-sm-inline">Filters</span>
        </button>
        <p class="results-count text-muted">Showing <?= count($products) ?> of <?= $totalItems ?> items</p>
      </div>
      
      <div class="top-bar-right">
        <!-- View Switcher -->
        <div class="view-switcher d-flex">
          <button type="button" class="view-btn js-view-grid-4 active" data-view="grid-4" title="4 Columns Grid">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><rect x="2" y="2" width="4" height="4" rx="1"/><rect x="8" y="2" width="4" height="4" rx="1"/><rect x="14" y="2" width="4" height="4" rx="1"/><rect x="20" y="2" width="4" height="4" rx="1"/><rect x="2" y="8" width="4" height="4" rx="1"/><rect x="8" y="8" width="4" height="4" rx="1"/><rect x="14" y="8" width="4" height="4" rx="1"/><rect x="20" y="8" width="4" height="4" rx="1"/><rect x="2" y="14" width="4" height="4" rx="1"/><rect x="8" y="14" width="4" height="4" rx="1"/><rect x="14" y="14" width="4" height="4" rx="1"/><rect x="20" y="14" width="4" height="4" rx="1"/><rect x="2" y="20" width="4" height="4" rx="1"/><rect x="8" y="20" width="4" height="4" rx="1"/><rect x="14" y="20" width="4" height="4" rx="1"/><rect x="20" y="20" width="4" height="4" rx="1"/></svg>
          </button>
          <button type="button" class="view-btn js-view-grid-3" data-view="grid-3" title="3 Columns Grid">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><rect x="3" y="3" width="5" height="5" rx="1"/><rect x="10" y="3" width="5" height="5" rx="1"/><rect x="17" y="3" width="5" height="5" rx="1"/><rect x="3" y="10" width="5" height="5" rx="1"/><rect x="10" y="10" width="5" height="5" rx="1"/><rect x="17" y="10" width="5" height="5" rx="1"/><rect x="3" y="17" width="5" height="5" rx="1"/><rect x="10" y="17" width="5" height="5" rx="1"/><rect x="17" y="17" width="5" height="5" rx="1"/></svg>
          </button>
          <button type="button" class="view-btn js-view-list" data-view="list" title="List View">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
        
        <!-- Sort Dropdown -->
        <div class="sort-container">
          <select name="sort" class="sort-select" form="catalogFilterForm" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Active Filter Badges -->
    <?php
    $hasActiveFilters = $catSlug || $minPrice > 0 || ($maxPrice > 0 && $maxPrice < $maxDbPrice) || $inStock || $onSale || $search;
    if ($hasActiveFilters): ?>
      <div class="active-filters-bar">
        <span class="active-filters-label">Active Filters:</span>
        <div class="active-filters-list">
          <?php if ($catSlug): ?>
            <span class="filter-badge">
              Category: <?= e(str_replace('-', ' ', $catSlug)) ?>
              <a href="<?= removeFilterUrl('category') ?>" class="remove-filter">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($minPrice > 0 || ($maxPrice > 0 && $maxPrice < $maxDbPrice)): ?>
            <span class="filter-badge">
              Price: <?= formatPrice($minPrice) ?> - <?= $maxPrice < $maxDbPrice ? formatPrice($maxPrice) : formatPrice($maxDbPrice) ?>
              <a href="<?= removeFilterUrl('price') ?>" class="remove-filter">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($inStock): ?>
            <span class="filter-badge">
              In Stock
              <a href="<?= removeFilterUrl('in_stock') ?>" class="remove-filter">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($onSale): ?>
            <span class="filter-badge">
              On Sale
              <a href="<?= removeFilterUrl('on_sale') ?>" class="remove-filter">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($search): ?>
            <span class="filter-badge">
              Search: "<?= e($search) ?>"
              <a href="<?= removeFilterUrl('q') ?>" class="remove-filter">&times;</a>
            </span>
          <?php endif; ?>
          <a href="<?= pageUrl('shop') ?>" class="clear-all-filters">Clear All</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Catalog Main Grid/Sidebar Layout -->
    <div class="catalog-layout-wrapper">
      
      <!-- Collapsible Sidebar Filters -->
      <aside class="catalog-sidebar js-catalog-sidebar">
        <div class="sidebar-inner-scroll">
          <div class="sidebar-header d-flex d-lg-none justify-content-between align-items-center mb-3">
            <h3 class="font-serif">Filters</h3>
            <button type="button" class="btn-close-sidebar js-toggle-sidebar">&times;</button>
          </div>
          
          <form id="catalogFilterForm" action="shop.php" method="GET" class="filter-form">
            <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
            
            <!-- Category Filter -->
            <div class="filter-group">
              <h4 class="filter-group-title font-serif">Categories</h4>
              <div class="filter-group-content">
                <ul class="filter-categories-list">
                  <li>
                    <a href="<?= removeFilterUrl('category') ?>" class="<?= !$catSlug ? 'active' : '' ?>">
                      All Collections
                    </a>
                  </li>
                  <?php foreach ($allCategories as $cat): 
                    $queryParams = $_GET;
                    unset($queryParams['category'], $queryParams['page']);
                    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                    $cleanUrl = categoryUrl($cat['slug']) . $queryString;
                  ?>
                    <li>
                      <a href="<?= $cleanUrl ?>" class="<?= $catSlug === $cat['slug'] ? 'active' : '' ?>">
                        <?= e($cat['name']) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

            <!-- Price Range Filter -->
            <div class="filter-group">
              <h4 class="filter-group-title font-serif">Price Range</h4>
              <div class="filter-group-content">
                <div class="price-inputs-wrapper">
                  <div class="price-input-box">
                    <span>Min</span>
                    <input type="number" name="min_price" value="<?= $minPrice > 0 ? (int)$minPrice : '' ?>" placeholder="0">
                  </div>
                  <div class="price-input-box">
                    <span>Max</span>
                    <input type="number" name="max_price" value="<?= ($maxPrice > 0 && $maxPrice < $maxDbPrice) ? (int)$maxPrice : '' ?>" placeholder="<?= (int)$maxDbPrice ?>">
                  </div>
                </div>
                <button type="submit" class="btn-apply-filters">Apply Price</button>
              </div>
            </div>

            <!-- Availability & Status -->
            <div class="filter-group">
              <h4 class="filter-group-title font-serif">Refine By</h4>
              <div class="filter-group-content">
                <label class="custom-checkbox">
                  <input type="checkbox" name="in_stock" value="1" <?= $inStock ? 'checked' : '' ?> onchange="this.form.submit()">
                  <span class="checkmark"></span>
                  In Stock Only
                </label>
                <label class="custom-checkbox mt-3">
                  <input type="checkbox" name="on_sale" value="1" <?= $onSale ? 'checked' : '' ?> onchange="this.form.submit()">
                  <span class="checkmark"></span>
                  On Sale
                </label>
              </div>
            </div>
          </form>
        </div>
      </aside>

      <!-- Backdrop for mobile drawer mode -->
      <div class="sidebar-backdrop js-toggle-sidebar"></div>

      <!-- Main Product Grid -->
      <main class="catalog-main-content">
        <?php if (!empty($products)): ?>
          <div class="products-catalog-grid js-products-catalog-grid">
            <?php foreach ($products as $prod): ?>
              <div class="product-card">
                <div class="product-img-wrapper">
                  <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
                    <span class="product-badge badge-gold">SALE</span>
                  <?php elseif ($prod['is_new_arrival']): ?>
                    <span class="product-badge">NEW</span>
                  <?php endif; ?>
                  
                  <a href="<?= productUrl($prod['slug']) ?>" class="product-img-link">
                    <img src="<?= productImageUrl($prod['featured_image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
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
                  <span class="pcard-type"><?= e($prod['category_name'] ?? 'Collection') ?></span>
                  <h4 class="product-title pcard-title">
                    <a href="<?= productUrl($prod['slug']) ?>"><?= e($prod['name']) ?></a>
                  </h4>
                  <p class="product-description-excerpt text-muted d-none">
                    <?php 
                      $decoded = html_entity_decode($prod['short_description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      echo truncate(e(strip_tags($decoded)), 140);
                    ?>
                  </p>
                  
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

          <!-- Pagination -->
          <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination-wrapper">
              <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): 
                $pageParams = $_GET;
                $pageParams['page'] = $i;
                if (isset($pageParams['category']) && $pageParams['category'] !== '') {
                    $cSlug = $pageParams['category'];
                    unset($pageParams['category']);
                    $pageQs = !empty($pageParams) ? '?' . http_build_query($pageParams) : '';
                    $pLink = categoryUrl($cSlug) . $pageQs;
                } else {
                    $pageQs = !empty($pageParams) ? '?' . http_build_query($pageParams) : '';
                    $pLink = pageUrl('shop') . $pageQs;
                }
              ?>
                <a href="<?= $pLink ?>" class="pagination-link <?= $i === $pagination['current'] ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div class="text-center py-5">
            <h3 class="font-serif">No products found</h3>
            <p class="text-muted">Try adjusting your filters or search terms.</p>
            <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold mt-4">RESET ALL FILTERS</a>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('.js-catalog-sidebar');
  const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
  const toggleButtons = document.querySelectorAll('.js-toggle-sidebar');
  const viewButtons = document.querySelectorAll('.view-btn');
  const catalogGrid = document.querySelector('.js-products-catalog-grid');
  
  // 1. Sidebar Toggle Logic
  if (sidebar) {
    const toggleSidebar = () => {
      sidebar.classList.toggle('active');
      sidebarBackdrop?.classList.toggle('active');
      document.body.classList.toggle('sidebar-open');
    };
    
    toggleButtons.forEach(btn => btn.addEventListener('click', toggleSidebar));
  }
  
  // 2. View Switcher Logic (Persistent)
  if (catalogGrid) {
    const storedView = localStorage.getItem('catalogViewMode') || 'grid-4';
    setViewMode(storedView);
    
    viewButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        setViewMode(view);
      });
    });
    
    function setViewMode(view) {
      // Remove all active classes from switcher buttons
      viewButtons.forEach(b => b.classList.remove('active'));
      const activeBtn = document.querySelector(`.view-btn[data-view="${view}"]`);
      if (activeBtn) activeBtn.classList.add('active');
      
      // Update catalog classes
      catalogGrid.className = 'products-catalog-grid js-products-catalog-grid';
      catalogGrid.classList.add(`view-${view}`);
      
      // Save state to local storage
      localStorage.setItem('catalogViewMode', view);
    }
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
