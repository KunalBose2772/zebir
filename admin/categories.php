<?php
/**
 * ZEBIR LIBAS – Admin Category Management (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

if (isset($_GET['delete'])) {
    verifyCsrf();
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    setFlash('success', 'Category deleted successfully.');
    redirectTo('admin/categories');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');

    if ($name) {
        $slug = uniqueSlug('categories', $name);
        $img = '';
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], UPLOAD_DIR . 'products/', 800, 1000);
            if ($uploaded) $img = $uploaded;
        }
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $desc, $img]);
        setFlash('success', 'Category created successfully.');
        redirectTo('admin/categories');
    }
}

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id DESC")->fetchAll();
?>

<div class="admin-page-header">
  <h2>Category Management</h2>
</div>

<div class="admin-grid-1-2">
  
  <!-- Add Category Form -->
  <div class="admin-card">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Add New Category</h3>
    <form action="categories" method="POST" enctype="multipart/form-data">
      <?= csrfField() ?>
      
      <div class="form-group">
        <label class="field-label">Category Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Silk Collection" required>
      </div>

      <div class="form-group">
        <label class="field-label">Description</label>
        <textarea name="description" class="form-control" style="height: 100px; resize: none;" placeholder="Provide a brief summary..."></textarea>
      </div>

      <div class="form-group">
        <label class="field-label">Cover Image</label>
        <input type="file" name="image" accept="image/*" class="form-control">
      </div>

      <button type="submit" class="btn-admin btn-admin-gold">Save Category</button>
    </form>
  </div>

  <!-- Categories List -->
  <div class="admin-card" style="min-width: 0;">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">All Categories</h3>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Products</th>
            <th style="width: 80px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <img src="<?= categoryImageUrl($cat['image']) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:6px; border: 1px solid var(--border-color);">
              </td>
              <td><strong style="color: var(--primary-color);"><?= e($cat['name']) ?></strong></td>
              <td><code><?= e($cat['slug']) ?></code></td>
              <td><span style="font-weight:700; background-color: var(--border-light); padding: 2px 8px; border-radius: 4px;"><?= $cat['product_count'] ?></span></td>
              <td>
                <a href="categories?delete=<?= $cat['id'] ?>&csrf_token=<?= csrfToken() ?>" onclick="return confirm('Delete category?')" class="btn-admin btn-admin-danger btn-admin-sm" style="padding:6px;" title="Delete">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
