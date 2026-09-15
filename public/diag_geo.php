<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('SALUVERA_APP', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/app.php';
require __DIR__ . '/../app/Core/Database.php';

$db = Database::getInstance();

echo "<pre style='font-family:monospace;font-size:12px'>";
echo "=== ORGANIZACIONES: latitud/longitud ===\n";
$orgs = $db->fetchAll("SELECT id, nombre, latitud, longitud, activo FROM organizaciones");
foreach ($orgs as $o) {
    $lat = var_export($o['latitud'], true);
    $lng = var_export($o['longitud'], true);
    echo "  id={$o['id']} | {$o['nombre']} | lat=$lat | lng=$lng | activo={$o['activo']}\n";
}

echo "\n=== TIPO DE DATO DE LAS COLUMNAS ===\n";
$cols = $db->fetchAll("DESCRIBE organizaciones");
foreach ($cols as $c) {
    if (in_array($c['Field'], ['latitud', 'longitud'])) {
        echo "  {$c['Field']}: {$c['Type']} | NULL: {$c['Null']} | Default: " . var_export($c['Default'], true) . "\n";
    }
}

echo "\n=== CUANTAS CUMPLEN EL FILTRO ACTUAL (IS NOT NULL) ===\n";
$count = $db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE activo = 1 AND latitud IS NOT NULL AND longitud IS NOT NULL");
echo "  Con IS NOT NULL: $count\n";

$count2 = $db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE activo = 1 AND latitud IS NOT NULL AND longitud IS NOT NULL AND latitud != '' AND longitud != '' AND latitud != 0");
echo "  Filtrando vacios y 0: $count2\n";
echo "</pre>";
