<?php
/**
 * ZEBIR LIBAS – Admin Order Detail & Shipping Update (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/mailer.php';

$id = (int)($_GET['id'] ?? 0);
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) redirectTo('admin/orders.php');

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

// Handle Status & Shipping Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (isset($_POST['update_status'])) {
        $newStatus     = sanitize($_POST['status'] ?? '');
        $paymentStatus = sanitize($_POST['payment_status'] ?? $order['payment_status']);
        $note          = sanitize($_POST['status_note'] ?? '');

        if ($newStatus && $newStatus !== $order['status']) {
            $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?")
                ->execute([$newStatus, $paymentStatus, $id]);

            $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_by) VALUES (?, ?, ?, 'admin')")
                ->execute([$id, $newStatus, $note]);

            // Email customer
            sendOrderStatusEmail(array_merge($order, ['status' => $newStatus]), $newStatus);

            setFlash('success', 'Order status updated to ' . strtoupper(str_replace('_', ' ', $newStatus)) . ' and customer notified.');
            redirectTo("admin/order-detail.php?id=$id");
        }
    }

    if (isset($_POST['update_shipping'])) {
        $courier    = sanitize($_POST['courier_name'] ?? '');
        $trackingNo = sanitize($_POST['tracking_number'] ?? '');
        $trackUrl   = sanitize($_POST['tracking_url'] ?? '');
        $dispatch   = $_POST['dispatch_date'] ?: null;
        $expected   = $_POST['expected_delivery'] ?: null;

        $pdo->prepare("UPDATE orders SET courier_name = ?, tracking_number = ?, tracking_url = ?, dispatch_date = ?, expected_delivery = ?, status = 'shipped' WHERE id = ?")
            ->execute([$courier, $trackingNo, $trackUrl, $dispatch, $expected, $id]);

        $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_by) VALUES (?, 'shipped', ?, 'admin')")
            ->execute([$id, "Shipped via $courier (Tracking #: $trackingNo)"]);

        // Send Shipping Email
        $updatedOrder = array_merge($order, [
            'courier_name' => $courier,
            'tracking_number' => $trackingNo,
            'tracking_url' => $trackUrl,
            'expected_delivery' => $expected
        ]);
        sendShippingEmail($updatedOrder);

        setFlash('success', 'Shipping details updated and notification email sent.');
        redirectTo("admin/order-detail.php?id=$id");
    }
}
?>

<div class="admin-page-header">
  <div>
    <h2>Order #<?= e($order['order_number']) ?></h2>
    <span style="font-size:0.8rem; color: var(--text-muted);">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
  </div>
  <div class="admin-page-actions">
    <a href="../invoice.php?id=<?= urlencode($order['order_number']) ?>" target="_blank" class="btn-admin btn-admin-gold btn-admin-sm">View / Print Invoice</a>
    <a href="shipping-bill.php?id=<?= $order['id'] ?>" target="_blank" class="btn-admin btn-admin-primary btn-admin-sm">Print Shipping Bill</a>
  </div>
</div>

<div class="admin-grid-2-1">
  
  <!-- Left Side: Order Items & Shipping Address -->
  <div style="display:flex; flex-direction:column; gap:24px;">
    
    <!-- Items Table -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Order Items</h3>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div style="display:flex; gap:12px; align-items:center;">
                    <img src="<?= productImageUrl($it['product_image']) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:6px; border: 1px solid var(--border-color);">
                    <div>
                      <strong style="color: var(--primary-color);"><?= e($it['product_name']) ?></strong>
                      <?php if ($it['size']): ?><span style="font-size:0.75rem; color: var(--text-muted); display:block; margin-top:2px;">Size: <?= e($it['size']) ?></span><?php endif; ?>
                      <?php if ($it['color']): ?><span style="font-size:0.75rem; color: var(--text-muted); display:block; margin-top:2px;">Color: <?= e($it['color']) ?></span><?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= formatPrice($it['price']) ?></td>
                <td><?= $it['quantity'] ?></td>
                <td><strong><?= formatPrice($it['total']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Totals Summary -->
      <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color); width:280px; margin-left:auto;">
        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem;">
          <span style="color: var(--text-muted);">Subtotal</span><span><?= formatPrice($order['subtotal']) ?></span>
        </div>
        <?php if ($order['discount_amount'] > 0): ?>
          <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem; color: var(--accent-gold);">
            <span>Discount</span><span>- <?= formatPrice($order['discount_amount']) ?></span>
          </div>
        <?php endif; ?>
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:0.85rem;">
          <span style="color: var(--text-muted);">Shipping</span><span><?= formatPrice($order['shipping_charge']) ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.1rem; border-top:2px solid var(--primary-color); padding-top:8px;">
          <span>Grand Total</span><span style="color: var(--accent-gold-hover);"><?= formatPrice($order['total']) ?></span>
        </div>
      </div>
    </div>

    <!-- Customer & Shipping Information -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Customer & Shipping Address</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
          <span style="font-size:0.75rem; text-transform:uppercase; color: var(--text-muted); font-weight:600; display:block;">Recipient Name</span>
          <span style="font-weight:600; font-size:0.9rem; margin-top:4px; display:block;"><?= e($order['shipping_name']) ?></span>
        </div>
        <div>
          <span style="font-size:0.75rem; text-transform:uppercase; color: var(--text-muted); font-weight:600; display:block;">Contact Number</span>
          <span style="font-weight:600; font-size:0.9rem; margin-top:4px; display:block;"><?= e($order['shipping_phone']) ?></span>
        </div>
        <div>
          <span style="font-size:0.75rem; text-transform:uppercase; color: var(--text-muted); font-weight:600; display:block;">Email Address</span>
          <span style="font-weight:600; font-size:0.9rem; margin-top:4px; display:block;"><?= e($order['shipping_email']) ?></span>
        </div>
      </div>
      <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border-light);">
        <span style="font-size:0.75rem; text-transform:uppercase; color: var(--text-muted); font-weight:600; display:block;">Delivery Address</span>
        <span style="font-weight:500; font-size:0.9rem; margin-top:4px; display:block; line-height:1.5;">
          <?= e($order['shipping_address_line1']) ?><?= $order['shipping_address_line2'] ? ', ' . e($order['shipping_address_line2']) : '' ?><br>
          <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> - <strong><?= e($order['shipping_pincode']) ?></strong>
        </span>
      </div>
    </div>

  </div>

  <!-- Right Side: Workflow Actions -->
  <div style="display:flex; flex-direction:column; gap:24px;">
    
    <!-- Order Status & Payment Verification -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Order & Payment Status</h3>
      
      <form action="order-detail.php?id=<?= $id ?>" method="POST">
        <?= csrfField() ?>
        
        <div class="form-group">
          <label class="field-label">Current Order Status</label>
          <select name="status" class="form-control">
            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="payment_verification" <?= $order['status'] === 'payment_verification' ? 'selected' : '' ?>>Payment Verification</option>
            <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="packed" <?= $order['status'] === 'packed' ? 'selected' : '' ?>>Packed</option>
            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>

        <div class="form-group">
          <label class="field-label">Payment Status</label>
          <select name="payment_status" class="form-control">
            <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="uploaded" <?= $order['payment_status'] === 'uploaded' ? 'selected' : '' ?>>Screenshot Uploaded</option>
            <option value="verified" <?= $order['payment_status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
            <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
          </select>
        </div>

        <button type="submit" name="update_status" class="btn-admin btn-admin-gold btn-full" style="margin-top: 10px;">Update Order Status</button>
      </form>

      <!-- Display Payment Screenshot if UPI -->
      <?php if ($order['payment_screenshot']): ?>
        <div style="border-top:1px solid var(--border-color); padding-top:16px; margin-top:20px;">
          <label class="field-label" style="color:#d97706;">Uploaded Payment Screenshot</label>
          <a href="<?= UPLOAD_URL . 'payments/' . e($order['payment_screenshot']) ?>" target="_blank" style="display: block; margin-top: 8px;">
            <img src="<?= UPLOAD_URL . 'payments/' . e($order['payment_screenshot']) ?>" style="width:100%; max-height:220px; object-fit:contain; border:1px solid var(--border-color); border-radius:8px;">
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Shipping & Tracking Details Entry -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Manual Courier & Tracking</h3>
      
      <form action="order-detail.php?id=<?= $id ?>" method="POST">
        <?= csrfField() ?>
        
        <div class="form-group">
          <label class="field-label">Courier Name</label>
          <input type="text" name="courier_name" class="form-control" placeholder="e.g. BlueDart, DTDC, Delhivery" value="<?= e($order['courier_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Tracking AWB / Number</label>
          <input type="text" name="tracking_number" class="form-control" placeholder="e.g. 12345678" value="<?= e($order['tracking_number'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Tracking URL (Optional)</label>
          <input type="url" name="tracking_url" class="form-control" placeholder="e.g. https://..." value="<?= e($order['tracking_url'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Dispatch Date</label>
          <input type="date" name="dispatch_date" class="form-control" value="<?= e($order['dispatch_date'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Expected Delivery Date</label>
          <input type="date" name="expected_delivery" class="form-control" value="<?= e($order['expected_delivery'] ?? '') ?>">
        </div>

        <button type="submit" name="update_shipping" class="btn-admin btn-admin-primary btn-full" style="margin-top: 10px;">Save Tracking & Email Dispatch</button>
      </form>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
