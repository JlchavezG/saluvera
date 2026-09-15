<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

/**
 * Reportes de Plataforma para Superadmin
 * Solo métricas operativas, SIN datos financieros de clientes.
 */
class ReportePlataforma
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // SEGMENTACIÓN DE USUARIOS
    // ========================================================================

    public function usuariosPorRol(): array
    {
        return $this->db->fetchAll(
            "SELECT r.nombre as rol, r.slug, COUNT(DISTINCT ur.usuario_id) as total
             FROM roles r
             LEFT JOIN usuario_roles ur ON r.id = ur.rol_id
             GROUP BY r.id, r.nombre, r.slug
             ORDER BY total DESC"
        );
    }

    public function profesionalesPorEspecialidad(): array
    {
        return $this->db->fetchAll(
            "SELECT e.nombre as especialidad, e.icono, COUNT(p.id) as total
             FROM especialidades e
             LEFT JOIN profesionales p ON e.id = p.especialidad_id AND p.activo = 1
             WHERE e.activo = 1
             GROUP BY e.id, e.nombre, e.icono
             ORDER BY total DESC"
        );
    }

    // ========================================================================
    // DEMOGRAFÍA
    // ========================================================================

    public function pacientesPorGenero(): array
    {
        return $this->db->fetchAll(
            "SELECT genero, COUNT(*) as total
             FROM pacientes
             WHERE activo = 1 AND genero IS NOT NULL
             GROUP BY genero
             ORDER BY total DESC"
        );
    }

    public function pacientesPorRangoEdad(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 18 THEN '0-17'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 36 THEN '18-35'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 56 THEN '36-55'
                    ELSE '56+'
                END as rango,
                COUNT(*) as total
             FROM pacientes
             WHERE activo = 1 AND fecha_nacimiento IS NOT NULL
             GROUP BY rango
             ORDER BY FIELD(rango, '0-17', '18-35', '36-55', '56+')"
        );
    }

    public function pacientesPorCiudad(): array
    {
        return $this->db->fetchAll(
            "SELECT COALESCE(ciudad, 'Sin especificar') as ciudad, COUNT(*) as total
             FROM pacientes
             WHERE activo = 1
             GROUP BY ciudad
             ORDER BY total DESC
             LIMIT 10"
        );
    }

    // ========================================================================
    // ENGAGEMENT Y RETENCIÓN
    // ========================================================================

    public function usuariosActivos(): array
    {
        $row = $this->db->fetchOne(
            "SELECT 
                SUM(CASE WHEN ultimo_acceso_en >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as activos_7d,
                SUM(CASE WHEN ultimo_acceso_en >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as activos_30d,
                SUM(CASE WHEN ultimo_acceso_en >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as activos_90d,
                COUNT(*) as total
             FROM usuarios
             WHERE activo = 1"
        );
        return $row ?: ['activos_7d' => 0, 'activos_30d' => 0, 'activos_90d' => 0, 'total' => 0];
    }

    public function ultimoAccesoPorUsuario(): array
    {
        return $this->db->fetchAll(
            "SELECT u.nombre, u.apellidos, u.correo, u.ultimo_acceso_en, u.activo,
                    r.nombre as rol
             FROM usuarios u
             LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id
             LEFT JOIN roles r ON ur.rol_id = r.id
             ORDER BY u.ultimo_acceso_en DESC
             LIMIT 10"
        );
    }

    // ========================================================================
    // BENCHMARK POR ORGANIZACIÓN
    // ========================================================================

    public function benchmarkOrganizaciones(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                o.id, o.nombre, o.activo,
                COUNT(DISTINCT p.id) as pacientes,
                COUNT(DISTINCT pr.id) as profesionales,
                COUNT(DISTINCT c.id) as citas
             FROM organizaciones o
             LEFT JOIN pacientes p ON o.id = p.organizacion_id
             LEFT JOIN profesionales pr ON o.id = pr.organizacion_id
             LEFT JOIN citas c ON o.id = c.organizacion_id
             GROUP BY o.id, o.nombre, o.activo
             ORDER BY citas DESC"
        );
    }

    public function ratioPacientesPorProfesional(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                o.nombre,
                COUNT(DISTINCT p.id) as pacientes,
                COUNT(DISTINCT pr.id) as profesionales,
                CASE 
                    WHEN COUNT(DISTINCT pr.id) > 0 
                    THEN ROUND(COUNT(DISTINCT p.id) / COUNT(DISTINCT pr.id), 1)
                    ELSE 0
                END as ratio
             FROM organizaciones o
             LEFT JOIN pacientes p ON o.id = p.organizacion_id
             LEFT JOIN profesionales pr ON o.id = pr.organizacion_id
             GROUP BY o.id, o.nombre
             HAVING profesionales > 0
             ORDER BY ratio DESC"
        );
    }

    // ========================================================================
    // OPERATIVA DE PLATAFORMA
    // ========================================================================

    public function topProcesos(): array
    {
        return $this->db->fetchAll(
            "SELECT 'Citas agendadas' as proceso, COUNT(*) as total FROM citas
             UNION ALL
             SELECT 'Consultas realizadas', COUNT(*) FROM consultas
             UNION ALL
             SELECT 'Documentos subidos', COUNT(*) FROM documentos WHERE eliminado = 0
             UNION ALL
             SELECT 'Notificaciones enviadas', COUNT(*) FROM notificaciones WHERE estado IN ('enviado', 'entregado', 'leido')
             UNION ALL
             SELECT 'Pacientes registrados', COUNT(*) FROM pacientes
             UNION ALL
             SELECT 'Horarios configurados', COUNT(*) FROM horarios
             ORDER BY total DESC"
        );
    }

    public function tasaNoShowPorEspecialidad(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                e.nombre as especialidad,
                COUNT(c.id) as total_citas,
                SUM(CASE WHEN c.estado = 'no_asistio' THEN 1 ELSE 0 END) as no_show,
                CASE 
                    WHEN COUNT(c.id) > 0 
                    THEN ROUND(SUM(CASE WHEN c.estado = 'no_asistio' THEN 1 ELSE 0 END) / COUNT(c.id) * 100, 1)
                    ELSE 0
                END as tasa_no_show
             FROM especialidades e
             LEFT JOIN profesionales p ON e.id = p.especialidad_id
             LEFT JOIN citas c ON p.id = c.profesional_id
             WHERE e.activo = 1
             GROUP BY e.id, e.nombre
             HAVING total_citas > 0
             ORDER BY tasa_no_show DESC"
        );
    }

    public function consultasPorEstado(): array
    {
        return $this->db->fetchAll(
            "SELECT estado, COUNT(*) as total
             FROM consultas
             GROUP BY estado
             ORDER BY total DESC"
        );
    }

    public function documentosPorTipo(): array
    {
        return $this->db->fetchAll(
            "SELECT COALESCE(tipo_documento, 'otro') as tipo, COUNT(*) as total
             FROM documentos
             WHERE eliminado = 0
             GROUP BY tipo_documento
             ORDER BY total DESC
             LIMIT 10"
        );
    }

    // ========================================================================
    // KPIs RESUMEN EJECUTIVO
    // ========================================================================

    public function kpisResumen(): array
    {
        $organizaciones = $this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones");
        $organizacionesActivas = $this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE activo = 1");
        $usuarios = $this->db->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
        $profesionales = $this->db->fetchColumn("SELECT COUNT(*) FROM profesionales WHERE activo = 1");
        $pacientes = $this->db->fetchColumn("SELECT COUNT(*) FROM pacientes WHERE activo = 1");
        $citasMes = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas WHERE fecha_cita >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        $consultasMes = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM consultas cs 
             INNER JOIN citas c ON cs.cita_id = c.id 
             WHERE c.fecha_cita >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );

        return [
            'organizaciones' => (int) $organizaciones,
            'organizaciones_activas' => (int) $organizacionesActivas,
            'usuarios' => (int) $usuarios,
            'profesionales' => (int) $profesionales,
            'pacientes' => (int) $pacientes,
            'citas_mes' => (int) $citasMes,
            'consultas_mes' => (int) $consultasMes,
        ];
    }

    public function crecimientoOrganizaciones(): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
             FROM organizaciones
             WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    public function citasPorMes(): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(fecha_cita, '%Y-%m') as mes, COUNT(*) as total
             FROM citas
             WHERE fecha_cita >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    // ========================================================================
    // FASE 2: ENGAGEMENT Y RETENCIÓN
    // ========================================================================

    public function organizacionesEnRiesgo(): array
    {
        return $this->db->fetchAll(
            "SELECT o.id, o.nombre,
                    MAX(c.fecha_cita) as ultima_cita,
                    COALESCE(DATEDIFF(CURDATE(), MAX(c.fecha_cita)), 999) as dias_sin_actividad
             FROM organizaciones o
             LEFT JOIN citas c ON o.id = c.organizacion_id
             WHERE o.activo = 1
             GROUP BY o.id, o.nombre
             HAVING ultima_cita IS NULL OR dias_sin_actividad > 30
             ORDER BY dias_sin_actividad DESC"
        );
    }

    public function usuariosPorSegmentoAcceso(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                CASE 
                    WHEN ultimo_acceso_en IS NULL THEN 'Nunca accedio'
                    WHEN DATEDIFF(NOW(), ultimo_acceso_en) <= 7 THEN 'Activo (7 dias)'
                    WHEN DATEDIFF(NOW(), ultimo_acceso_en) <= 30 THEN 'Reciente (8-30 dias)'
                    WHEN DATEDIFF(NOW(), ultimo_acceso_en) <= 90 THEN 'Inactivo (31-90 dias)'
                    ELSE 'En riesgo (+90 dias)'
                END as segmento,
                COUNT(*) as total
             FROM usuarios
             WHERE activo = 1
             GROUP BY segmento
             ORDER BY FIELD(segmento, 'Activo (7 dias)', 'Reciente (8-30 dias)', 'Inactivo (31-90 dias)', 'En riesgo (+90 dias)', 'Nunca accedio')"
        );
    }

    public function alertasPlataforma(): array
    {
        $orgsRiesgo = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT o.id) FROM organizaciones o
             LEFT JOIN citas c ON o.id = c.organizacion_id
             WHERE o.activo = 1
             GROUP BY o.id
             HAVING MAX(c.fecha_cita) IS NULL OR DATEDIFF(CURDATE(), MAX(c.fecha_cita)) > 30"
        );

        $usuariosRiesgo = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM usuarios
             WHERE activo = 1 AND (ultimo_acceso_en IS NULL OR ultimo_acceso_en < DATE_SUB(NOW(), INTERVAL 90 DAY))"
        );

        $cedulasPorVencer = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM profesionales
             WHERE activo = 1 AND cedula_expira IS NOT NULL
               AND cedula_expira BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)"
        );

        return [
            'orgs_sin_actividad' => (int) $orgsRiesgo,
            'usuarios_en_riesgo' => (int) $usuariosRiesgo,
            'cedulas_por_vencer' => (int) $cedulasPorVencer,
        ];
    }

    // ========================================================================
    // FASE 2: GEOGRAFÍA
    // ========================================================================

    public function pacientesPorEstado(): array
    {
        return $this->db->fetchAll(
            "SELECT COALESCE(estado, 'Sin especificar') as estado, COUNT(*) as total
             FROM pacientes
             WHERE activo = 1
             GROUP BY estado
             ORDER BY total DESC
             LIMIT 15"
        );
    }

    public function organizacionesConUbicacion(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nombre, latitud, longitud
             FROM organizaciones
             WHERE activo = 1 AND latitud IS NOT NULL AND longitud IS NOT NULL"
        );
    }

    // ========================================================================
    // FASE 2: USO DE FEATURES Y COHORTES
    // ========================================================================

    public function usoFeaturesPorOrganizacion(): array
    {
        return $this->db->fetchAll(
            "SELECT 
                o.nombre,
                (SELECT COUNT(*) FROM citas c WHERE c.organizacion_id = o.id) as citas,
                (SELECT COUNT(*) FROM consultas cs INNER JOIN citas c2 ON cs.cita_id = c2.id WHERE c2.organizacion_id = o.id) as consultas,
                (SELECT COUNT(*) FROM pacientes p WHERE p.organizacion_id = o.id) as pacientes,
                (SELECT COUNT(*) FROM horarios h INNER JOIN profesionales pr ON h.profesional_id = pr.id WHERE pr.organizacion_id = o.id) as horarios
             FROM organizaciones o
             WHERE o.activo = 1
             ORDER BY citas DESC"
        );
    }

    public function cohortesOrganizaciones(): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
             FROM organizaciones
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    public function usuariosRegistradosPorMes(): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
             FROM usuarios
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }
}
