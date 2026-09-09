<?php
/**
 * SALUVERA - Constantes Globales del Sistema
 *
 * Define constantes que se usan en toda la aplicacion.
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
// FUNCION env() - DEBE ESTAR AQUI PARA QUE COMPOSER LA CARGUE PRIMERO
// ============================================================================

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

// ============================================================================
// CONSTANTES DE LA APLICACION
// ============================================================================

/** Nombre de la aplicacion */
define('APP_NAME', env('APP_NAME', 'SALUVERA'));

/** Version actual */
define('APP_VERSION', env('APP_VERSION', '1.0.0'));

/** Entorno de ejecucion (local, staging, production) */
define('APP_ENV', env('APP_ENV', 'production'));

/** Modo debug activado */
define('APP_DEBUG', filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));

/** URL base de la aplicacion */
define('APP_URL', env('APP_URL', 'http://localhost/saluvera/public'));

/** Zona horaria */
define('APP_TIMEZONE', env('APP_TIMEZONE', 'America/Mexico_City'));

/** Idioma por defecto */
define('APP_LOCALE', env('APP_LOCALE', 'es_MX'));

// ============================================================================
// CONSTANTES DE RUTAS
// ============================================================================

/** Ruta raiz del proyecto */
define('BASE_PATH', dirname(__DIR__, 2));

/** Ruta de la carpeta app/ */
define('APP_PATH', dirname(__DIR__));

/** Ruta de la carpeta public/ */
define('PUBLIC_PATH', BASE_PATH . '/public');

/** Ruta de la carpeta storage/ */
define('STORAGE_PATH', BASE_PATH . '/' . env('STORAGE_PATH', 'storage'));

/** Ruta de uploads */
define('UPLOAD_PATH', BASE_PATH . '/' . env('UPLOAD_PATH', 'storage/uploads'));

/** Ruta de logs */
define('LOG_PATH', BASE_PATH . '/' . env('LOG_PATH', 'storage/logs'));

/** Ruta de cache */
define('CACHE_PATH', BASE_PATH . '/' . env('CACHE_PATH', 'storage/cache'));

/** Ruta de configuracion */
define('CONFIG_PATH', dirname(__DIR__) . '/Config');

/** Ruta de modulos */
define('MODULES_PATH', dirname(__DIR__) . '/Modules');

/** Ruta de vistas compartidas */
define('VIEWS_PATH', dirname(__DIR__) . '/Shared');

// ============================================================================
// CONSTANTES DE SEGURIDAD
// ============================================================================

/** Nombre del token CSRF */
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'saluvera_csrf'));

/** Algoritmo de hash para contrasenas */
define('HASH_ALGORITHM', env('HASH_ALGORITHM', 'bcrypt'));

/** Costo del hash bcrypt (10-12 recomendado) */
define('HASH_COST', (int) env('HASH_COST', 12));

/** Duracion de sesion en minutos */
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 120));

/** Nombre de la cookie de sesion */
define('SESSION_NAME', env('SESSION_NAME', 'saluvera_session'));

// ============================================================================
// CONSTANTES DE SUBIDA DE ARCHIVOS
// ============================================================================

/** Tamano maximo de subida en bytes (10 MB por defecto) */
define('MAX_UPLOAD_SIZE', (int) env('MAX_UPLOAD_SIZE', 10485760));

/** Tipos de archivo permitidos */
define('ALLOWED_FILE_TYPES', explode(',', env('ALLOWED_FILE_TYPES', 'pdf,jpg,jpeg,png,webp,doc,docx')));

/** Extensiones de imagen permitidas */
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/** Extensiones de documento permitidas */
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);

/** Tamano maximo de avatar en bytes (2 MB) */
define('MAX_AVATAR_SIZE', 2097152);

/** Tamano maximo de firma en bytes (1 MB) */
define('MAX_SIGNATURE_SIZE', 1048576);

// ============================================================================
// CONSTANTES DE PAGINACION
// ============================================================================

/** Items por pagina por defecto */
define('DEFAULT_PAGE_SIZE', 15);

/** Items maximos por pagina */
define('MAX_PAGE_SIZE', 100);

/** Items minimos por pagina */
define('MIN_PAGE_SIZE', 5);

// ============================================================================
// CONSTANTES DE ESTADOS - CITAS
// ============================================================================

/** Estados posibles de una cita */
define('APPOINTMENT_STATUS', [
    'PENDING' => 'pendiente',
    'CONFIRMED' => 'confirmada',
    'IN_PROGRESS' => 'en_progreso',
    'COMPLETED' => 'completada',
    'CANCELLED' => 'cancelada',
    'NO_SHOW' => 'no_asistio',
    'RESCHEDULED' => 'reprogramada',
]);

/** Estados activos (que ocupan espacio en agenda) */
define('APPOINTMENT_ACTIVE_STATUSES', [
    'pendiente',
    'confirmada',
    'en_progreso',
]);

// ============================================================================
// CONSTANTES DE ESTADOS - CONSULTAS
// ============================================================================

/** Estados posibles de una consulta */
define('CONSULTATION_STATUS', [
    'DRAFT' => 'borrador',
    'COMPLETED' => 'completada',
    'SIGNED' => 'firmada',
]);

// ============================================================================
// CONSTANTES DE ESTADOS - DIAGNOSTICOS
// ============================================================================

/** Estados posibles de un diagnostico */
define('DIAGNOSIS_STATUS', [
    'ACTIVE' => 'activo',
    'RESOLVED' => 'resuelto',
    'MONITORING' => 'monitoreo',
]);

// ============================================================================
// CONSTANTES DE ESTADOS - TRATAMIENTOS
// ============================================================================

/** Estados posibles de un tratamiento */
define('TREATMENT_STATUS', [
    'ACTIVE' => 'activo',
    'COMPLETED' => 'completado',
    'SUSPENDED' => 'suspendido',
]);

// ============================================================================
// CONSTANTES DE ESTADOS - INFORMES
// ============================================================================

/** Estados posibles de un informe */
define('REPORT_STATUS', [
    'DRAFT' => 'borrador',
    'FINAL' => 'final',
    'DELIVERED' => 'entregado',
]);

// ============================================================================
// CONSTANTES DE TIPOS - DOCUMENTOS
// ============================================================================

/** Tipos de documentos permitidos */
define('DOCUMENT_TYPES', [
    'LAB_RESULT' => 'resultado_laboratorio',
    'IMAGING' => 'imagen',
    'REPORT' => 'informe',
    'EXTERNAL' => 'externo',
    'ADMINISTRATIVE' => 'administrativo',
    'OTHER' => 'otro',
]);

// ============================================================================
// CONSTANTES DE ROLES DEL SISTEMA
// ============================================================================

/** Roles disponibles */
define('SYSTEM_ROLES', [
    'SUPERADMIN' => 'superadmin',
    'CLINIC_ADMIN' => 'clinic_admin',
    'PROFESSIONAL' => 'professional',
    'RECEPTIONIST' => 'receptionist',
    'PATIENT' => 'patient',
]);

// ============================================================================
// CONSTANTES DE GENERO
// ============================================================================

/** Opciones de genero */
define('GENDER_OPTIONS', [
    'MALE' => 'masculino',
    'FEMALE' => 'femenino',
    'OTHER' => 'otro',
    'PREFER_NOT_TO_SAY' => 'prefiero_no_decir',
]);

// ============================================================================
// CONSTANTES DE ESTADO CIVIL
// ============================================================================

/** Opciones de estado civil */
define('MARITAL_STATUS_OPTIONS', [
    'SINGLE' => 'soltero',
    'MARRIED' => 'casado',
    'DIVORCED' => 'divorciado',
    'WIDOWED' => 'viudo',
    'UNION_FREE' => 'union_libre',
    'OTHER' => 'otro',
]);

// ============================================================================
// CONSTANTES DE TIPO DE SANGRE
// ============================================================================

/** Opciones de tipo de sangre */
define('BLOOD_TYPE_OPTIONS', [
    'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
]);

// ============================================================================
// CONSTANTES DE PLANES DE SUSCRIPCION
// ============================================================================

/** Planes disponibles */
define('SUBSCRIPTION_PLANS', [
    'FREE' => 'free',
    'PROFESSIONAL' => 'professional',
    'CLINIC' => 'clinic',
]);

/** Estado de suscripcion */
define('SUBSCRIPTION_STATUS', [
    'ACTIVE' => 'active',
    'TRIAL' => 'trial',
    'SUSPENDED' => 'suspended',
    'CANCELLED' => 'cancelled',
]);

// ============================================================================
// CONSTANTES DE AUDITORIA
// ============================================================================

/** Acciones comunes de auditoria */
define('AUDIT_ACTIONS', [
    'CREATE' => 'create',
    'UPDATE' => 'update',
    'DELETE' => 'delete',
    'LOGIN' => 'login',
    'LOGOUT' => 'logout',
    'VIEW' => 'view',
    'EXPORT' => 'export',
    'IMPORT' => 'import',
    'SIGN' => 'sign',
]);

// ============================================================================
// CONSTANTES DE MENSAJES
// ============================================================================

/** Mensajes de exito */
define('SUCCESS_MESSAGES', [
    'CREATED' => 'Registro creado exitosamente',
    'UPDATED' => 'Registro actualizado exitosamente',
    'DELETED' => 'Registro eliminado exitosamente',
    'SAVED' => 'Cambios guardados exitosamente',
    'SIGNED' => 'Documento firmado exitosamente',
]);

/** Mensajes de error */
define('ERROR_MESSAGES', [
    'NOT_FOUND' => 'Registro no encontrado',
    'UNAUTHORIZED' => 'No tienes permisos para realizar esta accion',
    'VALIDATION_FAILED' => 'Los datos enviados no son validos',
    'SERVER_ERROR' => 'Error interno del servidor',
    'CONFLICT' => 'Conflicto con otro registro',
]);

// ============================================================================
// CONSTANTES DE CONFIGURACION DE FECHAS
// ============================================================================

/** Formato de fecha para mostrar */
define('DATE_FORMAT_DISPLAY', 'd/m/Y');

/** Formato de fecha y hora para mostrar */
define('DATETIME_FORMAT_DISPLAY', 'd/m/Y H:i');

/** Formato de hora para mostrar */
define('TIME_FORMAT_DISPLAY', 'H:i');

/** Formato de fecha para MySQL */
define('DATE_FORMAT_MYSQL', 'Y-m-d');

/** Formato de fecha y hora para MySQL */
define('DATETIME_FORMAT_MYSQL', 'Y-m-d H:i:s');

// ============================================================================
// CONSTANTES DE CONFIGURACION DE NOTIFICACIONES
// ============================================================================

/** Canales de notificacion */
define('NOTIFICATION_CHANNELS', [
    'EMAIL' => 'correo',
    'SMS' => 'sms',
    'WHATSAPP' => 'whatsapp',
    'PUSH' => 'push',
]);

/** Tipos de notificacion */
define('NOTIFICATION_TYPES', [
    'APPOINTMENT_REMINDER' => 'recordatorio_cita',
    'APPOINTMENT_CONFIRMATION' => 'confirmacion_cita',
    'APPOINTMENT_CANCELLATION' => 'cancelacion_cita',
    'APPOINTMENT_RESCHEDULED' => 'cita_reprogramada',
    'NEW_DOCUMENT' => 'nuevo_documento',
    'SYSTEM' => 'sistema',
    'OTHER' => 'otro',
]);