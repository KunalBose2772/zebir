<?php
/**
 * ZEBIR LIBAS – Forgot Password
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = "Forgot Password – ZEBIR LIBAS";
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = sanitize($_POST['email'] ?? '');

    if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer) {
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $pdo->prepare("UPDATE customers SET reset_token = ?, reset_expiry = ? WHERE id = ?")
                ->execute([$token, $expiry, $customer['id']]);

            require_once __DIR__ . '/includes/mailer.php';
            sendPasswordResetEmail($customer['email'], $customer['name'], $token);

            $msg = 'Password reset instructions have been sent to your email address.';
        } else {
            // Fake success for security
            $msg = 'If an account exists with that email, instructions have been sent.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container" style="max-width: 480px;">
    <div class="text-center mb-4">
      <h1 class="font-serif display-4" style="font-size:2.5rem;">Reset Password</h1>
      <p class="text-muted">Enter your email to receive password reset instructions</p>
    </div>

    <?php if ($msg): ?>
      <div class="mb-4 p-3 text-center" style="background-color:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:4px; font-size:0.85rem;">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="mb-4 p-3 text-center" style="background-color:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:4px; font-size:0.85rem;">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="forgot-password.php" method="POST">
      <?= csrfField() ?>
      
      <div class="mb-4">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Email Address</label>
        <input type="email" name="email" class="newsletter-input" style="width:100%; border-radius:2px;" required autofocus>
      </div>

      <button type="submit" class="btn-luxury btn-gold btn-full mb-4">SEND RESET LINK</button>

      <div class="text-center" style="font-size:0.85rem; color:var(--text-muted);">
        Remember password? <a href="login.php" style="color:var(--text-main); font-weight:600; text-decoration:underline;">Sign In here</a>
      </div>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
