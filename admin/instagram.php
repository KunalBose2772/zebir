<?php
/**
 * ZEBIR LIBAS – Admin Instagram Reels Manager (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

// Handle Delete Request
if (isset($_GET['delete'])) {
    verifyCsrf();
    $id = (int)$_GET['delete'];
    
    // Fetch image path to delete the physical file
    $stmt = $pdo->prepare("SELECT image FROM instagram_gallery WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if ($item) {
        if (!empty($item['image']) && file_exists(UPLOAD_DIR . 'instagram/' . $item['image'])) {
            @unlink(UPLOAD_DIR . 'instagram/' . $item['image']);
        }
        
        $delStmt = $pdo->prepare("DELETE FROM instagram_gallery WHERE id = ?");
        $delStmt->execute([$id]);
        setFlash('success', 'Instagram reel deleted successfully.');
    }
    redirectTo('admin/instagram.php');
}

// Handle Add/Edit Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $url = sanitize($_POST['url'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($url)) {
        setFlash('danger', 'Instagram Reel URL is required.');
    } else {
        // Handle Image Upload (Optional cover thumbnail)
        $imageName = '';
        if (!empty($_FILES['image']['name'])) {
            // Ensure target directory exists
            if (!is_dir(UPLOAD_DIR . 'instagram/')) {
                @mkdir(UPLOAD_DIR . 'instagram/', 0777, true);
            }
            $uploaded = uploadImage($_FILES['image'], UPLOAD_DIR . 'instagram/', 480, 720);
            if ($uploaded) {
                $imageName = $uploaded;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO instagram_gallery (image, url, sort_order, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$imageName, $url, $sortOrder, $isActive]);
        
        setFlash('success', 'New Instagram reel added successfully.');
        redirectTo('admin/instagram.php');
    }
}

// Fetch all reels
$reels = $pdo->query("SELECT * FROM instagram_gallery ORDER BY sort_order ASC, id DESC")->fetchAll();
?>

<div class="admin-page-header">
  <h2>Instagram Feed Manager</h2>
  <span style="font-size:0.85rem; color: var(--text-muted);">Manage your homepage Instagram Reels block</span>
</div>

<div class="admin-grid-2-1">
  
  <!-- Left Side: Reels List Table -->
  <div class="admin-card">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Active Reels Gallery</h3>
    
    <?php if (empty($reels)): ?>
      <div style="padding:40px; text-align:center; color: var(--text-muted); background: var(--border-light); border-radius:12px; border: 1px dashed var(--border-color);">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" width="40" height="40" style="margin:0 auto 12px; display:block; opacity:0.6;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
        </svg>
        No custom reels linked yet. Core placeholder reels are rendered on the frontend until a custom link is added here.
      </div>
    <?php else: ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 80px;">Thumbnail</th>
              <th>Reel Post Link</th>
              <th>Sort Index</th>
              <th>Visibility</th>
              <th style="width: 80px; text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reels as $r): ?>
              <tr>
                <td>
                  <?php if ($r['image']): ?>
                    <img src="<?= UPLOAD_URL . 'instagram/' . e($r['image']) ?>" style="width:40px; height:60px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                  <?php else: ?>
                    <div style="width:40px; height:60px; background:var(--border-light); border-radius:6px; display:flex; align-items:center; justify-content:center; color: var(--text-muted); font-size:0.65rem; border:1px solid var(--border-color); font-weight:bold; text-align:center;">NO IMAGE</div>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= e($r['url']) ?>" target="_blank" style="color: var(--accent-gold); font-weight:600; font-size:0.85rem; text-decoration: none;">
                    View Reel Post &nearr;
                  </a>
                  <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($r['url']) ?></div>
                </td>
                <td><span style="background-color: var(--border-light); padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?= (int)$r['sort_order'] ?></span></td>
                <td>
                  <span class="status-badge <?= $r['is_active'] ? 'status-delivered' : 'status-cancelled' ?>">
                    <?= $r['is_active'] ? 'Active' : 'Disabled' ?>
                  </span>
                </td>
                <td style="text-align:right;">
                  <a href="instagram.php?delete=<?= $r['id'] ?>&csrf_token=<?= csrfToken() ?>" onclick="return confirm('Are you sure you want to delete this Instagram reel?');" class="btn-admin btn-admin-danger btn-admin-sm" style="padding:6px;" title="Delete">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Side: Add New Reel Form -->
  <div class="admin-card">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Link New Reel</h3>
    <form action="instagram.php" method="POST" enctype="multipart/form-data">
      <?= csrfField() ?>
      
      <div class="form-group">
        <label class="field-label">Instagram Reel URL *</label>
        <input type="url" name="url" placeholder="https://www.instagram.com/reel/..." class="form-control" required>
        <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:6px;">Paste the full URL of the public Instagram Reel.</span>
      </div>

      <div class="form-group">
        <label class="field-label">Cover Thumbnail Image</label>
        <input type="file" name="image" accept="image/*" class="form-control">
        <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:6px;">Upload a vertical thumbnail (480x720) for preview speed.</span>
      </div>

      <div class="admin-form-inline-grid form-group">
        <div>
          <label class="field-label">Sort Index</label>
          <input type="number" name="sort_order" class="form-control" value="0">
        </div>
        <div>
          <label class="field-label">Display Status</label>
          <div style="display:flex; align-items:center; height:42px;">
            <input type="checkbox" name="is_active" id="is_active" checked style="width:18px; height:18px; margin-right:8px; cursor:pointer;">
            <label for="is_active" style="font-weight:600; font-size:0.85rem; cursor:pointer; color: var(--text-main);">Active & Visible</label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-gold btn-full" style="margin-top: 10px;">Link Video Reel</button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
