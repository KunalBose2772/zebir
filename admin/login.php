<?php
/**
 * ZEBIR LIBAS – Admin Login (Premium Redesign)
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (isAdminLoggedIn()) {
    redirectTo('admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && verifyPassword($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        redirectTo('admin/index.php');
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign In – ZEBIR LIBAS</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/admin.css">
  <style>
    body {
      background-color: var(--primary-color);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      padding: 24px;
      box-sizing: border-box;
    }
    .login-container {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255,255,255,0.1);
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div style="text-align:center; margin-bottom:32px;">
      <img src="<?= assetUrl('images/logoZ.webp') ?>" alt="Logo" style="max-width: 140px; filter: brightness(0); margin-bottom: 12px;">
      <div style="font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; color: var(--text-muted); font-weight: 700;">Admin Console</div>
    </div>

    <?php if ($error): ?>
      <div style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:12px; border-radius:8px; font-size:0.85rem; margin-bottom:20px; text-align:center; font-weight: 600;">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <?= csrfField() ?>
      
      <div class="form-group">
        <label class="field-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="admin@zebirl.com" value="admin@zebirl.com" required autofocus>
      </div>

      <div class="form-group" style="margin-bottom: 28px;">
        <label class="field-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn-admin btn-admin-gold btn-full" style="padding: 12px; font-size: 0.95rem;">Sign In</button>
    </form>
  </div>
</body>
</html>
