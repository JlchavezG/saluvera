<?php
/**
 * SALUVERA - Configuracion de la Aplicacion
 *
 * Este archivo devuelve un array con la configuracion general
 * de la aplicacion. Lee las variables del archivo .env
 *
 * @author SALUVERA
 * @version 1.0.0
 */

declare(strict_types=1);

// Definir la funcion env() si no existe
// Esta funcion sera movida a app/Helpers/functions.php en el Paso 1.8
if (!function_exists('env')) {
    /**
     * Lee una variable del archivo .env o del entorno del sistema
     *
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        // Intentar leer del entorno del sistema primero
        $value = getenv($key);

        // Si no existe en el entorno, intentar leer del archivo .env
        if ($value === false) {
            static $envFile = null;

            if ($envFile === null) {
                $envPath = dirname(__DIR__, 2) . '/.env';

                if (file_exists($envPath)) {
                    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $envFile = [];

                    foreach ($lines as $line) {
                        $line = trim($line);

                        // Ignorar comentarios
                        if ($line === '' || str_starts_with($line, '#')) {
                            continue;
                        }

                        // Parsear KEY=VALUE
                        if (str_contains($line, '=')) {
                            [$envKey, $envValue] = explode('=', $line, 2);
                            $envKey = trim($envKey);
                            $envValue = trim($envValue);

                            // Quitar comillas si existen
                            if (
                                (str_starts_with($envValue, '"') && str_ends_with($envValue, '"')) ||
                                (str_starts_with($envValue, "'") && str_ends_with($envValue, "'"))
                            ) {
                                $envValue = substr($envValue, 1, -1);
                            }

                            // Convertir tipos booleanos
                            $envValue = match (strtolower($envValue)) {
                                'true', '(true)' => true,
                                'false', '(false)' => false,
                                'null', '(null)' => null,
                                'empty', '(empty)' => '',
                                default => $envValue,
                            };

                            $envFile[$envKey] = $envValue;
                        }
                    }
                } else {
                    $envFile = [];
                }
            }

            $value = $envFile[$key] ?? $default;
        }

        return $value;
    }
}

// Devolver la configuracion de la aplicacion
return [
    // --------------------------------------------------------------------
    // INFORMACION GENERAL
    // --------------------------------------------------------------------
    'name' => env('APP_NAME', 'SALUVERA'),
    'version' => env('APP_VERSION', '1.0.0'),
    'environment' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => env('APP_URL', 'http://localhost/saluvera/public'),
    'timezone' => env('APP_TIMEZONE', 'America/Mexico_City'),
    'locale' => env('APP_LOCALE', 'es_MX'),

    // --------------------------------------------------------------------
    // RUTAS DEL SISTEMA
    // --------------------------------------------------------------------
    'paths' => [
        'root' => dirname(__DIR__, 2),
        'app' => dirname(__DIR__),
        'public' => dirname(__DIR__, 2) . '/public',
        'storage' => dirname(__DIR__, 2) . '/' . env('STORAGE_PATH', 'storage'),
        'uploads' => dirname(__DIR__, 2) . '/' . env('UPLOAD_PATH', 'storage/uploads'),
        'logs' => dirname(__DIR__, 2) . '/' . env('LOG_PATH', 'storage/logs'),
        'cache' => dirname(__DIR__, 2) . '/' . env('CACHE_PATH', 'storage/cache'),
        'config' => __DIR__,
    ],

    // --------------------------------------------------------------------
    // SESIONES
    // --------------------------------------------------------------------
    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => (int) env('SESSION_LIFETIME', 120),
        'name' => env('SESSION_NAME', 'saluvera_session'),
    ],

    // --------------------------------------------------------------------
    // SEGURIDAD
    // --------------------------------------------------------------------
    'security' => [
        'csrf_token_name' => env('CSRF_TOKEN_NAME', 'saluvera_csrf'),
        'hash_algorithm' => env('HASH_ALGORITHM', 'bcrypt'),
        'hash_cost' => (int) env('HASH_COST', 12),
    ],

    // --------------------------------------------------------------------
    // SUBIDA DE ARCHIVOS
    // --------------------------------------------------------------------
    'upload' => [
        'max_size' => (int) env('MAX_UPLOAD_SIZE', 10485760),
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'pdf,jpg,jpeg,png')),
    ],

    // --------------------------------------------------------------------
    // LOGGING
    // --------------------------------------------------------------------
    'logging' => [
        'level' => env('LOG_LEVEL', 'error'),
        'channel' => env('LOG_CHANNEL', 'file'),
    ],
];
