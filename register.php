<?php
/**
 * ZEBIR LIBAS – Customer Registration Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    redirectTo('account.php');
}

$pageTitle = "Register – ZEBIR LIBAS";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $phone    = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name) $errors[] = 'Full name is required.';
    if (!isValidEmail($email)) $errors[] = 'Valid email address is required.';
    if ($phone && !isValidPhone($phone)) $errors[] = 'Invalid phone number format.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo = getDB();
        // Check duplicate email
        $chk = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $errors[] = 'Email address is already registered.';
        } else {
            $hash = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $hash]);
            $id = $pdo->lastInsertId();

            $oldSessionId = session_id();
            $_SESSION['customer_id']   = $id;
            $_SESSION['customer_name'] = $name;
            mergeSessionData($oldSessionId, $id);

            setFlash('success', 'Registration successful! Welcome to ZEBIR LIBAS.');
            redirectTo('account.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5">
  <div class="container" style="max-width: 480px;">
    <div class="text-center mb-4">
      <h1 class="font-serif display-4">Create Account</h1>
      <p class="text-muted">Join the ZEBIR LIBAS private client circle</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="mb-4 p-3" style="background-color:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:4px; font-size:0.85rem;">
        <ul class="mb-0 pl-3">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="content-card" style="padding: 1.5rem;">
      <?= csrfField() ?>
      
      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Full Name *</label>
        <input type="text" name="name" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['name'] ?? '') ?>" required autofocus>
      </div>

      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Email Address *</label>
        <input type="email" name="email" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Phone Number</label>
        <input type="tel" name="phone" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($_POST['phone'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Password *</label>
        <input type="password" name="password" class="newsletter-input" style="width:100%; border-radius:2px;" required>
      </div>

      <div class="mb-4">
        <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Confirm Password *</label>
        <input type="password" name="confirm_password" class="newsletter-input" style="width:100%; border-radius:2px;" required>
      </div>

      <button type="submit" class="btn-luxury btn-gold btn-full mb-4">REGISTER NOW</button>

      <div class="text-center" style="font-size:0.85rem; color:var(--text-muted);">
        Already registered? <a href="login.php" style="color:var(--text-main); font-weight:600; text-decoration:underline;">Sign In here</a>
      </div>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
