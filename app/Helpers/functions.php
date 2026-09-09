<?php
/**
 * SALUVERA - Funciones Auxiliares
 *
 * Funciones globales usadas en toda la aplicacion.
 * Este archivo se carga automaticamente via composer.json
 *
 * @author SALUVERA
 * @version 1.0.1
 */

declare(strict_types=1);

// Prevenir acceso directo
if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

// ============================================================================
// FUNCIONES DE RUTAS Y URLs
// ============================================================================

/**
 * Genera una URL completa desde una ruta relativa
 */
function url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', 'http://localhost/saluvera/public'), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Genera una URL para un asset (CSS, JS, imagen)
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Genera una URL para un asset de la aplicacion (no landing)
 */
function app_asset(string $path): string
{
    return url('app-assets/' . ltrim($path, '/'));
}

/**
 * Devuelve la ruta fisica completa desde la raiz del proyecto
 */
function base_path(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

/**
 * Devuelve la ruta fisica de la carpeta storage
 */
function storage_path(string $path = ''): string
{
    return STORAGE_PATH . '/' . ltrim($path, '/');
}

// ============================================================================
// FUNCIONES DE REDIRECCION
// ============================================================================

/**
 * Redirige a una URL y termina la ejecucion
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redirige a una ruta interna
 */
function redirect_to(string $path): never
{
    redirect(url($path));
}

/**
 * Redirige de vuelta a la pagina anterior
 */
function redirect_back(string $default = '/'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? url($default);
    redirect($referer);
}

// ============================================================================
// FUNCIONES DE VISTAS
// ============================================================================

/**
 * Renderiza una vista y devuelve el HTML
 */
function view(string $template, array $data = []): string
{
    $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
    
    if (!file_exists($file)) {
        throw new RuntimeException("Vista no encontrada: {$template}");
    }
    
    extract($data, EXTR_SKIP);
    ob_start();
    include $file;
    return ob_get_clean();
}

/**
 * Imprime una vista directamente
 */
function render(string $template, array $data = []): void
{
    echo view($template, $data);
}

/**
 * Incluye un partial (fragmento de vista)
 */
function partial(string $name, array $data = []): void
{
    $file = VIEWS_PATH . '/Partials/' . $name . '.php';
    
    if (file_exists($file)) {
        extract($data, EXTR_SKIP);
        include $file;
    }
}

// ============================================================================
// FUNCIONES DE SEGURIDAD
// ============================================================================

/**
 * Genera un token CSRF
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Genera el campo HTML oculto para CSRF
 */
function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

/**
 * Verifica si el token CSRF es valido
 */
function csrf_verify(?string $token = null): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = $token ?? ($_POST[CSRF_TOKEN_NAME] ?? null);
    
    if ($token === null || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Escapa HTML para prevenir XSS
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Hashea una contrasena
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verifica una contrasena contra un hash
 */
function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Verifica si un hash necesita ser rehasheado (CORREGIDO: renombrado)
 */
function check_password_needs_rehash(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Genera una cadena aleatoria segura
 */
function random_string(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Genera un slug desde un string
 */
function slug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}]/u', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ============================================================================
// FUNCIONES DE SESION
// ============================================================================

/**
 * Obtiene un valor de la sesion
 */
function session_get(string $key, mixed $default = null): mixed
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION[$key] ?? $default;
}

/**
 * Guarda un valor en la sesion
 */
function session_set(string $key, mixed $value): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION[$key] = $value;
}

/**
 * Elimina un valor de la sesion
 */
function session_remove(string $key): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    unset($_SESSION[$key]);
}

/**
 * Destruye la sesion completa
 */
function session_destroy_all(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Genera un mensaje flash (se muestra una vez)
 */
function flash(string $type, string $message): void
{
    session_set('flash_messages', [
        'type' => $type,
        'message' => $message,
    ]);
}

/**
 * Obtiene y elimina el mensaje flash
 */
function get_flash(): ?array
{
    $flash = session_get('flash_messages');
    session_remove('flash_messages');
    return $flash;
}

// ============================================================================
// FUNCIONES DE VALIDACION
// ============================================================================

/**
 * Verifica si un email es valido
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Verifica si una fecha es valida
 */
function is_valid_date(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Sanitiza un string para uso seguro
 */
function sanitize(string $input): string
{
    $input = trim($input);
    $input = stripslashes($input);
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// ============================================================================
// FUNCIONES DE FORMATO
// ============================================================================

/**
 * Formatea una fecha para mostrar
 */
function format_date(?string $date, string $format = DATE_FORMAT_DISPLAY): string
{
    if ($date === null || $date === '') {
        return '-';
    }
    
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Formatea una fecha y hora para mostrar
 */
function format_datetime(?string $datetime): string
{
    return format_date($datetime, DATETIME_FORMAT_DISPLAY);
}

/**
 * Formatea un tamano de archivo en bytes a formato legible
 */
function format_file_size(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Formatea un monto monetario
 */
function format_money(float $amount, string $currency = 'MXN'): string
{
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Calcula la edad desde una fecha de nacimiento
 */
function calculate_age(string $birthDate): int
{
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    return $today->diff($birth)->y;
}

// ============================================================================
// FUNCIONES DE ARCHIVOS
// ============================================================================

/**
 * Verifica si un tipo de archivo esta permitido
 */
function is_allowed_file_type(string $filename): bool
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_FILE_TYPES, true);
}

/**
 * Genera un nombre unico para un archivo
 */
function unique_filename(string $originalName): string
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('file_', true) . '.' . $extension;
}

// ============================================================================
// FUNCIONES DE DEBUG (solo en entorno de desarrollo)
// ============================================================================

/**
 * Imprime una variable de forma legible (solo debug)
 */
function dd(mixed $var): never
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}

/**
 * Imprime una variable sin detener la ejecucion (solo debug)
 */
function dump(mixed $var): void
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

/**
 * Registra un mensaje en el log
 */
function log_message(string $message, string $level = 'info'): void
{
    $logFile = LOG_PATH . '/app-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($logFile, $entry, FILE_APPEND);
}