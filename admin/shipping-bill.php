<?php
/**
 * ZEBIR LIBAS – Professional Shipping Bill / Label Generator
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) die('Order not specified.');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) die('Order not found.');

// Fetch items
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$siteName = getSetting('site_name', 'ZEBIR LIBAS');
$sitePhone = getSetting('site_phone', '+91 9006666622');
$siteEmail = getSetting('site_email', 'zebirlibas@gmail.com');
$siteAddress = getSetting('site_address', 'Dhipatoli Pundag Ranchi, Mirza Lane');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shipping Bill - <?= e($order['order_number']) ?></title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000; margin: 0; padding: 20px; background: #fff; font-size: 14px; }
    .label-container { max-width: 600px; margin: 0 auto; border: 2px dashed #000; padding: 30px; border-radius: 8px; position: relative; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
    .logo-img { height: 45px; width: auto; object-fit: contain; }
    .title { font-size: 20px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; border: 2px solid #000; padding: 4px 12px; }
    .section { margin-bottom: 20px; }
    .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 6px; letter-spacing: 1px; }
    .address-box { font-size: 16px; line-height: 1.6; font-weight: 500; }
    .address-box strong { font-size: 19px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 15px 0; margin-bottom: 20px; }
    .meta-item { font-size: 13px; line-height: 1.5; }
    .meta-value { font-size: 16px; font-weight: bold; }
    .payment-badge { display: inline-block; border: 3px solid #000; padding: 6px 16px; font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .items-table th, .items-table td { border: 1px solid #000; padding: 8px 10px; text-align: left; font-size: 12px; }
    .items-table th { background: #f0f0f0; text-transform: uppercase; font-weight: bold; }
    .footer { display: flex; justify-content: space-between; font-size: 12px; border-top: 1px solid #000; padding-top: 15px; margin-top: 30px; }
    .btn-print { background: #000; color: #fff; border: none; padding: 10px 20px; cursor: pointer; font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; font-weight: bold; margin-bottom: 20px; }
    @media print { .btn-print-wrapper { display: none; } .label-container { border: 2px solid #000; } }
  </style>
</head>
<body>

<div class="btn-print-wrapper" style="text-align: center;">
  <button onclick="window.print()" class="btn-print">Print Shipping Bill</button>
</div>

<div class="label-container">
  
  <!-- Header -->
  <div class="header">
    <div style="background: #0f172a; padding: 10px 16px; border-radius: 4px; display: inline-block; line-height: 1;">
      <img src="<?= BASE_URL ?>assets/images/logoZ.webp" alt="ZEBIR LIBAS" style="height: 36px; width: auto; display: block; object-fit: contain;">
    </div>
    <div class="title">SHIPPING BILL</div>
  </div>

  <!-- Shipping Address -->
  <div class="section">
    <div class="section-title">Deliver To:</div>
    <div class="address-box">
      <strong><?= e($order['shipping_name']) ?></strong><br>
      <?= e($order['shipping_address_line1']) ?><br>
      <?php if ($order['shipping_address_line2']): ?><?= e($order['shipping_address_line2']) ?><br><?php endif; ?>
      <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> - <strong><?= e($order['shipping_pincode']) ?></strong><br>
      <strong>Phone: <?= e($order['shipping_phone']) ?></strong><br>
      Email: <?= e($order['shipping_email']) ?>
    </div>
  </div>

  <!-- Meta Grid -->
  <div class="grid-2">
    <div>
      <div class="meta-item">Order Number:</div>
      <div class="meta-value">#<?= e($order['order_number']) ?></div>
      
      <div class="meta-item" style="margin-top: 10px;">Order Date:</div>
      <div class="meta-value"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
    </div>
    <div style="text-align: right;">
      <div class="meta-item">Payment Method:</div>
      <div class="payment-badge">
        <?= e(strtoupper($order['payment_method'])) ?>
      </div>
      
      <div class="meta-item" style="margin-top: 10px;">Amount to Collect:</div>
      <div class="meta-value" style="font-size: 20px;">
        <?= $order['payment_method'] === 'cod' ? formatPrice($order['total']) : '₹0.00 (PREPAID)' ?>
      </div>
    </div>
  </div>

  <!-- Items Table -->
  <div class="section">
    <div class="section-title">Package Contents:</div>
    <table class="items-table">
      <thead>
        <tr>
          <th>Item</th>
          <th style="width: 80px;">Size</th>
          <th style="width: 80px;">Color</th>
          <th style="width: 60px; text-align: center;">Qty</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><strong><?= e($item['product_name']) ?></strong></td>
            <td><?= e($item['size'] ?: '-') ?></td>
            <td><?= e($item['color'] ?: '-') ?></td>
            <td style="text-align: center;"><strong><?= $item['quantity'] ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div>
      <strong>Sender:</strong><br>
      <?= e($siteName) ?><br>
      <?= e($siteAddress) ?><br>
      Phone: <?= e($sitePhone) ?>
    </div>
    <div style="text-align: right; align-self: flex-end;">
      <strong>Signature / Date</strong><br>
      _______________________
    </div>
  </div>

</div>

</body>
</html>
