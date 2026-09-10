<?php
/**
 * SALUVERA - Archivo de Rutas
 *
 * @version 2.6.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

// ============================================================================
// RUTA PRINCIPAL
// ============================================================================
$router->get('/', function ($request, $response) {
    try {
        $content = View::render('Pages.welcome');
        $html = View::render('Layouts.main', [
            'pageTitle' => 'Inicio',
            'content' => $content,
        ]);
        $response->html($html);
    } catch (Exception $e) {
        $response->html('<h1>Error al renderizar</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
    }
});

// ============================================================================
// AUTENTICACION
// ============================================================================
$router->group(['prefix' => '', 'middlewares' => ['GuestMiddleware']], function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
});

$router->get('/logout', [AuthController::class, 'logout']);

// ============================================================================
// PANEL
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [DashboardController::class, 'index']);
});

// ============================================================================
// MODULO PACIENTES
// ============================================================================
$router->group(['prefix' => '/panel/pacientes', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [PatientController::class, 'index']);
    $router->get('/nuevo', [PatientController::class, 'create']);
    $router->post('/', [PatientController::class, 'store']);
});

// ============================================================================
// OTROS MODULOS (placeholders temporales)
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $modulos = [
        '/agenda' => 'Agenda',
        '/profesionales' => 'Profesionales',
        '/configuracion' => 'Configuracion',
    ];

    foreach ($modulos as $path => $nombre) {
        $router->get($path, function ($request, $response) use ($nombre) {
            $content = View::render('Pages.placeholder', ['modulo' => $nombre]);
            $html = View::render('Layouts.panel', [
                'pageTitle' => $nombre,
                'content' => $content,
            ]);
            $response->html($html);
        });
    }
});

// ============================================================================
// APIS
// ============================================================================
$router->get('/api/status', function ($request, $response) {
    $response->json([
        'status' => 'ok',
        'message' => 'SALUVERA API funcionando',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => APP_VERSION ?? '1.0.0',
    ]);
});

$router->get('/api/db-test', function ($request, $response) {
    try {
        $db = Database::getInstance();
        $roles = $db->fetchAll("SELECT nombre, slug FROM roles ORDER BY id");
        $response->jsonSuccess([
            'roles' => $roles,
            'total' => count($roles),
        ], 'Base de datos accesible');
    } catch (Exception $e) {
        $response->jsonError('Error al conectar: ' . $e->getMessage(), 500);
    }
});

$router->get('/api/session-test', function ($request, $response) {
    Session::set('test_value', 'funciona');
    $value = Session::get('test_value');
    $response->jsonSuccess([
        'session_active' => Session::isStarted(),
        'test_value' => $value,
        'csrf_token' => Session::token(),
    ], 'Sesion funcionando');
});
