<?php
/**
 * ZEBIR LIBAS – Shopping Bag / Cart Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Shopping Bag – ZEBIR LIBAS";
$cart = getCart();

// Coupon processing
$couponError = '';
$discount = 0;
if (isset($_POST['apply_coupon'])) {
    verifyCsrf();
    $code = strtoupper(sanitize($_POST['coupon_code'] ?? ''));
    if ($code) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if ($coupon) {
            $subtotal = cartTotal();
            if ($subtotal >= $coupon['min_order_amount']) {
                if ($coupon['type'] === 'percentage') {
                    $discount = ($subtotal * $coupon['value']) / 100;
                } else {
                    $discount = (float)$coupon['value'];
                }
                $_SESSION['applied_coupon'] = [
                    'code'     => $coupon['code'],
                    'discount' => $discount
                ];
                setFlash('success', 'Coupon code applied successfully!');
            } else {
                $couponError = 'Minimum order amount for this coupon is ' . formatPrice($coupon['min_order_amount']);
            }
        } else {
            $couponError = 'Invalid or expired coupon code.';
        }
    }
}

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$subtotal = cartTotal();
$discount = $appliedCoupon['discount'] ?? 0;
$freeShippingThreshold = (float)getSetting('free_shipping_amount', '999');
$shippingCharge = ($subtotal - $discount) >= $freeShippingThreshold || $subtotal == 0 ? 0 : (float)getSetting('shipping_charge', '99');
$grandTotal = max(0, $subtotal - $discount + $shippingCharge);

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4">
  <div class="container text-center">
    <h1 class="font-serif display-4">Shopping Bag</h1>
    <p class="text-muted mt-3" style="max-width: 720px; margin: 0 auto;">Review your selected pieces and complete your order with confidence.</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php if (!empty($cart)): ?>
      <div class="cart-layout-container">
        
        <!-- Cart Items Table -->
        <div>
          <table class="cart-table">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 12px 0; font-size: 0.75rem; letter-spacing: 1.5px; text-transform: uppercase;">Product</th>
                <th style="padding: 12px 0; font-size: 0.75rem; letter-spacing: 1.5px; text-transform: uppercase;">Price</th>
                <th style="padding: 12px 0; font-size: 0.75rem; letter-spacing: 1.5px; text-transform: uppercase; text-align: center;">Quantity</th>
                <th style="padding: 12px 0; font-size: 0.75rem; letter-spacing: 1.5px; text-transform: uppercase; text-align: right;">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $key => $item): 
                $itemTotal = $item['price'] * $item['qty'];
              ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                  <td class="cart-product-cell" style="padding: 20px 0;">
                    <div style="display: flex; gap: 16px; align-items: center;">
                      <img src="<?= productImageUrl($item['image']) ?>" alt="<?= e($item['name']) ?>" style="width: 70px; height: 90px; object-fit: cover; border-radius: 2px;">
                      <div>
                        <h4 class="font-serif" style="font-size: 1.1rem; margin-bottom: 4px;"><?= e($item['name']) ?></h4>
                        <?php if ($item['size']): ?><span style="font-size:0.75rem; color:var(--text-muted); display:block;">Size: <?= e($item['size']) ?></span><?php endif; ?>
                        <?php if ($item['color']): ?><span style="font-size:0.75rem; color:var(--text-muted); display:block;">Color: <?= e($item['color']) ?></span><?php endif; ?>
                        <button type="button" class="btn-text text-danger mt-2" onclick="removeBagItem('<?= $key ?>')" style="background:none; border:none; padding:0; cursor:pointer; font-size:0.75rem; text-decoration:underline;">Remove</button>
                      </div>
                    </div>
                  </td>
                  <td data-label="Price" class="cart-price-cell" style="padding: 20px 0; font-weight: 500;"><?= formatPrice($item['price']) ?></td>
                  <td data-label="Quantity" class="cart-qty-cell" style="padding: 20px 0; text-align: center;">
                    <div class="cart-qty-selector">
                      <button type="button" class="cart-qty-btn qty-minus" onclick="updateBagQty('<?= $key ?>', <?= $item['qty'] - 1 ?>)">&minus;</button>
                      <input type="text" class="cart-qty-input" value="<?= $item['qty'] ?>" readonly>
                      <button type="button" class="cart-qty-btn qty-plus" onclick="updateBagQty('<?= $key ?>', <?= $item['qty'] + 1 ?>)">&plus;</button>
                    </div>
                  </td>
                  <td data-label="Total" class="cart-total-cell" style="padding: 20px 0; text-align: right; font-weight: 600;"><?= formatPrice($itemTotal) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Order Summary Card -->
        <div>
          <div style="background-color: var(--bg-secondary); padding: 32px; border-radius: 4px;">
            <h3 class="font-serif mb-4" style="font-size: 1.5rem;">Order Summary</h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
              <span>Subtotal</span>
              <span><?= formatPrice($subtotal) ?></span>
            </div>

            <?php if ($appliedCoupon): ?>
              <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; color: var(--accent-gold);">
                <span>Coupon (<?= e($appliedCoupon['code']) ?>)</span>
                <span>- <?= formatPrice($discount) ?></span>
              </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 0.9rem;">
              <span>Shipping</span>
              <span><?= $shippingCharge > 0 ? formatPrice($shippingCharge) : '<span class="text-success">FREE</span>' ?></span>
            </div>

            <!-- Coupon Input -->
            <form action="cart.php" method="POST" class="coupon-form mb-4">
              <?= csrfField() ?>
              <div class="coupon-input-group">
                <input type="text" name="coupon_code" class="coupon-input" placeholder="Coupon Code" value="<?= e($appliedCoupon['code'] ?? '') ?>">
                <button type="submit" name="apply_coupon" class="coupon-btn">APPLY</button>
              </div>
              <?php if ($couponError): ?>
                <span class="text-danger d-block mt-1" style="font-size:0.75rem;"><?= e($couponError) ?></span>
              <?php endif; ?>
            </form>

            <div style="border-top: 2px solid var(--border-color); padding-top: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700;">
              <span>Total</span>
              <span><?= formatPrice($grandTotal) ?></span>
            </div>

            <a href="<?= pageUrl('checkout') ?>" class="btn-luxury btn-gold btn-full text-center">PROCEED TO CHECKOUT</a>
          </div>
        </div>

      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <h2 class="font-serif mb-3">Your bag is empty</h2>
        <p class="text-muted mb-4">Discover our high-fashion editorial collections.</p>
        <a href="<?= pageUrl('shop') ?>" class="btn-luxury btn-gold">EXPLORE CATALOGUE</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
function removeBagItem(key) {
  fetch(BASE_URL + "ajax/cart.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "action=remove&key=" + encodeURIComponent(key) + "&csrf_token=" + CSRF_TOKEN
  }).then(() => window.location.reload());
}

function updateBagQty(key, newQty) {
  if (newQty < 1) return removeBagItem(key);
  fetch(BASE_URL + "ajax/cart.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "action=update&key=" + encodeURIComponent(key) + "&qty=" + newQty + "&csrf_token=" + CSRF_TOKEN
  }).then(() => window.location.reload());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
