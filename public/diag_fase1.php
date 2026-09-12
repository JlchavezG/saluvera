<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('SALUVERA_APP', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/app.php';
require __DIR__ . '/../app/Core/Database.php';

$db = Database::getInstance();

echo "<pre style='font-family:monospace;font-size:12px;line-height:1.6'>";

// 1. Especialidades: estructura y datos
echo "=== 1. TABLA ESPECIALIDADES ===\n";
try {
    $cols = $db->fetchAll("DESCRIBE especialidades");
    foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";
    echo "\n  DATOS:\n";
    $esp = $db->fetchAll("SELECT * FROM especialidades LIMIT 20");
    foreach ($esp as $e) echo "  " . json_encode($e, JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// 2. Usuarios: estructura (buscar ultimo_acceso)
echo "\n=== 2. TABLA USUARIOS ===\n";
try {
    $cols = $db->fetchAll("DESCRIBE usuarios");
    foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";
    $total = $db->fetchColumn("SELECT COUNT(*) FROM usuarios");
    echo "\n  Total usuarios: $total\n";
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// 3. Pacientes: estructura (buscar genero)
echo "\n=== 3. TABLA PACIENTES ===\n";
try {
    $cols = $db->fetchAll("DESCRIBE pacientes");
    foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";
    $gen = $db->fetchAll("SELECT genero, COUNT(*) as c FROM pacientes GROUP BY genero");
    echo "\n  GENEROS:\n";
    foreach ($gen as $g) echo "  - " . ($g['genero'] ?? 'NULL') . ": {$g['c']}\n";
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// 4. Profesionales: estructura
echo "\n=== 4. TABLA PROFESIONALES ===\n";
try {
    $cols = $db->fetchAll("DESCRIBE profesionales");
    foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// 5. Roles: datos
echo "\n=== 5. TABLA ROLES ===\n";
try {
    $roles = $db->fetchAll("SELECT * FROM roles");
    foreach ($roles as $r) echo "  " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// 6. Verificar tablas de auditoria/logs existentes
echo "\n=== 6. TABLAS EXISTENTES (para features) ===\n";
try {
    $tablas = $db->fetchAll("SHOW TABLES");
    foreach ($tablas as $t) {
        $nombre = array_values($t)[0];
        echo "  - $nombre\n";
    }
} catch (Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

echo "</pre>";
