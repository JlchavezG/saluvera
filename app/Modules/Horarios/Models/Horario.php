<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Horario
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ISO: 1=Lunes ... 7=Domingo
    public static function dias(): array
    {
        return [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves',
            5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo',
        ];
    }

    public static function nombreDia(int $dia): string
    {
        $d = self::dias();
        return $d[$dia] ?? ('Dia ' . $dia);
    }

    // ========================================================================
    // CONSULTAS
    // ========================================================================

    public function searchByProf(int $profId): array
    {
        return $this->db->fetchAll(
            "SELECT h.*, c.nombre as consultorio_nombre
             FROM horarios h
             LEFT JOIN consultorios c ON h.consultorio_id = c.id
             WHERE h.profesional_id = ?
             ORDER BY h.dia_semana ASC, h.hora_inicio ASC",
            [$profId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT h.*, c.nombre as consultorio_nombre,
                    u.nombre as prof_nombre, u.apellidos as prof_apellidos
             FROM horarios h
             LEFT JOIN consultorios c ON h.consultorio_id = c.id
             INNER JOIN profesionales p ON h.profesional_id = p.id
             INNER JOIN usuarios u ON p.usuario_id = u.id
             WHERE h.id = ?",
            [$id]
        );
    }

    // ========================================================================
    // CRUD
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('horarios', [
            'organizacion_id' => $d['organizacion_id'],
            'profesional_id' => $d['profesional_id'],
            'consultorio_id' => $d['consultorio_id'] ?? null,
            'dia_semana' => $d['dia_semana'],
            'hora_inicio' => $d['hora_inicio'],
            'hora_fin' => $d['hora_fin'],
            'es_recurrente' => $d['es_recurrente'] ?? 1,
            'valido_desde' => $d['valido_desde'] ?? null,
            'valido_hasta' => $d['valido_hasta'] ?? null,
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('horarios', $d, 'id = ?', [$id]);
    }

    public function setActive(int $id, int $activo): int
    {
        return $this->db->update('horarios', [
            'activo' => $activo,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->query("DELETE FROM horarios WHERE id = ?", [$id])->rowCount();
    }

    // ========================================================================
    // VALIDACION DE DISPONIBILIDAD (usada al agendar)
    // ========================================================================

    public function tieneHorariosActivos(int $profId): bool
    {
        $c = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM horarios WHERE profesional_id = ? AND activo = 1",
            [$profId]
        );
        return (int) $c > 0;
    }

    public function franjasActivasDeFecha(int $profId, string $fecha): array
    {
        $dia = (int) date('N', strtotime($fecha));

        return $this->db->fetchAll(
            "SELECT * FROM horarios
             WHERE profesional_id = ? AND dia_semana = ? AND activo = 1
               AND (es_recurrente = 1
                    OR ((valido_desde IS NULL OR valido_desde <= ?)
                        AND (valido_hasta IS NULL OR valido_hasta >= ?)))
             ORDER BY hora_inicio ASC",
            [$profId, $dia, $fecha, $fecha]
        );
    }

    // Si el profesional NO tiene horarios configurados, no se restringe (true).
    // Si tiene, la cita debe caer contenida en alguna franja del dia.
    public function dentroDeHorario(int $profId, string $fecha, string $horaInicio, string $horaFin): bool
    {
        if (!$this->tieneHorariosActivos($profId)) {
            return true;
        }

        $franjas = $this->franjasActivasDeFecha($profId, $fecha);

        if (empty($franjas)) {
            return false;
        }

        $ini = strtotime($horaInicio);
        $fin = strtotime($horaFin);

        foreach ($franjas as $f) {
            $fIni = strtotime($f['hora_inicio']);
            $fFin = strtotime($f['hora_fin']);
            if ($ini >= $fIni && $fin <= $fFin) {
                return true;
            }
        }

        return false;
    }
}
