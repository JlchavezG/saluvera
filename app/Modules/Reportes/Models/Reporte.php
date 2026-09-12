<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Reporte
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function rango(string $periodo, ?string $desde = null, ?string $hasta = null): array
    {
        $hoy = date('Y-m-d');

        switch ($periodo) {
            case 'hoy':
                return [$hoy, $hoy];
            case 'semana':
                return [date('Y-m-d', strtotime('-6 days')), $hoy];
            case 'mes':
                return [date('Y-m-01'), date('Y-m-t')];
            case 'anio':
                return [date('Y-01-01'), date('Y-12-31')];
            case 'personalizado':
                return [$desde ?: $hoy, $hasta ?: $hoy];
            default:
                return [date('Y-m-01'), date('Y-m-t')];
        }
    }

    public static function nombrePeriodo(string $periodo): string
    {
        $mapa = ['hoy' => 'Hoy', 'semana' => 'Ultimos 7 dias', 'mes' => 'Mes actual', 'anio' => 'Año actual', 'personalizado' => 'Personalizado'];
        return $mapa[$periodo] ?? 'Mes actual';
    }

    public function consultasCompletadas(int $orgId, int $profId, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(*) FROM citas WHERE estado = 'completada' AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function ingresos(int $orgId, int $profId, string $desde, string $hasta): float
    {
        $sql = "SELECT COALESCE(SUM(monto_consulta), 0) FROM citas
                WHERE estado = 'completada' AND monto_consulta IS NOT NULL
                  AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (float) $this->db->fetchColumn($sql, $params);
    }

    public function citasPasadas(int $orgId, int $profId, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(*) FROM citas WHERE fecha_cita BETWEEN ? AND ? AND estado != 'pendiente'";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function citasNoAsistidas(int $orgId, int $profId, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(*) FROM citas WHERE estado = 'no_asistio' AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function citasCanceladas(int $orgId, int $profId, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(*) FROM citas WHERE estado = 'cancelada' AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function ingresosPorProfesional(int $orgId, string $desde, string $hasta): array
    {
        $sql = "SELECT p.id, u.nombre, u.apellidos,
                       COUNT(*) as consultas,
                       COALESCE(SUM(c.monto_consulta), 0) as total
                FROM citas c
                INNER JOIN profesionales p ON c.profesional_id = p.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE c.estado = 'completada' AND c.monto_consulta IS NOT NULL
                  AND c.fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND c.organizacion_id = ?"; $params[] = $orgId; }

        $sql .= " GROUP BY p.id, u.nombre, u.apellidos ORDER BY total DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function ingresosPorOrganizacion(string $desde, string $hasta): array
    {
        return $this->db->fetchAll(
            "SELECT o.id, o.nombre,
                    COUNT(*) as consultas,
                    COALESCE(SUM(c.monto_consulta), 0) as total
             FROM citas c
             INNER JOIN organizaciones o ON c.organizacion_id = o.id
             WHERE c.estado = 'completada' AND c.monto_consulta IS NOT NULL
               AND c.fecha_cita BETWEEN ? AND ?
             GROUP BY o.id, o.nombre
             ORDER BY total DESC",
            [$desde, $hasta]
        );
    }

    public function ingresosPorMes(int $orgId, int $profId, string $desde, string $hasta): array
    {
        $sql = "SELECT DATE_FORMAT(fecha_cita, '%Y-%m') as mes,
                       COALESCE(SUM(monto_consulta), 0) as total,
                       COUNT(*) as consultas
                FROM citas
                WHERE estado = 'completada' AND monto_consulta IS NOT NULL
                  AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        $sql .= " GROUP BY mes ORDER BY mes ASC";

        return $this->db->fetchAll($sql, $params);
    }

    public function minutosTrabajados(int $orgId, int $profId, string $desde, string $hasta): int
    {
        if ($profId > 0) {
            $duracion = (int) $this->db->fetchColumn(
                "SELECT COALESCE(duracion_consulta, 0) FROM profesionales WHERE id = ?",
                [$profId]
            );
            if ($duracion > 0) {
                $completadas = $this->consultasCompletadas($orgId, $profId, $desde, $hasta);
                return $completadas * $duracion;
            }
        }

        $sql = "SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fin)), 0)
                FROM citas
                WHERE estado = 'completada' AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function diagnosticosFrecuentes(int $orgId, int $profId, string $desde, string $hasta, int $limit = 10): array
    {
        $sql = "SELECT d.nombre_diagnostico, COUNT(*) as veces
                FROM diagnosticos d
                INNER JOIN consultas cs ON d.consulta_id = cs.id
                INNER JOIN citas c ON cs.cita_id = c.id
                WHERE c.fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND c.organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND c.profesional_id = ?"; $params[] = $profId; }

        $sql .= " GROUP BY d.nombre_diagnostico ORDER BY veces DESC LIMIT " . (int) $limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function totalPacientes(int $orgId): int
    {
        $sql = "SELECT COUNT(*) FROM pacientes WHERE activo = 1";
        $params = [];
        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        return (int) $this->db->fetchColumn($sql, $params);
    }

    // ========================================================================
    // METRICAS DE PLATAFORMA (superadmin) - SOLO operativas, sin montos
    // ========================================================================

    public function totalUsuarios(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM usuarios");
    }

    public function usuariosActivos(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
    }

    public function totalProfesionalesGlobal(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM profesionales WHERE activo = 1");
    }

    public function totalOrganizacionesGlobal(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones");
    }

    public function organizacionesActivas(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE activo = 1");
    }

    public function totalCitasGlobal(string $desde, string $hasta): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas WHERE fecha_cita BETWEEN ? AND ?",
            [$desde, $hasta]
        );
    }

    public function citasPorEstadoGlobal(string $desde, string $hasta): array
    {
        return $this->db->fetchAll(
            "SELECT estado, COUNT(*) as total FROM citas WHERE fecha_cita BETWEEN ? AND ? GROUP BY estado ORDER BY total DESC",
            [$desde, $hasta]
        );
    }

    public function totalConsultasGlobal(string $desde, string $hasta): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM consultas cs INNER JOIN citas c ON cs.cita_id = c.id WHERE c.fecha_cita BETWEEN ? AND ?",
            [$desde, $hasta]
        );
    }

    public function pacientesPorOrganizacion(): array
    {
        return $this->db->fetchAll(
            "SELECT o.id, o.nombre, o.activo, COUNT(p.id) as pacientes
             FROM organizaciones o
             LEFT JOIN pacientes p ON o.id = p.organizacion_id
             GROUP BY o.id, o.nombre, o.activo
             ORDER BY pacientes DESC"
        );
    }

    public function actividadReciente(): array
    {
        return $this->db->fetchAll(
            "SELECT c.fecha_cita, c.estado, o.nombre as organizacion,
                    CONCAT(p.nombre, ' ', p.apellidos) as paciente
             FROM citas c
             INNER JOIN organizaciones o ON c.organizacion_id = o.id
             LEFT JOIN pacientes p ON c.paciente_id = p.id
             ORDER BY c.fecha_cita DESC, c.id DESC
             LIMIT 10"
        );
    }

    public function pacientesAtendidos(int $orgId, int $profId, string $desde, string $hasta): int
    {
        $sql = "SELECT COUNT(DISTINCT paciente_id) FROM citas
                WHERE estado = 'completada' AND fecha_cita BETWEEN ? AND ?";
        $params = [$desde, $hasta];

        if ($orgId > 0) { $sql .= " AND organizacion_id = ?"; $params[] = $orgId; }
        if ($profId > 0) { $sql .= " AND profesional_id = ?"; $params[] = $profId; }

        return (int) $this->db->fetchColumn($sql, $params);
    }
}
