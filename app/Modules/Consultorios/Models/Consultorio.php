<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Consultorio
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // BUSQUEDA POR ORGANIZACION (usuarios normales)
    // ========================================================================

    public function search(int $organizacionId, string $q = '', int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT id, nombre, ubicacion, direccion, capacidad, latitud, longitud, activo, creado_en
                FROM consultorios
                WHERE organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE ? OR ubicacion LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY creado_en ASC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearch(int $organizacionId, string $q = ''): int
    {
        $sql = "SELECT COUNT(*) FROM consultorios WHERE organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE ? OR ubicacion LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM consultorios WHERE id = ?",
            [$id]
        );
    }

    public function todosActivos(int $organizacionId): array
    {
        return $this->db->fetchAll(
            "SELECT id, nombre, ubicacion FROM consultorios
             WHERE organizacion_id = ? AND activo = 1
             ORDER BY nombre",
            [$organizacionId]
        );
    }

    // ========================================================================
    // BUSQUEDA GLOBAL (superadmin: todas las organizaciones)
    // ========================================================================

    public function searchGlobal(string $q = '', int $orgId = 0, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT c.id, c.nombre, c.ubicacion, c.direccion, c.capacidad, c.latitud, c.longitud, c.activo, c.creado_en,
                       o.nombre as organizacion_nombre
                FROM consultorios c
                INNER JOIN organizaciones o ON c.organizacion_id = o.id
                WHERE 1=1";
        $params = [];

        if ($orgId > 0) {
            $sql .= " AND c.organizacion_id = ?";
            $params[] = $orgId;
        }

        if ($q !== '') {
            $sql .= " AND (c.nombre LIKE ? OR c.ubicacion LIKE ? OR o.nombre LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY c.creado_en ASC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearchGlobal(string $q = '', int $orgId = 0): int
    {
        $sql = "SELECT COUNT(*) FROM consultorios c
                INNER JOIN organizaciones o ON c.organizacion_id = o.id
                WHERE 1=1";
        $params = [];

        if ($orgId > 0) {
            $sql .= " AND c.organizacion_id = ?";
            $params[] = $orgId;
        }

        if ($q !== '') {
            $sql .= " AND (c.nombre LIKE ? OR c.ubicacion LIKE ? OR o.nombre LIKE ?)";
            $like = '%' . $q . '%';
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
    // CREACION Y ACTUALIZACION
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('consultorios', [
            'organizacion_id' => $d['organizacion_id'],
            'nombre' => $d['nombre'],
            'ubicacion' => $d['ubicacion'] ?? null,
            'capacidad' => $d['capacidad'] ?? null,
            'direccion' => $d['direccion'] ?? null,
            'latitud' => $d['latitud'] ?? null,
            'longitud' => $d['longitud'] ?? null,
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('consultorios', $d, 'id = ?', [$id]);
    }

    public function setActive(int $id, int $activo): int
    {
        return $this->db->update('consultorios', [
            'activo' => $activo,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function countActive(int $organizacionId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM consultorios WHERE organizacion_id = ? AND activo = 1",
            [$organizacionId]
        );
    }
}
