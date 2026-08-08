<?php
/**
 * ZEBIR LIBAS – Order History & Tracking
 */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$pageTitle = "My Orders – ZEBIR LIBAS";
$customer = currentCustomer();
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$stmt->execute([$customer['id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4">
  <div class="container text-center">
    <h1 class="font-serif display-4">Order History</h1>
    <p class="text-muted mt-3" style="max-width: 720px; margin: 0 auto;">Track your purchases and stay updated on every delivery milestone.</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="account-grid-layout">
      
      <!-- Account Sidebar -->
      <aside style="border-right: 1px solid var(--border-color); padding-right: 24px;" class="account-sidebar">
        <ul style="list-style:none; line-height:2.2;">
          <li><a href="account.php">Profile Details</a></li>
          <li><a href="orders.php" style="font-weight:600; color:var(--accent-gold);">Order History</a></li>
          <li><a href="wishlist.php">Saved Wishlist</a></li>
          <li class="mt-3"><a href="logout.php" class="text-danger" style="font-size:0.85rem;">Sign Out</a></li>
        </ul>
      </aside>

      <!-- Orders List -->
      <main>
        <?php if (!empty($orders)): ?>
          <div style="display:flex; flex-direction:column; gap:24px;">
            <?php foreach ($orders as $o): 
              $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
              $itemsStmt->execute([$o['id']]);
              $items = $itemsStmt->fetchAll();
            ?>
              <div class="content-card" style="padding: 24px;">
                <div class="order-card-header">
                  <div>
                    <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; display:block;">Order Placed</span>
                    <strong style="font-size:0.9rem;"><?= date('d M Y', strtotime($o['created_at'])) ?></strong>
                  </div>
                  <div>
                    <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; display:block;">Order Number</span>
                    <strong style="font-size:0.9rem;"><?= e($o['order_number']) ?></strong>
                  </div>
                  <div>
                    <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; display:block;">Status</span>
                    <span class="status-badge" style="background:var(--bg-secondary); color:var(--text-main); border:1px solid var(--border-color); padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:600;">
                      <?= e(str_replace('_', ' ', strtoupper($o['status']))) ?>
                    </span>
                  </div>
                  <div>
                    <a href="invoice.php?id=<?= urlencode($o['order_number']) ?>" target="_blank" class="btn-luxury-outline btn-sm">INVOICE</a>
                  </div>
                </div>

                <!-- Items Preview -->
                <div class="mb-3">
                  <?php foreach ($items as $it): ?>
                    <div style="display:flex; gap:12px; align-items:center; margin-bottom:10px;">
                      <img src="<?= productImageUrl($it['product_image']) ?>" alt="<?= e($it['product_name']) ?>" style="width:40px; height:50px; object-fit:cover; border-radius:2px;">
                      <div style="font-size:0.85rem;">
                        <span style="font-weight:500;"><?= e($it['product_name']) ?></span> × <?= $it['quantity'] ?>
                        <span style="display:block; color:var(--text-muted); font-size:0.75rem;"><?= formatPrice($it['price']) ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Shipping & Tracking Details -->
                <?php if ($o['courier_name'] || $o['tracking_number']): ?>
                  <div style="background-color:var(--bg-secondary); padding:16px; border-radius:4px; font-size:0.85rem; margin-top:16px;">
                    <h5 class="font-serif mb-1" style="font-size:1rem; color:var(--accent-gold);">Shipping Tracking Details</h5>
                    <p class="mb-1">Courier: <strong><?= e($o['courier_name']) ?></strong> | AWB/Tracking: <strong><?= e($o['tracking_number']) ?></strong></p>
                    <?php if ($o['expected_delivery']): ?>
                      <p class="mb-1">Expected Delivery: <strong><?= date('d M Y', strtotime($o['expected_delivery'])) ?></strong></p>
                    <?php endif; ?>
                    <?php if ($o['tracking_url']): ?>
                      <a href="<?= e($o['tracking_url']) ?>" target="_blank" style="color:var(--text-main); font-weight:600; text-decoration:underline;">Track Package →</a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <!-- Order Tracking Timeline -->
                <?php
                  $historyStmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC");
                  $historyStmt->execute([$o['id']]);
                  $history = $historyStmt->fetchAll();
                  if (!empty($history)):
                ?>
                  <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--border-color);">
                    <h5 class="font-serif mb-3" style="font-size:1.1rem;">Order Tracking</h5>
                    <div class="tracking-timeline" style="margin-top:12px;">
                      <?php foreach ($history as $idx => $h): 
                        $isLast = ($idx === count($history) - 1);
                      ?>
                        <div style="display:flex; gap:16px; margin-bottom:<?= $isLast ? '0' : '20px' ?>; position:relative;">
                          <?php if (!$isLast): ?>
                            <!-- Connecting Line -->
                            <div style="position:absolute; left:7px; top:20px; bottom:-20px; width:2px; background:var(--border-color); z-index:1;"></div>
                          <?php endif; ?>
                          
                          <!-- Dot -->
                          <div style="width:16px; height:16px; border-radius:50%; background:<?= $isLast ? 'var(--accent-gold)' : 'var(--bg-secondary)' ?>; border:2px solid <?= $isLast ? 'var(--accent-gold)' : 'var(--border-color)' ?>; z-index:2; margin-top:2px;"></div>
                          
                          <!-- Content -->
                          <div style="flex:1;">
                            <div style="font-weight:700; font-size:0.9rem; color:var(--text-main); text-transform:uppercase; letter-spacing:1px;">
                              <?= e(str_replace('_', ' ', $h['status'])) ?>
                            </div>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">
                              <?= date('d M Y, h:i A', strtotime($h['created_at'])) ?>
                            </div>
                            <?php if ($h['note']): ?>
                              <div style="font-size:0.85rem; color:var(--text-main); background:var(--bg-secondary); border:1px solid var(--border-color); padding:8px 12px; border-radius:4px; display:inline-block; margin-top:4px;">
                                <?= e($h['note']) ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <h3 class="font-serif mb-2">No orders placed yet</h3>
            <p class="text-muted mb-4">When you place orders, you can track them here.</p>
            <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold">EXPLORE SHOP</a>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
