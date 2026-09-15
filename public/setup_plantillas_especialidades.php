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

// Helpers para construir secciones
function sec($id, $titulo, $tipo = 'texto_largo', $req = true, $ph = '') {
    return ['id' => $id, 'titulo' => $titulo, 'tipo' => $tipo, 'requerido' => $req, 'placeholder' => $ph];
}

function estructura($secciones) {
    return [
        'mostrar_datos_paciente' => true,
        'mostrar_datos_profesional' => true,
        'mostrar_firma' => true,
        'secciones' => $secciones,
    ];
}

// Mapa slug => id de especialidades
$espMap = [];
$rows = $pdo->query("SELECT id, slug FROM especialidades WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $espMap[$r['slug']] = (int) $r['id'];
}

// ============================================
// DEFINICION DE PLANTILLAS POR ESPECIALIDAD
// ============================================
$plantillas = [
    'medicina-general' => [
        ['nombre' => 'Consulta Médica', 'tipo' => 'informe', 'descripcion' => 'Registro completo de consulta médica',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true, 'Síntomas y razón de la consulta...'),
            sec('signos', 'Signos vitales', 'texto_corto', false, 'TA, FC, FR, Temp, Peso, Talla...'),
            sec('exploracion', 'Exploración física', 'texto_largo', false, 'Hallazgos de la exploración...'),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true, 'Diagnóstico presuntivo o definitivo...'),
            sec('tratamiento', 'Tratamiento', 'texto_largo', true, 'Medicamentos y medidas indicadas...'),
            sec('indicaciones', 'Indicaciones al paciente', 'texto_largo', false, 'Cuidados y signos de alarma...'),
         ])],
        ['nombre' => 'Certificado Médico', 'tipo' => 'certificado', 'descripcion' => 'Constancia del estado de salud',
         'estructura' => estructura([
            sec('fecha', 'Fecha de expedición', 'fecha', true),
            sec('texto', 'Texto del certificado', 'texto_largo', true, 'Se hace constar que {{nombre_paciente}}, de {{edad}}, se encuentra en estado de salud...'),
         ])],
        ['nombre' => 'Justificante Médico', 'tipo' => 'justificante', 'descripcion' => 'Justifica la asistencia a consulta',
         'estructura' => estructura([
            sec('fecha', 'Fecha de la consulta', 'fecha', true),
            sec('hora', 'Hora de la consulta', 'texto_corto', false, 'Ej. 10:00 a 11:00 hrs'),
            sec('texto', 'Texto del justificante', 'texto_largo', true, 'Se justifica la asistencia de {{nombre_paciente}} a consulta médica el día {{fecha}}...'),
         ])],
        ['nombre' => 'Receta Médica', 'tipo' => 'receta', 'descripcion' => 'Prescripción de medicamentos',
         'estructura' => estructura([
            sec('diagnostico', 'Diagnóstico', 'texto_corto', true, 'Diagnóstico que justifica la prescripción...'),
            sec('medicamentos', 'Medicamentos', 'texto_largo', true, 'Medicamento, dosis, frecuencia, duración...'),
            sec('indicaciones', 'Indicaciones', 'texto_largo', false, 'Recomendaciones y advertencias...'),
         ])],
    ],
    'psiquiatria' => [
        ['nombre' => 'Evaluación Psiquiátrica', 'tipo' => 'evaluacion', 'descripcion' => 'Evaluación integral del estado mental',
         'estructura' => estructura([
            sec('motivo', 'Motivo de evaluación', 'texto_largo', true),
            sec('examen_mental', 'Examen del estado mental', 'texto_largo', true, 'Apariencia, conducta, lenguaje, ánimo, pensamiento, percepción, cognición...'),
            sec('historia', 'Historia psiquiátrica', 'texto_largo', false),
            sec('medicacion', 'Medicación actual', 'texto_largo', false),
            sec('riesgo', 'Evaluación de riesgo', 'texto_largo', true, 'Riesgo suicida, autoagresividad, heteroagresividad...'),
            sec('impresion', 'Impresión diagnóstica', 'texto_largo', true),
            sec('plan', 'Plan de tratamiento', 'texto_largo', true),
         ])],
        ['nombre' => 'Seguimiento Farmacológico', 'tipo' => 'evolucion', 'descripcion' => 'Control de medicación psiquiátrica',
         'estructura' => estructura([
            sec('medicacion', 'Medicación actual', 'texto_largo', true),
            sec('adherencia', 'Adherencia al tratamiento', 'texto_corto', true, 'Buena, regular, mala...'),
            sec('efectos', 'Efectos secundarios', 'texto_largo', false),
            sec('ajustes', 'Ajustes realizados', 'texto_largo', false),
            sec('plan', 'Plan de seguimiento', 'texto_largo', true),
         ])],
    ],
    'nutriologia' => [
        ['nombre' => 'Evaluación Nutricional', 'tipo' => 'evaluacion', 'descripcion' => 'Valoración integral del estado nutricional',
         'estructura' => estructura([
            sec('historia', 'Historia dietética', 'texto_largo', true, 'Hábitos alimentarios, frecuencia, preferencias...'),
            sec('antropometria', 'Antropometría', 'texto_corto', true, 'Peso, talla, IMC, circunferencias...'),
            sec('composicion', 'Composición corporal', 'texto_corto', false, '% grasa, % músculo, masa ósea...'),
            sec('analisis', 'Análisis y diagnóstico nutricional', 'texto_largo', true),
            sec('objetivos', 'Objetivos', 'texto_largo', true),
         ])],
        ['nombre' => 'Plan Alimentario', 'tipo' => 'informe', 'descripcion' => 'Plan de alimentación personalizado',
         'estructura' => estructura([
            sec('diagnostico', 'Diagnóstico nutricional', 'texto_largo', true),
            sec('plan', 'Plan alimentario', 'texto_largo', true, 'Distribución de alimentos, comidas, porciones...'),
            sec('recomendaciones', 'Recomendaciones', 'texto_largo', false),
            sec('seguimiento', 'Plan de seguimiento', 'texto_largo', false),
         ])],
    ],
    'pediatria' => [
        ['nombre' => 'Control de Niño Sano', 'tipo' => 'informe', 'descripcion' => 'Revisión periódica de crecimiento y desarrollo',
         'estructura' => estructura([
            sec('motivo', 'Motivo de la visita', 'texto_corto', true),
            sec('crecimiento', 'Crecimiento y desarrollo', 'texto_largo', true, 'Peso, talla, percentiles, hitos del desarrollo...'),
            sec('vacunacion', 'Estado de vacunación', 'texto_corto', false, 'Completo, pendiente, refuerzos...'),
            sec('exploracion', 'Exploración física', 'texto_largo', false),
            sec('indicaciones', 'Indicaciones', 'texto_largo', true, 'Alimentación, suplementos, próxima visita...'),
         ])],
        ['nombre' => 'Informe Pediátrico', 'tipo' => 'informe', 'descripcion' => 'Informe clínico pediátrico',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('antecedentes', 'Antecedentes', 'texto_largo', false, 'Nacimiento, vacunación, desarrollo...'),
            sec('exploracion', 'Exploración física', 'texto_largo', false),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('tratamiento', 'Tratamiento', 'texto_largo', true),
         ])],
    ],
    'ginecologia' => [
        ['nombre' => 'Informe Ginecológico', 'tipo' => 'informe', 'descripcion' => 'Informe de consulta ginecológica',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('antecedentes', 'Antecedentes gineco-obstétricos', 'texto_largo', false),
            sec('exploracion', 'Exploración', 'texto_largo', false),
            sec('estudios', 'Estudios realizados', 'texto_largo', false, 'Papanicolaou, ultrasonido, laboratorio...'),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('tratamiento', 'Tratamiento y seguimiento', 'texto_largo', true),
         ])],
    ],
    'odontologia' => [
        ['nombre' => 'Informe Odontológico', 'tipo' => 'informe', 'descripcion' => 'Informe de valoración dental',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('examen', 'Examen oral / Odontograma', 'texto_largo', true, 'Estado de piezas, caries, enfermedad periodontal...'),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('plan', 'Plan de tratamiento', 'texto_largo', true),
         ])],
    ],
    'fisioterapia' => [
        ['nombre' => 'Evaluación Funcional', 'tipo' => 'evaluacion', 'descripcion' => 'Valoración de la función y movilidad',
         'estructura' => estructura([
            sec('motivo', 'Motivo de evaluación', 'texto_largo', true),
            sec('funcional', 'Evaluación funcional', 'texto_largo', true),
            sec('dolor', 'Escala de dolor', 'texto_corto', true, 'Ej. EVA 0-10...'),
            sec('rango', 'Rango de movimiento', 'texto_corto', false),
            sec('plan', 'Plan de rehabilitación', 'texto_largo', true),
         ])],
    ],
    'cardiologia' => [
        ['nombre' => 'Informe Cardiológico', 'tipo' => 'informe', 'descripcion' => 'Informe de valoración cardiovascular',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('antecedentes', 'Antecedentes', 'texto_largo', false),
            sec('examen', 'Examen cardiovascular', 'texto_largo', true),
            sec('resultados', 'Resultados de estudios', 'texto_largo', false, 'ECG, ecocardiograma, prueba de esfuerzo...'),
            sec('riesgo', 'Factores de riesgo', 'texto_largo', false),
            sec('plan', 'Plan de tratamiento', 'texto_largo', true),
         ])],
    ],
    'dermatologia' => [
        ['nombre' => 'Informe Dermatológico', 'tipo' => 'informe', 'descripcion' => 'Informe de valoración de piel',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('examen', 'Examen de piel', 'texto_largo', true),
            sec('lesiones', 'Descripción de lesiones', 'texto_largo', false),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('tratamiento', 'Tratamiento', 'texto_largo', true),
         ])],
    ],
    'traumatologia' => [
        ['nombre' => 'Informe Traumatológico', 'tipo' => 'informe', 'descripcion' => 'Informe de valoración musculoesquelética',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('examen', 'Examen ortopédico', 'texto_largo', true),
            sec('imagenes', 'Resultados de imágenes', 'texto_largo', false, 'Rayos X, resonancia, tomografía...'),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('tratamiento', 'Tratamiento', 'texto_largo', true),
         ])],
    ],
    'oftalmologia' => [
        ['nombre' => 'Informe Oftalmológico', 'tipo' => 'informe', 'descripcion' => 'Informe de valoración visual',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('agudeza', 'Agudeza visual', 'texto_corto', true, 'OD y OS con/sin corrección...'),
            sec('examen', 'Examen ocular', 'texto_largo', false),
            sec('presion', 'Presión intraocular', 'texto_corto', false),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('prescripcion', 'Prescripción / Tratamiento', 'texto_largo', true),
         ])],
    ],
    'otorrinolaringologia' => [
        ['nombre' => 'Informe ORL', 'tipo' => 'informe', 'descripcion' => 'Informe de oído, nariz y garganta',
         'estructura' => estructura([
            sec('motivo', 'Motivo de consulta', 'texto_largo', true),
            sec('examen', 'Examen ORL', 'texto_largo', true),
            sec('audiometria', 'Audiometría / Estudios', 'texto_largo', false),
            sec('diagnostico', 'Diagnóstico', 'texto_largo', true),
            sec('tratamiento', 'Tratamiento', 'texto_largo', true),
         ])],
    ],
    'enfermeria' => [
        ['nombre' => 'Nota de Enfermería', 'tipo' => 'evolucion', 'descripcion' => 'Registro de cuidados de enfermería',
         'estructura' => estructura([
            sec('signos', 'Signos vitales', 'texto_corto', true, 'TA, FC, FR, Temp, SatO2...'),
            sec('evaluacion', 'Evaluación del paciente', 'texto_largo', true),
            sec('procedimientos', 'Procedimientos realizados', 'texto_largo', false),
            sec('medicamentos', 'Medicamentos administrados', 'texto_largo', false),
            sec('observaciones', 'Observaciones', 'texto_largo', false),
         ])],
    ],
    'terapia' => [
        ['nombre' => 'Nota de Terapia', 'tipo' => 'evolucion', 'descripcion' => 'Registro de sesión de terapia ocupacional/lenguaje',
         'estructura' => estructura([
            sec('funcional', 'Evaluación funcional', 'texto_largo', true),
            sec('objetivos', 'Objetivos de la sesión', 'texto_largo', true),
            sec('tecnicas', 'Técnicas utilizadas', 'texto_largo', false),
            sec('progreso', 'Progreso observado', 'texto_largo', true),
         ])],
    ],
];

// ============================================
// INSERTAR PLANTILLAS
// ============================================
$ahora = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("INSERT INTO plantillas_reporte 
    (organizacion_id, nombre, tipo, especialidad_id, descripcion, estructura_json, es_sistema, activo, creado_por, creado_en, actualizado_en)
    VALUES (NULL, ?, ?, ?, ?, ?, 1, 1, NULL, ?, ?)");

// Verificar existentes para no duplicar
$existentes = $pdo->query("SELECT CONCAT(nombre, '|', IFNULL(especialidad_id, 'x')) as clave FROM plantillas_reporte WHERE es_sistema = 1")->fetchAll(PDO::FETCH_COLUMN);
$existentes = array_flip($existentes);

$creadas = 0;
$saltadas = 0;

foreach ($plantillas as $slug => $lista) {
    $espId = $espMap[$slug] ?? null;
    
    if ($espId === null) {
        echo "⚠ Especialidad '$slug' no existe, saltando...\n";
        continue;
    }
    
    echo "\n📋 Especialidad: $slug (id=$espId)\n";
    
    foreach ($lista as $p) {
        $clave = $p['nombre'] . '|' . $espId;
        
        if (isset($existentes[$clave])) {
            echo "  ⊘ Ya existe: {$p['nombre']}\n";
            $saltadas++;
            continue;
        }
        
        $stmt->execute([
            $p['nombre'],
            $p['tipo'],
            $espId,
            $p['descripcion'],
            json_encode($p['estructura'], JSON_UNESCAPED_UNICODE),
            $ahora,
            $ahora,
        ]);
        
        echo "  ✓ Creada: {$p['nombre']} ({$p['tipo']})\n";
        $creadas++;
    }
}

echo "\n========================================\n";
echo "✅ RESUMEN\n";
echo "========================================\n";
echo "Plantillas creadas: $creadas\n";
echo "Plantillas saltadas (ya existían): $saltadas\n";

$total = $pdo->query("SELECT COUNT(*) FROM plantillas_reporte WHERE es_sistema = 1")->fetchColumn();
echo "Total de plantillas del sistema: $total\n";

echo "</pre>";
