<?php
/**
 * ZEBIR LIBAS – Admin Add Product (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name         = sanitize($_POST['name'] ?? '');
    $rawSku       = sanitize($_POST['sku'] ?? '');
    $sku          = !empty($rawSku) ? $rawSku : ('ZBL-' . strtoupper(substr(md5($name . microtime()), 0, 8)));
    $categoryId   = (int)($_POST['category_id'] ?? 0) ?: null;
    $price        = (float)($_POST['price'] ?? 0);
    $salePrice    = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $stock        = (int)($_POST['stock'] ?? 0);
    $shortDesc    = sanitize($_POST['short_description'] ?? '');
    $desc         = sanitize($_POST['description'] ?? '');
    $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
    $isTrending   = isset($_POST['is_trending']) ? 1 : 0;
    $isBestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
    $isNewArrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $seoTitle     = sanitize($_POST['seo_title'] ?? '');
    $seoDesc      = sanitize($_POST['seo_description'] ?? '');

    if (!$name) $errors[] = 'Product name is required.';
    if ($price <= 0) $errors[] = 'Valid product price is required.';

    $featuredImg = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $uploaded = uploadImage($_FILES['featured_image'], UPLOAD_DIR . 'products/', 800, 1000);
        if ($uploaded) {
            $featuredImg = $uploaded;
        } else {
            $errors[] = 'Failed to upload featured image.';
        }
    }

    if (empty($errors)) {
        $slug = uniqueSlug('products', $name);

        $stmt = $pdo->prepare("INSERT INTO products 
            (category_id, name, slug, sku, short_description, description, price, sale_price, stock, featured_image, is_featured, is_trending, is_best_seller, is_new_arrival, seo_title, seo_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $categoryId, $name, $slug, $sku, $shortDesc, $desc, $price, $salePrice, $stock, $featuredImg, $isFeatured, $isTrending, $isBestSeller, $isNewArrival, $seoTitle, $seoDesc
        ]);

        $productId = $pdo->lastInsertId();

        // Process Variants (Sizes & Colors)
        if (!empty($_POST['sizes'])) {
            $vStmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, stock) VALUES (?, ?, ?)");
            foreach ($_POST['sizes'] as $sz) {
                if ($sz = trim($sz)) {
                    $vStmt->execute([$productId, $sz, $stock]);
                }
            }
        }

        // Process Gallery Images
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $gStmt = $pdo->prepare("INSERT INTO product_gallery (product_id, image) VALUES (?, ?)");
            foreach ($_FILES['gallery_images']['tmp_name'] as $idx => $tmpName) {
                if ($tmpName) {
                    $fileArr = [
                        'name' => $_FILES['gallery_images']['name'][$idx],
                        'type' => $_FILES['gallery_images']['type'][$idx],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['gallery_images']['error'][$idx],
                        'size' => $_FILES['gallery_images']['size'][$idx],
                    ];
                    $gImg = uploadImage($fileArr, UPLOAD_DIR . 'products/', 800, 1000);
                    if ($gImg) {
                        $gStmt->execute([$productId, $gImg]);
                    }
                }
            }
        }

        setFlash('success', 'Product created successfully.');
        redirectTo('admin/products.php');
    }
}
?>

<div class="admin-page-header">
  <h2>Add New Product</h2>
</div>

<?php if (!empty($errors)): ?>
  <div style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:16px; border-radius:12px; margin-bottom:24px;">
    <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="product-add.php" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  
  <div class="admin-grid-2-1">
    
    <!-- Main Product Details -->
    <div class="admin-card">
      <div class="form-group">
        <label class="field-label">Product Title *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Pure Georgette Silk Anarkali Suit" required>
      </div>

      <div class="admin-form-inline-grid form-group">
        <div>
          <label class="field-label">Regular Price (₹) *</label>
          <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g. 4999" required>
        </div>
        <div>
          <label class="field-label">Sale Price (₹)</label>
          <input type="number" step="0.01" name="sale_price" class="form-control" placeholder="e.g. 3999">
        </div>
      </div>

      <div class="form-group">
        <label class="field-label">Short Description</label>
        <textarea name="short_description" class="form-control" style="height:80px; resize: none;" placeholder="A brief summary for previews..."></textarea>
      </div>

      <div class="form-group">
        <label class="field-label">Full Editorial Description</label>
        <textarea name="description" class="form-control" style="height:200px; resize: vertical;" placeholder="Detailed product craftsmanship details..."></textarea>
      </div>

      <div class="form-group" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-light);">
        <label class="field-label" style="margin-bottom: 12px;">Available Sizes</label>
        <div class="toggle-pill-group">
          <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
            <label class="toggle-pill">
              <input type="checkbox" name="sizes[]" value="<?= $sz ?>">
              <span><?= $sz ?></span>
            </label>
          <?php endforeach; ?>
        </div>
        <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:8px;">Select all size variants in stock.</span>
      </div>
    </div>

    <!-- Right Sidebar Attributes -->
    <div style="display:flex; flex-direction:column; gap:24px;">
      
      <!-- Category & Stock -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Catalog Mapping</h3>
        
        <div class="form-group">
          <label class="field-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="field-label">SKU</label>
          <input type="text" name="sku" class="form-control" placeholder="Auto-generated if blank">
        </div>

        <div class="form-group">
          <label class="field-label">Stock Level</label>
          <input type="number" name="stock" class="form-control" value="10">
        </div>
      </div>

      <!-- Images Upload -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Media Gallery</h3>
        
        <div class="form-group">
          <label class="field-label">Featured Image</label>
          <input type="file" name="featured_image" accept="image/*" class="form-control">
        </div>

        <div class="form-group">
          <label class="field-label">Gallery Images</label>
          <input type="file" name="gallery_images[]" accept="image/*" multiple class="form-control">
          <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:6px;">Hold Ctrl to choose multiple images.</span>
        </div>
      </div>

      <!-- Merchandising Flags -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Merchandising Flags</h3>
        <div class="toggle-pill-group">
          <label class="toggle-pill"><input type="checkbox" name="is_featured" value="1"><span>Featured</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_trending" value="1"><span>Trending</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_best_seller" value="1"><span>Best Seller</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_new_arrival" value="1" checked><span>New</span></label>
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-gold">Save Product</button>

    </div>

  </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
