<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Consulta
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function baseSelect(): string
    {
        return "SELECT c.*,
                       p.nombre as paciente_nombre, p.apellidos as paciente_apellidos,
                       u.nombre as prof_nombre, u.apellidos as prof_apellidos,
                       o.nombre as organizacion_nombre
                FROM consultas c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN profesionales pr ON c.profesional_id = pr.id
                INNER JOIN usuarios u ON pr.usuario_id = u.id
                INNER JOIN organizaciones o ON c.organizacion_id = o.id";
    }

    // ========================================================================
    // CONSULTAS
    // ========================================================================

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne($this->baseSelect() . " WHERE c.id = ?", [$id]);
    }

    public function findByCita(int $citaId): ?array
    {
        return $this->db->fetchOne(
            $this->baseSelect() . " WHERE c.cita_id = ? LIMIT 1",
            [$citaId]
        );
    }

    public function create(array $d): int
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('consultas', [
            'organizacion_id' => $d['organizacion_id'],
            'paciente_id' => $d['paciente_id'],
            'profesional_id' => $d['profesional_id'],
            'cita_id' => $d['cita_id'] ?? null,
            'fecha_consulta' => $d['fecha_consulta'] ?? $now,
            'motivo' => $d['motivo'] ?? null,
            'subjetivo' => $d['subjetivo'] ?? null,
            'objetivo' => $d['objetivo'] ?? null,
            'evaluacion' => $d['evaluacion'] ?? null,
            'plan' => $d['plan'] ?? null,
            'signos_vitales' => $d['signos_vitales'] ?? null,
            'exploracion_fisica' => $d['exploracion_fisica'] ?? null,
            'evolucion' => $d['evolucion'] ?? null,
            'notas_privadas' => $d['notas_privadas'] ?? null,
            'estado' => $d['estado'] ?? 'borrador',
            'creado_en' => $now,
            'actualizado_en' => $now,
            'creado_por' => $d['creado_por'] ?? null,
        ]);
    }

    public function update(int $id, array $d, int $userId): int
    {
        $d['actualizado_en'] = date('Y-m-d H:i:s');
        $d['actualizado_por'] = $userId;

        return $this->db->update('consultas', $d, 'id = ?', [$id]);
    }

    public function setEstado(int $id, string $estado, int $userId): int
    {
        return $this->update($id, ['estado' => $estado], $userId);
    }

    // ========================================================================
    // SIGNOS VITALES (JSON)
    // ========================================================================

    public static function vitalesArray(?string $json): array
    {
        if (empty($json)) {
            return [];
        }

        $d = json_decode($json, true);
        return is_array($d) ? $d : [];
    }

    public static function vitalesJson(array $v): ?string
    {
        $limpio = array_filter($v, function ($x) {
            return $x !== null && $x !== '';
        });

        return empty($limpio) ? null : json_encode($limpio);
    }

    // ========================================================================
    // DIAGNOSTICOS (sin borrado fisico: se cambia estado)
    // ========================================================================

    public function diagnosticos(int $consultaId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM diagnosticos WHERE consulta_id = ? ORDER BY es_principal DESC, id",
            [$consultaId]
        );
    }

    public function agregarDiagnostico(array $d): int
    {
        return $this->db->insert('diagnosticos', [
            'consulta_id' => $d['consulta_id'],
            'paciente_id' => $d['paciente_id'],
            'organizacion_id' => $d['organizacion_id'],
            'codigo_diagnostico' => $d['codigo_diagnostico'] ?? null,
            'nombre_diagnostico' => $d['nombre_diagnostico'],
            'descripcion' => $d['descripcion'] ?? null,
            'es_principal' => $d['es_principal'] ?? 0,
            'es_cronico' => $d['es_cronico'] ?? 0,
            'fecha_inicio' => $d['fecha_inicio'] ?? null,
            'estado' => $d['estado'] ?? 'activo',
            'creado_en' => date('Y-m-d H:i:s'),
            'creado_por' => $d['creado_por'] ?? null,
        ]);
    }

    public function actualizarDiagnostico(int $id, array $d): int
    {
        return $this->db->update('diagnosticos', $d, 'id = ?', [$id]);
    }

    // ========================================================================
    // TRATAMIENTOS (sin borrado fisico: se cambia estado)
    // ========================================================================

    public function tratamientos(int $consultaId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM tratamientos WHERE consulta_id = ? ORDER BY id",
            [$consultaId]
        );
    }

    public function agregarTratamiento(array $d): int
    {
        return $this->db->insert('tratamientos', [
            'consulta_id' => $d['consulta_id'],
            'paciente_id' => $d['paciente_id'],
            'organizacion_id' => $d['organizacion_id'],
            'nombre_tratamiento' => $d['nombre_tratamiento'],
            'descripcion' => $d['descripcion'] ?? null,
            'dosis' => $d['dosis'] ?? null,
            'frecuencia' => $d['frecuencia'] ?? null,
            'duracion' => $d['duracion'] ?? null,
            'via_administracion' => $d['via_administracion'] ?? null,
            'instrucciones' => $d['instrucciones'] ?? null,
            'fecha_inicio' => $d['fecha_inicio'] ?? null,
            'fecha_fin' => $d['fecha_fin'] ?? null,
            'estado' => $d['estado'] ?? 'activo',
            'notas' => $d['notas'] ?? null,
            'creado_en' => date('Y-m-d H:i:s'),
            'creado_por' => $d['creado_por'] ?? null,
        ]);
    }

    public function actualizarTratamiento(int $id, array $d): int
    {
        return $this->db->update('tratamientos', $d, 'id = ?', [$id]);
    }

    // ========================================================================
    // CATALOGO DE MEDICAMENTOS (apoyo para tratamientos)
    // ========================================================================

    public function buscarMedicamentos(string $q, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT id, nombre, nombre_generico, presentacion, concentracion
             FROM medicamentos
             WHERE activo = 1 AND (nombre LIKE ? OR nombre_generico LIKE ?)
             ORDER BY nombre
             LIMIT " . (int) $limit,
            ['%' . $q . '%', '%' . $q . '%']
        );
    }
}
