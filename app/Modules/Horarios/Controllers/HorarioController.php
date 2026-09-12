<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class HorarioController
{
    private Horario $model;

    public function __construct()
    {
        $this->model = new Horario();
    }

    private function orgId(): int
    {
        $userModel = new User();
        $user = $userModel->findById(Session::getUserId());
        return (int) ($user['organizacion_id'] ?? 1);
    }

    private function rol(): string
    {
        $user = Session::user() ?? [];
        return $user['rol_slug'] ?? '';
    }

    private function esSuperAdmin(): bool
    {
        return $this->rol() === 'superadmin';
    }

    private function miProfesionalId(): ?int
    {
        if ($this->rol() !== 'professional') {
            return null;
        }
        $db = Database::getInstance();
        $prof = $db->fetchOne("SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1", [Session::getUserId()]);
        return $prof !== null ? (int) $prof['id'] : null;
    }

    private function horarioEnAlcance(int $id): ?array
    {
        $h = $this->model->findById($id);
        if ($h === null) return null;
        if ($this->esSuperAdmin()) return $h;
        if ((int) $h['organizacion_id'] !== $this->orgId()) return null;
        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || (int) $h['profesional_id'] !== $profId) return null;
        }
        return $h;
    }

    private function profesionalesSeleccionables(): array
    {
        $db = Database::getInstance();
        if ($this->esSuperAdmin()) {
            return $db->fetchAll(
                "SELECT p.id, u.nombre, u.apellidos, o.nombre as org_nombre
                 FROM profesionales p
                 INNER JOIN usuarios u ON p.usuario_id = u.id
                 INNER JOIN organizaciones o ON p.organizacion_id = o.id
                 WHERE p.activo = 1 ORDER BY o.nombre, u.nombre"
            );
        }
        return $db->fetchAll(
            "SELECT p.id, u.nombre, u.apellidos
             FROM profesionales p
             INNER JOIN usuarios u ON p.usuario_id = u.id
             WHERE p.organizacion_id = ? AND p.activo = 1 ORDER BY u.nombre",
            [$this->orgId()]
        );
    }

    // ========================================================================
    // LISTADO
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $rol = $this->rol();

        if ($rol === 'professional') {
            $horarios = $this->model->searchByProf($this->miProfesionalId() ?? 0);
        } else {
            $profFiltro = (int) $request->input('profesional_id', 0);
            if ($profFiltro > 0) {
                $horarios = $this->model->searchByProf($profFiltro);
            } else {
                $horarios = [];
            }
        }

        $content = View::render('Pages/horarios/list', [
            'horarios' => $horarios,
            'profesionales' => $rol === 'professional' ? [] : $this->profesionalesSeleccionables(),
            'profFiltro' => (int) $request->input('profesional_id', 0),
            'esProfessional' => $rol === 'professional',
            'esSuperAdmin' => $this->esSuperAdmin(),
            'dias' => Horario::dias(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => $rol === 'professional' ? 'Mis Horarios' : 'Horarios de Atencion',
            'content' => $content,
        ]);
        $response->html($html);
    }

    // ========================================================================
    // CREAR
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $rol = $this->rol();
        $conModel = new Consultorio();

        $content = View::render('Pages/horarios/form', [
            'horario' => null,
            'profesionales' => $rol === 'professional' ? [] : $this->profesionalesSeleccionables(),
            'consultorios' => $this->esSuperAdmin() ? [] : $conModel->todosActivos($this->orgId()),
            'dias' => Horario::dias(),
            'esProfessional' => $rol === 'professional',
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Nuevo Horario', 'content' => $content]);
        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $data = $this->datos($request);

        if (!$this->validar($data, $response, '/panel/mis-horarios/nuevo')) {
            return;
        }

        // Determinar profesional y org
        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            if ($profId === 0) {
                Session::flashError('No tienes perfil de profesional.');
                $response->redirectTo('/panel/mis-horarios');
                return;
            }
            $data['profesional_id'] = $profId;
        }

        $profModel = new Profesional();
        $prof = $profModel->findById((int) $data['profesional_id']);
        if ($prof === null) {
            Session::flash('form_errors', ['profesional_id' => ['Profesional no valido']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/mis-horarios/nuevo');
            return;
        }
        if (!$this->esSuperAdmin() && (int) $prof['organizacion_id'] !== $this->orgId()) {
            Session::flashError('Profesional fuera de tu organizacion.');
            $response->redirectTo('/panel/mis-horarios/nuevo');
            return;
        }

        $this->model->create([
            'organizacion_id' => (int) $prof['organizacion_id'],
            'profesional_id' => (int) $data['profesional_id'],
            'consultorio_id' => $data['consultorio_id'] > 0 ? $data['consultorio_id'] : null,
            'dia_semana' => (int) $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'es_recurrente' => $data['es_recurrente'],
            'valido_desde' => $data['valido_desde'],
            'valido_hasta' => $data['valido_hasta'],
        ]);

        Session::flashSuccess('Horario agregado correctamente.');
        $response->redirectTo('/panel/mis-horarios');
    }

    // ========================================================================
    // EDITAR / ELIMINAR
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $horario = $this->horarioEnAlcance($id);

        if ($horario === null) {
            Session::flashError('Horario no encontrado o sin permiso.');
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $conModel = new Consultorio();

        $content = View::render('Pages/horarios/form', [
            'horario' => $horario,
            'profesionales' => $this->rol() === 'professional' ? [] : $this->profesionalesSeleccionables(),
            'consultorios' => $this->esSuperAdmin() ? [] : $conModel->todosActivos((int) $horario['organizacion_id']),
            'dias' => Horario::dias(),
            'esProfessional' => $this->rol() === 'professional',
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Editar Horario', 'content' => $content]);
        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $horario = $this->horarioEnAlcance($id);
        if ($horario === null) {
            Session::flashError('Horario no encontrado o sin permiso.');
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $data = $this->datos($request);
        if (!$this->validar($data, $response, '/panel/mis-horarios/' . $id . '/editar')) {
            return;
        }

        $this->model->update($id, [
            'consultorio_id' => $data['consultorio_id'] > 0 ? $data['consultorio_id'] : null,
            'dia_semana' => (int) $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'es_recurrente' => $data['es_recurrente'],
            'valido_desde' => $data['valido_desde'],
            'valido_hasta' => $data['valido_hasta'],
        ]);

        Session::flashSuccess('Horario actualizado.');
        $response->redirectTo('/panel/mis-horarios');
    }

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $horario = $this->horarioEnAlcance($id);
        if ($horario === null) {
            Session::flashError('Horario no encontrado o sin permiso.');
            $response->redirectTo('/panel/mis-horarios');
            return;
        }

        $this->model->delete($id);
        Session::flashSuccess('Horario eliminado.');
        $response->redirectTo('/panel/mis-horarios');
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function datos(Request $request): array
    {
        $nulo = function ($v) { $v = trim((string) $v); return $v === '' ? null : $v; };

        return [
            'profesional_id' => (int) $request->input('profesional_id', 0),
            'consultorio_id' => (int) $request->input('consultorio_id', 0),
            'dia_semana' => (int) $request->input('dia_semana', 0),
            'hora_inicio' => trim((string) $request->input('hora_inicio', '')),
            'hora_fin' => trim((string) $request->input('hora_fin', '')),
            'es_recurrente' => $request->input('es_recurrente', '0') === '1' ? 1 : 0,
            'valido_desde' => $nulo($request->input('valido_desde', '')),
            'valido_hasta' => $nulo($request->input('valido_hasta', '')),
        ];
    }

    private function validar(array $data, Response $response, string $redirect): bool
    {
        $errores = [];

        if ($data['dia_semana'] < 1 || $data['dia_semana'] > 7) {
            $errores['dia_semana'] = ['Selecciona un dia de la semana'];
        }
        if ($data['hora_inicio'] === '' || $data['hora_fin'] === '') {
            $errores['hora_inicio'] = ['Las horas son obligatorias'];
        } elseif ($data['hora_fin'] <= $data['hora_inicio']) {
            $errores['hora_fin'] = ['La hora fin debe ser posterior a la hora inicio'];
        }
        if ($this->rol() !== 'professional' && $data['profesional_id'] <= 0) {
            $errores['profesional_id'] = ['Selecciona el profesional'];
        }

        if (!empty($errores)) {
            Session::flash('form_errors', $errores);
            Session::flash('form_old', $data);
            $response->redirectTo($redirect);
            return false;
        }

        return true;
    }
}
