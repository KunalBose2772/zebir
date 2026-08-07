<?php
/**
 * ZEBIR LIBAS – Admin Coupon Management (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

if (isset($_GET['delete'])) {
    verifyCsrf();
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    setFlash('success', 'Coupon deleted successfully.');
    redirectTo('admin/coupons.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $code    = strtoupper(sanitize($_POST['code'] ?? ''));
    $type    = sanitize($_POST['type'] ?? 'percentage');
    $val     = (float)($_POST['value'] ?? 0);
    $minOrd  = (float)($_POST['min_order_amount'] ?? 0);
    $expiry  = $_POST['expiry_date'] ?: null;

    if ($code && $val > 0) {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_order_amount, expiry_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $val, $minOrd, $expiry]);
        setFlash('success', 'Coupon created successfully.');
        redirectTo('admin/coupons.php');
    }
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="admin-page-header">
  <h2>Coupon Management</h2>
</div>

<div class="admin-grid-1-2">
  
  <div class="admin-card">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Create Coupon</h3>
    <form action="coupons.php" method="POST">
      <?= csrfField() ?>
      
      <div class="form-group">
        <label class="field-label">Coupon Code *</label>
        <input type="text" name="code" class="form-control" placeholder="e.g. FESTIVE15" style="text-transform:uppercase;" required>
      </div>

      <div class="form-group">
        <label class="field-label">Discount Type</label>
        <select name="type" class="form-control">
          <option value="percentage">Percentage (%)</option>
          <option value="fixed">Fixed Amount (₹)</option>
        </select>
      </div>

      <div class="form-group">
        <label class="field-label">Discount Value *</label>
        <input type="number" step="0.01" name="value" class="form-control" placeholder="e.g. 15 or 250" required>
      </div>

      <div class="form-group">
        <label class="field-label">Min Order Amount (₹)</label>
        <input type="number" step="0.01" name="min_order_amount" class="form-control" value="0">
      </div>

      <div class="form-group">
        <label class="field-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control">
      </div>

      <button type="submit" class="btn-admin btn-admin-gold">Create Coupon</button>
    </form>
  </div>

  <div class="admin-card" style="min-width: 0;">
    <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Active Coupons</h3>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Discount</th>
            <th>Min Order</th>
            <th>Expiry</th>
            <th style="width: 80px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coupons as $c): ?>
            <tr>
              <td><strong><code><?= e($c['code']) ?></code></strong></td>
              <td>
                <span style="font-weight: 700; color: var(--primary-color);">
                  <?= $c['type'] === 'percentage' ? $c['value'] . '%' : formatPrice($c['value']) ?>
                </span>
              </td>
              <td><?= formatPrice($c['min_order_amount']) ?></td>
              <td>
                <?php if ($c['expiry_date']): ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted);"><?= $c['expiry_date'] ?></span>
                <?php else: ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No Expiry</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="coupons.php?delete=<?= $c['id'] ?>&csrf_token=<?= csrfToken() ?>" onclick="return confirm('Delete coupon?')" class="btn-admin btn-admin-danger btn-admin-sm" style="padding:6px;" title="Delete">
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
