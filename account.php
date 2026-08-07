<?php
/**
 * ZEBIR LIBAS – Customer Account Profile
 */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$pageTitle = "My Account – ZEBIR LIBAS";
$customer = currentCustomer();
$pdo = getDB();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name  = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if ($name) {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $customer['id']]);
        $_SESSION['customer_name'] = $name;
        setFlash('success', 'Profile updated successfully.');
        redirectTo('account.php');
    }
}

// Fetch recent orders count
$orderCountStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
$orderCountStmt->execute([$customer['id']]);
$totalOrders = (int)$orderCountStmt->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-4">
  <div class="container text-center">
    <h1 class="font-serif display-4">My Account</h1>
    <p class="text-muted mt-3" style="max-width: 720px; margin: 0 auto;">Manage your profile and stay connected to your recent orders in one place.</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div style="display: grid; grid-template-columns: 240px 1fr; gap: 48px;">
      
      <!-- Account Nav Sidebar -->
      <aside class="content-card" style="padding: 1.25rem; border-right: 1px solid var(--border-color);">
        <div class="mb-4">
          <h4 class="font-serif" style="font-size:1.25rem;"><?= e($customer['name']) ?></h4>
          <span style="font-size:0.8rem; color:var(--text-muted);"><?= e($customer['email']) ?></span>
        </div>

        <ul style="list-style:none; line-height:2.2;">
          <li><a href="account.php" style="font-weight:600; color:var(--accent-gold);">Profile Details</a></li>
          <li><a href="orders.php">Order History (<?= $totalOrders ?>)</a></li>
          <li><a href="wishlist.php">Saved Wishlist</a></li>
          <li class="mt-3"><a href="logout.php" class="text-danger" style="font-size:0.85rem;">Sign Out</a></li>
        </ul>
      </aside>

      <!-- Account Main Content -->
      <main>
        <h3 class="font-serif mb-4" style="font-size: 1.75rem;">Profile Information</h3>

        <form action="account.php" method="POST" class="content-card" style="max-width: 500px; padding: 1.25rem;">
          <?= csrfField() ?>
          
          <div class="mb-3">
            <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Full Name</label>
            <input type="text" name="name" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($customer['name']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Email Address</label>
            <input type="email" class="newsletter-input" style="width:100%; border-radius:2px; background:var(--bg-secondary); color:var(--text-muted); cursor:not-allowed; opacity:0.8;" value="<?= e($customer['email']) ?>" readonly>
            <span style="font-size:0.75rem; color:var(--text-muted);">Email address cannot be changed.</span>
          </div>

          <div class="mb-4">
            <label class="d-block font-weight-bold mb-1" style="font-size:0.8rem; text-transform:uppercase;">Phone Number</label>
            <input type="tel" name="phone" class="newsletter-input" style="width:100%; border-radius:2px;" value="<?= e($customer['phone']) ?>">
          </div>

          <button type="submit" class="btn-luxury btn-gold">UPDATE PROFILE</button>
        </form>
      </main>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
