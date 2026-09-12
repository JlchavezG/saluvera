<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Profesional
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseSelect(): string
    {
        return "SELECT p.id, p.usuario_id, p.organizacion_id, p.especialidad_id,
                       p.numero_cedula, p.cedula_expira, p.duracion_consulta,
                       p.costo_consulta, p.activo, p.creado_en,
                       u.nombre, u.apellidos, u.correo, u.telefono,
                       e.nombre as especialidad_nombre, o.nombre as organizacion_nombre
                FROM profesionales p
                INNER JOIN organizaciones o ON p.organizacion_id = o.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN especialidades e ON p.especialidad_id = e.id";
    }

    // ========================================================================
    // LISTADO Y BUSQUEDA
    // ========================================================================

    public function search(int $organizacionId, string $q = '', int $limit = 10, int $offset = 0): array
    {
        $sql = $this->baseSelect() . " WHERE p.organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (u.nombre LIKE ? OR u.apellidos LIKE ? OR u.correo LIKE ? OR p.numero_cedula LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY p.creado_en DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearch(int $organizacionId, string $q = ''): int
    {
        $sql = "SELECT COUNT(*) FROM profesionales p
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (u.nombre LIKE ? OR u.apellidos LIKE ? OR u.correo LIKE ? OR p.numero_cedula LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . " WHERE p.id = ?",
            [$id]
        );
    }

    public function todosActivos(int $organizacionId): array
    {
        return $this->db->fetchAll(
            $this->baseSelect() . " WHERE p.organizacion_id = ? AND p.activo = 1 ORDER BY u.nombre",
            [$organizacionId]
        );
    }

// ========================================================================
// BUSQUEDA GLOBAL (superadmin: ve todas las organizaciones)
// ========================================================================

public function searchGlobal(string $q = '', int $orgId = 0, int $limit = 10, int $offset = 0): array
{
    $sql = $this->baseSelect() . " WHERE 1=1";
    $params = [];

    if ($orgId > 0) {
        $sql .= " AND p.organizacion_id = ?";
        $params[] = $orgId;
    }

    if ($q !== '') {
        $sql .= " AND (u.nombre LIKE ? OR u.apellidos LIKE ? OR u.correo LIKE ? OR p.numero_cedula LIKE ? OR o.nombre LIKE ?)";
        $like = '%' . $q . '%';
        $params[] = $like;
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
    $sql = "SELECT COUNT(*) FROM profesionales p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            INNER JOIN organizaciones o ON p.organizacion_id = o.id
            WHERE 1=1";
    $params = [];

    if ($orgId > 0) {
        $sql .= " AND p.organizacion_id = ?";
        $params[] = $orgId;
    }

    if ($q !== '') {
        $sql .= " AND (u.nombre LIKE ? OR u.apellidos LIKE ? OR u.correo LIKE ? OR p.numero_cedula LIKE ? OR o.nombre LIKE ?)";
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    return (int) $this->db->fetchColumn($sql, $params);
}
    // ========================================================================
    // CREACION Y ACTUALIZACION
    // ========================================================================

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('profesionales', [
            'usuario_id' => $d['usuario_id'],
            'organizacion_id' => $d['organizacion_id'],
            'especialidad_id' => $d['especialidad_id'] ?? null,
            'numero_cedula' => $d['numero_cedula'] ?? null,
            'cedula_expira' => $d['cedula_expira'] ?? null,
            'duracion_consulta' => $d['duracion_consulta'] ?? 30,
            'costo_consulta' => $d['costo_consulta'] ?? null,
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('profesionales', $d, 'id = ?', [$id]);
    }

    public function setActive(int $id, int $activo): int
    {
        return $this->db->update('profesionales', [
            'activo' => $activo,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    // ========================================================================
    // CATALOGOS
    // ========================================================================

    public function getEspecialidades(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT id, nombre FROM especialidades ORDER BY nombre"
            );
        } catch (Exception $e) {
            return [];
        }
    }

public function getOrganizaciones(): array
{
    return $this->db->fetchAll(
        "SELECT id, nombre FROM organizaciones WHERE activo = 1 ORDER BY nombre"
    );
}
    public function existePorUsuario(int $usuarioId): bool
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM profesionales WHERE usuario_id = ?",
            [$usuarioId]
        );
        return (int) $count > 0;
    }
}
