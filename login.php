<?php
/**
 * ZEBIR LIBAS – Customer Login Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    redirectTo('account.php');
}

$pageTitle = "Sign In – ZEBIR LIBAS";
$error = '';
$redirect = sanitize($_GET['redirect'] ?? 'account.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && verifyPassword($password, $customer['password'])) {
            $oldSessionId = session_id();
            $_SESSION['customer_id']   = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            mergeSessionData($oldSessionId, $customer['id']);
            setFlash('success', 'Welcome back, ' . $customer['name']);
            redirectTo($redirect);
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container" style="max-width: 480px;">
    <div class="text-center mb-4">
      <h1 class="font-serif display-4">Sign In</h1>
      <p class="text-muted">Enter your details to access your account</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-4 p-3 text-center" style="background-color:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:4px; font-size:0.85rem;">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="login.php?redirect=<?= urlencode($redirect) ?>" method="POST" class="content-card" style="padding: 1.5rem;">
      <?= csrfField() ?>
      
      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Email Address</label>
        <input type="email" name="email" class="newsletter-input" style="width:100%; border-radius:2px;" required autofocus>
      </div>

      <div class="mb-3">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <label class="font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Password</label>
          <a href="forgot-password.php" style="font-size:0.75rem; color:var(--text-muted); text-decoration:underline;">Forgot Password?</a>
        </div>
        <input type="password" name="password" class="newsletter-input" style="width:100%; border-radius:2px;" required>
      </div>

      <button type="submit" class="btn-luxury btn-gold btn-full mb-4">SIGN IN</button>

      <div class="text-center" style="font-size:0.85rem; color:var(--text-muted);">
        Don't have an account? <a href="register.php" style="color:var(--text-main); font-weight:600; text-decoration:underline;">Register here</a>
      </div>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
