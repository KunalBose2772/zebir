<?php
/**
 * ZEBIR LIBAS – Reset Password Confirmation
 */
require_once __DIR__ . '/includes/bootstrap.php';

$token = sanitize($_GET['token'] ?? '');
if (!$token) redirectTo('login.php');

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM customers WHERE reset_token = ? AND reset_expiry >= NOW() AND is_active = 1");
$stmt->execute([$token]);
$customer = $stmt->fetch();

if (!$customer) {
    setFlash('error', 'Invalid or expired password reset link.');
    redirectTo('forgot-password.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = hashPassword($password);
        $pdo->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?")
            ->execute([$hash, $customer['id']]);

        setFlash('success', 'Your password has been reset successfully. Please sign in.');
        redirectTo('login.php');
    }
}

$pageTitle = "Set New Password – ZEBIR LIBAS";
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container" style="max-width: 480px;">
    <div class="text-center mb-4">
      <h1 class="font-serif display-4" style="font-size:2.5rem;">New Password</h1>
    </div>

    <?php if ($error): ?>
      <div class="mb-4 p-3 text-center" style="background-color:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:4px; font-size:0.85rem;">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="reset-password.php?token=<?= urlencode($token) ?>" method="POST">
      <?= csrfField() ?>
      
      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">New Password</label>
        <input type="password" name="password" class="newsletter-input" style="width:100%; border-radius:2px;" required autofocus>
      </div>

      <div class="mb-4">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Confirm Password</label>
        <input type="password" name="confirm_password" class="newsletter-input" style="width:100%; border-radius:2px;" required>
      </div>

      <button type="submit" class="btn-luxury btn-gold btn-full mb-4">UPDATE PASSWORD</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
