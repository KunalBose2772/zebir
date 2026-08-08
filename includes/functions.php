<?php
/**
 * Global Helper Functions
 * ZEBIR LIBAS
 */

// ── Sanitize & Escape ──────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

// ── Redirect ───────────────────────────────────────────────────
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

function redirectTo(string $path): void {
    redirect(BASE_URL . ltrim($path, '/'));
}

// ── Slug Generator ─────────────────────────────────────────────
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ── Unique Slug ────────────────────────────────────────────────
function uniqueSlug(string $table, string $text, int $excludeId = 0): string {
    $pdo  = getDB();
    $slug = $base = slugify($text);
    $i    = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        $params = [$slug];
        if ($excludeId > 0) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        $row = $pdo->prepare($sql);
        $row->execute($params);
        if (!$row->fetch()) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ── Price Formatting ───────────────────────────────────────────
function formatPrice(float $amount): string {
    $symbol = getSetting('currency_symbol', '₹');
    return $symbol . number_format($amount, 2);
}

// ── Order Number ───────────────────────────────────────────────
function generateOrderNumber(): string {
    return 'ZBL-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

// ── Flash Messages ─────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ── Auth helpers ───────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['customer_id']);
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to continue.');
        redirectTo('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        redirectTo('admin/login');
    }
}

function currentCustomer(): ?array {
    if (!isLoggedIn()) return null;
    static $customer = null;
    if ($customer === null) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['customer_id']]);
        $customer = $stmt->fetch() ?: null;
    }
    return $customer;
}

// ── Cart helpers ───────────────────────────────────────────────
function getCartKey(): string {
    return 'cart_' . (isLoggedIn() ? $_SESSION['customer_id'] : session_id());
}

function getCart(): array {
    return $_SESSION[getCartKey()] ?? [];
}

function saveCart(array $cart): void {
    $_SESSION[getCartKey()] = $cart;
}

function mergeSessionData(string $oldSessionId, int $customerId): void {
    $pdo = getDB();

    // 1. Merge cart
    $guestKey = 'cart_' . $oldSessionId;
    $customerKey = 'cart_' . $customerId;

    $guestCart = $_SESSION[$guestKey] ?? [];
    $customerCart = $_SESSION[$customerKey] ?? [];

    if (!empty($guestCart)) {
        foreach ($guestCart as $guestItemKey => $guestItem) {
            if (isset($customerCart[$guestItemKey])) {
                $customerCart[$guestItemKey]['qty'] += $guestItem['qty'];
            } else {
                $customerCart[$guestItemKey] = $guestItem;
            }
        }
        $_SESSION[$customerKey] = $customerCart;
        unset($_SESSION[$guestKey]);
    }

    // 2. Merge wishlist
    $stmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE session_id = ?");
    $stmt->execute([$oldSessionId]);
    $guestItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($guestItems)) {
        foreach ($guestItems as $prodId) {
            $chk = $pdo->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
            $chk->execute([$customerId, $prodId]);
            if (!$chk->fetch()) {
                $ins = $pdo->prepare("INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?)");
                $ins->execute([$customerId, $prodId]);
            }
        }
        $del = $pdo->prepare("DELETE FROM wishlists WHERE session_id = ?");
        $del->execute([$oldSessionId]);
    }
}

function cartCount(): int {
    return array_sum(array_column(getCart(), 'qty'));
}

function cartTotal(): float {
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

// ── Wishlist ───────────────────────────────────────────────────
function wishlistCount(): int {
    $pdo = getDB();
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE customer_id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    return (int) $stmt->fetchColumn();
}

function isInWishlist(int $productId): bool {
    $pdo = getDB();
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['customer_id'], $productId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE session_id = ? AND product_id = ?");
        $stmt->execute([session_id(), $productId]);
    }
    return (bool) $stmt->fetch();
}

// ── Customer Address Helpers ────────────────────────────────────
function getCustomerDefaultAddress(int $customerId): array|null {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id DESC LIMIT 1");
    $stmt->execute([$customerId]);
    return $stmt->fetch() ?: null;
}

function saveCustomerAddress(int $customerId, array $data): void {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM customer_addresses WHERE customer_id = ? LIMIT 1");
    $stmt->execute([$customerId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE customer_addresses SET 
            name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, pincode = ?, is_default = 1
            WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['address_line1'],
            $data['address_line2'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $existing['id']
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO customer_addresses 
            (customer_id, name, phone, address_line1, address_line2, city, state, pincode, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $customerId,
            $data['name'],
            $data['phone'],
            $data['address_line1'],
            $data['address_line2'],
            $data['city'],
            $data['state'],
            $data['pincode']
        ]);
    }
}

// ── Image Helpers ──────────────────────────────────────────────
function productImageUrl(string $img): string {
    if (!$img) {
        return BASE_URL . 'assets/images/placeholder.jpg';
    }

    $webpImg = preg_replace('/\.(png|jpe?g)$/i', '.webp', $img);
    
    // Check uploads first
    $uploadWebpCandidate = BASE_PATH . 'uploads/products/' . ltrim($webpImg, '/');
    if (file_exists($uploadWebpCandidate)) {
        return UPLOAD_PRODUCTS_URL . ltrim($webpImg, '/');
    }

    // Check assets/images next
    $assetWebpCandidate = BASE_PATH . 'assets/images/' . ltrim($webpImg, '/');
    if (file_exists($assetWebpCandidate)) {
        return assetUrl('images/' . ltrim($webpImg, '/'));
    }

    return UPLOAD_PRODUCTS_URL . ltrim($img, '/');
}

function bannerImageUrl(string $img): string {
    if (!$img) {
        return BASE_URL . 'assets/images/banner-placeholder.jpg';
    }

    $webpImg = preg_replace('/\.(png|jpe?g)$/i', '.webp', $img);

    // 1. Check if the image exists in assets/images/
    $assetWebpCandidate = BASE_PATH . 'assets/images/' . ltrim($webpImg, '/');
    if (file_exists($assetWebpCandidate)) {
        return assetUrl('images/' . ltrim($webpImg, '/'));
    }
    $assetCandidate = BASE_PATH . 'assets/images/' . ltrim($img, '/');
    if (file_exists($assetCandidate)) {
        return assetUrl('images/' . ltrim($img, '/'));
    }

    // 2. Otherwise check in uploads/banners/
    $uploadWebpCandidate = BASE_PATH . 'uploads/banners/' . ltrim($webpImg, '/');
    if (file_exists($uploadWebpCandidate)) {
        return UPLOAD_BANNERS_URL . ltrim($webpImg, '/');
    }

    return UPLOAD_BANNERS_URL . ltrim($img, '/');
}

function bannerMobileImageUrl(string $img): string {
    if (!$img) {
        return BASE_URL . 'assets/images/banner-placeholder.jpg';
    }

    // 1. Check if the image name contains "_DESKTOP" and replace it with "_MOBILE"
    if (stripos($img, '_DESKTOP') !== false) {
        $mobileImg = str_ireplace('_DESKTOP', '_MOBILE', $img);
        $webpMobileImg = preg_replace('/\.(png|jpe?g)$/i', '.webp', $mobileImg);
        
        $assetWebpCandidate = BASE_PATH . 'assets/images/' . ltrim($webpMobileImg, '/');
        if (file_exists($assetWebpCandidate)) {
            return assetUrl('images/' . ltrim($webpMobileImg, '/'));
        }

        $assetCandidate = BASE_PATH . 'assets/images/' . ltrim($mobileImg, '/');
        if (file_exists($assetCandidate)) {
            return assetUrl('images/' . ltrim($mobileImg, '/'));
        }
    }

    // 2. For custom uploaded banners, check if a mobile version exists with "_mobile" suffix
    $pathInfo = pathinfo($img);
    $ext = $pathInfo['extension'] ?? '';
    $filename = $pathInfo['filename'] ?? '';
    
    $webpMobileFilename = preg_replace('/_desktop$/i', '', $filename) . '_mobile.webp';
    $uploadWebpMobileCandidate = BASE_PATH . 'uploads/banners/' . $webpMobileFilename;
    if (file_exists($uploadWebpMobileCandidate)) {
        return UPLOAD_BANNERS_URL . $webpMobileFilename;
    }

    $mobileFilename = preg_replace('/_desktop$/i', '', $filename) . '_mobile.' . $ext;
    $uploadMobileCandidate = BASE_PATH . 'uploads/banners/' . $mobileFilename;
    if (file_exists($uploadMobileCandidate)) {
        return UPLOAD_BANNERS_URL . $mobileFilename;
    }

    // Fallback: return the desktop webp banner if available, or original
    return bannerImageUrl($img);
}

function categoryImageUrl(string $img): string {
    if (!$img) {
        return BASE_URL . 'assets/images/placeholder.jpg';
    }

    $webpImg = preg_replace('/\.(png|jpe?g)$/i', '.webp', $img);

    // Check assets first (both original and webp)
    $assetWebpCandidate = BASE_PATH . 'assets/images/' . ltrim($webpImg, '/');
    if (file_exists($assetWebpCandidate)) {
        return assetUrl('images/' . ltrim($webpImg, '/'));
    }
    $assetCandidate = BASE_PATH . 'assets/images/' . ltrim($img, '/');
    if (file_exists($assetCandidate)) {
        return assetUrl('images/' . ltrim($img, '/'));
    }

    // Check uploads/products next (both original and webp)
    $uploadWebpCandidate = BASE_PATH . 'uploads/products/' . ltrim($webpImg, '/');
    if (file_exists($uploadWebpCandidate)) {
        return UPLOAD_PRODUCTS_URL . ltrim($webpImg, '/');
    }

    return UPLOAD_PRODUCTS_URL . ltrim($img, '/');
}

function getHomepageCategoryDefaults(): array {
    return [
        ['slug' => 'pakistani-lawn-suits', 'name' => 'Pakistani Lawn Suits', 'image' => 'CAT_PAKISTANI_LAWN.webp', 'description' => 'Soft premium lawn fabric styled for daily elegance.'],
        ['slug' => 'masleen-suits', 'name' => 'Masleen Suits', 'image' => 'CAT_MASLEEN.webp', 'description' => 'Lightweight masleen suits for festive and everyday luxury.'],
        ['slug' => 'cotton-suits', 'name' => 'Cotton Suits', 'image' => 'CAT_COTTON_SUITS.webp', 'description' => 'Comfortable cotton suits with modern embroidery detail.'],
        ['slug' => 'festive-collection', 'name' => 'Festive Collection', 'image' => 'CAT_FESTIVE.webp', 'description' => 'Special occasion designs crafted with rich textures and shine.'],
        ['slug' => 'lawn-cotton-suits', 'name' => 'Lawn Cotton Suits', 'image' => 'CAT_LAWN_COTTON_SUITS.webp', 'description' => 'Breathable lawn cotton suits with elegant prints.'],
        ['slug' => 'silk-collection', 'name' => 'Silk Collection', 'image' => 'CAT_SILK.webp', 'description' => 'Smooth silk sets designed for glamour and comfort.'],
    ];
}

function getSettingArray(string $key, array $default = []): array {
    $value = getSetting($key, '');
    if ($value === '') {
        return $default;
    }
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : $default;
}

function resizeAndConvertToWebP(string $path, string $destPath, int $maxW, int $maxH, int $quality = 82): bool {
    [$srcW, $srcH, $type] = @getimagesize($path);
    if (!$srcW) return false;

    $ratio = min($maxW / $srcW, $maxH / $srcH, 1);
    $newW  = (int)($srcW * $ratio);
    $newH  = (int)($srcH * $ratio);

    $src = match($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => @imagecreatefrompng($path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        default        => null,
    };
    if (!$src) return false;

    $dst = imagecreatetruecolor($newW, $newH);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

    $success = imagewebp($dst, $destPath, $quality);

    imagedestroy($src);
    imagedestroy($dst);

    return $success;
}

function uploadImage(array $file, string $destDir, int $width = 800, int $height = 1000): string|false {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $filename = uniqid('img_') . '.webp';
    $dest     = rtrim($destDir, '/') . '/' . $filename;

    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $tempFile = rtrim($destDir, '/') . '/temp_' . uniqid() . '_' . basename($file['name']);
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) return false;

    $success = resizeAndConvertToWebP($tempFile, $dest, $width, $height);
    @unlink($tempFile);

    return $success ? $filename : false;
}

function resizeImage(string $path, int $maxW, int $maxH): void {
    [$srcW, $srcH, $type] = getimagesize($path);
    if (!$srcW) return;

    $ratio = min($maxW / $srcW, $maxH / $srcH, 1);
    $newW  = (int)($srcW * $ratio);
    $newH  = (int)($srcH * $ratio);

    $src = match($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => imagecreatefrompng($path),
        IMAGETYPE_WEBP => imagecreatefromwebp($path),
        default        => null,
    };
    if (!$src) return;

    $dst = imagecreatetruecolor($newW, $newH);
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

    match($type) {
        IMAGETYPE_JPEG => imagejpeg($dst, $path, 90),
        IMAGETYPE_PNG  => imagepng($dst, $path),
        IMAGETYPE_WEBP => imagewebp($dst, $path, 90),
        default        => null,
    };

    imagedestroy($src);
    imagedestroy($dst);
}

// ── Pagination ─────────────────────────────────────────────────
function paginate(int $total, int $perPage, int $currentPage, string $url): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => ($currentPage - 1) * $perPage,
        'url'         => $url,
    ];
}

// ── Misc ───────────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60     => 'just now',
        $diff < 3600   => round($diff / 60) . ' min ago',
        $diff < 86400  => round($diff / 3600) . ' hours ago',
        $diff < 604800 => round($diff / 86400) . ' days ago',
        default        => date('d M Y', strtotime($datetime)),
    };
}

function truncate(string $str, int $len = 100): string {
    return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . '…' : $str;
}

function activeClass(string $page): string {
    $current = basename($_SERVER['PHP_SELF'] ?? '');
    $pageClean = preg_replace('/\.php$/', '', $page);
    $currentClean = preg_replace('/\.php$/', '', $current);
    return ($currentClean === $pageClean) ? 'active' : '';
}

function assetUrl(string $path): string {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

// ── Clean URL Helpers ──────────────────────────────────────────
function productUrl(string $slug): string {
    return BASE_URL . 'product/' . $slug;
}

function categoryUrl(string $slug): string {
    return BASE_URL . 'category/' . $slug;
}

function pageUrl(string $page): string {
    $page = preg_replace('/\.php$/', '', $page);
    return BASE_URL . ($page === 'index' ? '' : $page);
}

