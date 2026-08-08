<?php
/**
 * ZEBIR LIBAS – Admin Orders List (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(o.order_number LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = "WHERE " . implode(" AND ", $where);
$stmt = $pdo->prepare("SELECT * FROM orders o $whereSql ORDER BY o.id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="admin-page-header">
  <h2>Customer Orders</h2>
</div>

<!-- Filters Bar -->
<div class="admin-search-bar" style="max-width: 580px;">
  <form action="orders" method="GET">
    <select name="status" class="form-control" onchange="this.form.submit()" style="width: 130px; height: 32px !important; padding: 4px 8px !important; font-size: 0.75rem !important;">
      <option value="">-- All Status --</option>
      <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="payment_verification" <?= $status === 'payment_verification' ? 'selected' : '' ?>>Verification</option>
      <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
      <option value="packed" <?= $status === 'packed' ? 'selected' : '' ?>>Packed</option>
      <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
      <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
      <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <div class="input-icon-wrap">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="text" name="q" class="form-control" placeholder="Search order #, name, phone..." value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn-admin btn-admin-primary">Search</button>
  </form>
</div>

<div class="admin-table-wrap">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><strong><?= e($o['order_number']) ?></strong></td>
            <td>
              <span style="font-weight: 600;"><?= e($o['shipping_name']) ?></span>
              <div style="font-size:0.75rem; color: var(--text-muted);"><?= e($o['shipping_phone']) ?></div>
            </td>
            <td><strong><?= formatPrice($o['total']) ?></strong></td>
            <td>
              <span style="text-transform:uppercase; font-size:0.75rem; font-weight:700;"><?= e($o['payment_method']) ?></span>
              <?php if ($o['payment_screenshot']): ?>
                <span style="display:block; font-size:0.7rem; color:#d97706; font-weight:bold; margin-top: 2px;">📸 Screenshot Attached</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="status-badge status-<?= e($o['status']) ?>">
                <?= e(str_replace('_', ' ', $o['status'])) ?>
              </span>
            </td>
            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td>
              <a href="order-detail?id=<?= $o['id'] ?>" class="btn-admin btn-admin-primary btn-admin-sm" title="View Details">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center py-4" style="text-align: center; color: var(--text-muted);">No orders found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
