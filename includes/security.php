<?php
/**
 * Security Helpers
 * ZEBIR LIBAS
 */

// ── CSRF ───────────────────────────────────────────────────────
function csrfToken(): string {
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrfToken() . '">';
}

function verifyCsrf(): void {
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_GET[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token.']));
    }
}

// ── XSS Output ────────────────────────────────────────────────
function clean(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Rate Limiter (simple session-based) ───────────────────────
function rateLimit(string $key, int $max, int $windowSeconds): bool {
    $now = time();
    $data = $_SESSION['rate_' . $key] ?? ['count' => 0, 'start' => $now];

    if ($now - $data['start'] > $windowSeconds) {
        $data = ['count' => 1, 'start' => $now];
    } else {
        $data['count']++;
    }
    $_SESSION['rate_' . $key] = $data;
    return $data['count'] <= $max;
}

// ── Password ──────────────────────────────────────────────────
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// ── JSON Response ─────────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): void {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

// ── Validate Email ────────────────────────────────────────────
function isValidEmail(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ── Validate Phone ────────────────────────────────────────────
function isValidPhone(string $phone): bool {
    return preg_match('/^[\+\d\s\-]{7,20}$/', $phone) === 1;
}
