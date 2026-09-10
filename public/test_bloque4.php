<?php
/**
 * SALUVERA - Test Bloque 4 (Autenticacion)
 * ELIMINAR ESTE ARCHIVO DESPUES DE PROBAR
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

$basePath = dirname(__DIR__);

// Cargar nucleo
require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/app/Config/app.php';
require_once $basePath . '/app/Helpers/constants.php';
require_once $basePath . '/app/Helpers/functions.php';
require_once $basePath . '/app/Core/Database.php';
require_once $basePath . '/app/Core/Request.php';
require_once $basePath . '/app/Core/Response.php';
require_once $basePath . '/app/Core/Router.php';
require_once $basePath . '/app/Core/Container.php';
require_once $basePath . '/app/Core/Session.php';
require_once $basePath . '/app/Core/View.php';
require_once $basePath . '/app/Core/Validator.php';
require_once $basePath . '/app/Core/Security.php';

// Iniciar sesion ANTES de cualquier output (evita warnings)
Session::start();

// Cargar modulos de autenticacion
$authLoadErrors = [];

$moduleFiles = [
    'app/Modules/Auth/Models/User.php',
    'app/Modules/Auth/Controllers/AuthController.php',
    'app/Middleware/AuthMiddleware.php',
    'app/Middleware/GuestMiddleware.php',
    'app/Middleware/RoleMiddleware.php',
];

foreach ($moduleFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        try {
            require_once $fullPath;
        } catch (Throwable $e) {
            $authLoadErrors[$file] = $e->getMessage();
        }
    } else {
        $authLoadErrors[$file] = 'ARCHIVO NO EXISTE';
    }
}

// Iniciar HTML
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
echo '<title>SALUVERA - Test Bloque 4</title>';
echo '<style>
    body { font-family: Inter, -apple-system, Arial, sans-serif; background: #F7F8F6; padding: 40px; max-width: 900px; margin: 0 auto; color: #17252A; }
    h1 { color: #123C46; }
    h2 { color: #1F5964; margin-top: 32px; border-bottom: 2px solid #4FD1B5; padding-bottom: 8px; }
    .test { background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #E9EEEC; margin: 12px 0; }
    .pass { border-left: 4px solid #36A269; }
    .fail { border-left: 4px solid #D65A5A; }
    .status { font-weight: bold; margin-right: 8px; }
    .pass .status { color: #36A269; }
    .fail .status { color: #D65A5A; }
    .result { background: #EEF9F6; padding: 12px; border-radius: 6px; margin-top: 8px; font-family: monospace; font-size: 13px; }
    .summary { padding: 20px; border-radius: 8px; margin-top: 24px; color: #fff; }
    .summary.success { background: #36A269; }
    .summary.error { background: #D65A5A; }
    .section-badge { display: inline-block; background: #DDF5EF; color: #123C46; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; margin-left: 8px; }
</style></head><body>';

echo '<h1>SALUVERA - Test Bloque 4 (Autenticacion)</h1>';
echo '<p>Verificacion de User, AuthController y Middlewares</p>';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest(string $name, callable $test): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;

    try {
        $result = $test();
        if ($result === true || $result === null) {
            echo '<div class="test pass"><span class="status">[OK]</span>' . htmlspecialchars($name) . '</div>';
            $passedTests++;
        } else {
            echo '<div class="test fail"><span class="status">[FALLO]</span>' . htmlspecialchars($name) . '<div class="result">' . htmlspecialchars(is_string($result) ? $result : var_export($result, true)) . '</div></div>';
            $failedTests++;
        }
    } catch (Throwable $e) {
        echo '<div class="test fail"><span class="status">[FALLO]</span>' . htmlspecialchars($name) . '<div class="result">' . htmlspecialchars($e->getMessage()) . ' (Linea ' . $e->getLine() . ')</div></div>';
        $failedTests++;
    }
}

// ============================================================================
// TEST 1: ARCHIVOS EN RUTAS CORRECTAS
// ============================================================================
echo '<h2>1. Archivos en rutas correctas <span class="section-badge">Estructura</span></h2>';

foreach ($moduleFiles as $file) {
    runTest('Archivo existe: ' . $file, function() use ($basePath, $file) {
        return file_exists($basePath . '/' . $file) ? true : 'No encontrado en ' . $basePath . '/' . $file;
    });
}

runTest('Sin errores de carga/sintaxis', function() use ($authLoadErrors) {
    if (empty($authLoadErrors)) {
        return true;
    }
    return implode('; ', $authLoadErrors);
});

// ============================================================================
// TEST 2: MODELO USER
// ============================================================================
echo '<h2>2. User.php <span class="section-badge">Modelo</span></h2>';

runTest('Clase User existe', function() {
    return class_exists('User');
});

runTest('Instancia de User se crea', function() {
    $user = new User();
    return $user instanceof User;
});

runTest('Tabla usuarios existe', function() {
    $db = Database::getInstance();
    return $db->tableExists('usuarios') ? true : 'Tabla usuarios no encontrada';
});

runTest('findByEmail con email inexistente devuelve null', function() {
    $user = new User();
    $result = $user->findByEmail('noexiste@saluvera.com');
    return $result === null ? true : 'Devolvio un resultado inesperado';
});

runTest('existsByEmail devuelve false para email inexistente', function() {
    $user = new User();
    return $user->existsByEmail('noexiste@saluvera.com') === false;
});

runTest('getPermissions(1) devuelve permisos del superadmin', function() {
    $user = new User();
    $permissions = $user->getPermissions(1);
    return is_array($permissions) && count($permissions) > 0
        ? true
        : 'Permisos vacios: ' . count($permissions);
});

runTest('attemptLogin con credenciales invalidas falla correctamente', function() {
    $user = new User();
    $result = $user->attemptLogin('noexiste@saluvera.com', 'Password123');
    return ($result['success'] === false && isset($result['error']))
        ? true
        : 'Respuesta inesperada';
});

runTest('countActive devuelve un entero', function() {
    $user = new User();
    return is_int($user->countActive());
});

// ============================================================================
// TEST 3: AUTHCONTROLLER
// ============================================================================
echo '<h2>3. AuthController.php <span class="section-badge">Controlador</span></h2>';

runTest('Clase AuthController existe', function() {
    return class_exists('AuthController');
});

runTest('Instancia de AuthController se crea', function() {
    $controller = new AuthController();
    return $controller instanceof AuthController;
});

runTest('Metodo showLogin existe', function() {
    return method_exists('AuthController', 'showLogin');
});

runTest('Metodo login existe', function() {
    return method_exists('AuthController', 'login');
});

runTest('Metodo logout existe', function() {
    return method_exists('AuthController', 'logout');
});

runTest('Metodo check existe', function() {
    return method_exists('AuthController', 'check');
});

// ============================================================================
// TEST 4: MIDDLEWARES
// ============================================================================
echo '<h2>4. Middlewares <span class="section-badge">Proteccion de rutas</span></h2>';

runTest('Clase AuthMiddleware existe', function() {
    return class_exists('AuthMiddleware');
});

runTest('AuthMiddleware tiene metodo handle', function() {
    return method_exists('AuthMiddleware', 'handle');
});

runTest('Clase GuestMiddleware existe', function() {
    return class_exists('GuestMiddleware');
});

runTest('GuestMiddleware permite acceso a invitados (sin sesion)', function() {
    // Solo valido si NO hay sesion autenticada en este test
    if (Session::isAuthenticated()) {
        return 'Hay sesion activa, no se puede probar como invitado';
    }
    $middleware = new GuestMiddleware();
    $request = new Request();
    $response = new Response();
    return $middleware->handle($request, $response) === true;
});

runTest('Clase RoleMiddleware existe', function() {
    return class_exists('RoleMiddleware');
});

runTest('RoleMiddleware acepta roles en constructor', function() {
    $middleware = new RoleMiddleware(['superadmin', 'clinic_admin']);
    return $middleware instanceof RoleMiddleware;
});

runTest('RoleMiddleware tiene metodo handle', function() {
    return method_exists('RoleMiddleware', 'handle');
});

// ============================================================================
// TEST 5: INTEGRACION SECURITY + USER
// ============================================================================
echo '<h2>5. Integracion Security <span class="section-badge">Hashing</span></h2>';

runTest('Hash de contrasena redondo (hash + verify)', function() {
    $password = 'PruebaSegura123';
    $hash = Security::hashPassword($password);
    return Security::verifyPassword($password, $hash);
});

runTest('Contrasena incorrecta NO verifica', function() {
    $hash = Security::hashPassword('Correcta123');
    return Security::verifyPassword('Incorrecta123', $hash) === false;
});

runTest('Hash nuevo NO necesita rehash', function() {
    $hash = Security::hashPassword('PruebaSegura123');
    return Security::needsRehash($hash) === false;
});

// ============================================================================
// RESUMEN
// ============================================================================
echo '<div class="summary ' . ($failedTests === 0 ? 'success' : 'error') . '">';
echo '<h2 style="margin:0">Resultado: ' . $passedTests . '/' . $totalTests . ' tests pasados</h2>';
echo '<p style="margin:8px 0 0">';
if ($failedTests === 0) {
    echo 'Bloque 4 (parte 1) verificado. Puedes continuar al Paso 4.4 (vista de login).';
} else {
    echo 'Hay ' . $failedTests . ' tests que fallaron. Revisa los errores arriba antes de continuar.';
}
echo '</p>';
echo '</div>';

echo '</body></html>';