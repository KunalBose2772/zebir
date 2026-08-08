<?php
/**
 * PHPMailer Email Sender
 * ZEBIR LIBAS
 */

// Check and load PHPMailer if files exist
$phpmailerBase = __DIR__ . '/../vendor/phpmailer/src/';
if (file_exists($phpmailerBase . 'Exception.php')) {
    require_once $phpmailerBase . 'Exception.php';
    require_once $phpmailerBase . 'PHPMailer.php';
    require_once $phpmailerBase . 'SMTP.php';
}

function getMailFrom(): array {
    $fromEmail = trim(getSetting('smtp_from_email')) ?: trim(getSetting('site_email'));
    if (!$fromEmail) {
        $host = parse_url(BASE_URL, PHP_URL_HOST) ?: 'localhost';
        $fromEmail = 'no-reply@' . preg_replace('/^www\./', '', $host);
    }

    $fromName = trim(getSetting('smtp_from_name', getSetting('site_name', 'ZEBIR LIBAS')));
    return ['email' => $fromEmail, 'name' => $fromName];
}

function isEmailConfigured(): bool {
    return !empty(trim(getSetting('smtp_host'))) && !empty(trim(getSetting('smtp_port')));
}

function getEmailServiceStatus(): array {
    $smtpHost = trim(getSetting('smtp_host'));
    $smtpPort = trim(getSetting('smtp_port'));
    $fromEmail = trim(getSetting('smtp_from_email')) ?: trim(getSetting('site_email'));
    $configured = !empty($smtpHost) && !empty($smtpPort) && filter_var($fromEmail, FILTER_VALIDATE_EMAIL);

    return [
        'configured' => $configured,
        'mode' => $smtpHost ? 'SMTP' : 'Not configured',
        'host' => $smtpHost ?: 'N/A',
        'port' => $smtpPort ?: 'N/A',
        'from_email' => $fromEmail ?: 'N/A',
        'status' => $configured ? 'Ready to send' : 'Not ready',
        'message' => $configured ? 'SMTP is configured and ready to send emails.' : 'Please configure SMTP details and a valid sender email for reliable delivery.'
    ];
}

function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $from = getMailFrom();
    $fromEmail = $from['email'];
    $fromName  = $from['name'];

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $error = 'PHPMailer library not available';
        $pdo = getDB();
        $pdo->prepare("INSERT INTO email_logs (to_email, subject, status, error) VALUES (?,?,'failed',?)")
            ->execute([$toEmail, $subject, $error]);
        
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['mailer_debug'][] = [
            'to' => $toEmail,
            'subject' => $subject,
            'status' => 'failed',
            'error' => $error
        ];
        return false;
    }

    $smtpHost = trim(getSetting('smtp_host'));
    $smtpPort = trim(getSetting('smtp_port'));
    $smtpUser = trim(getSetting('smtp_user'));
    $smtpPass = trim(getSetting('smtp_pass'));

    // Force the From email to match the authenticated SMTP user if they are both defined and SMTP user is a valid email.
    // This resolves the "553 5.7.1 Sender address rejected: not owned by user" error on strict SMTP servers like Hostinger.
    if (!empty($smtpUser) && filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $smtpUser;
    }

    if (!$smtpHost || !$smtpPort || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'SMTP configuration incomplete or sender email invalid.';
        $pdo = getDB();
        $pdo->prepare("INSERT INTO email_logs (to_email, subject, status, error) VALUES (?,?,'failed',?)")
            ->execute([$toEmail, $subject, $error]);
        error_log("Mailer Error: {$error}");

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['mailer_debug'][] = [
            'to' => $toEmail,
            'subject' => $subject,
            'status' => 'failed',
            'error' => $error
        ];
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->Port       = (int)$smtpPort;
        $mail->SMTPAuth   = !empty($smtpUser) && !empty($smtpPass);
        if ($mail->SMTPAuth) {
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
        }
        if ((int)$smtpPort === 465) {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromEmail, $fromName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->CharSet = 'UTF-8';
        $mail->send();

        $pdo = getDB();
        $pdo->prepare("INSERT INTO email_logs (to_email, subject, status) VALUES (?,?,'sent')")
            ->execute([$toEmail, $subject]);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['mailer_debug'][] = [
            'to' => $toEmail,
            'subject' => $subject,
            'status' => 'sent',
            'error' => null
        ];
        return true;
    } catch (\Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
        $pdo = getDB();
        $pdo->prepare("INSERT INTO email_logs (to_email, subject, status, error) VALUES (?,?,'failed',?)")
            ->execute([$toEmail, $subject, $error]);
        error_log("Mailer Error: " . $error);

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['mailer_debug'][] = [
            'to' => $toEmail,
            'subject' => $subject,
            'status' => 'failed',
            'error' => $error
        ];
        return false;
    }
}

// ── Email Templates ───────────────────────────────────────────
function emailWrapper(string $content): string {
    $siteName = getSetting('site_name', 'ZEBIR LIBAS');
    $siteUrl  = BASE_URL;
    $siteYear = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body { margin:0; padding:0; background:#f4f4f0; font-family:'Instrument Sans', 'Segoe UI', sans-serif; color:#1a1a1a; }
  .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:4px; overflow:hidden; box-shadow:0 2px 20px rgba(0,0,0,.08); }
  .header { background:#1a1a1a; padding:32px 40px; text-align:center; }
  .header h1 { margin:0; color:#fff; font-size:22px; letter-spacing:6px; font-weight:400; font-family:Georgia,serif; }
  .body { padding:40px; }
  .body h2 { font-size:20px; font-weight:400; color:#1a1a1a; margin:0 0 16px; }
  .body p { font-size:15px; line-height:1.7; color:#555; margin:0 0 16px; }
  .btn { display:inline-block; padding:14px 32px; background:#1a1a1a; color:#fff !important; text-decoration:none; font-size:13px; letter-spacing:2px; font-weight:600; border-radius:2px; margin:8px 0; }
  .order-table { width:100%; border-collapse:collapse; margin:20px 0; }
  .order-table th { background:#f4f4f0; padding:10px 12px; text-align:left; font-size:12px; letter-spacing:1px; text-transform:uppercase; color:#888; }
  .order-table td { padding:12px; border-bottom:1px solid #eee; font-size:14px; color:#333; }
  .total-row td { font-weight:600; color:#1a1a1a; border-bottom:none; }
  .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#f0fdf4; color:#16a34a; }
  .footer { background:#f4f4f0; padding:24px 40px; text-align:center; }
  .footer p { font-size:12px; color:#999; margin:0; line-height:1.8; }
  .footer a { color:#888; text-decoration:none; }
  .divider { border:none; border-top:1px solid #eee; margin:24px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header"><h1>{$siteName}</h1></div>
  <div class="body">{$content}</div>
  <div class="footer">
    <p>{$siteName} · <a href="{$siteUrl}">{$siteUrl}</a></p>
    <p>© {$siteYear} {$siteName}. All rights reserved.</p>
  </div>
</div>
</body>
</html>
HTML;
}

function sendOrderConfirmationEmail(array $order, array $items): void {
    $rows = '';
    foreach ($items as $item) {
        $name = $item['name'] ?? $item['product_name'] ?? 'Item';
        $qty = $item['qty'] ?? $item['quantity'] ?? 1;
        $price = $item['price'] ?? 0;
        $total = $item['total'] ?? ((float)$price * (int)$qty);
        $size = !empty($item['size']) ? " / {$item['size']}" : '';
        $color = !empty($item['color']) ? " / {$item['color']}" : '';
        
        $rows .= "<tr><td>{$name}{$size}{$color}</td><td>{$qty}</td><td>" . formatPrice($price) . "</td><td>" . formatPrice($total) . "</td></tr>";
    }
    $content = "<h2>Order Confirmed!</h2>
    <p>Hi {$order['shipping_name']}, thank you for your order. We'll notify you when it's on its way.</p>
    <p><strong>Order Number:</strong> {$order['order_number']}</p>
    <hr class='divider'>
    <table class='order-table'>
      <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
      {$rows}
      <tr class='total-row'><td colspan='3'>Grand Total</td><td>" . formatPrice($order['total']) . "</td></tr>
    </table>
    <p>Payment Method: <strong>" . strtoupper($order['payment_method']) . "</strong></p>
    <a href='" . BASE_URL . "orders.php' class='btn'>VIEW ORDER</a>";

    // Email customer
    sendMail($order['shipping_email'], $order['shipping_name'], 'Order Confirmed – ' . $order['order_number'], emailWrapper($content));

    // Email admin — always use the hardcoded ADMIN_EMAIL constant as guaranteed fallback
    $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'zebirlibas@gmail.com';
    // Also send to site_email if it's a different valid address
    $siteEmail = trim(getSetting('site_email', ''));
    
    $orderDetailUrl = BASE_URL . 'admin/order-detail?id=' . ($order['id'] ?? '');
    $adminContent = "<h2>🛒 New Order Received!</h2>
    <p>A new order has been placed on <strong>Zebir Libas</strong>.</p>
    <table class='order-table'>
      <tr><th>Field</th><th>Detail</th></tr>
      <tr><td>Order Number</td><td><strong>{$order['order_number']}</strong></td></tr>
      <tr><td>Customer Name</td><td>{$order['shipping_name']}</td></tr>
      <tr><td>Customer Email</td><td>{$order['shipping_email']}</td></tr>
      <tr><td>Total Amount</td><td><strong>" . formatPrice($order['total']) . "</strong></td></tr>
      <tr><td>Payment Method</td><td>" . strtoupper($order['payment_method']) . "</td></tr>
    </table>
    <hr class='divider'>
    <table class='order-table'>
      <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
      {$rows}
      <tr class='total-row'><td colspan='3'>Grand Total</td><td>" . formatPrice($order['total']) . "</td></tr>
    </table>
    <br>
    <a href='{$orderDetailUrl}' class='btn'>VIEW ORDER IN ADMIN PANEL</a>";

    // Send to primary admin email (hardcoded constant)
    sendMail($adminEmail, 'Zebir Libas Admin', '🛒 New Order – ' . $order['order_number'], emailWrapper($adminContent));
    
    // Also CC site_email if different from ADMIN_EMAIL
    if ($siteEmail && filter_var($siteEmail, FILTER_VALIDATE_EMAIL) && strtolower($siteEmail) !== strtolower($adminEmail)) {
        sendMail($siteEmail, 'Zebir Libas', '🛒 New Order – ' . $order['order_number'], emailWrapper($adminContent));
    }
}

function sendOrderStatusEmail(array $order, string $status): void {
    $messages = [
        'confirmed'            => 'Your order has been confirmed and is being prepared.',
        'packed'               => 'Your order has been packed and is ready for dispatch.',
        'shipped'              => 'Your order is on its way! ' . ($order['courier_name'] ? "Courier: {$order['courier_name']}, Tracking: {$order['tracking_number']}" : ''),
        'delivered'            => 'Your order has been delivered. We hope you love it!',
        'cancelled'            => 'Your order has been cancelled. If you have any questions, please contact us.',
        'payment_verification' => 'We have received your payment screenshot and are verifying it.',
    ];
    $msg     = $messages[$status] ?? "Your order status has been updated to: " . ucfirst($status);
    $content = "<h2>Order Update</h2>
    <p>Hi {$order['shipping_name']},</p>
    <p>{$msg}</p>
    <p><strong>Order:</strong> {$order['order_number']} &nbsp;|&nbsp; <span class='status-badge'>" . ucfirst(str_replace('_', ' ', $status)) . "</span></p>
    <a href='" . BASE_URL . "orders.php' class='btn'>VIEW ORDER</a>";

    sendMail($order['shipping_email'], $order['shipping_name'], 'Order Update – ' . $order['order_number'], emailWrapper($content));
}

function sendPasswordResetEmail(string $email, string $name, string $token): void {
    $link    = BASE_URL . 'reset-password.php?token=' . $token;
    $content = "<h2>Reset Your Password</h2>
    <p>Hi {$name}, click the button below to reset your password. This link is valid for 1 hour.</p>
    <a href='{$link}' class='btn'>RESET PASSWORD</a>
    <p style='font-size:12px;color:#999;margin-top:20px;'>If you didn't request this, ignore this email.</p>";

    sendMail($email, $name, 'Password Reset – ZEBIR LIBAS', emailWrapper($content));
}

function sendShippingEmail(array $order): void {
    $tracking = $order['tracking_number'] ? "Tracking Number: <strong>{$order['tracking_number']}</strong><br>" : '';
    $trackUrl = $order['tracking_url'] ? "<a href='{$order['tracking_url']}' class='btn'>TRACK SHIPMENT</a>" : '';
    $content = "<h2>Your Order is Shipped!</h2>
    <p>Hi {$order['shipping_name']}, your order <strong>{$order['order_number']}</strong> is on its way.</p>
    <p>Courier: <strong>{$order['courier_name']}</strong><br>{$tracking}Expected Delivery: <strong>{$order['expected_delivery']}</strong></p>
    {$trackUrl}
    <a href='" . BASE_URL . "orders.php' class='btn' style='margin-left:8px;'>MY ORDERS</a>";

    sendMail($order['shipping_email'], $order['shipping_name'], 'Your Order is Shipped – ' . $order['order_number'], emailWrapper($content));
}

function sendAccountCreatedEmail(string $email, string $name, string $password): void {
    $content = "<h2>Welcome to ZEBIR LIBAS!</h2>
    <p>Hi {$name},</p>
    <p>Thank you for your order! To make it easier for you to track your orders, we have automatically created an account for you.</p>
    <p><strong>Your Email:</strong> {$email}</p>
    <p><strong>Your Password:</strong> {$password}</p>
    <p>You can use this to log in and view your order details.</p>
    <a href='" . BASE_URL . "login.php' class='btn'>LOG IN TO YOUR ACCOUNT</a>
    <p style='font-size:12px;color:#999;margin-top:20px;'>We recommend changing this password from your account dashboard once you log in.</p>";

    sendMail($email, $name, 'Welcome to ZEBIR LIBAS - Account Created', emailWrapper($content));
}
