<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Notificacion
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function tipos(): array
    {
        return [
            'recordatorio_cita' => 'Recordatorio de cita',
            'confirmacion_cita' => 'Confirmacion de cita',
            'cancelacion_cita' => 'Cancelacion de cita',
            'cita_reprogramada' => 'Cita reprogramada',
            'nuevo_documento' => 'Nuevo documento',
            'sistema' => 'Sistema',
            'otro' => 'Otro',
        ];
    }

    public static function nombreTipo(string $t): string
    {
        $mapa = self::tipos();
        return $mapa[$t] ?? ucfirst($t);
    }

    public static function estados(): array
    {
        return [
            'pendiente' => 'Pendiente',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'fallido' => 'Fallido',
            'leido' => 'Leido',
        ];
    }

    // ========================================================================
    // CREACION
    // ========================================================================

    public function crear(array $d): int
    {
        return $this->db->insert('notificaciones', [
            'organizacion_id' => $d['organizacion_id'],
            'tipo_destinatario' => $d['tipo_destinatario'] ?? 'paciente',
            'destinatario_id' => $d['destinatario_id'],
            'tipo_notificacion' => $d['tipo_notificacion'],
            'canal' => $d['canal'] ?? 'whatsapp',
            'titulo' => $d['titulo'],
            'cuerpo' => $d['cuerpo'] ?? null,
            'datos' => $d['datos'] ?? null,
            'estado' => 'pendiente',
            'creado_en' => date('Y-m-d H:i:s'),
        ]);
    }

    // ========================================================================
    // CONSULTAS
    // ========================================================================

    public function findAll(int $limit = 200, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT n.*,
                    p.nombre as pac_nombre, p.apellidos as pac_apellidos, p.telefono as pac_telefono,
                    u.nombre as usu_nombre, u.apellidos as usu_apellidos, u.telefono as usu_telefono
             FROM notificaciones n
             LEFT JOIN pacientes p ON n.tipo_destinatario = 'paciente' AND n.destinatario_id = p.id
             LEFT JOIN usuarios u ON n.tipo_destinatario = 'usuario' AND n.destinatario_id = u.id
             ORDER BY (n.estado = 'pendiente') DESC, n.creado_en DESC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset
        );
    }

    public function countPendientesTodas(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM notificaciones WHERE estado = 'pendiente'"
        );
    }

    public function findByOrg(int $orgId, int $limit = 100, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT n.*,
                    p.nombre as pac_nombre, p.apellidos as pac_apellidos, p.telefono as pac_telefono,
                    u.nombre as usu_nombre, u.apellidos as usu_apellidos, u.telefono as usu_telefono
             FROM notificaciones n
             LEFT JOIN pacientes p ON n.tipo_destinatario = 'paciente' AND n.destinatario_id = p.id
             LEFT JOIN usuarios u ON n.tipo_destinatario = 'usuario' AND n.destinatario_id = u.id
             WHERE n.organizacion_id = ?
             ORDER BY (n.estado = 'pendiente') DESC, n.creado_en DESC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
            [$orgId]
        );
    }

    public function countPendientesOrg(int $orgId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM notificaciones WHERE organizacion_id = ? AND estado = 'pendiente'",
            [$orgId]
        );
    }

    public function findByPaciente(int $pacienteId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notificaciones
             WHERE tipo_destinatario = 'paciente' AND destinatario_id = ?
             ORDER BY creado_en DESC
             LIMIT " . (int) $limit,
            [$pacienteId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM notificaciones WHERE id = ?", [$id]);
    }

    // ========================================================================
    // ESTADOS
    // ========================================================================

    public function marcar(int $id, string $estado, ?string $error = null): int
    {
        $d = ['estado' => $estado];

        if ($estado === 'enviado') $d['enviado_en'] = date('Y-m-d H:i:s');
        if ($estado === 'entregado') $d['entregado_en'] = date('Y-m-d H:i:s');
        if ($estado === 'leido') $d['leido_en'] = date('Y-m-d H:i:s');
        if ($estado === 'fallido') $d['mensaje_error'] = $error;

        return $this->db->update('notificaciones', $d, 'id = ?', [$id]);
    }

    public function marcarLeidasPaciente(int $pacienteId): int
    {
        return $this->db->update(
            'notificaciones',
            ['estado' => 'leido', 'leido_en' => date('Y-m-d H:i:s')],
            "tipo_destinatario = 'paciente' AND destinatario_id = ? AND estado IN ('pendiente','enviado','entregado')",
            [$pacienteId]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->query("DELETE FROM notificaciones WHERE id = ?", [$id])->rowCount();
    }

    // ========================================================================
    // RECORDATORIOS AUTOMATICOS (citas de manana)
    // ========================================================================

    public function existeRecordatorio(int $citaId): bool
    {
        $c = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM notificaciones
             WHERE tipo_notificacion = 'recordatorio_cita' AND datos LIKE ?",
            ['%"cita_id":' . $citaId . '%']
        );
        return (int) $c > 0;
    }

    public function generarRecordatoriosManana(int $orgId): int
    {
        $manana = date('Y-m-d', strtotime('+1 day'));

        $citas = $this->db->fetchAll(
            "SELECT c.id, c.paciente_id, c.fecha_cita, c.hora_inicio,
                    p.nombre as pac_nombre, p.apellidos as pac_apellidos,
                    o.nombre as org_nombre
             FROM citas c
             INNER JOIN pacientes p ON c.paciente_id = p.id
             INNER JOIN organizaciones o ON c.organizacion_id = o.id
             WHERE c.organizacion_id = ? AND c.fecha_cita = ?
               AND c.estado IN ('pendiente', 'confirmada')",
            [$orgId, $manana]
        );

        $creadas = 0;
        foreach ($citas as $c) {
            if ($this->existeRecordatorio((int) $c['id'])) {
                continue;
            }

            $this->crear([
                'organizacion_id' => $orgId,
                'tipo_destinatario' => 'paciente',
                'destinatario_id' => (int) $c['paciente_id'],
                'tipo_notificacion' => 'recordatorio_cita',
                'canal' => 'whatsapp',
                'titulo' => 'Recordatorio de cita',
                'cuerpo' => 'Hola ' . $c['pac_nombre'] . ', te recordamos tu cita de manana '
                    . date('d/m/Y', strtotime($manana)) . ' a las ' . substr($c['hora_inicio'], 0, 5)
                    . ' en ' . ($c['org_nombre'] ?? 'la clinica')
                    . '. Si no puedes asistir, avisanos con tiempo.',
                'datos' => json_encode(['cita_id' => (int) $c['id']]),
            ]);
            $creadas++;
        }

        return $creadas;
    }
}
