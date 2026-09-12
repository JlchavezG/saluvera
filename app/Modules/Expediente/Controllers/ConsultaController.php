<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ConsultaController
{
    private Consulta $model;
    private Paciente $pacientes;

    public function __construct()
    {
        $this->model = new Consulta();
        $this->pacientes = new Paciente();
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

    private function miProfesionalId(): ?int
    {
        if ($this->rol() !== 'professional') {
            return null;
        }

        $db = Database::getInstance();
        $prof = $db->fetchOne(
            "SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1",
            [Session::getUserId()]
        );
        return $prof !== null ? (int) $prof['id'] : null;
    }

    private function puedeEscribir(): bool
    {
        return in_array($this->rol(), ['superadmin', 'clinic_admin', 'professional'], true);
    }

    private function consultaEnAlcance(int $id): ?array
    {
        $consulta = $this->model->findById($id);

        if ($consulta === null) {
            return null;
        }

        if ($this->rol() === 'superadmin') {
            return $consulta;
        }

        if ((int) $consulta['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || (int) $consulta['profesional_id'] !== $profId) {
                return null;
            }
        }

        return $consulta;
    }

    private function pacienteEnAlcance(int $id): ?array
    {
        $paciente = $this->pacientes->findById($id);

        if ($paciente === null) {
            return null;
        }

        if ($this->rol() === 'superadmin') {
            return $paciente;
        }

        if ((int) $paciente['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || !$this->pacientes->esPacienteDe($id, $profId)) {
                return null;
            }
        }

        return $paciente;
    }

    // ========================================================================
    // FORMULARIOS DE CREACION
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        if (!$this->puedeEscribir()) {
            Session::flashError('Tu rol no puede registrar consultas medicas.');
            $response->redirectTo('/panel/pacientes');
        }

        $pacienteId = (int) $request->routeParam('id', 0);
        $paciente = $this->pacienteEnAlcance($pacienteId);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $this->renderForm($response, $paciente, null, null);
    }

    public function createDesdeCita(Request $request, Response $response): void
    {
        if (!$this->puedeEscribir()) {
            Session::flashError('Tu rol no puede registrar consultas medicas.');
            $response->redirectTo('/panel/agenda');
        }

        $citaId = (int) $request->routeParam('id', 0);
        $citaModel = new Cita();
        $cita = $citaModel->findById($citaId);

        if ($cita === null) {
            Session::flashError('Cita no encontrada.');
            $response->redirectTo('/panel/agenda');
        }

        // Alcance de la cita
        if ($this->rol() !== 'superadmin') {
            if ((int) $cita['organizacion_id'] !== $this->orgId()) {
                Session::flashError('Cita no encontrada.');
                $response->redirectTo('/panel/agenda');
            }
            if ($this->rol() === 'professional') {
                $profId = $this->miProfesionalId();
                if ($profId === null || (int) $cita['profesional_id'] !== $profId) {
                    Session::flashError('Cita no encontrada.');
                    $response->redirectTo('/panel/agenda');
                }
            }
        }

        // Si ya existe consulta para esta cita, abrir la existente
        $existente = $this->model->findByCita($citaId);
        if ($existente !== null) {
            $response->redirectTo('/panel/consultas/' . (int) $existente['id'] . '/editar');
        }

        $paciente = $this->pacienteEnAlcance((int) $cita['paciente_id']);

        if ($paciente === null) {
            Session::flashError('Paciente de la cita no disponible.');
            $response->redirectTo('/panel/agenda');
        }

        $this->renderForm($response, $paciente, $cita, null);
    }

    private function renderForm(Response $response, array $paciente, ?array $cita, ?array $consulta): void
    {
        $rol = $this->rol();
        $profModel = new Profesional();

        $orgPaciente = (int) $paciente['organizacion_id'];

        if ($rol === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            $profesionalUnico = $profId > 0 ? $profModel->findById($profId) : null;
            $profesionales = [];
        } else {
            $profesionales = $profModel->search($orgPaciente, '', 100, 0);
            $profesionalUnico = null;
        }

        $valores = [];
        if ($consulta !== null) {
            $valores = $consulta;
            $valores = array_merge($valores, Consulta::vitalesArray($consulta['signos_vitales'] ?? null));
        } elseif ($cita !== null) {
            $valores['profesional_id'] = $cita['profesional_id'];
            $valores['motivo'] = $cita['motivo'];
        }

        $content = View::render('Pages/consultas/form', [
            'paciente' => $paciente,
            'cita' => $cita,
            'consulta' => $consulta,
            'profesionales' => $profesionales,
            'profesionalUnico' => $profesionalUnico,
            'diagnosticos' => $consulta !== null ? $this->model->diagnosticos((int) $consulta['id']) : [],
            'tratamientos' => $consulta !== null ? $this->model->tratamientos((int) $consulta['id']) : [],
            'valores' => $valores,
            'esProfessional' => $rol === 'professional',
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => $consulta !== null ? 'Editar Consulta' : 'Nueva Consulta',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // GUARDAR
    // ========================================================================

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken() || !$this->puedeEscribir()) {
            $response->redirectTo('/panel/pacientes');
        }

        $data = $this->datosDelFormulario($request);

        $validator = Validator::make($data, [
            'paciente_id' => 'required',
            'profesional_id' => 'required',
            'motivo' => 'max:500',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $request->inputs());
            $response->redirectTo('/panel/pacientes/' . (int) $request->input('paciente_id', 0) . '/consulta/nueva');
        }

        $paciente = $this->pacienteEnAlcance((int) $data['paciente_id']);

        if ($paciente === null) {
            Session::flashError('Paciente no valido.');
            $response->redirectTo('/panel/pacientes');
        }

        // Profesional debe ser de la organizacion del paciente
        $profModel = new Profesional();
        $prof = $profModel->findById((int) $data['profesional_id']);

        if ($prof === null || (int) $prof['organizacion_id'] !== (int) $paciente['organizacion_id']) {
            Session::flash('form_errors', ['profesional_id' => ['El profesional no pertenece a la organizacion del paciente']]);
            Session::flash('form_old', $request->inputs());
            $response->redirectTo('/panel/pacientes/' . (int) $data['paciente_id'] . '/consulta/nueva');
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            if ((int) $data['profesional_id'] !== $profId) {
                Session::flashError('Solo puedes registrar consultas propias.');
                $response->redirectTo('/panel/pacientes');
            }
        }

        $estado = $request->input('accion', 'borrador') === 'completada' ? 'completada' : 'borrador';

        $consultaId = $this->model->create([
            'organizacion_id' => (int) $paciente['organizacion_id'],
            'paciente_id' => (int) $data['paciente_id'],
            'profesional_id' => (int) $data['profesional_id'],
            'cita_id' => (int) $request->input('cita_id', 0) > 0 ? (int) $request->input('cita_id', 0) : null,
            'motivo' => $data['motivo'],
            'subjetivo' => $data['subjetivo'],
            'objetivo' => $data['objetivo'],
            'evaluacion' => $data['evaluacion'],
            'plan' => $data['plan'],
            'signos_vitales' => Consulta::vitalesJson($this->vitalesDelFormulario($request)),
            'exploracion_fisica' => $data['exploracion_fisica'],
            'evolucion' => $data['evolucion'],
            'notas_privadas' => $data['notas_privadas'],
            'estado' => $estado,
            'creado_por' => Session::getUserId(),
        ]);

        Session::flashSuccess('Consulta registrada como "' . $estado . '".');
        $response->redirectTo('/panel/consultas/' . $consultaId . '/editar');
    }

    // ========================================================================
    // EDITAR / ACTUALIZAR
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        if (!$this->puedeEscribir()) {
            Session::flashError('Tu rol no puede acceder a consultas medicas.');
            $response->redirectTo('/panel/pacientes');
        }

        $id = (int) $request->routeParam('id', 0);
        $consulta = $this->consultaEnAlcance($id);

        if ($consulta === null) {
            Session::flashError('Consulta no encontrada o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $paciente = $this->pacientes->findById((int) $consulta['paciente_id']);

        $this->renderForm($response, $paciente ?? [], null, $consulta);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken() || !$this->puedeEscribir()) {
            $response->redirectTo('/panel/pacientes');
        }

        $consulta = $this->consultaEnAlcance($id);

        if ($consulta === null) {
            Session::flashError('Consulta no encontrada o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $data = $this->datosDelFormulario($request);

        $update = [
            'motivo' => $data['motivo'],
            'subjetivo' => $data['subjetivo'],
            'objetivo' => $data['objetivo'],
            'evaluacion' => $data['evaluacion'],
            'plan' => $data['plan'],
            'signos_vitales' => Consulta::vitalesJson($this->vitalesDelFormulario($request)),
            'exploracion_fisica' => $data['exploracion_fisica'],
            'evolucion' => $data['evolucion'],
            'notas_privadas' => $data['notas_privadas'],
        ];

        // Una consulta firmada no se edita
        if ($consulta['estado'] !== 'firmada') {
            if ($request->input('accion', '') === 'completada') {
                $update['estado'] = 'completada';
            } elseif ($request->input('accion', '') === 'borrador') {
                $update['estado'] = 'borrador';
            }
        }

        $this->model->update($id, $update, Session::getUserId());

        Session::flashSuccess('Consulta actualizada.');
        $response->redirectTo('/panel/consultas/' . $id . '/editar');
    }

    public function firmar(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken() || !$this->puedeEscribir()) {
            $response->redirectTo('/panel/pacientes');
        }

        $consulta = $this->consultaEnAlcance($id);

        if ($consulta === null) {
            Session::flashError('Consulta no encontrada o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        if ($consulta['estado'] !== 'completada') {
            Session::flashError('Solo se pueden firmar consultas completadas.');
            $response->redirectTo('/panel/consultas/' . $id . '/editar');
        }

        $this->model->setEstado($id, 'firmada', Session::getUserId());

        Session::flashSuccess('Consulta firmada. Queda bloqueada para edicion.');
        $response->redirectTo('/panel/consultas/' . $id . '/editar');
    }

    // ========================================================================
    // DIAGNOSTICOS Y TRATAMIENTOS
    // ========================================================================

    public function storeDiagnostico(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken() || !$this->puedeEscribir()) {
            $response->redirectTo('/panel/pacientes');
        }

        $consulta = $this->consultaEnAlcance($id);

        if ($consulta === null || $consulta['estado'] === 'firmada') {
            Session::flashError('No se puede modificar una consulta firmada.');
            $response->redirectTo('/panel/consultas/' . $id . '/editar');
        }

        $nombre = trim((string) $request->input('nombre_diagnostico', ''));

        if ($nombre === '') {
            Session::flashError('El nombre del diagnostico es obligatorio.');
            $response->redirectTo('/panel/consultas/' . $id . '/editar');
        }

        $esPrincipal = $request->input('es_principal', '0') === '1' ? 1 : 0;

        if ($esPrincipal === 1) {
            $db = Database::getInstance();
            $db->update('diagnosticos', ['es_principal' => 0], 'consulta_id = ?', [$id]);
        }

        $this->model->agregarDiagnostico([
            'consulta_id' => $id,
            'paciente_id' => (int) $consulta['paciente_id'],
            'organizacion_id' => (int) $consulta['organizacion_id'],
            'codigo_diagnostico' => trim((string) $request->input('codigo_diagnostico', '')) !== '' ? trim((string) $request->input('codigo_diagnostico', '')) : null,
            'nombre_diagnostico' => $nombre,
            'descripcion' => trim((string) $request->input('descripcion', '')) !== '' ? trim((string) $request->input('descripcion', '')) : null,
            'es_principal' => $esPrincipal,
            'es_cronico' => $request->input('es_cronico', '0') === '1' ? 1 : 0,
            'fecha_inicio' => date('Y-m-d'),
            'estado' => 'activo',
            'creado_por' => Session::getUserId(),
        ]);

        Session::flashSuccess('Diagnostico agregado.');
        $response->redirectTo('/panel/consultas/' . $id . '/editar');
    }

    public function storeTratamiento(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken() || !$this->puedeEscribir()) {
            $response->redirectTo('/panel/pacientes');
        }

        $consulta = $this->consultaEnAlcance($id);

        if ($consulta === null || $consulta['estado'] === 'firmada') {
            Session::flashError('No se puede modificar una consulta firmada.');
            $response->redirectTo('/panel/consultas/' . $id . '/editar');
        }

        $nombre = trim((string) $request->input('nombre_tratamiento', ''));

        if ($nombre === '') {
            Session::flashError('El nombre del tratamiento es obligatorio.');
            $response->redirectTo('/panel/consultas/' . $id . '/editar');
        }

        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        $this->model->agregarTratamiento([
            'consulta_id' => $id,
            'paciente_id' => (int) $consulta['paciente_id'],
            'organizacion_id' => (int) $consulta['organizacion_id'],
            'nombre_tratamiento' => $nombre,
            'dosis' => $nulo($request->input('dosis', '')),
            'frecuencia' => $nulo($request->input('frecuencia', '')),
            'duracion' => $nulo($request->input('duracion', '')),
            'via_administracion' => $nulo($request->input('via_administracion', '')),
            'instrucciones' => $nulo($request->input('instrucciones', '')),
            'fecha_inicio' => $nulo($request->input('fecha_inicio', '')),
            'estado' => 'activo',
            'creado_por' => Session::getUserId(),
        ]);

        Session::flashSuccess('Tratamiento agregado.');
        $response->redirectTo('/panel/consultas/' . $id . '/editar');
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function datosDelFormulario(Request $request): array
    {
        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        return [
            'paciente_id' => (int) $request->input('paciente_id', 0),
            'profesional_id' => (int) $request->input('profesional_id', 0),
            'motivo' => $nulo($request->input('motivo', '')),
            'subjetivo' => $nulo($request->input('subjetivo', '')),
            'objetivo' => $nulo($request->input('objetivo', '')),
            'evaluacion' => $nulo($request->input('evaluacion', '')),
            'plan' => $nulo($request->input('plan', '')),
            'exploracion_fisica' => $nulo($request->input('exploracion_fisica', '')),
            'evolucion' => $nulo($request->input('evolucion', '')),
            'notas_privadas' => $nulo($request->input('notas_privadas', '')),
        ];
    }

    private function vitalesDelFormulario(Request $request): array
    {
        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        return [
            'pa_sis' => $nulo($request->input('pa_sis', '')),
            'pa_dia' => $nulo($request->input('pa_dia', '')),
            'fc' => $nulo($request->input('fc', '')),
            'fr' => $nulo($request->input('fr', '')),
            'temp' => $nulo($request->input('temp', '')),
            'peso' => $nulo($request->input('peso', '')),
            'talla' => $nulo($request->input('talla', '')),
            'spo2' => $nulo($request->input('spo2', '')),
        ];
    }
}
