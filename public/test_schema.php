<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/app/Config/app.php';
require_once $basePath . '/app/Helpers/constants.php';
require_once $basePath . '/app/Core/Database.php';

$db = Database::getInstance();

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Esquema Real</title></head><body>';
echo '<h1>Esquema real de la base de datos SALUVERA</h1>';

// ============================================================================
// TODAS LAS TABLAS
// ============================================================================
echo '<h2>1. Todas las tablas</h2><pre style="background:#f0f0f0;padding:16px">';
$tables = $db->fetchAll("SHOW TABLES");
$tableNames = [];
foreach ($tables as $row) {
    $name = array_values($row)[0];
    $tableNames[] = $name;
    echo $name . "\n";
}
echo '</pre>';

// ============================================================================
// COLUMNAS DE TABLAS CLAVE
// ============================================================================
$important = ['usuarios', 'roles', 'permisos'];

// Agregar cualquier tabla que contenga "permiso" o "rol" en el nombre
foreach ($tableNames as $name) {
    if (stripos($name, 'permiso') !== false || stripos($name, 'rol') !== false) {
        if (!in_array($name, $important, true)) {
            $important[] = $name;
        }
    }
}

foreach ($important as $table) {
    echo '<h2>COLUMNAS DE: ' . htmlspecialchars($table) . '</h2>';
    echo '<pre style="background:#f0f0f0;padding:16px">';
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
        foreach ($cols as $c) {
            echo str_pad($c['Field'], 30) . ' | ' . str_pad($c['Type'], 30) . ' | Null: ' . $c['Null'] . ' | Key: ' . $c['Key'] . "\n";
        }
    } catch (Exception $e) {
        echo 'ERROR: ' . htmlspecialchars($e->getMessage());
    }
    echo '</pre>';
}

echo '<p><strong>Copia TODO este resultado y pegamelo.</strong></p>';
echo '</body></html>';