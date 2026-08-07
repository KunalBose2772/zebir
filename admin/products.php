<?php
/**
 * ZEBIR LIBAS – Admin Products List (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

// Delete Action
if (isset($_GET['delete'])) {
    verifyCsrf();
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    setFlash('success', 'Product deleted successfully.');
    redirectTo('admin/products.php');
}

$search = sanitize($_GET['q'] ?? '');
$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where ORDER BY p.id DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="admin-page-header">
  <h2>Product Catalogue</h2>
  <div class="admin-page-actions">
    <a href="product-add.php" class="btn-admin btn-admin-gold btn-admin-sm">+ Add New Product</a>
  </div>
</div>

<div class="admin-search-bar">
  <form action="products.php" method="GET">
    <div class="input-icon-wrap">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="text" name="q" class="form-control" placeholder="Search product name or SKU..." value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn-admin btn-admin-primary">Search</button>
  </form>
</div>

<div class="admin-table-wrap">
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width: 80px;">Image</th>
        <th>Product Name</th>
        <th>SKU</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Tags / Flags</th>
        <th style="width: 100px;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <tr>
            <td>
              <img src="<?= productImageUrl($p['featured_image']) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:6px; border: 1px solid var(--border-color);">
            </td>
            <td>
              <strong style="color: var(--primary-color);"><?= e($p['name']) ?></strong>
            </td>
            <td><code><?= e($p['sku'] ?: '-') ?></code></td>
            <td><span style="font-size: 0.8rem; color: var(--text-muted);"><?= e($p['category_name'] ?: 'Uncategorized') ?></span></td>
            <td>
              <strong><?= formatPrice($p['sale_price'] ?: $p['price']) ?></strong>
              <?php if ($p['sale_price']): ?>
                <span style="text-decoration:line-through; font-size:0.75rem; color:var(--text-muted); display:block;"><?= formatPrice($p['price']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($p['stock'] <= $p['low_stock_threshold']): ?>
                <span style="color:#dc2626; font-weight:700; font-size: 0.85rem; background-color: #fee2e2; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca;"><?= $p['stock'] ?> (Low)</span>
              <?php else: ?>
                <span style="font-weight: 500;"><?= $p['stock'] ?></span>
              <?php endif; ?>
            </td>
            <td>
              <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                <?php if ($p['is_featured']): ?>
                  <span style="font-size:0.7rem; font-weight: 700; color: #b45309; background-color: #fef3c7; border: 1px solid #fde68a; padding: 2px 6px; border-radius: 4px;">Featured</span>
                <?php endif; ?>
                <?php if ($p['is_trending']): ?>
                  <span style="font-size:0.7rem; font-weight: 700; color: #dc2626; background-color: #fee2e2; border: 1px solid #fecaca; padding: 2px 6px; border-radius: 4px;">Trending</span>
                <?php endif; ?>
                <?php if ($p['is_new_arrival']): ?>
                  <span style="font-size:0.7rem; font-weight: 700; color: #1d4ed8; background-color: #dbeafe; border: 1px solid #bfdbfe; padding: 2px 6px; border-radius: 4px;">New</span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-gold btn-admin-sm" style="padding:6px;" title="Edit">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </a>
                <a href="products.php?delete=<?= $p['id'] ?>&csrf_token=<?= csrfToken() ?>" onclick="return confirm('Delete this product?')" class="btn-admin btn-admin-danger btn-admin-sm" style="padding:6px;" title="Delete">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center py-4" style="text-align: center; color: var(--text-muted);">No products found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
