<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class MiPerfilController
{
    private User $usuarios;

    public function __construct()
    {
        $this->usuarios = new User();
    }

    private function db(): Database
    {
        return Database::getInstance();
    }

    private function miUsuario(): ?array
    {
        return $this->usuarios->findById(Session::getUserId());
    }

    private function miProfesional(): ?array
    {
        return $this->db()->fetchOne(
            "SELECT * FROM profesionales WHERE usuario_id = ? LIMIT 1",
            [Session::getUserId()]
        );
    }

    // ========================================================================
    // VER / EDITAR
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $user = $this->miUsuario();

        if ($user === null) {
            $response->redirectTo('/');
            return;
        }

        $prof = $this->miProfesional();
        $especialidades = [];
        $misEspecialidades = [];

        if ($prof !== null) {
            $especialidades = $this->db()->fetchAll(
                "SELECT id, nombre FROM especialidades WHERE activo = 1 ORDER BY nombre"
            );
            $rows = $this->db()->fetchAll(
                "SELECT especialidad_id FROM profesional_especialidades WHERE profesional_id = ?",
                [(int) $prof['id']]
            );
            $misEspecialidades = array_map(function ($r) { return (int) $r['especialidad_id']; }, $rows);
        }

        $content = View::render('Pages/perfil/form', [
            'user' => $user,
            'prof' => $prof,
            'especialidades' => $especialidades,
            'misEspecialidades' => $misEspecialidades,
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Mi Perfil', 'content' => $content]);
        $response->html($html);
    }

    // ========================================================================
    // GUARDAR
    // ========================================================================

    public function update(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/mi-perfil');
            return;
        }

        $user = $this->miUsuario();
        if ($user === null) {
            $response->redirectTo('/');
            return;
        }

        $nulo = function ($v) { $v = trim((string) $v); return $v === '' ? null : $v; };

        $data = [
            'nombre' => trim((string) $request->input('nombre', '')),
            'apellidos' => trim((string) $request->input('apellidos', '')),
            'telefono' => $nulo($request->input('telefono', '')),
            'password' => (string) $request->input('password', ''),
            'password_confirmar' => (string) $request->input('password_confirmar', ''),
        ];

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:100',
            'apellidos' => 'required|min:2|max:100',
            'telefono' => 'max:50',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/mi-perfil');
            return;
        }

        // Contrasena opcional
        if ($data['password'] !== '') {
            if (strlen($data['password']) < 8) {
                Session::flash('form_errors', ['password' => ['La contrasena debe tener al menos 8 caracteres']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/mi-perfil');
                return;
            }
            if ($data['password'] !== $data['password_confirmar']) {
                Session::flash('form_errors', ['password_confirmar' => ['Las contrasenas no coinciden']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/mi-perfil');
                return;
            }
        }

        // Actualizar datos del usuario
        $this->usuarios->update((int) $user['id'], [
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'telefono' => $data['telefono'],
        ]);

        if ($data['password'] !== '') {
            $this->usuarios->update((int) $user['id'], [
                'contrasena_hash' => Security::hashPassword($data['password']),
            ]);
        }

        // Datos profesionales (si aplica)
        $prof = $this->miProfesional();

        if ($prof !== null) {
            $profData = [
                'numero_cedula' => $nulo($request->input('numero_cedula', '')),
                'cedula_expira' => $nulo($request->input('cedula_expira', '')),
                'biografia' => $nulo($request->input('biografia', '')),
                'especialidad_id' => (int) $request->input('especialidad_id', 0) > 0
                    ? (int) $request->input('especialidad_id', 0)
                    : null,
            ];

            $this->db()->update('profesionales', $profData, 'id = ?', [(int) $prof['id']]);

            // Sincronizar especialidades (checkboxes)
            $idsSel = array_map('intval', (array) $request->input('especialidades', []));
            $principal = (int) $request->input('especialidad_id', 0);

            // Garantizar que la principal este incluida
            if ($principal > 0 && !in_array($principal, $idsSel, true)) {
                $idsSel[] = $principal;
            }

            $this->db()->query("DELETE FROM profesional_especialidades WHERE profesional_id = ?", [(int) $prof['id']]);

            foreach ($idsSel as $eid) {
                if ($eid <= 0) continue;
                $this->db()->insert('profesional_especialidades', [
                    'profesional_id' => (int) $prof['id'],
                    'especialidad_id' => $eid,
                    'es_principal' => ($eid === $principal) ? 1 : 0,
                ]);
            }
        }

        Session::flashSuccess('Perfil actualizado correctamente.' . ($data['password'] !== '' ? ' Contrasena cambiada.' : ''));
        $response->redirectTo('/panel/mi-perfil');
    }
}
