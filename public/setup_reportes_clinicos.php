<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('SALUVERA_APP', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/app.php';
require __DIR__ . '/../app/Core/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "<pre style='font-family:monospace'>";

// ============================================
// TABLA: plantillas_reporte
// ============================================
$pdo->exec("CREATE TABLE IF NOT EXISTS plantillas_reporte (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizacion_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(150) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    especialidad_id BIGINT UNSIGNED NULL,
    descripcion TEXT NULL,
    estructura_json LONGTEXT NOT NULL,
    es_sistema TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    creado_por BIGINT UNSIGNED NULL,
    creado_en DATETIME NOT NULL,
    actualizado_en DATETIME NOT NULL,
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK: tabla plantillas_reporte\n";

// ============================================
// TABLA: reportes_paciente
// ============================================
$pdo->exec("CREATE TABLE IF NOT EXISTS reportes_paciente (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paciente_id BIGINT UNSIGNED NOT NULL,
    profesional_id BIGINT UNSIGNED NOT NULL,
    organizacion_id BIGINT UNSIGNED NOT NULL,
    plantilla_id BIGINT UNSIGNED NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido_json LONGTEXT NOT NULL,
    estado ENUM('borrador','firmado','entregado') DEFAULT 'borrador',
    compartido_portal TINYINT(1) DEFAULT 0,
    firmado_en DATETIME NULL,
    creado_en DATETIME NOT NULL,
    actualizado_en DATETIME NOT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (profesional_id) REFERENCES profesionales(id) ON DELETE CASCADE,
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (plantilla_id) REFERENCES plantillas_reporte(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK: tabla reportes_paciente\n";

// ============================================
// SEMILLA: Plantillas de Psicología
// ============================================
$existe = $pdo->query("SELECT COUNT(*) FROM plantillas_reporte WHERE es_sistema = 1")->fetchColumn();
if ($existe > 0) {
    echo "\nLas plantillas del sistema ya existen ($existe). No se duplican.\n";
    echo "</pre>";
    exit;
}

$ahora = date('Y-m-d H:i:s');
$psicologia = $pdo->query("SELECT id FROM especialidades WHERE slug = 'psicologia' LIMIT 1")->fetchColumn();

$plantillas = [
    [
        'nombre' => 'Informe Psicológico Inicial',
        'tipo' => 'informe',
        'descripcion' => 'Informe de primera consulta con evaluación inicial del paciente',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'motivo', 'titulo' => 'Motivo de consulta', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Describa el motivo de consulta expresado por el paciente...'],
                ['id' => 'antecedentes', 'titulo' => 'Antecedentes relevantes', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Antecedentes personales, familiares, médicos relevantes...'],
                ['id' => 'historia_actual', 'titulo' => 'Historia del problema actual', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Inicio, duración, factores desencadenantes, síntomas...'],
                ['id' => 'observaciones', 'titulo' => 'Observaciones conductuales', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Apariencia, actitud, lenguaje, estado de ánimo observado...'],
                ['id' => 'impresion', 'titulo' => 'Impresión diagnóstica', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Hipótesis diagnóstica inicial...'],
                ['id' => 'plan', 'titulo' => 'Plan de tratamiento', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Objetivos terapéuticos, enfoque, frecuencia de sesiones...'],
                ['id' => 'recomendaciones', 'titulo' => 'Recomendaciones', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Recomendaciones para el paciente...']
            ]
        ]
    ],
    [
        'nombre' => 'Reporte de Evolución de Sesión',
        'tipo' => 'evolucion',
        'descripcion' => 'Nota de evolución de una sesión de terapia',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'fecha_sesion', 'titulo' => 'Fecha de la sesión', 'tipo' => 'fecha', 'requerido' => true],
                ['id' => 'temas', 'titulo' => 'Temas abordados', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Temas principales trabajados en la sesión...'],
                ['id' => 'tecnicas', 'titulo' => 'Técnicas utilizadas', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Técnicas o intervenciones terapéuticas aplicadas...'],
                ['id' => 'progreso', 'titulo' => 'Progreso observado', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Avances, cambios, respuesta del paciente...'],
                ['id' => 'tareas', 'titulo' => 'Tareas asignadas', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Actividades o tareas para el paciente entre sesiones...'],
                ['id' => 'proxima', 'titulo' => 'Plan para próxima sesión', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Objetivos para la siguiente sesión...']
            ]
        ]
    ],
    [
        'nombre' => 'Informe de Cierre de Tratamiento',
        'tipo' => 'informe',
        'descripcion' => 'Informe final al concluir el proceso terapéutico',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'motivo_cierre', 'titulo' => 'Motivo de cierre', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Razón de la conclusión del tratamiento...'],
                ['id' => 'resumen', 'titulo' => 'Resumen del proceso terapéutico', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Duración, número de sesiones, enfoque utilizado...'],
                ['id' => 'objetivos', 'titulo' => 'Objetivos alcanzados', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Objetivos cumplidos durante el proceso...'],
                ['id' => 'estado_actual', 'titulo' => 'Estado actual del paciente', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Condición del paciente al momento del cierre...'],
                ['id' => 'seguimiento', 'titulo' => 'Recomendaciones de seguimiento', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Sugerencias para mantener los avances...']
            ]
        ]
    ],
    [
        'nombre' => 'Evaluación Psicológica',
        'tipo' => 'evaluacion',
        'descripcion' => 'Resultado de evaluación con instrumentos psicométricos',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'motivo_eval', 'titulo' => 'Motivo de la evaluación', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Razón por la que se realiza la evaluación...'],
                ['id' => 'instrumentos', 'titulo' => 'Instrumentos aplicados', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Tests, escalas o instrumentos utilizados...'],
                ['id' => 'resultados', 'titulo' => 'Resultados', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Puntuaciones y hallazgos obtenidos...'],
                ['id' => 'analisis', 'titulo' => 'Análisis e interpretación', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Interpretación clínica de los resultados...'],
                ['id' => 'conclusiones', 'titulo' => 'Conclusiones', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Conclusiones de la evaluación...'],
                ['id' => 'recomendaciones', 'titulo' => 'Recomendaciones', 'tipo' => 'texto_largo', 'requerido' => false, 'placeholder' => 'Recomendaciones basadas en la evaluación...']
            ]
        ]
    ],
    [
        'nombre' => 'Certificado de Asistencia a Terapia',
        'tipo' => 'certificado',
        'descripcion' => 'Constancia de que el paciente asiste a proceso terapéutico',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'fecha_inicio', 'titulo' => 'Fecha de inicio del tratamiento', 'tipo' => 'fecha', 'requerido' => true],
                ['id' => 'frecuencia', 'titulo' => 'Frecuencia de las sesiones', 'tipo' => 'texto_corto', 'requerido' => true, 'placeholder' => 'Ej. Semanal, quincenal...'],
                ['id' => 'texto_certifica', 'titulo' => 'Texto del certificado', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Por medio del presente se hace constar que {{nombre_paciente}} se encuentra en proceso terapéutico...']
            ]
        ]
    ],
    [
        'nombre' => 'Justificante de Sesión',
        'tipo' => 'justificante',
        'descripcion' => 'Justificante de asistencia a una sesión específica',
        'estructura' => [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => [
                ['id' => 'fecha_sesion', 'titulo' => 'Fecha de la sesión', 'tipo' => 'fecha', 'requerido' => true],
                ['id' => 'hora_sesion', 'titulo' => 'Hora de la sesión', 'tipo' => 'texto_corto', 'requerido' => false, 'placeholder' => 'Ej. 10:00 a 11:00 hrs'],
                ['id' => 'texto_justifica', 'titulo' => 'Texto del justificante', 'tipo' => 'texto_largo', 'requerido' => true, 'placeholder' => 'Por medio del presente se justifica la asistencia de {{nombre_paciente}} a sesión de terapia el día {{fecha}}...']
            ]
        ]
    ]
];

$stmt = $pdo->prepare("INSERT INTO plantillas_reporte 
    (organizacion_id, nombre, tipo, especialidad_id, descripcion, estructura_json, es_sistema, activo, creado_por, creado_en, actualizado_en)
    VALUES (NULL, ?, ?, ?, ?, ?, 1, 1, NULL, ?, ?)");

foreach ($plantillas as $p) {
    $stmt->execute([
        $p['nombre'],
        $p['tipo'],
        $psicologia ?: null,
        $p['descripcion'],
        json_encode($p['estructura'], JSON_UNESCAPED_UNICODE),
        $ahora,
        $ahora
    ]);
    echo "OK: plantilla '{$p['nombre']}'\n";
}

echo "\n=== RESUMEN ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM plantillas_reporte")->fetchColumn();
echo "Total de plantillas creadas: $total\n";
echo "Especialidad psicología id: " . ($psicologia ?: 'NO ENCONTRADA') . "\n";

echo "</pre>";
