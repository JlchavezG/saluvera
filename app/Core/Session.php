<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = defined('SESSION_NAME') ? SESSION_NAME : 'saluvera_session';
        $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 120;

        session_name($sessionName);

        $cookieParams = [
            'lifetime' => $lifetime * 60,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        session_set_cookie_params($cookieParams);
        session_start();

        self::$started = true;

        self::regenerateIfExpired();
    }

    public static function isStarted(): bool
    {
        return self::$started || session_status() === PHP_SESSION_ACTIVE;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::remove($key);
        return $value;
    }

    public static function all(): array
    {
        self::start();
        return $_SESSION ?? [];
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        self::$started = false;

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }

    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::start();
        session_regenerate_id($deleteOldSession);
    }

    private static function regenerateIfExpired(): void
    {
        $lastRegeneration = $_SESSION['_last_regeneration'] ?? 0;
        $currentTime = time();

        if ($currentTime - $lastRegeneration > 1800) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = $currentTime;
        }
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();

        if (!isset($_SESSION['_flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }

    public static function flashSuccess(string $message): void
    {
        self::flash('success', $message);
    }

    public static function flashError(string $message): void
    {
        self::flash('error', $message);
    }

    public static function flashWarning(string $message): void
    {
        self::flash('warning', $message);
    }

    public static function flashInfo(string $message): void
    {
        self::flash('info', $message);
    }

    public static function getFlashMessages(): array
    {
        self::start();

        $messages = [];
        $types = ['success', 'error', 'warning', 'info'];

        foreach ($types as $type) {
            if (isset($_SESSION['_flash'][$type])) {
                $messages[] = [
                    'type' => $type,
                    'message' => $_SESSION['_flash'][$type],
                ];
                unset($_SESSION['_flash'][$type]);
            }
        }

        return $messages;
    }

    public static function token(): string
    {
        self::start();

        $tokenKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'saluvera_csrf';

        if (empty($_SESSION[$tokenKey])) {
            $_SESSION[$tokenKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$tokenKey];
    }

    public static function verifyToken(?string $token = null): bool
    {
        self::start();

        $tokenKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'saluvera_csrf';
        $token = $token ?? ($_POST[$tokenKey] ?? null);

        if ($token === null || empty($_SESSION[$tokenKey])) {
            return false;
        }

        return hash_equals($_SESSION[$tokenKey], $token);
    }

    public static function user(): ?array
    {
        return self::get('user');
    }

    public static function setUserId(int $userId): void
    {
        self::set('user_id', $userId);
    }

    public static function getUserId(): ?int
    {
        $userId = self::get('user_id');
        return $userId !== null ? (int) $userId : null;
    }

    public static function isAuthenticated(): bool
    {
        return self::getUserId() !== null;
    }

    public static function id(): string
    {
        self::start();
        return session_id();
    }
}