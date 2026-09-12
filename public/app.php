<?php
/**
 * SALUVERA - Punto de Entrada de la Aplicacion
 *
 * @version 2.2.0
 */

define('SALUVERA_APP', true);

error_reporting(E_ALL);

$appConfig = require __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Config/maps.php';

if ($appConfig['debug'] ?? false) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

register_shutdown_function(function () {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $isDebug = defined('APP_DEBUG') && APP_DEBUG;

        if (!$isDebug) {
            http_response_code(500);
            echo '<!DOCTYPE html><html><head><title>Error</title></head>';
            echo '<body><h1>Error interno del servidor</h1>';
            echo '<p>Ha ocurrido un error. Por favor intenta de nuevo.</p>';
            echo '</body></html>';
        } else {
            http_response_code(500);
            echo '<pre>';
            echo 'Error fatal: ' . htmlspecialchars($error['message']) . "\n";
            echo 'Archivo: ' . htmlspecialchars($error['file']) . "\n";
            echo 'Linea: ' . $error['line'];
            echo '</pre>';
        }
    }
});

set_exception_handler(function (Throwable $e) {
    $isDebug = defined('APP_DEBUG') && APP_DEBUG;

    if (!$isDebug) {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title></head>';
        echo '<body><h1>Error interno del servidor</h1>';
        echo '<p>Ha ocurrido un error. Por favor intenta de nuevo.</p>';
        echo '</body></html>';
    } else {
        http_response_code(500);
        echo '<pre>';
        echo 'Excepcion: ' . htmlspecialchars(get_class($e)) . "\n";
        echo 'Mensaje: ' . htmlspecialchars($e->getMessage()) . "\n";
        echo 'Archivo: ' . htmlspecialchars($e->getFile()) . "\n";
        echo 'Linea: ' . $e->getLine() . "\n\n";
        echo "Stack trace:\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    }

    $logFile = __DIR__ . '/../storage/logs/error-' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] ERROR: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
});

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/Helpers/constants.php';

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $appConfig['debug'] ?? false);
}

if (!defined('APP_KEY')) {
    $appKey = env('APP_KEY', 'saluvera_default_key_2024');
    define('APP_KEY', $appKey);
}

// ============================================================================
// CLASES DEL CORE
// ============================================================================
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Container.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/View.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Core/Security.php';

// ============================================================================
// MODULOS Y MIDDLEWARES
// ============================================================================
require_once __DIR__ . '/../app/Modules/Auth/Models/User.php';
require_once __DIR__ . '/../app/Modules/Auth/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Middleware/GuestMiddleware.php';
require_once __DIR__ . '/../app/Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../app/Modules/Panel/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Modules/Pacientes/Models/Paciente.php';
require_once __DIR__ . '/../app/Modules/Pacientes/Controllers/PatientController.php';
require_once __DIR__ . '/../app/Modules/Profesionales/Models/Profesional.php';
require_once __DIR__ . '/../app/Modules/Profesionales/Controllers/ProfessionalController.php';
require_once __DIR__ . '/../app/Modules/Agenda/Models/Cita.php';
require_once __DIR__ . '/../app/Modules/Agenda/Controllers/AppointmentController.php';
require_once __DIR__ . '/../app/Modules/Expediente/Models/Expediente.php';
require_once __DIR__ . '/../app/Modules/Expediente/Models/Consulta.php';
require_once __DIR__ . '/../app/Modules/Expediente/Controllers/ExpedienteController.php';
require_once __DIR__ . '/../app/Modules/Expediente/Controllers/ConsultaController.php';
require_once __DIR__ . '/../app/Modules/Documentos/Models/Documento.php';
require_once __DIR__ . '/../app/Modules/Documentos/Controllers/DocumentoController.php';
require_once __DIR__ . '/../app/Middleware/PortalMiddleware.php';
require_once __DIR__ . '/../app/Modules/Portal/Controllers/PortalController.php';
require_once __DIR__ . '/../app/Modules/Bloqueos/Models/BloqueoAgenda.php';
require_once __DIR__ . '/../app/Modules/Bloqueos/Controllers/BloqueoController.php';
require_once __DIR__ . '/../app/Modules/Panel/Controllers/GananciaController.php';
require_once __DIR__ . '/../app/Modules/Horarios/Models/Horario.php';
require_once __DIR__ . '/../app/Modules/Horarios/Controllers/HorarioController.php';
require_once __DIR__ . '/../app/Modules/Panel/Controllers/MiPerfilController.php';
require_once __DIR__ . '/../app/Modules/Panel/Controllers/MiPanelController.php';
require_once __DIR__ . '/../app/Modules/Consultorios/Models/Consultorio.php';
require_once __DIR__ . '/../app/Modules/Consultorios/Controllers/ConsultorioController.php';
require_once __DIR__ . '/../app/Modules/Organizaciones/Models/Organizacion.php';
require_once __DIR__ . '/../app/Modules/Organizaciones/Controllers/OrganizacionController.php';
require_once __DIR__ . '/../app/Modules/Organizaciones/Controllers/OrganizationAdminController.php';

// ============================================================================
// ZONA HORARIA Y SESION
// ============================================================================
date_default_timezone_set(APP_TIMEZONE ?? 'America/Mexico_City');

Session::start();

// ============================================================================
// CONTENEDOR DE DEPENDENCIAS
// ============================================================================
$container = Container::getInstance();

$container->singleton('request', function () {
    return new Request();
});

$container->singleton('response', function () {
    return new Response();
});

$container->singleton('database', function () {
    return Database::getInstance();
});

// ============================================================================
// ROUTER Y RUTAS
// ============================================================================
$router = new Router();

$routesFile = __DIR__ . '/../app/routes.php';

if (file_exists($routesFile)) {
    require_once $routesFile;
} else {
    $router->get('/', function ($request, $response) {
        $response->html('<h1>SALUVERA</h1><p>El sistema esta funcionando.</p>');
    });
}

// ============================================================================
// DESPACHAR
// ============================================================================
require_once __DIR__ . '/../app/Modules/Notificaciones/Models/Notificacion.php';
require_once __DIR__ . '/../app/Modules/Notificaciones/Controllers/NotificacionController.php';
$router->dispatch();
