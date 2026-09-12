<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Paciente
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Filtro de alcance: si hay profesionalId, solo sus pacientes
    private function scopeProfesional(?int $profesionalId): array
    {
        if ($profesionalId === null) {
            return ['', []];
        }

        $sql = " AND (p.profesional_id = ? OR p.id IN (
                    SELECT paciente_id FROM citas WHERE profesional_id = ?
                ))";
        return [$sql, [$profesionalId, $profesionalId]];
    }

    // ========================================================================
    // BUSQUEDA POR ORGANIZACION (con alcance por profesional)
    // ========================================================================

    public function search(int $organizacionId, string $q = '', int $limit = 10, int $offset = 0, ?int $profesionalId = null): array
    {
        $sql = "SELECT p.id, p.nombre, p.apellidos, p.correo, p.telefono, p.fecha_nacimiento,
                       p.genero, p.tipo_sangre, p.activo, p.creado_en, p.profesional_id,
                       o.nombre as organizacion_nombre
                FROM pacientes p
                INNER JOIN organizaciones o ON p.organizacion_id = o.id
                WHERE p.organizacion_id = ?";
        $params = [$organizacionId];

        [$scopeSql, $scopeParams] = $this->scopeProfesional($profesionalId);
        $sql .= $scopeSql;
        $params = array_merge($params, $scopeParams);

        if ($q !== '') {
            $sql .= " AND (p.nombre LIKE ? OR p.apellidos LIKE ? OR p.correo LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY p.creado_en DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearch(int $organizacionId, string $q = '', ?int $profesionalId = null): int
    {
        $sql = "SELECT COUNT(*) FROM pacientes p
                INNER JOIN organizaciones o ON p.organizacion_id = o.id
                WHERE p.organizacion_id = ?";
        $params = [$organizacionId];

        [$scopeSql, $scopeParams] = $this->scopeProfesional($profesionalId);
        $sql .= $scopeSql;
        $params = array_merge($params, $scopeParams);

        if ($q !== '') {
            $sql .= " AND (p.nombre LIKE ? OR p.apellidos LIKE ? OR p.correo LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    // ========================================================================
    // BUSQUEDA GLOBAL (superadmin: todas las organizaciones)
    // ========================================================================

    public function searchGlobal(string $q = '', int $orgId = 0, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.id, p.nombre, p.apellidos, p.correo, p.telefono, p.fecha_nacimiento,
                       p.genero, p.tipo_sangre, p.activo, p.creado_en, p.profesional_id,
                       o.nombre as organizacion_nombre
                FROM pacientes p
                INNER JOIN organizaciones o ON p.organizacion_id = o.id
                WHERE 1=1";
        $params = [];

        if ($orgId > 0) {
            $sql .= " AND p.organizacion_id = ?";
            $params[] = $orgId;
        }

        if ($q !== '') {
            $sql .= " AND (p.nombre LIKE ? OR p.apellidos LIKE ? OR p.correo LIKE ? OR o.nombre LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY p.creado_en DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearchGlobal(string $q = '', int $orgId = 0): int
    {
        $sql = "SELECT COUNT(*) FROM pacientes p
                INNER JOIN organizaciones o ON p.organizacion_id = o.id
                WHERE 1=1";
        $params = [];

        if ($orgId > 0) {
            $sql .= " AND p.organizacion_id = ?";
            $params[] = $orgId;
        }

        if ($q !== '') {
            $sql .= " AND (p.nombre LIKE ? OR p.apellidos LIKE ? OR p.correo LIKE ? OR o.nombre LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function getOrganizaciones(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nombre FROM organizaciones WHERE activo = 1 ORDER BY nombre"
        );
    }

    // ========================================================================
    // CONSULTAS DIRECTAS (sin join, sin ambiguedad)
    // ========================================================================

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM pacientes WHERE id = ?",
            [$id]
        );
    }

    public function esPacienteDe(int $pacienteId, int $profesionalId): bool
    {
        $paciente = $this->findById($pacienteId);

        if ($paciente !== null && (int) ($paciente['profesional_id'] ?? 0) === $profesionalId) {
            return true;
        }

        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM citas WHERE paciente_id = ? AND profesional_id = ?",
            [$pacienteId, $profesionalId]
        );

        return (int) $count > 0;
    }

    // ========================================================================
    // CREACION Y ACTUALIZACION
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('pacientes', [
            'organizacion_id' => $d['organizacion_id'],
            'profesional_id' => $d['profesional_id'] ?? null,
            'nombre' => $d['nombre'],
            'apellidos' => $d['apellidos'],
            'fecha_nacimiento' => $d['fecha_nacimiento'] ?? null,
            'genero' => $d['genero'] ?? null,
            'correo' => $d['correo'] ?? null,
            'telefono' => $d['telefono'] ?? null,
            'telefono_secundario' => $d['telefono_secundario'] ?? null,
            'direccion' => $d['direccion'] ?? null,
            'ciudad' => $d['ciudad'] ?? null,
            'estado' => $d['estado'] ?? null,
            'codigo_postal' => $d['codigo_postal'] ?? null,
            'ocupacion' => $d['ocupacion'] ?? null,
            'estado_civil' => $d['estado_civil'] ?? null,
            'tipo_sangre' => $d['tipo_sangre'] ?? null,
            'notas' => $d['notas'] ?? null,
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('pacientes', $d, 'id = ?', [$id]);
    }

    public function setActive(int $id, int $activo): int
    {
        return $this->db->update('pacientes', [
            'activo' => $activo,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    // ========================================================================
    // UTILIDADES
    // ========================================================================

    public function countActive(int $organizacionId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM pacientes WHERE organizacion_id = ? AND activo = 1",
            [$organizacionId]
        );
    }

    public function setPortalToken(int $id, string $token, string $expira): int
    {
        return $this->db->update('pacientes', [
            'portal_token' => $token,
            'portal_token_expira' => $expira,
        ], 'id = ?', [$id]);
    }

    public function nombreCompleto(array $paciente): string
    {
        return trim($paciente['nombre'] . ' ' . $paciente['apellidos']);
    }

    public function edad(?string $fechaNacimiento): ?int
    {
        if (empty($fechaNacimiento)) {
            return null;
        }

        try {
            $fecha = new DateTime($fechaNacimiento);
            return $fecha->diff(new DateTime('now'))->y;
        } catch (Exception $e) {
            return null;
        }
    }
}
