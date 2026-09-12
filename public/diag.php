<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

define('SALUVERA_APP', true);

echo "<pre style='font-size:13px'>";
echo "PHP: " . PHP_VERSION . "\n\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "[OK] vendor/autoload.php\n";

    require __DIR__ . '/../app/Config/app.php';
    echo "[OK] Config/app.php\n";

    require __DIR__ . '/../app/Core/Database.php';
    echo "[OK] Core/Database.php\n";

    require __DIR__ . '/../app/Core/Session.php';
    echo "[OK] Core/Session.php\n";

    require __DIR__ . '/../app/Core/Security.php';
    echo "[OK] Core/Security.php\n";

    require __DIR__ . '/../app/Core/Request.php';
    echo "[OK] Core/Request.php\n";

    require __DIR__ . '/../app/Core/Response.php';
    echo "[OK] Core/Response.php\n";

    require __DIR__ . '/../app/Core/View.php';
    echo "[OK] Core/View.php\n";

    require __DIR__ . '/../app/Core/Router.php';
    echo "[OK] Core/Router.php\n";

    require __DIR__ . '/../app/Modules/Reportes/Models/Reporte.php';
    echo "[OK] Reportes/Models/Reporte.php\n";

    require __DIR__ . '/../app/Modules/Reportes/Controllers/ReporteController.php';
    echo "[OK] Reportes/Controllers/ReporteController.php\n";

    echo "\n=== Probando instanciar ===\n";
    $ctrl = new ReporteController();
    echo "[OK] ReporteController instanciado\n";

    echo "\n=== Probando Reporte model ===\n";
    $r = new Reporte();
    echo "[OK] Reporte instanciado\n";
    $c = $r->consultasCompletadas(2, 0, '2026-09-01', '2026-09-30');
    echo "[OK] consultasCompletadas = $c\n";

    echo "\nTODO FUNCIONA - el problema esta en el ROUTER o la SESION web\n";

} catch (Throwable $e) {
    echo "\n!!! ERROR !!!\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
