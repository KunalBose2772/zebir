<?php
/**
 * ZEBIR LIBAS – Admin Change Password (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDB();
$adminId = $_SESSION['admin_id'] ?? 0;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $errors[] = 'All fields are required.';
    } else {
        // Fetch current password hash
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();

        if (!$admin || !verifyPassword($currentPass, $admin['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            // Update password
            $newHash = hashPassword($newPass);
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->execute([$newHash, $adminId]);
            $success = 'Password changed successfully.';
        }
    }
}
?>

<div class="admin-page-header" style="justify-content: center;">
  <h2>Change Password</h2>
</div>

<div class="admin-card" style="max-width: 540px; margin: 0 auto;">
  <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px; text-align: center;">Update Admin Credentials</h3>

  <?php if (!empty($errors)): ?>
    <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px;">
      <ul style="margin: 0; padding-left: 16px;">
        <?php foreach ($errors as $error): ?>
          <li><?= e($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; font-weight: 600; text-align: center;">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

  <form action="change-password.php" method="POST">
    <?= csrfField() ?>
    
    <div class="form-group">
      <label class="field-label">Current Password *</label>
      <input type="password" name="current_password" class="form-control" required>
    </div>

    <div class="form-group">
      <label class="field-label">New Password *</label>
      <input type="password" name="new_password" class="form-control" required>
      <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:6px;">Password must be at least 6 characters long.</span>
    </div>

    <div class="form-group">
      <label class="field-label">Confirm New Password *</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <button type="submit" class="btn-admin btn-admin-gold btn-full" style="margin-top: 10px;">Update Password</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
