<?php
/**
 * SALUVERA - Test de Estructura de Carpetas
 * 
 * Este archivo verifica que todas las carpetas del proyecto estén creadas correctamente.
 * 
 * USO:
 * 1. Colocar este archivo en la carpeta public/
 * 2. Acceder desde el navegador: http://localhost/saluvera/public/test_structure.php
 * 3. Revisar el resultado en pantalla
 * 4. Eliminar este archivo después de verificar (no debe estar en producción)
 * 
 * @author SALUVERA
 * @version 1.0.0
 */

declare(strict_types=1);

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Definir la ruta raíz del proyecto (un nivel arriba de public/)
$projectRoot = dirname(__DIR__);

// Lista completa de carpetas que debe tener el proyecto
$requiredFolders = [
    // Carpeta public
    'public/assets/css',
    'public/assets/js',
    'public/assets/img',
    
    // Carpeta app/Core
    'app/Core',
    
    // Carpeta app/Config
    'app/Config',
    
    // Carpeta app/Middleware
    'app/Middleware',
    
    // Carpeta app/Helpers
    'app/Helpers',
    
    // Módulos - Auth
    'app/Modules/Auth/Controllers',
    'app/Modules/Auth/Services',
    'app/Modules/Auth/Repositories',
    'app/Modules/Auth/Models',
    'app/Modules/Auth/views',
    
    // Módulos - Dashboard
    'app/Modules/Dashboard/Controllers',
    'app/Modules/Dashboard/Services',
    'app/Modules/Dashboard/Repositories',
    'app/Modules/Dashboard/views',
    
    // Módulos - Patients
    'app/Modules/Patients/Controllers',
    'app/Modules/Patients/Services',
    'app/Modules/Patients/Repositories',
    'app/Modules/Patients/Models',
    'app/Modules/Patients/views',
    
    // Módulos - Appointments
    'app/Modules/Appointments/Controllers',
    'app/Modules/Appointments/Services',
    'app/Modules/Appointments/Repositories',
    'app/Modules/Appointments/Models',
    'app/Modules/Appointments/views',
    
    // Módulos - MedicalRecords
    'app/Modules/MedicalRecords/Controllers',
    'app/Modules/MedicalRecords/Services',
    'app/Modules/MedicalRecords/Repositories',
    'app/Modules/MedicalRecords/Models',
    'app/Modules/MedicalRecords/views',
    
    // Módulos - Settings
    'app/Modules/Settings/Controllers',
    'app/Modules/Settings/Services',
    'app/Modules/Settings/Repositories',
    'app/Modules/Settings/views',
    
    // Módulos - Audit
    'app/Modules/Audit/Controllers',
    'app/Modules/Audit/Repositories',
    'app/Modules/Audit/views',
    
    // Shared
    'app/Shared/Traits',
    'app/Shared/Exceptions',
    'app/Shared/Interfaces',
    'app/Shared/views/layouts',
    'app/Shared/views/components',
    
    // Database
    'database/migrations',
    'database/seeds',
    
    // Storage
    'storage/logs',
    'storage/uploads/documents',
    'storage/uploads/photos',
    'storage/cache',
];

// Contadores
$totalFolders = count($requiredFolders);
$existingFolders = 0;
$missingFolders = 0;
$missingList = [];

// Verificar cada carpeta
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SALUVERA - Test de Estructura</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        .header p {
            margin: 0;
            font-size: 1.2em;
            opacity: 0.9;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .summary h2 {
            margin-top: 0;
            color: #333;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .stat {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat.total {
            background: #e3f2fd;
            color: #1976d2;
        }
        .stat.success {
            background: #e8f5e9;
            color: #388e3c;
        }
        .stat.error {
            background: #ffebee;
            color: #d32f2f;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            display: block;
        }
        .stat-label {
            font-size: 0.9em;
            margin-top: 5px;
        }
        .results {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .results h2 {
            margin-top: 0;
            color: #333;
        }
        .folder-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .folder-item {
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            display: flex;
            align-items: center;
        }
        .folder-item.exists {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        .folder-item.missing {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }
        .icon {
            font-size: 1.3em;
            margin-right: 10px;
            min-width: 25px;
        }
        .path {
            flex: 1;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🏥 SALUVERA</h1>
        <p>Tecnología para cuidar lo que importa.</p>
        <p style='margin-top: 15px; font-size: 1em;'>Test de Estructura de Carpetas</p>
    </div>
";

echo "<div class='summary'>
        <h2>📊 Resumen</h2>
        <div class='stats'>";

// Verificar cada carpeta
foreach ($requiredFolders as $folder) {
    $fullPath = $projectRoot . '/' . $folder;
    
    if (is_dir($fullPath)) {
        $existingFolders++;
    } else {
        $missingFolders++;
        $missingList[] = $folder;
    }
}

// Mostrar estadísticas
echo "<div class='stat total'>
        <span class='stat-number'>{$totalFolders}</span>
        <span class='stat-label'>Total de carpetas requeridas</span>
    </div>
    <div class='stat success'>
        <span class='stat-number'>{$existingFolders}</span>
        <span class='stat-label'>Carpetas existentes</span>
    </div>
    <div class='stat error'>
        <span class='stat-number'>{$missingFolders}</span>
        <span class='stat-label'>Carpetas faltantes</span>
    </div>";

echo "</div></div>";

// Mostrar lista de carpetas
echo "<div class='results'>
        <h2>📁 Detalle de Carpetas</h2>
        <ul class='folder-list'>";

foreach ($requiredFolders as $folder) {
    $fullPath = $projectRoot . '/' . $folder;
    $exists = is_dir($fullPath);
    $class = $exists ? 'exists' : 'missing';
    $icon = $exists ? '✅' : '❌';
    
    echo "<li class='folder-item {$class}'>
            <span class='icon'>{$icon}</span>
            <span class='path'>{$folder}</span>
          </li>";
}

echo "</ul>";

// Mostrar mensaje final
if ($missingFolders === 0) {
    echo "<div class='alert alert-success'>
            <strong>✅ ¡Perfecto!</strong> Todas las carpetas están creadas correctamente.
            <br><br>
            <strong>Siguiente paso:</strong> Puedes eliminar este archivo y continuar con el Bloque 1.
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <strong>⚠️ Atención:</strong> Faltan {$missingFolders} carpetas por crear.
            <br><br>
            <strong>Carpetas faltantes:</strong>
            <ul style='margin-top: 10px;'>";
    
    foreach ($missingList as $missing) {
        echo "<li><code>{$missing}</code></li>";
    }
    
    echo "</ul>
            <br>
            <strong>Acción requerida:</strong> Crea las carpetas faltantes antes de continuar.
          </div>";
}

echo "<div class='alert alert-info'>
        <strong>ℹ️ Información:</strong>
        <ul style='margin-top: 10px;'>
            <li><strong>Ruta raíz del proyecto:</strong> <code>{$projectRoot}</code></li>
            <li><strong>Fecha de verificación:</strong> " . date('d/m/Y H:i:s') . "</li>
            <li><strong>Versión PHP:</strong> " . phpversion() . "</li>
        </ul>
        <br>
        <strong>⚠️ Importante:</strong> Elimina este archivo después de verificar la estructura. No debe estar en producción.
      </div>";

echo "</div></body></html>";