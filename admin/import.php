<?php
/**
 * ZEBIR LIBAS – WooCommerce CSV Product & Category Importer (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

// Increase max execution time & memory for image downloads during import
set_time_limit(300);
ini_set('memory_limit', '256M');

$summary = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    verifyCsrf();

    $file = $_FILES['csv_file'];
    if ($file['error'] !== UPLOAD_ERR_OK || pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $errors[] = 'Please upload a valid .csv file.';
    } else {
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle, 4096, ',');
            // Normalize header columns
            $headersMap = [];
            foreach ($header as $idx => $colName) {
                $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $colName)));
                $headersMap[$cleanCol] = $idx;
            }

            $pdo = getDB();
            $importedCount = 0;
            $updatedCount  = 0;
            $catCreatedCount = 0;
            $imgDownloadedCount = 0;

            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                if (empty(array_filter($row))) continue;

                $getValue = function($possibleKeys) use ($headersMap, $row) {
                    foreach ((array)$possibleKeys as $key) {
                        if (isset($headersMap[$key]) && isset($row[$headersMap[$key]])) {
                            return trim($row[$headersMap[$key]]);
                        }
                    }
                    return '';
                };

                $sku          = $getValue(['sku', 'sku_number']);
                $name         = $getValue(['name', 'title', 'post_title']);
                $shortDesc    = $getValue(['short description', 'post_excerpt']);
                $desc         = $getValue(['description', 'post_content']);
                $regularPrice = (float)($getValue(['regular price', 'price']) ?: 0);
                $salePriceVal = $getValue(['sale price']);
                $salePrice    = $salePriceVal !== '' ? (float)$salePriceVal : null;
                $stock        = (int)($getValue(['stock', 'stock_qty', 'quantity']) ?: 10);
                $categoriesStr= $getValue(['categories', 'category']);
                $imagesStr    = $getValue(['images', 'image']);
                $isFeatured   = (int)in_array(strtolower($getValue(['is featured?', 'featured'])), ['1', 'yes', 'true']);

                if (!$name) continue;

                // Process Category
                $categoryId = null;
                if ($categoriesStr) {
                    $catParts = explode(',', $categoriesStr);
                    $firstCatName = trim(end($catParts)); // Take last child or primary category
                    
                    if ($firstCatName) {
                        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                        $catStmt->execute([$firstCatName]);
                        $existingCat = $catStmt->fetchColumn();

                        if ($existingCat) {
                            $categoryId = $existingCat;
                        } else {
                            $catSlug = uniqueSlug('categories', $firstCatName);
                            $insCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                            $insCat->execute([$firstCatName, $catSlug]);
                            $categoryId = $pdo->lastInsertId();
                            $catCreatedCount++;
                        }
                    }
                }

                // Process Main Image Download
                $featuredImage = '';
                if ($imagesStr) {
                    $imgUrls = explode(',', $imagesStr);
                    $firstUrl = trim($imgUrls[0]);

                    if (filter_var($firstUrl, FILTER_VALIDATE_URL)) {
                        $imgData = @file_get_contents($firstUrl);
                        if ($imgData) {
                            $ext = pathinfo(parse_url($firstUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = uniqid('wc_') . '.' . $ext;
                            $savePath = UPLOAD_DIR . 'products/' . $filename;
                            if (!is_dir(UPLOAD_DIR . 'products/')) mkdir(UPLOAD_DIR . 'products/', 0755, true);
                            file_put_contents($savePath, $imgData);
                            resizeImage($savePath, 800, 1000);
                            $featuredImage = $filename;
                            $imgDownloadedCount++;
                        }
                    } else {
                        $featuredImage = basename($firstUrl);
                    }
                }

                // Check if product exists by SKU or Name to update or insert
                $prodId = null;
                $rawSku = $getValue(['sku', 'sku_number']);
                $sku    = !empty($rawSku) ? $rawSku : null;

                if ($sku !== null) {
                    $chk = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
                    $chk->execute([$sku]);
                    $prodId = $chk->fetchColumn();
                }

                if (!$prodId) {
                    $chkName = $pdo->prepare("SELECT id FROM products WHERE name = ?");
                    $chkName->execute([$name]);
                    $prodId = $chkName->fetchColumn();
                }

                if ($prodId) {
                    // Update existing product
                    $upd = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, short_description = ?, description = ?, price = ?, sale_price = ?, stock = ?, is_featured = ? " . ($featuredImage ? ", featured_image = ?" : "") . " WHERE id = ?");
                    $params = [$categoryId, $name, $shortDesc, $desc, $regularPrice, $salePrice, $stock, $isFeatured];
                    if ($featuredImage) $params[] = $featuredImage;
                    $params[] = $prodId;
                    $upd->execute($params);
                    $updatedCount++;
                } else {
                    // Insert new product - ensure guaranteed unique SKU
                    $finalSku = $sku ?: ('ZBL-' . strtoupper(substr(md5($name . microtime() . rand(100, 999)), 0, 8)));
                    $slug = uniqueSlug('products', $name);
                    $ins = $pdo->prepare("INSERT INTO products (category_id, name, slug, sku, short_description, description, price, sale_price, stock, featured_image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$categoryId, $name, $slug, $finalSku, $shortDesc, $desc, $regularPrice, $salePrice, $stock, $featuredImage, $isFeatured]);
                    $importedCount++;
                }
            }

            fclose($handle);

            $summary = [
                'imported'  => $importedCount,
                'updated'   => $updatedCount,
                'categories'=> $catCreatedCount,
                'images'    => $imgDownloadedCount
            ];
        }
    }
}
?>

<div class="admin-page-header">
  <h2>Bulk CSV Importer</h2>
</div>

<?php if ($summary): ?>
  <div style="background:#e6f4ea; border:1px solid #34a853; color:#137333; padding:20px; border-radius:12px; margin-bottom: 24px;">
    <h4 style="margin-top:0; font-size:1.05rem; font-weight:700; margin-bottom:12px;">Import Summary Results:</h4>
    <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem; line-height: 1.6;">
      <li><strong><?= $summary['imported'] ?></strong> brand-new products added</li>
      <li><strong><?= $summary['updated'] ?></strong> existing products updated successfully</li>
      <li><strong><?= $summary['categories'] ?></strong> categories generated automatically</li>
      <li><strong><?= $summary['images'] ?></strong> images fetched and compressed</li>
    </ul>
  </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:16px; border-radius:12px; margin-bottom: 24px;">
    <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
      <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="admin-card" style="max-width:640px;">
  <h3 style="margin-top: 0; font-size: 1.15rem; font-weight: 700; margin-bottom: 12px;">Upload WooCommerce Export CSV</h3>
  <p style="color: var(--text-muted); font-size:0.875rem; margin-bottom:24px; line-height: 1.5;">
    Upload your standard WooCommerce exported products CSV catalog file. The importer engine will automatically map matching categories, download media files, process stock levels, and set product attributes.
  </p>

  <form action="import" method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    
    <div class="form-group">
      <label class="field-label">Select catalog CSV file *</label>
      <input type="file" name="csv_file" accept=".csv" class="form-control" required>
    </div>

    <button type="submit" class="btn-admin btn-admin-gold btn-full" style="margin-top: 16px;">Start CSV Import</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
