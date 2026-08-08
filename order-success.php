<?php
/**
 * ZEBIR LIBAS – Order Confirmation / Success Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$orderNumber = sanitize($_GET['id'] ?? '');
if (!$orderNumber) redirectTo('index.php');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) redirectTo('index.php');

$pageTitle = "Order Placed – ZEBIR LIBAS";
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5 text-center">
  <div class="container" style="max-width: 600px;">
    <div style="width: 80px; height: 80px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: #16a34a; font-size: 2rem;">
      ✓
    </div>

    <h1 class="font-serif display-4 mb-2">Thank You For Your Order</h1>
    <p class="text-muted mb-4">Your order number is <strong class="text-main"><?= e($order['order_number']) ?></strong></p>

    <div style="background-color: var(--bg-secondary); padding: 24px; border-radius: 4px; text-align: left; margin-bottom: 32px;">
      <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
        <span class="text-muted">Payment Method</span>
        <span style="font-weight:600; text-transform:uppercase;"><?= e($order['payment_method']) ?></span>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
        <span class="text-muted">Total Amount</span>
        <span style="font-weight:600;"><?= formatPrice($order['total']) ?></span>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
        <span class="text-muted">Order Status</span>
        <span class="text-gold" style="font-weight:600; text-transform:uppercase;"><?= e(str_replace('_', ' ', $order['status'])) ?></span>
      </div>
    </div>
    
    <?php if (!empty($_SESSION['mailer_debug'])): ?>
      <div style="background-color: #1b1b1b; border: 1px solid var(--border-color); padding: 24px; border-radius: 8px; text-align: left; margin-bottom: 32px; border-left: 4px solid var(--accent-gold);">
        <h4 style="font-family: monospace; font-size: 1rem; margin-top: 0; margin-bottom: 12px; color: var(--accent-gold); display: flex; justify-content: space-between; align-items: center; letter-spacing: 1px; text-transform: uppercase;">
          <span>🛠️ SMTP Mailer Debug Log</span>
          <span style="font-size: 0.65rem; background: var(--accent-gold); color: #000; padding: 2px 8px; border-radius: 4px; font-weight: bold; letter-spacing: 0.5px;">Developer Debug</span>
        </h4>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php foreach ($_SESSION['mailer_debug'] as $log): ?>
            <div style="font-family: monospace; font-size: 0.8rem; padding: 12px; border-radius: 4px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); color: #eee;">
              <strong>Recipient:</strong> <?= e($log['to']) ?><br>
              <strong>Subject:</strong> <?= e($log['subject']) ?><br>
              <strong>Status:</strong> 
              <?php if ($log['status'] === 'sent'): ?>
                <span style="color: #2e7d32; background: #e8f5e9; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 0.7rem;">SUCCESSFULLY SENT</span>
              <?php else: ?>
                <span style="color: #c62828; background: #ffebee; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 0.7rem;">DELIVERY FAILED</span><br>
                <strong style="color: #ef5350;">Error Details:</strong> <span style="color: #ef5350; font-size: 0.78rem; word-break: break-all;"><?= e($log['error']) ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php unset($_SESSION['mailer_debug']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['new_account_password'])): ?>
      <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: 24px; border-radius: 8px; text-align: left; margin-bottom: 32px; border-left: 4px solid var(--accent-gold);">
        <h4 style="font-family: Georgia, serif; font-size: 1.15rem; margin-top: 0; margin-bottom: 8px; color: var(--accent-gold);">Account Created Automatically!</h4>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
          An account has been created for you using your shipping details. You can log in using these credentials to track your order and check out faster next time:
        </p>
        <div style="font-family: monospace; font-size: 0.9rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 16px; border-radius: 4px; color: var(--text-main);">
          <strong style="color:var(--text-muted);">Email:</strong> <?= e($_SESSION['new_account_email']) ?><br>
          <strong style="color:var(--text-muted); margin-top: 6px; display:inline-block;">Temporary Password:</strong> <span style="background:rgba(200, 150, 12, 0.12); padding:2px 8px; border-radius:4px; font-weight:700; border:1px dashed var(--accent-gold); color:var(--accent-gold);"><?= e($_SESSION['new_account_password']) ?></span>
        </div>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 12px; margin-bottom: 0;">
          Please copy or write down this password. You can change it anytime in your Profile Settings.
        </p>
      </div>
      <?php 
        unset($_SESSION['new_account_password']);
        unset($_SESSION['new_account_email']);
      ?>
    <?php endif; ?>

    <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:32px;">
      We have sent an order confirmation email to <strong><?= e($order['shipping_email']) ?></strong> with tracking instructions.
    </p>

    <div style="display:flex; gap:16px; justify-content:center;">
      <a href="<?= pageUrl('shop') ?>" class="btn-luxury-outline">CONTINUE SHOPPING</a>
      <a href="<?= pageUrl('orders') ?>" class="btn-luxury btn-gold">VIEW MY ORDERS</a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
