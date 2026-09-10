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

    // ========================================================================
    // BUSQUEDA Y LISTADO
    // ========================================================================

    public function search(int $organizacionId, string $q = '', int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT id, nombre, apellidos, correo, telefono, fecha_nacimiento,
                       genero, tipo_sangre, activo, creado_en
                FROM pacientes
                WHERE organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE ? OR apellidos LIKE ? OR correo LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY creado_en DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function countSearch(int $organizacionId, string $q = ''): int
    {
        $sql = "SELECT COUNT(*) FROM pacientes WHERE organizacion_id = ?";
        $params = [$organizacionId];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE ? OR apellidos LIKE ? OR correo LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM pacientes WHERE id = ?",
            [$id]
        );
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
