<?php
/**
 * ZEBIR LIBAS – Professional Invoice Generator
 */
require_once __DIR__ . '/includes/bootstrap.php';

$orderNumber = sanitize($_GET['id'] ?? '');
if (!$orderNumber) die('Order not specified.');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) die('Order not found.');

// Security check: Only allow customer who placed it or admin
if (!isAdminLoggedIn()) {
    if (!isLoggedIn() || (int)$order['customer_id'] !== (int)$_SESSION['customer_id']) {
        die('Unauthorized access to invoice.');
    }
}

// Fetch items
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$siteName = getSetting('site_name', 'ZEBIR LIBAS');
$sitePhone = getSetting('site_phone', '+91 00000 00000');
$siteEmail = getSetting('site_email', 'hello@zebirl.com');
$siteAddress = getSetting('site_address', 'Mumbai, India');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice - <?= e($order['order_number']) ?></title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1a1a1a; margin: 0; padding: 40px; background: #fff; font-size: 14px; }
    .invoice-container { max-width: 800px; margin: 0 auto; border: 1px solid #eee; padding: 40px; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #1a1a1a; padding-bottom: 20px; margin-bottom: 30px; }
    .logo { font-size: 24px; font-weight: bold; letter-spacing: 4px; font-family: Georgia, serif; background: #0f172a; padding: 12px 16px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .invoice-title { font-size: 28px; text-transform: uppercase; letter-spacing: 2px; color: #888; text-align: right; }
    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    .table th { background: #f9f9f9; padding: 12px; text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; color: #666; border-bottom: 1px solid #ddd; }
    .table td { padding: 14px 12px; border-bottom: 1px solid #eee; }
    .totals { width: 300px; margin-left: auto; }
    .totals div { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
    .totals .grand-total { font-weight: bold; font-size: 16px; border-top: 2px solid #1a1a1a; padding-top: 10px; margin-top: 6px; }
    .btn-print { background: #1a1a1a; color: #fff; border: none; padding: 12px 24px; cursor: pointer; font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; font-weight: bold; }
    @media print { .btn-print { display: none; } .invoice-container { border: none; padding: 0; } .logo { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
  </style>
</head>
<body>

<div class="invoice-container">
  <div style="text-align: right; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn-print">PRINT / DOWNLOAD INVOICE</button>
  </div>

  <div class="header" style="align-items: center;">
    <div>
      <div class="logo">
        <img src="<?= BASE_URL ?>assets/images/logoZ.webp" alt="ZEBIR LIBAS" style="height: 36px; width: auto; display: block; object-fit: contain;">
      </div>
      <div style="color: #666; font-size: 12px; margin-top: 10px;">
        <?= e($siteAddress) ?><br>
        Email: <?= e($siteEmail) ?> | Phone: <?= e($sitePhone) ?>
      </div>
    </div>
    <div>
      <div class="invoice-title">INVOICE</div>
      <div style="text-align: right; margin-top: 6px;">
        <strong>Invoice #:</strong> <?= e($order['order_number']) ?><br>
        <strong>Date:</strong> <?= date('d M Y', strtotime($order['created_at'])) ?>
      </div>
    </div>
  </div>

  <div class="details-grid">
    <div>
      <h4 style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; color: #888; margin: 0 0 10px;">Billed & Shipped To:</h4>
      <strong><?= e($order['shipping_name']) ?></strong><br>
      <?= e($order['shipping_address_line1']) ?><br>
      <?php if ($order['shipping_address_line2']): ?><?= e($order['shipping_address_line2']) ?><br><?php endif; ?>
      <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> - <?= e($order['shipping_pincode']) ?><br>
      Phone: <?= e($order['shipping_phone']) ?><br>
      Email: <?= e($order['shipping_email']) ?>
    </div>

    <div>
      <h4 style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; color: #888; margin: 0 0 10px;">Order Info:</h4>
      <strong>Payment Method:</strong> <?= strtoupper(e($order['payment_method'])) ?><br>
      <strong>Payment Status:</strong> <?= ucfirst(e($order['payment_status'])) ?><br>
      <strong>Order Status:</strong> <?= ucfirst(e(str_replace('_', ' ', $order['status']))) ?>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Item Description</th>
        <th>Price</th>
        <th>Qty</th>
        <th style="text-align: right;">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td>
            <strong><?= e($item['product_name']) ?></strong>
            <?php if ($item['size']): ?> &nbsp;| Size: <?= e($item['size']) ?><?php endif; ?>
            <?php if ($item['color']): ?> &nbsp;| Color: <?= e($item['color']) ?><?php endif; ?>
          </td>
          <td><?= formatPrice($item['price']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td style="text-align: right;"><?= formatPrice($item['total']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="totals">
    <div><span>Subtotal</span><span><?= formatPrice($order['subtotal']) ?></span></div>
    <?php if ($order['discount_amount'] > 0): ?>
      <div><span>Discount</span><span>- <?= formatPrice($order['discount_amount']) ?></span></div>
    <?php endif; ?>
    <div><span>Shipping Charge</span><span><?= formatPrice($order['shipping_charge']) ?></span></div>
    <div class="grand-total"><span>Grand Total</span><span><?= formatPrice($order['total']) ?></span></div>
  </div>

  <div style="margin-top: 60px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #888; font-size: 12px;">
    Thank you for shopping with <?= e($siteName) ?>. For inquiries, contact <?= e($siteEmail) ?>
  </div>
</div>

</body>
</html>
