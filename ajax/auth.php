<?php
/**
 * ZEBIR LIBAS – AJAX Authentication handler
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Verify CSRF
try {
    verifyCsrf();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'CSRF verification failed. Please refresh the page.']);
    exit;
}

$action = sanitize($_POST['action'] ?? '');

if ($action === 'login') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Please enter email and password.']);
        exit;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer && verifyPassword($password, $customer['password'])) {
        $oldSessionId = session_id();
        $_SESSION['customer_id']   = $customer['id'];
        $_SESSION['customer_name'] = $customer['name'];
        mergeSessionData($oldSessionId, $customer['id']);
        
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email address or password.']);
        exit;
    }
}

elseif ($action === 'register') {
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $phone    = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    if (!$name) $errors[] = 'Full name is required.';
    if (!isValidEmail($email)) $errors[] = 'Valid email address is required.';
    if ($phone && !isValidPhone($phone)) $errors[] = 'Invalid phone number format.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }

    $pdo = getDB();
    // Check duplicate email
    $chk = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
    $chk->execute([$email]);
    if ($chk->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address is already registered.']);
        exit;
    }

    $hash = hashPassword($password);
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $hash]);
    $id = $pdo->lastInsertId();

    $oldSessionId = session_id();
    $_SESSION['customer_id']   = $id;
    $_SESSION['customer_name'] = $name;
    mergeSessionData($oldSessionId, $id);

    // Send Welcome Email if needed (wrapped in try/catch to not block success response)
    try {
        require_once __DIR__ . '/../includes/mailer.php';
        sendAccountCreatedEmail($email, $name, $password);
    } catch (Exception $mailEx) {
        error_log("Welcome email failed during AJAX registration: " . $mailEx->getMessage());
    }

    echo json_encode(['success' => true]);
    exit;
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}
