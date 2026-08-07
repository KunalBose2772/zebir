<?php
/**
 * AJAX Cart Handler
 * ZEBIR LIBAS
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$action = sanitize($_REQUEST['action'] ?? '');

switch ($action) {

    // ── Get Cart Count ─────────────────────────────────────────
    case 'get_count':
        jsonResponse(['count' => cartCount()]);
        break;

    // ── Get Cart Drawer Content HTML ───────────────────────────
    case 'get_drawer':
        $cart = getCart();
        $subtotal = cartTotal();
        $freeShippingThreshold = (float)getSetting('free_shipping_amount', '999');
        $shippingCharge = $subtotal >= $freeShippingThreshold || $subtotal == 0 ? 0 : (float)getSetting('shipping_charge', '99');
        
        if (empty($cart)) {
            echo '<div class="text-center py-5">
                    <p class="text-muted mb-3">Your shopping bag is empty.</p>
                    <a href="' . pageUrl('shop') . '" class="btn-luxury btn-sm">EXPLORE SHOP</a>
                  </div>';
            exit;
        }

        echo '<div style="display:flex; flex-direction:column; gap:20px;">';
        foreach ($cart as $key => $item) {
            $itemTotal = $item['price'] * $item['qty'];
            echo '
            <div style="display:flex; gap:16px; align-items:center; padding-bottom:16px; border-bottom:1px solid var(--border-color);">
                <img src="' . productImageUrl($item['image']) . '" alt="' . e($item['name']) . '" style="width:70px; height:90px; object-fit:cover; border-radius:2px;">
                <div style="flex:1;">
                    <h5 style="font-size:0.9rem; margin-bottom:4px; font-weight:500;">' . e($item['name']) . '</h5>
                    ' . ($item['size'] ? '<span style="font-size:0.75rem; color:var(--text-muted); display:block;">Size: ' . e($item['size']) . '</span>' : '') . '
                    ' . ($item['color'] ? '<span style="font-size:0.75rem; color:var(--text-muted); display:block;">Color: ' . e($item['color']) . '</span>' : '') . '
                    <div style="font-weight:600; font-size:0.85rem; margin-top:4px;">' . $item['qty'] . ' × ' . formatPrice($item['price']) . '</div>
                </div>
                <button type="button" class="icon-btn text-muted" onclick="removeCartItem(\'' . $key . '\')" title="Remove">&times;</button>
            </div>';
        }
        echo '</div>';

        echo '
        <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--border-color);">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
                <span>Subtotal</span>
                <span class="font-weight-bold">' . formatPrice($subtotal) . '</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:16px; font-size:0.85rem; color:var(--text-muted);">
                <span>Estimated Shipping</span>
                <span>' . ($shippingCharge > 0 ? formatPrice($shippingCharge) : '<span class="text-success">FREE</span>') . '</span>
            </div>
            <div style="display:flex; gap:12px; margin-top:16px;">
                <a href="' . pageUrl('cart') . '" class="btn-luxury-outline" style="flex:1; text-align:center; padding:12px; font-size:0.72rem; letter-spacing:1px; border-radius:99px; height:42px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; text-transform:uppercase;">VIEW BAG</a>
                <a href="' . pageUrl('checkout') . '" class="btn-luxury btn-gold" style="flex:1; text-align:center; padding:12px; font-size:0.72rem; letter-spacing:1px; border-radius:99px; height:42px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; text-transform:uppercase;">CHECKOUT</a>
            </div>
        </div>
        <script>
        function removeCartItem(key) {
            fetch(BASE_URL + "ajax/cart.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=remove&key=" + encodeURIComponent(key) + "&csrf_token=" + CSRF_TOKEN
            })
            .then(res => res.json())
            .then(data => {
                loadCartDrawerContents();
                updateCartCounts();
            });
        }
        </script>';
        break;

    // ── Add Item to Cart ───────────────────────────────────────
    case 'add':
        verifyCsrf();
        $productId = (int)($_POST['product_id'] ?? 0);
        $size      = sanitize($_POST['size'] ?? '');
        $color     = sanitize($_POST['color'] ?? '');
        $qty       = max(1, (int)($_POST['qty'] ?? 1));

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $cart = getCart();
        $itemKey = $productId . '_' . md5($size . '_' . $color);
        $unitPrice = (float)($product['sale_price'] ?: $product['price']);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['qty'] += $qty;
        } else {
            $cart[$itemKey] = [
                'id'       => $product['id'],
                'name'     => $product['name'],
                'slug'     => $product['slug'],
                'image'    => $product['featured_image'],
                'price'    => $unitPrice,
                'size'     => $size,
                'color'    => $color,
                'qty'      => $qty,
            ];
        }

        saveCart($cart);
        jsonResponse([
            'success' => true, 
            'message' => 'Added to bag successfully.',
            'count'   => cartCount()
        ]);
        break;

    // ── Remove Item from Cart ──────────────────────────────────
    case 'remove':
        verifyCsrf();
        $key = sanitize($_POST['key'] ?? '');
        $cart = getCart();
        if (isset($cart[$key])) {
            unset($cart[$key]);
            saveCart($cart);
        }
        jsonResponse(['success' => true, 'count' => cartCount()]);
        break;

    // ── Update Item Quantity ───────────────────────────────────
    case 'update':
        verifyCsrf();
        $key = sanitize($_POST['key'] ?? '');
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $cart = getCart();
        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $qty;
            saveCart($cart);
        }
        jsonResponse(['success' => true, 'count' => cartCount(), 'total' => cartTotal()]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
