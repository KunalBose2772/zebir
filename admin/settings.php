<?php
/**
 * ZEBIR LIBAS – Admin Global Website Settings (Premium Redesign)
 */
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/mailer.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $isChangingUpiId = (isset($_POST['upi_id']) && sanitize($_POST['upi_id']) !== getSetting('upi_id'));
    $isUploadingQrCode = (!empty($_FILES['upi_qr_code']['name']));
    $isChangingCod = (isset($_POST['enable_cod']) && sanitize($_POST['enable_cod']) !== getSetting('enable_cod', '1'));

    if ($isChangingUpiId || $isUploadingQrCode || $isChangingCod) {
        $submittedPassword = $_POST['payment_settings_password'] ?? '';
        if ($submittedPassword !== PAYMENT_SETTINGS_PASSWORD) {
            setFlash('error', 'Authentication failed: Invalid Payment Settings Password.');
            redirectTo('admin/settings');
        }
    }

    $settingsToUpdate = [
        'site_name', 'site_phone', 'site_whatsapp', 'site_email', 'site_address',
        'seo_title', 'seo_description', 'meta_keywords', 'google_analytics',
        'theme', 'currency', 'currency_symbol', 'shipping_charge', 'free_shipping_amount',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'
    ];

    if (!$isChangingUpiId || ($_POST['payment_settings_password'] ?? '') === PAYMENT_SETTINGS_PASSWORD) {
        $settingsToUpdate[] = 'upi_id';
    }

    if (!$isChangingCod || ($_POST['payment_settings_password'] ?? '') === PAYMENT_SETTINGS_PASSWORD) {
        $settingsToUpdate[] = 'enable_cod';
    }

    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

    foreach ($settingsToUpdate as $key) {
        if (isset($_POST[$key])) {
            $stmt->execute([$key, sanitize($_POST[$key])]);
        }
    }

    // Process UPI QR Code upload
    if ($isUploadingQrCode && ($_POST['payment_settings_password'] ?? '') === PAYMENT_SETTINGS_PASSWORD) {
        $qrName = uploadImage($_FILES['upi_qr_code'], UPLOAD_DIR . 'qr/', 600, 600);
        if ($qrName) {
            $stmt->execute(['upi_qr_code', $qrName]);
        }
    }

    setFlash('success', 'Global settings updated successfully.');
    redirectTo('admin/settings');
}

$status = getEmailServiceStatus();
?>

<div class="admin-page-header">
  <h2>Website Settings</h2>
</div>

<!-- Email Status banner -->
<div style="margin-bottom: 24px;">
  <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 8px;">
    <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color: var(--text-muted);">Email Delivery Gateway</div>
    <div style="font-size:1.15rem; font-weight:700; color:<?= $status['configured'] ? '#059669' : '#dc2626' ?>; display: flex; align-items: center; gap: 8px;">
      <span style="width: 8px; height: 8px; background-color: <?= $status['configured'] ? '#059669' : '#dc2626' ?>; border-radius: 50%;"></span>
      <?= e($status['status']) ?>
    </div>
    <p style="margin: 4px 0 12px; color: var(--text-muted); font-size: 0.875rem; line-height: 1.5;"><?= e($status['message']) ?></p>
    <div style="font-size:0.8rem; color: var(--text-main); display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; background-color: var(--border-light); padding: 12px 16px; border-radius: 8px;">
      <div><strong>Gateway Mode:</strong> <?= e($status['mode']) ?></div>
      <div><strong>Host Address:</strong> <?= e($status['host']) ?></div>
      <div><strong>Active Port:</strong> <?= e($status['port']) ?></div>
      <div><strong>Outbound Email:</strong> <?= e($status['from_email']) ?></div>
    </div>
  </div>
</div>

<form action="settings" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  
  <div class="admin-grid-2">
    
    <!-- Branding & General Settings -->
    <div class="admin-card">
      <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">Branding & Identity</h3>
      
      <div class="form-group">
        <label class="field-label">Site Name</label>
        <input type="text" name="site_name" class="form-control" value="<?= e(getSetting('site_name', 'ZEBIR LIBAS')) ?>">
      </div>

      <div class="form-group">
        <label class="field-label">Store Theme (Admin Controlled)</label>
        <select name="theme" class="form-control">
          <option value="light" <?= getSetting('theme') === 'light' ? 'selected' : '' ?>>Light Theme (Default Luxury)</option>
          <option value="dark" <?= getSetting('theme') === 'dark' ? 'selected' : '' ?>>Dark Theme (Midnight Fashion)</option>
        </select>
      </div>

      <div class="admin-form-inline-grid form-group">
        <div>
          <label class="field-label">Contact Phone</label>
          <input type="text" name="site_phone" class="form-control" value="<?= e(getSetting('site_phone')) ?>">
        </div>
        <div>
          <label class="field-label">Contact Email</label>
          <input type="email" name="site_email" class="form-control" value="<?= e(getSetting('site_email')) ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="field-label">Atelier Address</label>
        <textarea name="site_address" class="form-control" style="height:80px; resize: none;"><?= e(getSetting('site_address')) ?></textarea>
      </div>

      <!-- Shipping Rates -->
      <h3 style="font-size:1.15rem; font-weight:700; margin-bottom: 20px; margin-top: 32px;">Shipping Configuration</h3>
      
      <div class="admin-form-inline-grid form-group">
        <div>
          <label class="field-label">Standard Shipping Charge (₹)</label>
          <input type="number" name="shipping_charge" class="form-control" value="<?= e(getSetting('shipping_charge', '99')) ?>">
        </div>
        <div>
          <label class="field-label">Free Shipping Threshold (₹)</label>
          <input type="number" name="free_shipping_amount" class="form-control" value="<?= e(getSetting('free_shipping_amount', '999')) ?>">
        </div>
      </div>
    </div>

    <!-- UPI Payment & SMTP Email Settings -->
    <div style="display:flex; flex-direction:column; gap:24px;">
      
      <!-- Payment Settings -->
      <div class="admin-card" style="border: 1px solid var(--accent-gold); position: relative;">
        <div style="position: absolute; top: 12px; right: 16px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--accent-gold); background: rgba(200, 150, 12, 0.1); padding: 4px 8px; border-radius: 4px;">Protected</div>
        <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px; color: var(--accent-gold);">Payment Settings</h3>
        
        <!-- Cash on Delivery Toggle -->
        <div class="form-group" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px dashed var(--border-color);">
          <label class="field-label" style="font-weight: 700;">Cash on Delivery (COD)</label>
          <select name="enable_cod" class="form-control" style="max-width: 200px;">
            <option value="1" <?= getSetting('enable_cod', '1') === '1' ? 'selected' : '' ?>>Enabled</option>
            <option value="0" <?= getSetting('enable_cod', '1') === '0' ? 'selected' : '' ?>>Disabled</option>
          </select>
          <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Toggle Cash on Delivery payment option at storefront checkout.</small>
        </div>

        <div class="form-group">
          <label class="field-label">UPI ID</label>
          <input type="text" name="upi_id" class="form-control" placeholder="e.g. merchant@upi" value="<?= e(getSetting('upi_id')) ?>">
        </div>

        <div class="form-group">
          <label class="field-label">UPI QR Code Image</label>
          <?php if ($qr = getSetting('upi_qr_code')): ?>
            <img src="<?= UPLOAD_URL . 'qr/' . e($qr) ?>" style="width:80px; height:80px; object-fit:contain; display:block; margin-bottom:12px; border:1px solid var(--border-color); border-radius:6px;">
          <?php endif; ?>
          <input type="file" name="upi_qr_code" accept="image/*" class="form-control">
        </div>

        <div class="form-group" style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border-color);">
          <label class="field-label" style="color: var(--accent-gold); font-weight: 700;">Payment Authorization Password</label>
          <input type="password" name="payment_settings_password" class="form-control" style="border: 1px solid var(--accent-gold);" placeholder="Required only if changing payment settings">
          <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Changing payment configurations (COD toggle, UPI settings) requires entering the payment security password to authorize changes.</small>
        </div>
      </div>

      <!-- SMTP Config -->
      <div class="admin-card">
        <h3 style="margin-top:0; font-size:1.15rem; font-weight:700; margin-bottom: 20px;">SMTP Outbound Email</h3>
        
        <div class="admin-form-inline-grid form-group">
          <div>
            <label class="field-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com" value="<?= e(getSetting('smtp_host')) ?>">
          </div>
          <div>
            <label class="field-label">Port</label>
            <input type="text" name="smtp_port" class="form-control" value="<?= e(getSetting('smtp_port', '587')) ?>">
          </div>
        </div>

        <div class="admin-form-inline-grid form-group">
          <div>
            <label class="field-label">SMTP Username</label>
            <input type="text" name="smtp_user" class="form-control" value="<?= e(getSetting('smtp_user')) ?>">
          </div>
          <div>
            <label class="field-label">SMTP Password</label>
            <input type="password" name="smtp_pass" class="form-control" value="<?= e(getSetting('smtp_pass')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="field-label">Sender Email</label>
          <input type="email" name="smtp_from_email" class="form-control" value="<?= e(getSetting('smtp_from_email', getSetting('site_email'))) ?>">
        </div>

        <div class="form-group">
          <label class="field-label">Sender Name</label>
          <input type="text" name="smtp_from_name" class="form-control" value="<?= e(getSetting('smtp_from_name', getSetting('site_name', 'ZEBIR LIBAS'))) ?>">
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-gold btn-full" style="padding: 12px; font-size: 0.95rem;">Save Global Settings</button>

    </div>

  </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
