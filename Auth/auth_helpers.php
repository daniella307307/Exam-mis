<?php
/**
 * Shared authentication helpers for the BLIS LMS.
 *
 * - Modern password hashing (bcrypt via password_hash/password_verify).
 * - Backward compatibility: verifies legacy MD5 hashes and signals when a
 *   user's stored hash should be upgraded to bcrypt on next successful
 *   login. Callers can persist the upgraded hash transparently.
 * - Session hardening: regenerates the session ID on every successful
 *   login to defeat session fixation.
 * - CSRF tokens for login/reset forms.
 *
 * Include with: require_once __DIR__ . '/Auth/auth_helpers.php';
 */

if (!function_exists('auth_hash_password')) {
    /**
     * Hash a plaintext password using bcrypt (cost 12).
     * Returns null on failure so callers can fail closed.
     */
    function auth_hash_password(string $plain): ?string
    {
        if ($plain === '') {
            return null;
        }
        $hash = password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
        return is_string($hash) ? $hash : null;
    }

    /**
     * Verify a plaintext password against a stored hash.
     *
     * Accepts:
     *   - bcrypt hashes ($2y$...) — preferred.
     *   - 32-char MD5 hex hashes — legacy accounts.
     *
     * @return array{ok: bool, needs_rehash: bool}
     */
    function auth_verify_password(string $plain, ?string $stored): array
    {
        $stored = $stored ?? '';
        if ($plain === '' || $stored === '') {
            return ['ok' => false, 'needs_rehash' => false];
        }

        // Bcrypt / Argon2 hashes always start with $2y$, $2a$, $2b$, $argon2...
        if (str_starts_with($stored, '$')) {
            $ok = password_verify($plain, $stored);
            $rehash = $ok && password_needs_rehash($stored, PASSWORD_BCRYPT, ['cost' => 12]);
            return ['ok' => $ok, 'needs_rehash' => $rehash];
        }

        // Legacy MD5: 32 hex chars.
        if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
            $ok = hash_equals(strtolower($stored), md5($plain));
            return ['ok' => $ok, 'needs_rehash' => $ok];
        }

        return ['ok' => false, 'needs_rehash' => false];
    }

    /**
     * Initialise a logged-in session. Regenerates the session ID to prevent
     * fixation, then writes the supplied keys.
     *
     * @param array<string, mixed> $extras
     */
    function auth_session_init(int $user_id, array $extras = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Drop any anonymous session attributes before regenerating.
        $_SESSION = [];
        session_regenerate_id(true);

        $_SESSION['logged_in']     = true;
        $_SESSION['user_id']       = $user_id;
        $_SESSION['login_time']    = time();
        $_SESSION['last_activity'] = time();
        foreach ($extras as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }

    /**
     * Get (and lazily create) the per-session CSRF token.
     */
    function auth_csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Constant-time compare a submitted token against the session token.
     */
    function auth_csrf_check(?string $submitted): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!is_string($submitted) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $submitted);
    }

    /**
     * Cryptographically random URL-safe token (used for password resets).
     */
    function auth_random_token(int $bytes = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }
}
