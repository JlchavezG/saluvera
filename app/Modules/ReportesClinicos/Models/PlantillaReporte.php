<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class PlantillaReporte
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listarDisponibles(int $orgId, int $creadoPor, ?int $especialidadId = null): array
    {
        $sql = "SELECT p.*, e.nombre as especialidad_nombre
                FROM plantillas_reporte p
                LEFT JOIN especialidades e ON p.especialidad_id = e.id
                WHERE p.activo = 1
                  AND (p.organizacion_id IS NULL OR p.organizacion_id = ? OR p.creado_por = ?)";
        $params = [$orgId, $creadoPor];

        if ($especialidadId !== null) {
            $sql .= " AND (p.especialidad_id IS NULL OR p.especialidad_id = ?)";
            $params[] = $especialidadId;
        }

        $sql .= " ORDER BY p.es_sistema DESC, p.nombre ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function buscar(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM plantillas_reporte WHERE id = ?", [$id]);
    }

    public function crear(array $datos): int
    {
        $ahora = date('Y-m-d H:i:s');
        return $this->db->insert('plantillas_reporte', [
            'organizacion_id' => $datos['organizacion_id'] ?? null,
            'nombre' => $datos['nombre'],
            'tipo' => $datos['tipo'] ?? 'informe',
            'especialidad_id' => $datos['especialidad_id'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'estructura_json' => json_encode($datos['estructura'] ?? [], JSON_UNESCAPED_UNICODE),
            'es_sistema' => 0,
            'activo' => 1,
            'creado_por' => $datos['creado_por'] ?? null,
            'creado_en' => $ahora,
            'actualizado_en' => $ahora
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $ahora = date('Y-m-d H:i:s');
        return $this->db->update('plantillas_reporte', [
            'nombre' => $datos['nombre'],
            'tipo' => $datos['tipo'] ?? 'informe',
            'especialidad_id' => $datos['especialidad_id'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'estructura_json' => json_encode($datos['estructura'] ?? [], JSON_UNESCAPED_UNICODE),
            'actualizado_en' => $ahora
        ], 'id = ?', [$id]);
    }

    public function duplicar(int $id, int $orgId, int $creadoPor): int
    {
        $original = $this->buscar($id);
        if (!$original) {
            return 0;
        }

        $ahora = date('Y-m-d H:i:s');
        return $this->db->insert('plantillas_reporte', [
            'organizacion_id' => $orgId,
            'nombre' => $original['nombre'] . ' (Copia)',
            'tipo' => $original['tipo'],
            'especialidad_id' => $original['especialidad_id'],
            'descripcion' => $original['descripcion'],
            'estructura_json' => $original['estructura_json'],
            'es_sistema' => 0,
            'activo' => 1,
            'creado_por' => $creadoPor,
            'creado_en' => $ahora,
            'actualizado_en' => $ahora
        ]);
    }

    public function eliminar(int $id): bool
    {
        $result = $this->db->delete('plantillas_reporte', 'id = ? AND es_sistema = 0', [$id]);
        return $result > 0;
    }

    public function tiposDisponibles(): array
    {
        return [
            'informe' => 'Informe',
            'evolucion' => 'Evolución',
            'evaluacion' => 'Evaluación',
            'certificado' => 'Certificado',
            'justificante' => 'Justificante',
            'consentimiento' => 'Consentimiento',
            'receta' => 'Receta',
            'referencia' => 'Referencia',
            'otro' => 'Otro',
        ];
    }
}
