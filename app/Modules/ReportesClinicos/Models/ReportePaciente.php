<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ReportePaciente
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listarPorPaciente(int $pacienteId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, pl.nombre as plantilla_nombre,
                    CONCAT(u.nombre, ' ', u.apellidos) as profesional_nombre
             FROM reportes_paciente r
             LEFT JOIN plantillas_reporte pl ON r.plantilla_id = pl.id
             LEFT JOIN profesionales p ON r.profesional_id = p.id
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             WHERE r.paciente_id = ?
             ORDER BY r.creado_en DESC",
            [$pacienteId]
        );
    }

    public function listarPorProfesional(int $profesionalId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, pl.nombre as plantilla_nombre,
                    CONCAT(pa.nombre, ' ', pa.apellidos) as paciente_nombre
             FROM reportes_paciente r
             LEFT JOIN plantillas_reporte pl ON r.plantilla_id = pl.id
             LEFT JOIN pacientes pa ON r.paciente_id = pa.id
             WHERE r.profesional_id = ?
             ORDER BY r.creado_en DESC",
            [$profesionalId]
        );
    }

    public function buscar(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT r.*, 
                    pl.nombre as plantilla_nombre, 
                    pl.estructura_json,
                    CONCAT(pa.nombre, ' ', pa.apellidos) as paciente_nombre,
                    pa.fecha_nacimiento, 
                    pa.genero,
                    CONCAT(u.nombre, ' ', u.apellidos) as profesional_nombre,
                    e.nombre as especialidad_nombre,
                    o.nombre as organizacion_nombre,
                    o.logo as organizacion_logo,
                    o.correo as organizacion_correo,
                    o.telefono as organizacion_telefono,
                    o.direccion as organizacion_direccion
             FROM reportes_paciente r
             LEFT JOIN plantillas_reporte pl ON r.plantilla_id = pl.id
             LEFT JOIN pacientes pa ON r.paciente_id = pa.id
             LEFT JOIN profesionales pr ON r.profesional_id = pr.id
             LEFT JOIN usuarios u ON pr.usuario_id = u.id
             LEFT JOIN especialidades e ON pr.especialidad_id = e.id
             LEFT JOIN organizaciones o ON r.organizacion_id = o.id
             WHERE r.id = ?",
            [$id]
        );
    }

    public function crear(array $datos): int
    {
        $ahora = date('Y-m-d H:i:s');

        return $this->db->insert('reportes_paciente', [
            'paciente_id' => $datos['paciente_id'],
            'profesional_id' => $datos['profesional_id'],
            'organizacion_id' => $datos['organizacion_id'],
            'plantilla_id' => $datos['plantilla_id'] ?? null,
            'titulo' => $datos['titulo'],
            'contenido_json' => json_encode($datos['contenido'] ?? [], JSON_UNESCAPED_UNICODE),
            'estado' => 'borrador',
            'compartido_portal' => 0,
            'creado_en' => $ahora,
            'actualizado_en' => $ahora,
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $ahora = date('Y-m-d H:i:s');

        $afectadas = $this->db->update('reportes_paciente', [
            'titulo' => $datos['titulo'],
            'contenido_json' => json_encode($datos['contenido'] ?? [], JSON_UNESCAPED_UNICODE),
            'actualizado_en' => $ahora,
        ], 'id = ? AND estado = "borrador"', [$id]);

        return $afectadas > 0;
    }

    public function firmar(int $id): bool
    {
        $ahora = date('Y-m-d H:i:s');

        $afectadas = $this->db->update('reportes_paciente', [
            'estado' => 'firmado',
            'firmado_en' => $ahora,
            'actualizado_en' => $ahora,
        ], 'id = ? AND estado = "borrador"', [$id]);

        return $afectadas > 0;
    }

    public function compartirPortal(int $id, bool $compartir): bool
    {
        $afectadas = $this->db->update('reportes_paciente', [
            'compartido_portal' => $compartir ? 1 : 0,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ? AND estado = "firmado"', [$id]);

        return $afectadas > 0;
    }

    public function eliminar(int $id): bool
    {
        $afectadas = $this->db->delete('reportes_paciente', 'id = ?', [$id]);
        return $afectadas > 0;
    }

    public function eliminarBorrador(int $id): bool
    {
        $afectadas = $this->db->delete('reportes_paciente', 'id = ? AND estado = "borrador"', [$id]);
        return $afectadas > 0;
    }

    public function listarCompartidosPortal(int $pacienteId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, pl.nombre as plantilla_nombre,
                    CONCAT(u.nombre, ' ', u.apellidos) as profesional_nombre,
                    e.nombre as especialidad_nombre,
                    o.nombre as organizacion_nombre
             FROM reportes_paciente r
             LEFT JOIN plantillas_reporte pl ON r.plantilla_id = pl.id
             LEFT JOIN profesionales p ON r.profesional_id = p.id
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             LEFT JOIN especialidades e ON p.especialidad_id = e.id
             LEFT JOIN organizaciones o ON r.organizacion_id = o.id
             WHERE r.paciente_id = ?
               AND r.compartido_portal = 1
               AND r.estado = 'firmado'
             ORDER BY r.firmado_en DESC, r.creado_en DESC",
            [$pacienteId]
        );
    }

    public function buscarCompartidoPortal(int $id, int $pacienteId): ?array
    {
        return $this->db->fetchOne(
            "SELECT r.*, 
                    pl.nombre as plantilla_nombre, 
                    pl.estructura_json,
                    CONCAT(pa.nombre, ' ', pa.apellidos) as paciente_nombre,
                    pa.fecha_nacimiento, 
                    pa.genero,
                    CONCAT(u.nombre, ' ', u.apellidos) as profesional_nombre,
                    e.nombre as especialidad_nombre,
                    o.nombre as organizacion_nombre,
                    o.logo as organizacion_logo,
                    o.correo as organizacion_correo,
                    o.telefono as organizacion_telefono,
                    o.direccion as organizacion_direccion
             FROM reportes_paciente r
             LEFT JOIN plantillas_reporte pl ON r.plantilla_id = pl.id
             LEFT JOIN pacientes pa ON r.paciente_id = pa.id
             LEFT JOIN profesionales pr ON r.profesional_id = pr.id
             LEFT JOIN usuarios u ON pr.usuario_id = u.id
             LEFT JOIN especialidades e ON pr.especialidad_id = e.id
             LEFT JOIN organizaciones o ON r.organizacion_id = o.id
             WHERE r.id = ?
               AND r.paciente_id = ?
               AND r.compartido_portal = 1
               AND r.estado = 'firmado'
             LIMIT 1",
            [$id, $pacienteId]
        );
    }
}
