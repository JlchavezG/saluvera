<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

$base = dirname(__DIR__);

require_once $base . '/vendor/autoload.php';
require_once $base . '/app/Config/app.php';
require_once $base . '/app/Helpers/constants.php';
require_once $base . '/app/Helpers/functions.php';
require_once $base . '/app/Core/Database.php';
require_once $base . '/app/Core/Request.php';
require_once $base . '/app/Core/Response.php';
require_once $base . '/app/Core/Router.php';
require_once $base . '/app/Core/Container.php';
require_once $base . '/app/Core/Session.php';
require_once $base . '/app/Core/View.php';
require_once $base . '/app/Core/Validator.php';
require_once $base . '/app/Core/Security.php';
require_once $base . '/app/Modules/Auth/Models/User.php';
require_once $base . '/app/Modules/Pacientes/Models/Paciente.php';
require_once $base . '/app/Modules/Pacientes/Controllers/PatientController.php';

Session::start();

echo '<pre style="font-family:monospace;padding:20px">';

function linea($ok, $texto) {
    $tag = $ok ? '[OK]     ' : '[FALLO]  ';
    echo $tag . $texto . "\n";
}

// ============================================================================
// 1. CONTROLADOR
// ============================================================================
echo "=== 1. CONTROLADOR ===\n";
linea(method_exists('PatientController', 'restore'), 'Metodo restore() existe en PatientController');
linea(method_exists('PatientController', 'delete'), 'Metodo delete() existe en PatientController');

// ============================================================================
// 2. RUTAS
// ============================================================================
echo "\n=== 2. RUTAS REGISTRADAS ===\n";

$routesSrc = file_get_contents($base . '/app/routes.php');
linea(strpos($routesSrc, 'reactivar') !== false, 'routes.php contiene la palabra "reactivar"');

$router = new Router();
require $base . '/app/routes.php';

$rutasReactivar = 0;
foreach ($router->getRoutes() as $r) {
    if (strpos($r['path'], 'reactivar') !== false || strpos($r['path'], 'eliminar') !== false) {
        $rutasReactivar++;
        $handler = is_array($r['handler']) ? implode('::', $r['handler']) : gettype($r['handler']);
        echo '  [' . $r['method'] . '] ' . $r['path'] . ' => ' . $handler . "\n";
    }
}
linea($rutasReactivar >= 2, 'Rutas eliminar y reactivar registradas (encontradas: ' . $rutasReactivar . ')');

// ============================================================================
// 3. VISTA DE LISTADO
// ============================================================================
echo "\n=== 3. VISTA DE LISTADO ===\n";

$pacienteInactivo = [
    'id' => 999,
    'nombre' => 'Prueba',
    'apellidos' => 'Inactivo',
    'correo' => null,
    'telefono' => null,
    'fecha_nacimiento' => null,
    'genero' => null,
    'tipo_sangre' => null,
    'creado_en' => date('Y-m-d H:i:s'),
    'activo' => 0,
];

$pacienteActivo = $pacienteInactivo;
$pacienteActivo['activo'] = 1;
$pacienteActivo['id'] = 998;

$htmlInactivo = View::render('Pages/pacientes/list', [
    'pacientes' => [$pacienteInactivo],
    'q' => '',
    'page' => 1,
    'totalPages' => 1,
    'total' => 1,
]);

$htmlActivo = View::render('Pages/pacientes/list', [
    'pacientes' => [$pacienteActivo],
    'q' => '',
    'page' => 1,
    'totalPages' => 1,
    'total' => 1,
]);

linea(strpos($htmlInactivo, 'reactivar') !== false, 'La vista con paciente INACTIVO incluye el formulario de reactivar');
linea(strpos($htmlInactivo, 'badge-inactive') !== false, 'La vista con paciente INACTIVO incluye el badge Inactivo');
linea(strpos($htmlActivo, 'eliminar') !== false, 'La vista con paciente ACTIVO incluye el formulario de eliminar');
linea(strpos($htmlInactivo, '/panel/pacientes/999/reactivar') !== false, 'La URL del form reactivar es correcta (/panel/pacientes/999/reactivar)');

// ============================================================================
// 4. MODELO
// ============================================================================
echo "\n=== 4. MODELO ===\n";
linea(method_exists('Paciente', 'setActive'), 'Metodo setActive() existe en Paciente');

echo "\n=== FIN DEL TEST ===\n";
echo '</pre>';
