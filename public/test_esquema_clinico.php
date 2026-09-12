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

// 1. Todas las tablas de la base
echo "========== TODAS LAS TABLAS ==========\n";
$tablas = $db->fetchAll("SHOW TABLES");
foreach ($tablas as $t) {
    echo "  - " . implode(' | ', $t) . "\n";
}

// 2. Tablas clinicas candidatas
$candidatas = [
    'expedientes_clinicos',
    'consultas',
    'diagnosticos',
    'cie10',
    'tratamientos',
    'recetas',
    'medicamentos',
    'signos_vitales',
    'adjuntos',
    'documentos',
    'registros_auditoria',
    'notificaciones',
];

foreach ($candidatas as $tabla) {
    echo "\n========== COLUMNAS DE: {$tabla} ==========\n";
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM {$tabla}");
        foreach ($cols as $c) {
            echo str_pad($c['Field'], 30) . ' | ' . str_pad($c['Type'], 30) . ' | Null: ' . str_pad($c['Null'], 3) . ' | Key: ' . $c['Key'] . "\n";
        }
    } catch (Exception $e) {
        echo "  (no existe)\n";
    }
}

echo '</pre>';
