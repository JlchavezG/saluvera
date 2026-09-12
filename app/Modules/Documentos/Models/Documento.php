<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Documento
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // TIPOS DE DOCUMENTO
    // ========================================================================

    public static function tipos(): array
    {
        return [
            'resultado_laboratorio' => 'Resultado de laboratorio',
            'imagen' => 'Imagen medica',
            'informe' => 'Informe medico',
            'externo' => 'Documento externo',
            'administrativo' => 'Administrativo',
            'otro' => 'Otro',
        ];
    }

    public static function nombreTipo(string $tipo): string
    {
        $t = self::tipos();
        return $t[$tipo] ?? ucfirst($tipo);
    }

    public static function iconoPorMime(string $mime): string
    {
        if (strpos($mime, 'image/') === 0) return 'image';
        if ($mime === 'application/pdf') return 'pdf';
        if (strpos($mime, 'word') !== false || strpos($mime, 'officedocument') !== false) return 'doc';
        if (strpos($mime, 'sheet') !== false || strpos($mime, 'excel') !== false) return 'xls';
        return 'file';
    }

    public static function puedeSubir(array $file): ?string
    {
        $err = $file['error'] ?? UPLOAD_ERR_INI_SIZE;

        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return 'El archivo supera el tamano maximo permitido.';
        }
        if ($err !== UPLOAD_ERR_OK) {
            return 'No se pudo subir el archivo (error ' . $err . ').';
        }

        $size = (int) ($file['size'] ?? 0);
        $maxBytes = 10 * 1024 * 1024; // 10 MB

        if ($size <= 0) {
            return 'El archivo esta vacio.';
        }
        if ($size > $maxBytes) {
            return 'El archivo supera 10 MB.';
        }

        $extBlacklist = ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'js', 'html', 'htm', 'svg'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if ($ext === '' || in_array($ext, $extBlacklist, true)) {
            return 'Extension no permitida: ' . $ext;
        }

        $allowedMimes = [
            'application/pdf',
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];

        $mime = $file['type'] ?? '';
        if (!in_array($mime, $allowedMimes, true)) {
            return 'Tipo de archivo no permitido: ' . $mime;
        }

        return null;
    }

    // ========================================================================
    // CREACION Y RUTA FISICA
    // ========================================================================

    public function guardarArchivo(array $file, int $orgId, int $pacienteId): ?string
    {
        $base = dirname(__DIR__, 2) . '/public/uploads/documentos';
        $dir = $base . '/' . $orgId . '/' . $pacienteId;

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return null;
            }
        }

        // Proteger con .htaccess (servir solo via PHP)
        $htaccess = $base . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $token = bin2hex(random_bytes(12));
        $nombre = $token . '.' . $ext;
        $ruta = $dir . '/' . $nombre;

        if (!move_uploaded_file($file['tmp_name'], $ruta)) {
            return null;
        }

        return '/uploads/documentos/' . $orgId . '/' . $pacienteId . '/' . $nombre;
    }

    public function crear(array $d): int
    {
        return $this->db->insert('documentos', [
            'organizacion_id' => $d['organizacion_id'],
            'paciente_id' => $d['paciente_id'],
            'consulta_id' => $d['consulta_id'] ?? null,
            'tipo_documento' => $d['tipo_documento'],
            'titulo' => $d['titulo'],
            'descripcion' => $d['descripcion'] ?? null,
            'nombre_archivo' => $d['nombre_archivo'],
            'ruta_archivo' => $d['ruta_archivo'],
            'tamano_archivo' => $d['tamano_archivo'] ?? null,
            'tipo_mime' => $d['tipo_mime'] ?? null,
            'subido_en' => date('Y-m-d H:i:s'),
            'subido_por' => $d['subido_por'] ?? null,
            'eliminado' => 0,
            'creado_en' => date('Y-m-d H:i:s'),
        ]);
    }

    public function eliminarLogico(int $id, int $userId): int
    {
        return $this->db->update('documentos', [
            'eliminado' => 1,
            'eliminado_en' => date('Y-m-d H:i:s'),
            'eliminado_por' => $userId,
        ], 'id = ?', [$id]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT d.*, u.nombre as subio_nombre, u.apellidos as subio_apellidos
             FROM documentos d
             LEFT JOIN usuarios u ON d.subido_por = u.id
             WHERE d.id = ?",
            [$id]
        );
    }

    public function findByPaciente(int $pacienteId, int $orgId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT d.*, u.nombre as subio_nombre, u.apellidos as subio_apellidos
             FROM documentos d
             LEFT JOIN usuarios u ON d.subido_por = u.id
             WHERE d.paciente_id = ? AND d.organizacion_id = ? AND d.eliminado = 0
             ORDER BY d.subido_en DESC
             LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
            [$pacienteId, $orgId]
        );
    }

    public function countByPaciente(int $pacienteId, int $orgId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM documentos
             WHERE paciente_id = ? AND organizacion_id = ? AND eliminado = 0",
            [$pacienteId, $orgId]
        );
    }

    public function consultasDelPaciente(int $pacienteId, int $orgId): array
    {
        return $this->db->fetchAll(
            "SELECT id, fecha_consulta, motivo FROM consultas
             WHERE paciente_id = ? AND organizacion_id = ?
             ORDER BY fecha_consulta DESC LIMIT 100",
            [$pacienteId, $orgId]
        );
    }

    public function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / (1024 * 1024), 2) . ' MB';
    }
}
