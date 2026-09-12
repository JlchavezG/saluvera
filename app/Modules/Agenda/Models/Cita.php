<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Cita
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseSelect(): string
    {
        return "SELECT c.id, c.organizacion_id, c.paciente_id, c.profesional_id, c.consultorio_id,
                       c.fecha_cita, c.hora_inicio, c.hora_fin, c.estado, c.motivo,
                       c.motivo_cancelacion, c.creado_en,
                       p.nombre as paciente_nombre, p.apellidos as paciente_apellidos,
                       pr.id as prof_id, u.nombre as prof_nombre, u.apellidos as prof_apellidos,
                       con.nombre as consultorio_nombre,
                       o.nombre as organizacion_nombre
                FROM citas c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                LEFT JOIN profesionales pr ON c.profesional_id = pr.id
                LEFT JOIN usuarios u ON pr.usuario_id = u.id
                LEFT JOIN consultorios con ON c.consultorio_id = con.id
                INNER JOIN organizaciones o ON c.organizacion_id = o.id";
    }

    // ========================================================================
    // CONSULTAS DIRECTAS
    // ========================================================================

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . " WHERE c.id = ?",
            [$id]
        );
    }

    // ========================================================================
    // LISTADOS CON ALCANCE POR ROL
    // ========================================================================

    public function searchGlobal(array $filtros, int $limit = 15, int $offset = 0): array
    {
        $sql = $this->baseSelect() . " WHERE 1=1";
        $params = [];
        [$sql, $params] = $this->aplicarFiltros($sql, $params, $filtros);
        $sql .= " ORDER BY c.fecha_cita DESC, c.hora_inicio ASC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function countGlobal(array $filtros): int
    {
        $sql = "SELECT COUNT(*) FROM citas c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                LEFT JOIN profesionales pr ON c.profesional_id = pr.id
                LEFT JOIN usuarios u ON pr.usuario_id = u.id
                INNER JOIN organizaciones o ON c.organizacion_id = o.id
                WHERE 1=1";
        $params = [];
        [$sql, $params] = $this->aplicarFiltros($sql, $params, $filtros);
        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function searchByOrg(int $orgId, array $filtros, int $limit = 15, int $offset = 0): array
    {
        $filtros['org_id'] = $orgId;
        return $this->searchGlobal($filtros, $limit, $offset);
    }

    public function countByOrg(int $orgId, array $filtros): int
    {
        $filtros['org_id'] = $orgId;
        return $this->countGlobal($filtros);
    }

    public function searchByProfessional(int $profesionalId, array $filtros, int $limit = 15, int $offset = 0): array
    {
        $filtros['profesional_id'] = $profesionalId;
        return $this->searchGlobal($filtros, $limit, $offset);
    }

    public function countByProfessional(int $profesionalId, array $filtros): int
    {
        $filtros['profesional_id'] = $profesionalId;
        return $this->countGlobal($filtros);
    }

    private function aplicarFiltros(string $sql, array $params, array $filtros): array
    {
        if (!empty($filtros['org_id'])) {
            $sql .= " AND c.organizacion_id = ?";
            $params[] = $filtros['org_id'];
        }

        if (!empty($filtros['profesional_id'])) {
            $sql .= " AND c.profesional_id = ?";
            $params[] = $filtros['profesional_id'];
        }

        if (!empty($filtros['fecha'])) {
            $sql .= " AND c.fecha_cita = ?";
            $params[] = $filtros['fecha'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['q'])) {
            $sql .= " AND (p.nombre LIKE ? OR p.apellidos LIKE ? OR u.nombre LIKE ? OR u.apellidos LIKE ? OR c.motivo LIKE ?)";
            $like = '%' . $filtros['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return [$sql, $params];
    }

    // ========================================================================
    // CITAS DEL DIA Y PROXIMAS (Mi Panel del profesional)
    // ========================================================================

    public function misCitasDeHoy(int $profesionalId): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             WHERE c.profesional_id = ? AND c.fecha_cita = CURDATE()
             ORDER BY c.hora_inicio",
            [$profesionalId]
        );
    }

    public function miProximaCita(int $profesionalId): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . "
             WHERE c.profesional_id = ?
               AND c.fecha_cita >= CURDATE()
               AND c.estado IN ('pendiente', 'confirmada')
             ORDER BY c.fecha_cita, c.hora_inicio
             LIMIT 1",
            [$profesionalId]
        );
    }

    public function countMisCitasHoy(int $profesionalId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas
             WHERE profesional_id = ? AND fecha_cita = CURDATE()
               AND estado NOT IN ('cancelada', 'no_asistio')",
            [$profesionalId]
        );
    }

    public function countHoyOrg(int $organizacionId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas
             WHERE organizacion_id = ? AND fecha_cita = CURDATE()",
            [$organizacionId]
        );
    }

    public function countByOrgTotal(int $organizacionId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas WHERE organizacion_id = ?",
            [$organizacionId]
        );
    }

    // ========================================================================
    // VALIDACION DE EMPALMES DE HORARIO
    // ========================================================================

    public function hayEmpalme(int $profesionalId, string $fecha, string $horaInicio, string $horaFin, ?int $excluirCitaId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM citas
                WHERE profesional_id = ?
                  AND fecha_cita = ?
                  AND estado NOT IN ('cancelada', 'no_asistio')
                  AND hora_inicio < ?
                  AND hora_fin > ?";
        $params = [$profesionalId, $fecha, $horaFin, $horaInicio];

        if ($excluirCitaId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluirCitaId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    // ========================================================================
    // CREACION Y ACTUALIZACION
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('citas', [
            'organizacion_id' => $d['organizacion_id'],
            'paciente_id' => $d['paciente_id'],
            'profesional_id' => $d['profesional_id'],
            'consultorio_id' => $d['consultorio_id'] ?? null,
            'fecha_cita' => $d['fecha_cita'],
            'hora_inicio' => $d['hora_inicio'],
            'hora_fin' => $d['hora_fin'],
            'estado' => $d['estado'] ?? 'pendiente',
            'motivo' => $d['motivo'] ?? null,
            'creado_por' => $d['creado_por'] ?? null,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function updateEstado(int $id, string $estado, array $extra = []): int
    {
        $data = array_merge(['estado' => $estado], $extra);

        $timestamps = [
            'confirmada' => 'confirmado_en',
            'completada' => 'completado_en',
            'cancelada' => 'cancelado_en',
            'en_progreso' => 'iniciado_en',
        ];

        if (isset($timestamps[$estado])) {
            $data[$timestamps[$estado]] = date('Y-m-d H:i:s');
        }

        $data['actualizado_en'] = date('Y-m-d H:i:s');

        return $this->db->update('citas', $data, 'id = ?', [$id]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('citas', $d, 'id = ?', [$id]);
    }

    // ========================================================================
    // DURACION DEL PROFESIONAL (para calcular hora_fin)
    // ========================================================================

    public function duracionProfesional(int $profesionalId): int
    {
        $dur = $this->db->fetchColumn(
            "SELECT duracion_consulta FROM profesionales WHERE id = ?",
            [$profesionalId]
        );
        return $dur !== null ? (int) $dur : 30;
    }

    // ========================================================================
    // ESTADOS DISPONIBLES
    // ========================================================================

    // ========================================================================
    // CITAS DEL PACIENTE (portal)
    // ========================================================================

    public function proximasDePaciente(int $pacienteId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             WHERE c.paciente_id = ?
               AND c.fecha_cita >= CURDATE()
               AND c.estado NOT IN ('cancelada')
             ORDER BY c.fecha_cita ASC, c.hora_inicio ASC
             LIMIT " . (int) $limit,
            [$pacienteId]
        );
    }

    public function historialDePaciente(int $pacienteId, int $limit = 30): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             WHERE c.paciente_id = ?
             ORDER BY c.fecha_cita DESC, c.hora_inicio DESC
             LIMIT " . (int) $limit,
            [$pacienteId]
        );
    }

    // ========================================================================
    // GANANCIAS DEL PROFESIONAL (citas completadas con monto congelado)
    // ========================================================================

    public function gananciasProfesional(int $profesionalId, ?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT COUNT(*) as total_citas, COALESCE(SUM(monto_consulta), 0) as total_monto
                FROM citas
                WHERE profesional_id = ? AND estado = 'completada' AND monto_consulta IS NOT NULL";
        $params = [$profesionalId];

        if ($desde !== null && $desde !== '') { $sql .= " AND fecha_cita >= ?"; $params[] = $desde; }
        if ($hasta !== null && $hasta !== '') { $sql .= " AND fecha_cita <= ?"; $params[] = $hasta; }

        $row = $this->db->fetchOne($sql, $params);

        return [
            'total_citas' => (int) ($row['total_citas'] ?? 0),
            'total_monto' => (float) ($row['total_monto'] ?? 0),
        ];
    }

    public function citasCompletadasProfesional(int $profesionalId, ?string $desde = null, ?string $hasta = null, int $limit = 200, int $offset = 0): array
    {
        $sql = $this->baseSelect() . "
                WHERE c.profesional_id = ? AND c.estado = 'completada' AND c.monto_consulta IS NOT NULL";
        $params = [$profesionalId];

        if ($desde !== null && $desde !== '') { $sql .= " AND c.fecha_cita >= ?"; $params[] = $desde; }
        if ($hasta !== null && $hasta !== '') { $sql .= " AND c.fecha_cita <= ?"; $params[] = $hasta; }

        $sql .= " ORDER BY c.fecha_cita DESC, c.hora_inicio DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public static function estados(): array
    {
        return [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'en_progreso' => 'En progreso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            'no_asistio' => 'No asistio',
            'reprogramada' => 'Reprogramada',
        ];
    }
}
