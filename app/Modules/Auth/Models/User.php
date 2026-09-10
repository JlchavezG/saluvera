<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseSelect(): string
    {
        return "SELECT u.id, u.organizacion_id, u.correo, u.contrasena_hash,
                       u.nombre, u.apellidos, u.telefono, u.avatar, u.activo,
                       u.correo_verificado_en, u.ultimo_acceso_en, u.ip_ultimo_acceso,
                       u.creado_en, u.actualizado_en,
                       ur.organizacion_id as rol_organizacion_id,
                       r.id as rol_id, r.nombre as rol_nombre, r.slug as rol_slug
                FROM usuarios u
                INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
                INNER JOIN roles r ON ur.rol_id = r.id";
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . " WHERE u.id = ? ORDER BY ur.id LIMIT 1",
            [$id]
        );
    }

    public function findByEmail(string $correo): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . " WHERE u.correo = ? ORDER BY ur.id LIMIT 1",
            [$correo]
        );
    }

    public function existsByEmail(string $correo): bool
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM usuarios WHERE correo = ?",
            [$correo]
        );
        return (int) $count > 0;
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');

        $userId = $this->db->insert('usuarios', [
            'organizacion_id' => $data['organizacion_id'],
            'correo' => $data['correo'],
            'contrasena_hash' => $data['contrasena_hash'],
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'telefono' => $data['telefono'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'activo' => $data['activo'] ?? 1,
            'dos_factores_activo' => $data['dos_factores_activo'] ?? 0,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);

        $this->db->insert('usuario_roles', [
            'usuario_id' => $userId,
            'rol_id' => $data['rol_id'],
            'organizacion_id' => $data['organizacion_id'],
            'otorgado_en' => $now,
            'otorgado_por' => $data['otorgado_por'] ?? null,
        ]);

        return $userId;
    }

    public function update(int $id, array $data): int
    {
        $data['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('usuarios', $data, 'id = ?', [$id]);
    }

    public function updatePassword(int $id, string $contrasenaHash): int
    {
        return $this->db->update('usuarios', [
            'contrasena_hash' => $contrasenaHash,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function updateLastLogin(int $id, ?string $ip = null): int
    {
        $data = ['ultimo_acceso_en' => date('Y-m-d H:i:s')];

        if ($ip !== null) {
            $data['ip_ultimo_acceso'] = $ip;
        }

        return $this->db->update('usuarios', $data, 'id = ?', [$id]);
    }

    public function attemptLogin(string $correo, string $password): array
    {
        $user = $this->findByEmail($correo);

        if ($user === null) {
            return [
                'success' => false,
                'error' => 'Credenciales invalidas',
            ];
        }

        if ((int) $user['activo'] !== 1) {
            return [
                'success' => false,
                'error' => 'Tu cuenta no esta activa. Contacta al administrador.',
            ];
        }

        if (!Security::verifyPassword($password, $user['contrasena_hash'])) {
            return [
                'success' => false,
                'error' => 'Credenciales invalidas',
            ];
        }

        if (Security::needsRehash($user['contrasena_hash'])) {
            $this->updatePassword($user['id'], Security::hashPassword($password));
        }

        $this->updateLastLogin($user['id']);

        unset($user['contrasena_hash']);

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public function getPermissions(int $roleId): array
    {
        $permissions = $this->db->fetchAll(
            "SELECT p.slug
             FROM permisos p
             INNER JOIN permiso_rol pr ON p.id = pr.permiso_id
             WHERE pr.rol_id = ?",
            [$roleId]
        );

        return array_column($permissions, 'slug');
    }

    public function hasPermission(int $roleId, string $permissionSlug): bool
    {
        $permissions = $this->getPermissions($roleId);
        return in_array($permissionSlug, $permissions, true);
    }

    public function countByRole(int $roleId): int
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM usuario_roles WHERE rol_id = ?",
            [$roleId]
        );
        return (int) $count;
    }

    public function countActive(): int
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM usuarios WHERE activo = 1"
        );
        return (int) $count;
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.nombre, u.apellidos, u.correo, u.activo,
                    u.ultimo_acceso_en, u.creado_en, r.nombre as rol_nombre
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             ORDER BY u.creado_en DESC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset
        );
    }
}
