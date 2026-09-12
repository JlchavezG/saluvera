<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

$base = dirname(__DIR__);
require_once $base . '/vendor/autoload.php';
require_once $base . '/app/Config/app.php';
require_once $base . '/app/Core/Database.php';

$db = Database::getInstance();

echo '<pre style="font-family:monospace;padding:20px">';

$tablas = [
    'horarios',
    'excepciones_horario',
    'especialidades',
    'profesional_especialidades',
    'profesionales',
    'usuarios',
];

foreach ($tablas as $tabla) {
    echo "========== COLUMNAS DE: {$tabla} ==========\n";
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM {$tabla}");
        foreach ($cols as $c) {
            echo str_pad($c['Field'], 28) . ' | ' . str_pad($c['Type'], 40) . ' | Null:' . str_pad($c['Null'], 4) . ' | Key:' . $c['Key'] . "\n";
        }
    } catch (Exception $e) {
        echo "  (no existe)\n";
    }
    echo "\n";
}

echo '</pre>';
