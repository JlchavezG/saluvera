<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Expediente
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // EXPEDIENTE (antecedentes) 1:1 con paciente
    // ========================================================================

    public function findByPaciente(int $pacienteId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM expedientes_clinicos WHERE paciente_id = ?",
            [$pacienteId]
        );
    }

    public function asegurar(int $pacienteId, int $orgId, int $userId): array
    {
        $exp = $this->findByPaciente($pacienteId);

        if ($exp !== null) {
            return $exp;
        }

        $now = date('Y-m-d H:i:s');
        $id = $this->db->insert('expedientes_clinicos', [
            'paciente_id' => $pacienteId,
            'organizacion_id' => $orgId,
            'creado_en' => $now,
            'actualizado_en' => $now,
            'creado_por' => $userId,
        ]);

        return $this->db->fetchOne("SELECT * FROM expedientes_clinicos WHERE id = ?", [$id]);
    }

    public function update(int $id, array $d, int $userId): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        $d['actualizado_por'] = $userId;

        return $this->db->update('expedientes_clinicos', $d, 'id = ?', [$id]);
    }

    // ========================================================================
    // LINEA DE TIEMPO CLINICA
    // ========================================================================

    public function timeline(int $pacienteId, ?int $profesionalId = null): array
    {
        $sql = "SELECT c.id, c.fecha_consulta, c.motivo, c.estado, c.signos_vitales,
                       u.nombre as prof_nombre, u.apellidos as prof_apellidos,
                       (SELECT COUNT(*) FROM diagnosticos d WHERE d.consulta_id = c.id) as total_diagnosticos,
                       (SELECT COUNT(*) FROM tratamientos t WHERE t.consulta_id = c.id) as total_tratamientos
                FROM consultas c
                INNER JOIN profesionales pr ON c.profesional_id = pr.id
                INNER JOIN usuarios u ON pr.usuario_id = u.id
                WHERE c.paciente_id = ?";
        $params = [$pacienteId];

        if ($profesionalId !== null) {
            $sql .= " AND c.profesional_id = ?";
            $params[] = $profesionalId;
        }

        $sql .= " ORDER BY c.fecha_consulta DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function diagnosticosActivos(int $pacienteId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM diagnosticos
             WHERE paciente_id = ? AND estado IN ('activo', 'monitoreo')
             ORDER BY es_principal DESC, creado_en DESC",
            [$pacienteId]
        );
    }

    public function tratamientosActivos(int $pacienteId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM tratamientos
             WHERE paciente_id = ? AND estado = 'activo'
             ORDER BY fecha_inicio DESC",
            [$pacienteId]
        );
    }
}
