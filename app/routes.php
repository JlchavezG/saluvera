<?php
/**
 * SALUVERA - Archivo de Rutas
 *
 * @version 2.9.0
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
// DASHBOARD ADMINISTRATIVO (solo superadmin y clinic_admin)
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/', [DashboardController::class, 'index']);
});

// ============================================================================
// MI PANEL (solo professional)
// ============================================================================
$router->group(['prefix' => '/panel/mi-panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [MiPanelController::class, 'index']);
});

// ============================================================================
// MODULO PACIENTES (con alcance por rol dentro del controller)
// ============================================================================
$router->group(['prefix' => '/panel/pacientes', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [PatientController::class, 'index']);
    $router->get('/nuevo', [PatientController::class, 'create']);
    $router->post('/', [PatientController::class, 'store']);
    $router->get('/{id}/editar', [PatientController::class, 'edit']);
    $router->post('/{id}/editar', [PatientController::class, 'update']);
    $router->post('/{id}/eliminar', [PatientController::class, 'delete']);
    $router->post('/{id}/reactivar', [PatientController::class, 'restore']);
});

// ============================================================================
// MODULO PROFESIONALES (solo admins)
// ============================================================================
$router->group(['prefix' => '/panel/profesionales', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/', [ProfessionalController::class, 'index']);
    $router->get('/nuevo', [ProfessionalController::class, 'create']);
    $router->post('/', [ProfessionalController::class, 'store']);
    $router->get('/{id}/editar', [ProfessionalController::class, 'edit']);
    $router->post('/{id}/editar', [ProfessionalController::class, 'update']);
    $router->post('/{id}/eliminar', [ProfessionalController::class, 'delete']);
    $router->post('/{id}/reactivar', [ProfessionalController::class, 'restore']);
});

// ============================================================================
// MODULO CONSULTORIOS (solo admins)
// ============================================================================
$router->group(['prefix' => '/panel/consultorios', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/', [ConsultorioController::class, 'index']);
    $router->get('/nuevo', [ConsultorioController::class, 'create']);
    $router->post('/', [ConsultorioController::class, 'store']);
    $router->get('/{id}/editar', [ConsultorioController::class, 'edit']);
    $router->post('/{id}/editar', [ConsultorioController::class, 'update']);
    $router->post('/{id}/eliminar', [ConsultorioController::class, 'delete']);
    $router->post('/{id}/reactivar', [ConsultorioController::class, 'restore']);
});
// ============================================================================
// MODULO ORGANIZACION (solo admins)
// ============================================================================
$router->group(['prefix' => '/panel/organizacion', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/', [OrganizacionController::class, 'show']);
    $router->get('/editar', [OrganizacionController::class, 'edit']);
    $router->post('/editar', [OrganizacionController::class, 'update']);
});
// ============================================================================
// GESTION DE ORGANIZACIONES (solo superadmin)
// ============================================================================
$router->group(['prefix' => '/panel/organizaciones', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/', [OrganizationAdminController::class, 'index']);
    $router->get('/nueva', [OrganizationAdminController::class, 'create']);
    $router->post('/', [OrganizationAdminController::class, 'store']);
    $router->get('/{id}/editar', [OrganizationAdminController::class, 'edit']);
    $router->post('/{id}/editar', [OrganizationAdminController::class, 'update']);
    $router->post('/{id}/suspender', [OrganizationAdminController::class, 'suspender']);
    $router->post('/{id}/activar', [OrganizationAdminController::class, 'activar']);
});
// ============================================================================
// OTROS MODULOS (placeholders)
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    });

$router->group(['prefix' => '/panel', 'middlewares' => ['AdminMiddleware']], function ($router) {
    $router->get('/configuracion', function ($request, $response) {
        $content = View::render('Pages.placeholder', ['modulo' => 'Configuracion']);
        $html = View::render('Layouts.panel', ['pageTitle' => 'Configuracion', 'content' => $content]);
        $response->html($html);
    });
});

// ============================================================================
// MODULO AGENDA (citas) con alcance por rol
// ============================================================================
$router->group(['prefix' => '/panel/agenda', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [AppointmentController::class, 'index']);
    $router->get('/nueva', [AppointmentController::class, 'create']);
    $router->post('/', [AppointmentController::class, 'store']);
    $router->post('/{id}/estado/{estado}', [AppointmentController::class, 'cambiarEstado']);
});
// ============================================================================
// MODULO EXPEDIENTE CLINICO (consultas, diagnosticos, tratamientos)
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    // Expediente del paciente (antecedentes + timeline)
    $router->get('/pacientes/{id}/expediente', [ExpedienteController::class, 'show']);
    $router->get('/pacientes/{id}/expediente/editar', [ExpedienteController::class, 'edit']);
    $router->post('/pacientes/{id}/expediente/editar', [ExpedienteController::class, 'update']);

    // Consultas medicas (nota SOAP)
    $router->get('/citas/{id}/consulta/nueva', [ConsultaController::class, 'createDesdeCita']);
    $router->get('/pacientes/{id}/consulta/nueva', [ConsultaController::class, 'create']);
    $router->post('/consultas', [ConsultaController::class, 'store']);
    $router->get('/consultas/{id}/editar', [ConsultaController::class, 'edit']);
    $router->post('/consultas/{id}/editar', [ConsultaController::class, 'update']);
    $router->post('/consultas/{id}/firmar', [ConsultaController::class, 'firmar']);

    // Diagnosticos y tratamientos de una consulta
    $router->post('/consultas/{id}/diagnosticos', [ConsultaController::class, 'storeDiagnostico']);
    $router->post('/consultas/{id}/tratamientos', [ConsultaController::class, 'storeTratamiento']);
});
// ============================================================================
// MODULO DOCUMENTOS DEL PACIENTE
// ============================================================================
$router->group(['prefix' => '/panel', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/pacientes/{id}/documentos/nuevo', [DocumentoController::class, 'create']);
    $router->post('/pacientes/{id}/documentos/nuevo', [DocumentoController::class, 'store']);
    $router->get('/documentos/{id}/ver', [DocumentoController::class, 'descargar']);
    $router->post('/documentos/{id}/eliminar', [DocumentoController::class, 'delete']);
});
// ============================================================================
// PORTAL DEL PACIENTE (acceso por token, sesion propia)
// ============================================================================
$router->get('/portal/acceso/{token}', [PortalController::class, 'acceso']);

$router->group(['prefix' => '/portal', 'middlewares' => ['PortalMiddleware']], function ($router) {
    $router->get('/inicio', [PortalController::class, 'inicio']);
    $router->get('/citas', [PortalController::class, 'citas']);
    $router->get('/expediente', [PortalController::class, 'expediente']);
    $router->get('/documentos', [PortalController::class, 'documentos']);
    $router->get('/documentos/{id}/ver', [PortalController::class, 'verDocumento']);
    $router->get('/salir', [PortalController::class, 'salir']);
});

// Generar enlace de acceso desde el panel
$router->group(['prefix' => '/panel/pacientes', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->post('/{id}/portal-acceso', [PortalController::class, 'generarAcceso']);
});
// ============================================================================
// MODULO BLOQUEOS DE AGENDA (dias no disponibles por profesional)
// ============================================================================
$router->group(['prefix' => '/panel/bloqueos', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [BloqueoController::class, 'index']);
    $router->get('/nuevo', [BloqueoController::class, 'create']);
    $router->post('/', [BloqueoController::class, 'store']);
    $router->get('/{id}/editar', [BloqueoController::class, 'edit']);
    $router->post('/{id}/editar', [BloqueoController::class, 'update']);
    $router->post('/{id}/eliminar', [BloqueoController::class, 'delete']);
});
// ============================================================================
// BALANCE DE GANANCIAS DEL PROFESIONAL
// ============================================================================
$router->group(['prefix' => '/panel/mis-ganancias', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [GananciaController::class, 'index']);
});
// ============================================================================
// MODULO HORARIOS DE ATENCION
// ============================================================================
$router->group(['prefix' => '/panel/mis-horarios', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [HorarioController::class, 'index']);
    $router->get('/nuevo', [HorarioController::class, 'create']);
    $router->post('/', [HorarioController::class, 'store']);
    $router->get('/{id}/editar', [HorarioController::class, 'edit']);
    $router->post('/{id}/editar', [HorarioController::class, 'update']);
    $router->post('/{id}/eliminar', [HorarioController::class, 'delete']);
});
// ============================================================================
// MI PERFIL (datos personales + profesionales + contrasena)
// ============================================================================
$router->group(['prefix' => '/panel/mi-perfil', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [MiPerfilController::class, 'edit']);
    $router->post('/', [MiPerfilController::class, 'update']);
});
// ============================================================================
// CENTRO DE NOTIFICACIONES (WhatsApp click-to-send + portal)
// ============================================================================
$router->group(['prefix' => '/panel/notificaciones', 'middlewares' => ['AuthMiddleware']], function ($router) {
    $router->get('/', [NotificacionController::class, 'index']);
    $router->get('/{id}/whatsapp', [NotificacionController::class, 'enviarWhatsApp']);
    $router->post('/{id}/enviada', [NotificacionController::class, 'marcarEnviada']);
    $router->post('/{id}/eliminar', [NotificacionController::class, 'delete']);
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
