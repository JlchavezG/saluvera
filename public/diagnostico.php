<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SALUVERA_APP', true);

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
echo '<title>SALUVERA - Diagnostico</title>';
echo '<style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
    .section { background: #fff; padding: 20px; border-radius: 8px; margin: 16px 0; }
    .ok { color: #36A269; }
    .fail { color: #D65A5A; }
    .warn { color: #D99A32; }
    .path { font-family: monospace; background: #f0f0f0; padding: 2px 6px; border-radius: 4px; }
    h1 { color: #123C46; }
    h2 { color: #1F5964; border-bottom: 2px solid #4FD1B5; padding-bottom: 8px; }
</style></head><body>';

echo '<h1>SALUVERA - Diagnostico Completo</h1>';

// ============================================================================
// 1. ESTRUCTURA DE CARPETAS
// ============================================================================
echo '<div class="section">';
echo '<h2>1. Estructura de Carpetas</h2>';

$basePath = dirname(__DIR__);
$carpetas = [
    'app',
    'app/Core',
    'app/Config',
    'app/Helpers',
    'app/Shared',
    'app/Shared/Layouts',
    'app/Shared/Pages',
    'app/Shared/Partials',
    'app/Shared/Components',
    'public',
    'public/app-assets',
    'public/app-assets/css',
    'public/app-assets/js',
    'public/app-assets/img',
    'storage',
    'storage/logs',
    'storage/uploads',
];

foreach ($carpetas as $carpeta) {
    $fullPath = $basePath . '/' . $carpeta;
    if (is_dir($fullPath)) {
        echo '<p class="ok">[OK] <span class="path">' . $carpeta . '/</span></p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $carpeta . '/</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 2. ARCHIVOS DEL CORE
// ============================================================================
echo '<div class="section">';
echo '<h2>2. Archivos del Core</h2>';

$coreFiles = [
    'app/Core/Database.php',
    'app/Core/Request.php',
    'app/Core/Response.php',
    'app/Core/Router.php',
    'app/Core/Container.php',
    'app/Core/Session.php',
    'app/Core/View.php',
    'app/Core/Validator.php',
    'app/Core/Security.php',
];

foreach ($coreFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        echo '<p class="ok">[OK] <span class="path">' . $file . '</span></p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $file . '</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 3. ARCHIVOS DE CONFIGURACION
// ============================================================================
echo '<div class="section">';
echo '<h2>3. Archivos de Configuracion</h2>';

$configFiles = [
    'app/Config/app.php',
    'app/Config/database.php',
    'app/Helpers/constants.php',
    'app/Helpers/functions.php',
    '.env',
    'app/routes.php',
];

foreach ($configFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        echo '<p class="ok">[OK] <span class="path">' . $file . '</span></p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $file . '</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 4. ARCHIVOS DE VISTAS
// ============================================================================
echo '<div class="section">';
echo '<h2>4. Archivos de Vistas</h2>';

$viewFiles = [
    'app/Shared/Layouts/main.php',
    'app/Shared/Pages/welcome.php',
];

foreach ($viewFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        echo '<p class="ok">[OK] <span class="path">' . $file . '</span></p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $file . '</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 5. ARCHIVOS DE ASSETS
// ============================================================================
echo '<div class="section">';
echo '<h2>5. Archivos de Assets</h2>';

$assetFiles = [
    'public/app-assets/css/app.css',
];

foreach ($assetFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo '<p class="ok">[OK] <span class="path">' . $file . '</span> (' . $size . ' bytes)</p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $file . '</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 6. ARCHIVO DE ENTRADA
// ============================================================================
echo '<div class="section">';
echo '<h2>6. Archivo de Entrada</h2>';

$entryFiles = [
    'public/app.php',
    'public/.htaccess',
];

foreach ($entryFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        echo '<p class="ok">[OK] <span class="path">' . $file . '</span></p>';
    } else {
        echo '<p class="fail">[FALTA] <span class="path">' . $file . '</span></p>';
    }
}

echo '</div>';

// ============================================================================
// 7. PROBAR CARGA DE CLASES
// ============================================================================
echo '<div class="section">';
echo '<h2>7. Prueba de Carga de Clases</h2>';

try {
    require_once $basePath . '/vendor/autoload.php';
    echo '<p class="ok">[OK] Composer autoload cargado</p>';
} catch (Throwable $e) {
    echo '<p class="fail">[FALLO] Composer autoload: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

try {
    require_once $basePath . '/app/Config/app.php';
    echo '<p class="ok">[OK] Config app.php cargado</p>';
} catch (Throwable $e) {
    echo '<p class="fail">[FALLO] Config app.php: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

try {
    require_once $basePath . '/app/Helpers/constants.php';
    echo '<p class="ok">[OK] Constants.php cargado</p>';
} catch (Throwable $e) {
    echo '<p class="fail">[FALLO] Constants.php: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Verificar constantes clave
$constantesClave = ['BASE_PATH', 'APP_PATH', 'VIEWS_PATH', 'STORAGE_PATH', 'LOG_PATH'];
foreach ($constantesClave as $const) {
    if (defined($const)) {
        echo '<p class="ok">[OK] Constante ' . $const . ' = <span class="path">' . constant($const) . '</span></p>';
    } else {
        echo '<p class="fail">[FALTA] Constante ' . $const . ' no definida</p>';
    }
}

echo '</div>';

// ============================================================================
// 8. PROBAR VIEW
// ============================================================================
echo '<div class="section">';
echo '<h2>8. Prueba de View</h2>';

try {
    require_once $basePath . '/app/Core/View.php';
    echo '<p class="ok">[OK] View.php cargado</p>';
    
    $viewBasePath = View::getBasePath();
    echo '<p>View base path: <span class="path">' . htmlspecialchars($viewBasePath) . '</span></p>';
    
    if (is_dir($viewBasePath)) {
        echo '<p class="ok">[OK] La carpeta de vistas existe</p>';
    } else {
        echo '<p class="fail">[FALLO] La carpeta de vistas NO existe</p>';
    }
    
    // Intentar renderizar
    if (file_exists($viewBasePath . '/Pages/welcome.php')) {
        $html = View::render('Pages.welcome');
        if (strlen($html) > 0) {
            echo '<p class="ok">[OK] Vista Pages.welcome renderizada (' . strlen($html) . ' caracteres)</p>';
        } else {
            echo '<p class="fail">[FALLO] Vista renderizada pero vacia</p>';
        }
    } else {
        echo '<p class="fail">[FALLO] El archivo Pages/welcome.php no existe en <span class="path">' . $viewBasePath . '/Pages/welcome.php</span></p>';
    }
    
} catch (Throwable $e) {
    echo '<p class="fail">[FALLO] View: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p class="fail">Archivo: ' . htmlspecialchars($e->getFile()) . ' Linea: ' . $e->getLine() . '</p>';
}

echo '</div>';

// ============================================================================
// 9. PROBAR URL DEL CSS
// ============================================================================
echo '<div class="section">';
echo '<h2>9. Prueba de URL del CSS</h2>';

try {
    require_once $basePath . '/app/Helpers/functions.php';
    
    $cssUrl = url('app-assets/css/app.css');
    echo '<p>URL generada para CSS: <span class="path">' . htmlspecialchars($cssUrl) . '</span></p>';
    
    // Verificar si el archivo existe en la ruta fisica
    $cssPhysicalPath = $basePath . '/public/app-assets/css/app.css';
    if (file_exists($cssPhysicalPath)) {
        echo '<p class="ok">[OK] El archivo CSS existe en la ruta fisica</p>';
    } else {
        echo '<p class="fail">[FALLO] El archivo CSS NO existe en <span class="path">' . $cssPhysicalPath . '</span></p>';
    }
    
} catch (Throwable $e) {
    echo '<p class="fail">[FALLO] URL test: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// ============================================================================
// RESUMEN
// ============================================================================
echo '<div class="section">';
echo '<h2>Resumen</h2>';
echo '<p>Revisa todos los puntos anteriores. Los elementos marcados en <span class="fail">rojo</span> son los que necesitan correccion.</p>';
echo '</div>';

echo '</body></html>';