<?php
/**
 * ZEBIR LIBAS – Admin Edit Product (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$id = (int)($_GET['id'] ?? 0);
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) redirectTo('admin/products');

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name         = sanitize($_POST['name'] ?? '');
    $rawSku       = sanitize($_POST['sku'] ?? '');
    $sku          = !empty($rawSku) ? $rawSku : ('ZBL-' . strtoupper(substr(md5($name . $id), 0, 8)));
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

    $featuredImg = $product['featured_image'];
    if (!empty($_FILES['featured_image']['name'])) {
        $uploaded = uploadImage($_FILES['featured_image'], UPLOAD_DIR . 'products/', 800, 1000);
        if ($uploaded) {
            $featuredImg = $uploaded;
        }
    }

    if ($name && $price > 0) {
        $stmt = $pdo->prepare("UPDATE products SET 
            category_id = ?, name = ?, sku = ?, short_description = ?, description = ?, price = ?, sale_price = ?, stock = ?, featured_image = ?, is_featured = ?, is_trending = ?, is_best_seller = ?, is_new_arrival = ? 
            WHERE id = ?");

        $stmt->execute([
            $categoryId, $name, $sku, $shortDesc, $desc, $price, $salePrice, $stock, $featuredImg, $isFeatured, $isTrending, $isBestSeller, $isNewArrival, $id
        ]);

        setFlash('success', 'Product updated successfully.');
        redirectTo('admin/products');
    } else {
        $errors[] = 'Name and price are required.';
    }
}
?>

<div class="admin-page-header">
  <h2>Edit Product #<?= $product['id'] ?></h2>
  <div class="admin-page-actions">
    <a href="products" class="btn-admin btn-admin-sm">&larr; Back to Catalogue</a>
  </div>
</div>

<form action="product-edit?id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  
  <div class="admin-grid-2-1">
    
    <!-- Left details form card -->
    <div class="admin-card">
      <div class="form-group">
        <label class="field-label">Product Title *</label>
        <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
      </div>

      <div class="admin-form-inline-grid form-group">
        <div>
          <label class="field-label">Regular Price (₹) *</label>
          <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>
        <div>
          <label class="field-label">Sale Price (₹)</label>
          <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= $product['sale_price'] ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="field-label">Short Description</label>
        <textarea name="short_description" class="form-control" style="height:80px; resize: none;"><?= e($product['short_description']) ?></textarea>
      </div>

      <div class="form-group">
        <label class="field-label">Full Editorial Description</label>
        <textarea name="description" class="form-control" style="height:200px; resize: vertical;"><?= e($product['description']) ?></textarea>
      </div>
    </div>

    <!-- Right details card -->
    <div style="display:flex; flex-direction:column; gap:24px;">
      
      <!-- Catalog Details -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Catalog Mapping</h3>
        
        <div class="form-group">
          <label class="field-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="field-label">SKU</label>
          <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Stock Level</label>
          <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>">
        </div>
      </div>

      <!-- Image upload -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Product Image</h3>
        <div class="form-group">
          <label class="field-label">Featured Image</label>
          <?php if ($product['featured_image']): ?>
            <img src="<?= productImageUrl($product['featured_image']) ?>" style="width:80px; height:100px; object-fit:cover; display:block; margin-bottom:12px; border-radius:8px; border: 1px solid var(--border-color);">
          <?php endif; ?>
          <input type="file" name="featured_image" accept="image/*" class="form-control">
        </div>
      </div>

      <!-- Flags -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom: 20px;">Merchandising Flags</h3>
        <div class="toggle-pill-group">
          <label class="toggle-pill"><input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>><span>Featured</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_trending" value="1" <?= $product['is_trending'] ? 'checked' : '' ?>><span>Trending</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_best_seller" value="1" <?= $product['is_best_seller'] ? 'checked' : '' ?>><span>Best Seller</span></label>
          <label class="toggle-pill"><input type="checkbox" name="is_new_arrival" value="1" <?= $product['is_new_arrival'] ? 'checked' : '' ?>><span>New</span></label>
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-gold">Save Product</button>

    </div>

  </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
