<?php
/**
 * ZEBIR LIBAS – Admin Dashboard Index (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();

// Stat Queries
$revenue = (float)$pdo->query("SELECT SUM(total) FROM orders WHERE status NOT IN ('cancelled')")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','payment_verification')")->fetchColumn();
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

// Latest 8 Orders
$latestOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 8")->fetchAll();
?>

<div class="admin-page-header">
  <h2>Dashboard Summary</h2>
</div>

<!-- Analytics Cards Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="info-block">
      <span class="lbl">Total Revenue</span>
      <span class="val"><?= formatPrice($revenue) ?></span>
    </div>
    <div class="icon-block">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="info-block">
      <span class="lbl">Total Orders</span>
      <span class="val"><?= number_format($totalOrders) ?></span>
    </div>
    <div class="icon-block">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
    </div>
  </div>

  <div class="stat-card featured-stat">
    <div class="info-block">
      <span class="lbl">Pending / Verification</span>
      <span class="val"><?= number_format($pendingOrders) ?></span>
    </div>
    <div class="icon-block">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="info-block">
      <span class="lbl">Active Products</span>
      <span class="val"><?= number_format($totalProducts) ?></span>
    </div>
    <div class="icon-block">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="info-block">
      <span class="lbl">Customers</span>
      <span class="val"><?= number_format($totalCustomers) ?></span>
    </div>
    <div class="icon-block">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
    </div>
  </div>
</div>

<!-- Latest Orders Section -->
<div class="admin-card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <h3 style="margin:0; font-size:1.2rem; font-weight: 700;">Latest Orders</h3>
    <a href="orders.php" class="btn-admin btn-admin-primary btn-admin-sm">View All Orders &rarr;</a>
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
        <?php if (!empty($latestOrders)): ?>
          <?php foreach ($latestOrders as $o): ?>
            <tr>
              <td><strong><?= e($o['order_number']) ?></strong></td>
              <td>
                <span style="font-weight: 600;"><?= e($o['shipping_name']) ?></span>
                <div style="font-size:0.75rem; color: var(--text-muted);"><?= e($o['shipping_email']) ?></div>
              </td>
              <td><strong><?= formatPrice($o['total']) ?></strong></td>
              <td>
                <span style="text-transform:uppercase; font-size:0.75rem; font-weight:700;">
                  <?= e($o['payment_method']) ?>
                </span>
                <?php if ($o['payment_status'] === 'uploaded'): ?>
                  <span style="display:block; font-size:0.7rem; color:#d97706; font-weight:600; margin-top:2px;">Verify Screenshot</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="status-badge status-<?= e($o['status']) ?>">
                  <?= e(str_replace('_', ' ', $o['status'])) ?>
                </span>
              </td>
              <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
              <td>
                <a href="order-detail.php?id=<?= $o['id'] ?>" class="btn-admin btn-admin-primary btn-admin-sm" title="View Details">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-4" style="text-align: center; color: var(--text-muted);">No orders placed yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
