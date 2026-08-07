<?php
/**
 * ZEBIR LIBAS – Admin Customer List (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();
$customers = $pdo->query("SELECT c.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent FROM customers c LEFT JOIN orders o ON c.id = o.customer_id GROUP BY c.id ORDER BY c.id DESC")->fetchAll();
?>

<div class="admin-page-header">
  <h2>Customer Accounts</h2>
</div>

<div class="admin-table-wrap">
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width: 80px;">ID</th>
        <th>Customer Name</th>
        <th>Email Address</th>
        <th>Phone</th>
        <th>Orders</th>
        <th>Total Spent</th>
        <th>Joined Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($customers)): ?>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><code>#<?= $c['id'] ?></code></td>
            <td><strong style="color: var(--primary-color);"><?= e($c['name']) ?></strong></td>
            <td><?= e($c['email']) ?></td>
            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?= e($c['phone'] ?: '-') ?></span></td>
            <td><span style="font-weight: 700; background-color: var(--border-light); padding: 2px 8px; border-radius: 4px;"><?= $c['order_count'] ?></span></td>
            <td><strong><?= formatPrice($c['total_spent'] ?: 0) ?></strong></td>
            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center py-4" style="text-align: center; color: var(--text-muted);">No customer accounts found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
