<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Security
{
    // ========================================================================
    // HASHING DE CONTRASENAS
    // ========================================================================

    public static function hashPassword(string $password): string
    {
        $cost = defined('HASH_COST') ? HASH_COST : 12;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        $cost = defined('HASH_COST') ? HASH_COST : 12;
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    // ========================================================================
    // CSRF PROTECTION
    // ========================================================================

    public static function generateCsrfToken(): string
    {
        Session::start();
        $tokenKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'saluvera_csrf';

        if (empty($_SESSION[$tokenKey])) {
            $_SESSION[$tokenKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$tokenKey];
    }

    public static function getCsrfToken(): string
    {
        return self::generateCsrfToken();
    }

    public static function csrfField(): string
    {
        $token = self::getCsrfToken();
        $name = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'saluvera_csrf';
        return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
    }

    public static function verifyCsrfToken(?string $token = null): bool
    {
        Session::start();
        $tokenKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'saluvera_csrf';

        $token = $token ?? ($_POST[$tokenKey] ?? null);

        if ($token === null || empty($_SESSION[$tokenKey])) {
            return false;
        }

        return hash_equals($_SESSION[$tokenKey], $token);
    }

    public static function validateCsrf(): bool
    {
        if (!self::verifyCsrfToken()) {
            return false;
        }
        return true;
    }

    // ========================================================================
    // SANITIZACION
    // ========================================================================

    public static function sanitize(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitize($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    public static function stripTags(?string $value): string
    {
        return strip_tags($value ?? '');
    }

    public static function cleanInput(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::sanitize($value);
        }

        if (is_array($value)) {
            return self::sanitizeArray($value);
        }

        return $value;
    }

    // ========================================================================
    // TOKENS ALEATORIOS
    // ========================================================================

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public static function generateApiKey(): string
    {
        return 'salu_' . bin2hex(random_bytes(24));
    }

    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function generateRandomCode(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    // ========================================================================
    // VALIDACION DE EMAIL Y URL
    // ========================================================================

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    // ========================================================================
    // SEGURIDAD DE SESION
    // ========================================================================

    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function fingerprint(): string
    {
        $data = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
        ];
        return hash('sha256', implode('|', $data));
    }

    public static function verifyFingerprint(?string $storedFingerprint = null): bool
    {
        if ($storedFingerprint === null) {
            return true;
        }

        return hash_equals($storedFingerprint, self::fingerprint());
    }

    // ========================================================================
    // RATE LIMITING BASICO
    // ========================================================================

    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        Session::start();

        $rateLimitKey = '_rate_limit_' . $key;
        $now = time();

        $attempts = Session::get($rateLimitKey, ['count' => 0, 'expires' => 0]);

        if ($now > $attempts['expires']) {
            Session::set($rateLimitKey, ['count' => 0, 'expires' => $now + ($decayMinutes * 60)]);
            return true;
        }

        if ($attempts['count'] >= $maxAttempts) {
            return false;
        }

        Session::set($rateLimitKey, [
            'count' => $attempts['count'] + 1,
            'expires' => $attempts['expires'],
        ]);

        return true;
    }

    public static function clearRateLimit(string $key): void
    {
        Session::remove('_rate_limit_' . $key);
    }

    // ========================================================================
    // ENCRIPTACION BASICA
    // ========================================================================

    public static function encrypt(string $data, ?string $key = null): string
    {
        $key = $key ?? (defined('APP_KEY') ? APP_KEY : 'saluvera_default_key');
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    public static function decrypt(string $data, ?string $key = null): ?string
    {
        $key = $key ?? (defined('APP_KEY') ? APP_KEY : 'saluvera_default_key');
        $data = base64_decode($data);

        if ($data === false) {
            return null;
        }

        [$iv, $encrypted] = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        return $decrypted !== false ? $decrypted : null;
    }

    // ========================================================================
    // SLUG GENERATION
    // ========================================================================

    public static function slug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]/u', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    // ========================================================================
    // XSS PROTECTION
    // ========================================================================

    public static function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';
        return strip_tags($html, $allowedTags);
    }

    public static function removeXss(string $input): string
    {
        $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
        $input = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        return $input;
    }

    // ========================================================================
    // COMPARACION SEGURA
    // ========================================================================

    public static function secureCompare(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }

    // ========================================================================
    // UTILIDADES
    // ========================================================================

    public static function generateSecureId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}