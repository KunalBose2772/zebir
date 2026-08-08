<?php
/**
 * ZEBIR LIBAS – Checkout Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$cart = getCart();
if (empty($cart)) {
    redirectTo('cart.php');
}

$pageTitle = "Checkout – ZEBIR LIBAS";
$customer = currentCustomer();
$savedAddress = null;
if ($customer) {
    $savedAddress = getCustomerDefaultAddress($customer['id']);
}
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$subtotal = cartTotal();
$discount = $appliedCoupon['discount'] ?? 0;
$freeShippingThreshold = (float)getSetting('free_shipping_amount', '999');
$shippingCharge = ($subtotal - $discount) >= $freeShippingThreshold || $subtotal == 0 ? 0 : (float)getSetting('shipping_charge', '99');
$grandTotal = max(0, $subtotal - $discount + $shippingCharge);

$upiId = getSetting('upi_id', 'zebir@upi');
$upiQr = getSetting('upi_qr_code', '');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $shippingName  = sanitize($_POST['shipping_name'] ?? '');
    $shippingPhone = sanitize($_POST['shipping_phone'] ?? '');
    $shippingEmail = sanitize($_POST['shipping_email'] ?? '');
    $address1      = sanitize($_POST['address_line1'] ?? '');
    $address2      = sanitize($_POST['address_line2'] ?? '');
    $city          = sanitize($_POST['city'] ?? '');
    $state         = sanitize($_POST['state'] ?? '');
    $pincode       = sanitize($_POST['pincode'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
    $orderNotes    = sanitize($_POST['order_notes'] ?? '');

    if (!$shippingName)  $errors[] = 'Full name is required.';
    if (!isValidPhone($shippingPhone)) $errors[] = 'Valid phone number is required.';
    if (!isValidEmail($shippingEmail)) $errors[] = 'Valid email address is required.';
    if (!$address1)      $errors[] = 'Shipping address is required.';
    if (!$city)          $errors[] = 'City is required.';
    if (!$state)         $errors[] = 'State is required.';
    if (!$pincode)       $errors[] = 'Pincode is required.';

    $paymentScreenshot = '';
    $orderStatus = 'pending';
    $paymentStatus = 'pending';

    if ($paymentMethod === 'upi') {
        if (!empty($_FILES['payment_screenshot']['name'])) {
            $uploaded = uploadImage($_FILES['payment_screenshot'], UPLOAD_DIR . 'payments/', 1000, 1000);
            if ($uploaded) {
                $paymentScreenshot = $uploaded;
                $orderStatus = 'payment_verification';
                $paymentStatus = 'uploaded';
            } else {
                $errors[] = 'Failed to upload payment screenshot. Please upload JPG/PNG image under 5MB.';
            }
        } else {
            $errors[] = 'Payment screenshot is required for UPI payments.';
        }
    } else {
        $orderStatus = 'confirmed';
    }

    if (empty($errors)) {
        $pdo = getDB();
        $pdo->beginTransaction();
        try {
            $orderNumber = generateOrderNumber();
            $customerId  = $customer['id'] ?? null;

            $generatedPassword = null;
            if (!$customerId) {
                $chk = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
                $chk->execute([$shippingEmail]);
                $existing = $chk->fetch();
                if ($existing) {
                    $customerId = $existing['id'];
                } else {
                    $generatedPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                    $hash = hashPassword($generatedPassword);
                    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$shippingName, $shippingEmail, $shippingPhone, $hash]);
                    $customerId = $pdo->lastInsertId();
                    
                    $_SESSION['customer_id'] = $customerId;
                    $_SESSION['customer_name'] = $shippingName;
                    $_SESSION['new_account_password'] = $generatedPassword;
                    $_SESSION['new_account_email'] = $shippingEmail;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO orders 
                (order_number, customer_id, shipping_name, shipping_phone, shipping_email, shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_pincode, subtotal, shipping_charge, discount_amount, coupon_code, total, payment_method, payment_status, payment_screenshot, status, order_notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $orderNumber,
                $customerId,
                $shippingName,
                $shippingPhone,
                $shippingEmail,
                $address1,
                $address2,
                $city,
                $state,
                $pincode,
                $subtotal,
                $shippingCharge,
                $discount,
                $appliedCoupon['code'] ?? null,
                $grandTotal,
                $paymentMethod,
                $paymentStatus,
                $paymentScreenshot,
                $orderStatus,
                $orderNotes
            ]);

            $orderId = $pdo->lastInsertId();

            if ($customerId) {
                saveCustomerAddress($customerId, [
                    'name' => $shippingName,
                    'phone' => $shippingPhone,
                    'address_line1' => $address1,
                    'address_line2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode
                ]);
            }

            // Insert Order Items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, size, color, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($cart as $item) {
                $itemTotal = $item['price'] * $item['qty'];
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['image'],
                    $item['size'],
                    $item['color'],
                    $item['qty'],
                    $item['price'],
                    $itemTotal
                ]);

                // Reduce Stock
                $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$item['qty'], $item['id']]);
            }

            // Insert Initial Status History
            $histStmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_by) VALUES (?, ?, ?, 'customer')");
            $histStmt->execute([$orderId, $orderStatus, 'Order placed by customer.']);

            $pdo->commit();

            // Clear Cart & Session Coupon
            saveCart([]);
            unset($_SESSION['applied_coupon']);

            // Send Confirmation Email (wrapped in try-catch so mailer failures don't block order completion)
            try {
                require_once __DIR__ . '/includes/mailer.php';
                $orderData = [
                    'id' => $orderId,
                    'order_number' => $orderNumber,
                    'shipping_name' => $shippingName,
                    'shipping_email' => $shippingEmail,
                    'total' => $grandTotal,
                    'payment_method' => $paymentMethod
                ];
                sendOrderConfirmationEmail($orderData, array_values($cart));
                
                if ($generatedPassword) {
                    sendAccountCreatedEmail($shippingEmail, $shippingName, $generatedPassword);
                }
            } catch (Exception $mailEx) {
                error_log("Order email confirmation failed: " . $mailEx->getMessage());
            }

            // Redirect to success page
            redirectTo("order-success.php?id=" . urlencode($orderNumber));

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order Exception: " . $e->getMessage());
            $errors[] = 'An error occurred while processing your order. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4">
  <div class="container text-center">
    <h1 class="font-serif display-4">Checkout</h1>
    <p class="text-muted mt-3" style="max-width: 720px; margin: 0 auto;">Secure, streamlined checkout with trusted delivery and payment options.</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    
    <?php if (!empty($errors)): ?>
      <div class="mb-4 p-3" style="background-color:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:4px;">
        <ul class="mb-0 pl-3">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <style>
      .checkout-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 32px;
      }
      .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
      }
      @media(min-width: 768px) {
        .form-grid-2 {
          grid-template-columns: 1fr 1fr;
        }
        .form-grid-3 {
          grid-template-columns: 1fr 1fr 1fr;
        }
      }
      @media(min-width: 992px) {
        .checkout-grid {
          grid-template-columns: 1fr 420px;
          gap: 48px;
        }
      }
    </style>

    <form action="checkout.php" method="POST" enctype="multipart/form-data" id="checkoutForm">
      <?= csrfField() ?>
      <div class="checkout-grid">
        
        <!-- Shipping & Customer Info -->
        <div>
          <h3 class="font-serif mb-4" style="font-size: 1.5rem;">Shipping Address</h3>
          
          <?php if ($savedAddress): ?>
            <!-- Saved Shipping Address Card -->
            <div id="savedAddressCard" class="content-card mb-4" style="border: 1px solid var(--accent-gold); padding: 24px; border-radius: 8px; position: relative;">
                <h4 class="font-serif mb-2" style="font-size: 1.1rem; color: var(--accent-gold);">Deliver to Saved Address</h4>
                <div style="font-size: 0.95rem; line-height: 1.6;">
                    <strong><?= e($savedAddress['name']) ?></strong><br>
                    <?= e($savedAddress['address_line1']) ?><br>
                    <?php if ($savedAddress['address_line2']): ?><?= e($savedAddress['address_line2']) ?><br><?php endif; ?>
                    <?= e($savedAddress['city']) ?>, <?= e($savedAddress['state']) ?> - <?= e($savedAddress['pincode']) ?><br>
                    Phone: <?= e($savedAddress['phone']) ?>
                </div>
                <button type="button" class="btn-link-guest mt-3" onclick="showAddressForm()" style="text-decoration: none; font-size: 0.8rem; letter-spacing: 1px; font-weight: 700; text-transform: uppercase;">
                    Edit or Use Another Address &rarr;
                </button>
            </div>
          <?php endif; ?>

          <div id="addressInputsContainer" <?php if ($savedAddress): ?>style="display:none;"<?php endif; ?>>
            <div class="form-grid-2 mb-3">
              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Full Name *</label>
                <input type="text" name="shipping_name" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['shipping_name'] ?? $savedAddress['name'] ?? $customer['name'] ?? '') ?>" required>
              </div>
              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Phone Number *</label>
                <input type="tel" name="shipping_phone" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['shipping_phone'] ?? $savedAddress['phone'] ?? $customer['phone'] ?? '') ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Email Address *</label>
              <input type="email" name="shipping_email" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['shipping_email'] ?? $savedAddress['email'] ?? $customer['email'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Address Line 1 *</label>
              <input type="text" name="address_line1" class="newsletter-input" placeholder="House/Flat No., Building Name, Street" style="width:100%; border-radius:2px;" value="<?= e($_POST['address_line1'] ?? $savedAddress['address_line1'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Address Line 2 (Optional)</label>
              <input type="text" name="address_line2" class="newsletter-input" placeholder="Landmark, Area" style="width:100%; border-radius:2px;" value="<?= e($_POST['address_line2'] ?? $savedAddress['address_line2'] ?? '') ?>">
            </div>

            <div class="form-grid-3 mb-4">
              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">City *</label>
                <input type="text" name="city" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['city'] ?? $savedAddress['city'] ?? '') ?>" required>
              </div>
              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">State *</label>
                <input type="text" name="state" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['state'] ?? $savedAddress['state'] ?? '') ?>" required>
              </div>
              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Pincode *</label>
                <input type="text" name="pincode" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['pincode'] ?? $savedAddress['pincode'] ?? '') ?>" required>
              </div>
            </div>
          </div>

          <!-- Payment Method Selection -->
          <h3 class="font-serif mb-4 mt-5" style="font-size: 1.5rem;">Payment Method</h3>
          
          <div class="mb-3" style="border:1px solid var(--border-color); padding:16px; border-radius:4px;">
            <label style="cursor:pointer; display:flex; align-items:flex-start; justify-content:flex-start; gap:12px; margin:0;">
              <input type="radio" name="payment_method" value="cod" checked onclick="toggleUpiSection(false)" style="width:auto; margin-top:4px; flex-shrink:0;">
              <span style="font-weight:600; text-align:left;">Cash on Delivery (COD)</span>
            </label>
          </div>

          <div class="mb-3" style="border:1px solid var(--border-color); padding:16px; border-radius:4px;">
            <label style="cursor:pointer; display:flex; align-items:flex-start; justify-content:flex-start; gap:12px; margin:0;">
              <input type="radio" name="payment_method" value="upi" onclick="toggleUpiSection(true)" style="width:auto; margin-top:4px; flex-shrink:0;">
              <span style="font-weight:600; text-align:left;">UPI Direct Transfer (Zero Charges)</span>
            </label>
            
            <!-- UPI Details Display -->
            <div id="upiDetailsSection" style="display:none; margin-top:16px; padding-top:16px; border-top:1px solid var(--border-color);">
              <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">
                Scan the QR code below or use the UPI ID to pay <strong><?= formatPrice($grandTotal) ?></strong> directly to our bank account. Upload your payment screenshot to verify.
              </p>
              
              <div class="text-center mb-3">
                <?php if ($upiQr): ?>
                  <img src="<?= UPLOAD_URL . 'qr/' . e($upiQr) ?>" alt="UPI QR Code" style="width:180px; height:180px; object-fit:contain; border:1px solid var(--border-color); padding:8px;">
                <?php endif; ?>
                <div class="mt-2" style="font-weight:600; font-size:1.1rem; letter-spacing:1px; color:var(--accent-gold);">
                  UPI ID: <?= e($upiId) ?>
                </div>
              </div>

              <div>
                <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Upload Payment Screenshot *</label>
                <input type="file" name="payment_screenshot" accept="image/*" class="newsletter-input" style="width:100%;">
              </div>
            </div>
          </div>

        </div>

        <!-- Order Summary Side Panel -->
        <div>
          <div style="background-color: var(--bg-secondary); padding: 32px; border-radius: 4px; position:sticky; top:100px;">
            <h3 class="font-serif mb-4" style="font-size: 1.5rem;">Your Order</h3>
            
            <div class="mb-4">
              <?php foreach ($cart as $item): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:0.85rem;">
                  <div>
                    <span style="font-weight:600;"><?= e($item['name']) ?></span> × <?= $item['qty'] ?>
                    <?php if ($item['size']): ?><span style="color:var(--text-muted); display:block;">Size: <?= e($item['size']) ?></span><?php endif; ?>
                  </div>
                  <span><?= formatPrice($item['price'] * $item['qty']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div style="border-top:1px solid var(--border-color); padding-top:16px;">
              <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
                <span>Subtotal</span>
                <span><?= formatPrice($subtotal) ?></span>
              </div>
              <?php if ($discount > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem; color:var(--accent-gold);">
                  <span>Discount</span>
                  <span>- <?= formatPrice($discount) ?></span>
                </div>
              <?php endif; ?>
              <div style="display:flex; justify-content:space-between; margin-bottom:16px; font-size:0.9rem;">
                <span>Shipping</span>
                <span><?= $shippingCharge > 0 ? formatPrice($shippingCharge) : '<span class="text-success">FREE</span>' ?></span>
              </div>
              <div style="border-top:2px solid var(--border-color); padding-top:16px; margin-bottom:24px; display:flex; justify-content:space-between; font-size:1.25rem; font-weight:700;">
                <span>Grand Total</span>
                <span><?= formatPrice($grandTotal) ?></span>
              </div>

              <button type="submit" id="placeOrderBtn" class="btn-luxury btn-gold btn-full">PLACE ORDER</button>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</section>

<script>
function showAddressForm() {
  const card = document.getElementById('savedAddressCard');
  const container = document.getElementById('addressInputsContainer');
  if (card && container) {
    card.style.display = 'none';
    container.style.display = 'block';
  }
}

function toggleUpiSection(show) {
  document.getElementById('upiDetailsSection').style.display = show ? 'block' : 'none';
}

document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('placeOrderBtn');
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            if (this.checkValidity()) {
                btn.innerHTML = 'PROCESSING...';
                btn.style.opacity = '0.7';
                btn.style.pointerEvents = 'none';
            }
        });
    }
});
</script>

<?php if (!isLoggedIn()): ?>
  <!-- Checkout Login / Signup Modal -->
  <div id="checkoutAuthModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-tabs">
        <button type="button" class="modal-tab-btn active" onclick="switchAuthTab('login')">LOG IN</button>
        <button type="button" class="modal-tab-btn" onclick="switchAuthTab('register')">SIGN UP</button>
      </div>
      
      <!-- Login Tab Content -->
      <div id="authTabLogin" class="auth-tab-content active">
        <h3 class="font-serif" style="font-size:1.3rem; margin-bottom:6px; color:var(--text-main);">Log In to Continue</h3>
        <p class="modal-subtitle">Access your account to speed up checkout and track orders.</p>
        <form id="modalLoginForm" onsubmit="handleModalAuth(event, 'login')">
          <div class="mb-3">
            <label class="form-label-small">Email Address</label>
            <input type="email" name="email" required class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div class="mb-4">
            <label class="form-label-small">Password</label>
            <input type="password" name="password" required class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div id="loginModalError" class="modal-error" style="display:none;"></div>
          <button type="submit" class="btn-luxury btn-gold btn-full" style="padding:12px; font-weight:700;">LOG IN</button>
        </form>
      </div>
      
      <!-- Signup Tab Content -->
      <div id="authTabRegister" class="auth-tab-content">
        <h3 class="font-serif" style="font-size:1.3rem; margin-bottom:6px; color:var(--text-main);">Create an Account</h3>
        <p class="modal-subtitle">Save your address and details for future luxury purchases.</p>
        <form id="modalRegisterForm" onsubmit="handleModalAuth(event, 'register')">
          <div class="mb-3">
            <label class="form-label-small">Full Name</label>
            <input type="text" name="name" required class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div class="mb-3">
            <label class="form-label-small">Email Address</label>
            <input type="email" name="email" required class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div class="mb-3">
            <label class="form-label-small">Phone Number</label>
            <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div class="mb-4">
            <label class="form-label-small">Password (min 6 chars)</label>
            <input type="password" name="password" required minlength="6" class="newsletter-input" style="width:100%; border-radius:2px; padding:10px 12px;">
          </div>
          <div id="registerModalError" class="modal-error" style="display:none;"></div>
          <button type="submit" class="btn-luxury btn-gold btn-full" style="padding:12px; font-weight:700;">CREATE ACCOUNT</button>
        </form>
      </div>
      
      <div class="modal-footer mt-4 text-center">
        <button type="button" class="btn-link-guest" onclick="closeAuthModal()">Continue as Guest &rarr;</button>
      </div>
    </div>
  </div>

  <style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(10px);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .modal-card {
    background: var(--bg-secondary, #1a1a1a);
    border: 1px solid var(--border-color, #2a2a2a);
    border-radius: 12px;
    width: 100%;
    max-width: 440px;
    padding: 30px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.6);
    position: relative;
    text-align: left;
  }
  .modal-tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color, #2a2a2a);
    margin-bottom: 24px;
    gap: 20px;
  }
  .modal-tab-btn {
    background: none;
    border: none;
    padding: 10px 0;
    color: var(--text-muted, #8d867d);
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 1px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
  }
  .modal-tab-btn.active {
    color: var(--accent-gold, #c5a880);
    border-bottom-color: var(--accent-gold, #c5a880);
  }
  .auth-tab-content {
    display: none;
  }
  .auth-tab-content.active {
    display: block;
  }
  .modal-subtitle {
    font-size: 0.8rem;
    color: var(--text-muted, #8d867d);
    margin-bottom: 20px;
    line-height: 1.5;
  }
  .modal-error {
    background: rgba(229,57,53,0.08);
    border: 1px solid rgba(229,57,53,0.2);
    color: #ef5350;
    font-size: 0.8rem;
    padding: 10px 14px;
    border-radius: 4px;
    margin-bottom: 16px;
    line-height: 1.4;
  }
  .btn-link-guest {
    background: none;
    border: none;
    color: var(--accent-gold, #c5a880);
    text-decoration: underline;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    transition: color 0.2s;
  }
  .btn-link-guest:hover {
    color: #ffffff;
  }
  .form-label-small {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted, #8d867d);
    margin-bottom: 6px;
  }
  </style>

  <script>
  function switchAuthTab(tab) {
    document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.auth-tab-content').forEach(content => content.classList.remove('active'));
    
    if (tab === 'login') {
      event.currentTarget.classList.add('active');
      document.getElementById('authTabLogin').classList.add('active');
    } else {
      event.currentTarget.classList.add('active');
      document.getElementById('authTabRegister').classList.add('active');
    }
  }

  function closeAuthModal() {
    const modal = document.getElementById('checkoutAuthModal');
    if (modal) {
      modal.style.transition = 'opacity 0.3s ease';
      modal.style.opacity = '0';
      setTimeout(() => modal.remove(), 300);
    }
  }

  function handleModalAuth(event, action) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const origText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = 'PROCESSING...';
    submitBtn.disabled = true;
    
    const errorDiv = document.getElementById(action + 'ModalError');
    errorDiv.style.display = 'none';
    
    const formData = new FormData(form);
    formData.append('action', action);
    formData.append('csrf_token', CSRF_TOKEN);
    
    fetch(BASE_URL + 'ajax/auth.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        errorDiv.innerHTML = data.message || 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        submitBtn.innerHTML = origText;
        submitBtn.disabled = false;
      }
    })
    .catch(err => {
      console.error(err);
      errorDiv.innerHTML = 'Network error. Please try again.';
      errorDiv.style.display = 'block';
      submitBtn.innerHTML = origText;
      submitBtn.disabled = false;
    });
  }
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
