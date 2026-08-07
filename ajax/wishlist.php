<?php
/**
 * AJAX Wishlist Toggle Handler
 * ZEBIR LIBAS
 */
require_once __DIR__ . '/../includes/bootstrap.php';

verifyCsrf();

$productId = (int)($_POST['product_id'] ?? 0);
if (!$productId) {
    jsonResponse(['success' => false, 'message' => 'Invalid product.'], 400);
}

$pdo = getDB();
$customerId = $_SESSION['customer_id'] ?? null;
$sessionId  = session_id();

if ($customerId) {
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$customerId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM wishlists WHERE id = ?");
        $del->execute([$existing['id']]);
        $inWishlist = false;
        $msg = 'Removed from wishlist';
    } else {
        $ins = $pdo->prepare("INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?)");
        $ins->execute([$customerId, $productId]);
        $inWishlist = true;
        $msg = 'Added to wishlist';
    }
} else {
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM wishlists WHERE id = ?");
        $del->execute([$existing['id']]);
        $inWishlist = false;
        $msg = 'Removed from wishlist';
    } else {
        $ins = $pdo->prepare("INSERT INTO wishlists (session_id, product_id) VALUES (?, ?)");
        $ins->execute([$sessionId, $productId]);
        $inWishlist = true;
        $msg = 'Added to wishlist';
    }
}

jsonResponse([
    'success'     => true,
    'in_wishlist' => $inWishlist,
    'message'     => $msg,
    'count'       => wishlistCount()
]);
