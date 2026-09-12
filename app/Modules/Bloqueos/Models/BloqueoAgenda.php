<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class BloqueoAgenda
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseSelect(): string
    {
        return "SELECT b.*,
                       u.nombre as prof_nombre, u.apellidos as prof_apellidos,
                       o.nombre as organizacion_nombre
                FROM bloqueos_agenda b
                INNER JOIN profesionales p ON b.profesional_id = p.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                INNER JOIN organizaciones o ON b.organizacion_id = o.id";
    }

    // ========================================================================
    // CONSULTAS
    // ========================================================================

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne($this->baseSelect() . " WHERE b.id = ?", [$id]);
    }

    public function searchByProf(int $profesionalId, int $limit = 200, int $offset = 0): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             WHERE b.profesional_id = ?
             ORDER BY (b.fecha_fin < CURDATE()), b.fecha_inicio ASC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
            [$profesionalId]
        );
    }

    public function searchByOrg(int $orgId, int $limit = 200, int $offset = 0): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             WHERE b.organizacion_id = ?
             ORDER BY (b.fecha_fin < CURDATE()), b.fecha_inicio ASC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
            [$orgId]
        );
    }

    public function searchGlobal(int $limit = 200, int $offset = 0): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . "
             ORDER BY (b.fecha_fin < CURDATE()), b.fecha_inicio ASC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset
        );
    }

    // ========================================================================
    // VALIDACION DE DISPONIBILIDAD (usada al agendar citas)
    // ========================================================================

    public function hayBloqueo(int $profesionalId, string $fecha): bool
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM bloqueos_agenda
             WHERE profesional_id = ? AND activo = 1
               AND ? BETWEEN fecha_inicio AND fecha_fin",
            [$profesionalId, $fecha]
        );

        return (int) $count > 0;
    }

    public function bloqueoDeFecha(int $profesionalId, string $fecha): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM bloqueos_agenda
             WHERE profesional_id = ? AND activo = 1
               AND ? BETWEEN fecha_inicio AND fecha_fin
             LIMIT 1",
            [$profesionalId, $fecha]
        );
    }

    // ========================================================================
    // CRUD
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('bloqueos_agenda', [
            'organizacion_id' => $d['organizacion_id'],
            'profesional_id' => $d['profesional_id'],
            'fecha_inicio' => $d['fecha_inicio'],
            'fecha_fin' => $d['fecha_fin'],
            'motivo' => $d['motivo'] ?? null,
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('bloqueos_agenda', $d, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->query("DELETE FROM bloqueos_agenda WHERE id = ?", [$id])->rowCount();
    }

    // ========================================================================
    // BLOQUEOS PROXIMOS (para mostrar en agenda)
    // ========================================================================

    public function proximosActivos(int $orgId = 0, int $profesionalId = 0, int $limit = 30): array
    {
        $sql = $this->baseSelect() . " WHERE b.activo = 1 AND b.fecha_fin >= CURDATE()";
        $params = [];

        if ($orgId > 0) {
            $sql .= " AND b.organizacion_id = ?";
            $params[] = $orgId;
        }
        if ($profesionalId > 0) {
            $sql .= " AND b.profesional_id = ?";
            $params[] = $profesionalId;
        }

        $sql .= " ORDER BY b.fecha_inicio ASC LIMIT " . (int) $limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function mapaPorProfesional(int $orgId): array
    {
        if ($orgId <= 0) {
            return [];
        }

        $rows = $this->db->fetchAll(
            "SELECT profesional_id, fecha_inicio, fecha_fin, motivo
             FROM bloqueos_agenda
             WHERE organizacion_id = ? AND activo = 1 AND fecha_fin >= CURDATE()
             ORDER BY fecha_inicio",
            [$orgId]
        );

        $mapa = [];
        foreach ($rows as $r) {
            $mapa[(int) $r['profesional_id']][] = [
                'inicio' => $r['fecha_inicio'],
                'fin' => $r['fecha_fin'],
                'motivo' => $r['motivo'],
            ];
        }

        return $mapa;
    }

    public function profesionalesDeOrg(int $orgId): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, u.nombre, u.apellidos
             FROM profesionales p
             INNER JOIN usuarios u ON p.usuario_id = u.id
             WHERE p.organizacion_id = ? AND p.activo = 1
             ORDER BY u.nombre",
            [$orgId]
        );
    }
}
