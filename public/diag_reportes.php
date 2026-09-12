<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/app.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Core/Security.php';
require __DIR__ . '/../app/Core/Request.php';
require __DIR__ . '/../app/Core/Response.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Core/Router.php';

// Cargar manualmente todos los requires del controlador (como hace app.php)
$base = __DIR__ . '/../app/';
foreach (glob($base . 'Modules/*/Models/*.php') as $f) require_once $f;
foreach (glob($base . 'Modules/*/Controllers/*.php') as $f) require_once $f;
foreach (glob($base . 'Services/*.php') as $f) require_once $f;
foreach (glob($base . 'Middleware/*.php') as $f) require_once $f;

echo "<pre>";

// Mockear sesion como Jessica (professional, org 2)
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 2;
$_SESSION['user'] = [
    'id' => 2,
    'nombre' => 'Jessica',
    'apellidos' => 'Pinon',
    'correo' => 'jessica@jp.com',
    'rol_slug' => 'professional',
    'organizacion_id' => 2,
];

echo "Sesion mockeada como Jessica (professional)\n\n";

try {
    $ctrl = new ReporteController();
    echo "[OK] ReporteController creado\n";

    $request = new Request();
    $_GET['periodo'] = 'mes';
    $response = new Response();

    echo "[OK] Ejecutando index()...\n\n";
    $ctrl->index($request, $response);

    echo "\n[OK] index() termino sin excepcion\n";

} catch (Throwable $e) {
    echo "\n!!! ERROR CAPTURADO !!!\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n\n";
    echo "=== TRACE ===\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
