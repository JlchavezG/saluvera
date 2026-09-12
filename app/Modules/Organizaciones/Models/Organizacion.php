<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Organizacion
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM organizaciones WHERE id = ?",
            [$id]
        );
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.organizacion_id = o.id) as total_usuarios,
                    (SELECT COUNT(*) FROM profesionales p WHERE p.organizacion_id = o.id AND p.activo = 1) as total_profesionales,
                    (SELECT COUNT(*) FROM pacientes pa WHERE pa.organizacion_id = o.id AND pa.activo = 1) as total_pacientes,
                    (SELECT COUNT(*) FROM citas c WHERE c.organizacion_id = o.id) as total_citas
             FROM organizaciones o
             ORDER BY o.creado_en DESC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset
        );
    }

    public function countAll(): int
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones");
    }

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('organizaciones', [
            'nombre' => $d['nombre'],
            'slug' => $this->generarSlug($d['nombre']),
            'correo' => $d['correo'] ?? null,
            'telefono' => $d['telefono'] ?? null,
            'direccion' => $d['direccion'] ?? null,
            'latitud' => $d['latitud'] ?? null,
            'longitud' => $d['longitud'] ?? null,
            'zona_horaria' => $d['zona_horaria'] ?? 'America/Mexico_City',
            'plan_suscripcion' => $d['plan_suscripcion'] ?? 'free',
            'estado_suscripcion' => $d['estado_suscripcion'] ?? 'trial',
            'activo' => 1,
            'creado_en' => $now,
            'actualizado_en' => $now,
        ]);
    }

    public function update(int $id, array $d): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        return $this->db->update('organizaciones', $d, 'id = ?', [$id]);
    }

    public function setActive(int $id, int $activo): int
    {
        return $this->db->update('organizaciones', [
            'activo' => $activo,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function countDependencias(int $id): array
    {
        return [
            'usuarios' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE organizacion_id = ?", [$id]),
            'profesionales' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM profesionales WHERE organizacion_id = ? AND activo = 1", [$id]),
            'pacientes' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM pacientes WHERE organizacion_id = ? AND activo = 1", [$id]),
            'consultorios' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM consultorios WHERE organizacion_id = ? AND activo = 1", [$id]),
            'citas' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM citas WHERE organizacion_id = ?", [$id]),
        ];
    }

    private function generarSlug(string $nombre): string
    {
        $base = strtolower(trim($nombre));
        $base = str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], $base);
        $base = preg_replace('/[^a-z0-9\s-]/', '', $base);
        $base = preg_replace('/[\s-]+/', '-', $base);
        $base = trim($base, '-');

        $slug = $base;
        $i = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE slug = ?", [$slug]) > 0) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public static function zonasHorarias(): array
    {
        return [
            'America/Mexico_City' => 'Mexico (Centro)',
            'America/Chihuahua' => 'Mexico (Pacifico)',
            'America/Mazatlan' => 'Mexico (Mazatlan)',
            'America/Tijuana' => 'Mexico (Tijuana)',
            'America/Cancun' => 'Mexico (Cancun)',
            'America/Monterrey' => 'Mexico (Monterrey)',
            'America/Guatemala' => 'Guatemala',
            'America/Bogota' => 'Colombia (Bogota)',
            'America/Lima' => 'Peru (Lima)',
            'America/Santiago' => 'Chile (Santiago)',
            'America/Buenos_Aires' => 'Argentina (Buenos Aires)',
            'America/Caracas' => 'Venezuela (Caracas)',
            'America/Costa_Rica' => 'Costa Rica',
            'America/Panama' => 'Panama',
        ];
    }

    public static function planes(): array
    {
        return [
            'free' => 'Gratis',
            'professional' => 'Profesional',
            'clinic' => 'Clinica / Hospital',
        ];
    }

    public static function estados(): array
    {
        return [
            'trial' => 'Prueba',
            'active' => 'Activa',
            'suspended' => 'Suspendida',
            'cancelled' => 'Cancelada',
        ];
    }
}
