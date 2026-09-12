<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class AppointmentController
{
    private Cita $model;

    public function __construct()
    {
        $this->model = new Cita();
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
        $db = Database::getInstance();
        $prof = $db->fetchOne(
            "SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1",
            [Session::getUserId()]
        );
        return $prof !== null ? (int) $prof['id'] : null;
    }

    private function buscarEnAlcance(int $id): ?array
    {
        $cita = $this->model->findById($id);

        if ($cita === null) {
            return null;
        }

        if ($this->esSuperAdmin()) {
            return $cita;
        }

        if ((int) $cita['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || (int) $cita['profesional_id'] !== $profId) {
                return null;
            }
        }

        return $cita;
    }

    // ========================================================================
    // READ: AGENDA
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $filtros = [
            'fecha' => $request->input('fecha', ''),
            'estado' => $request->input('estado', ''),
            'q' => trim((string) $request->input('q', '')),
            'profesional_id' => (int) $request->input('profesional_id', 0),
        ];

        $orgFiltro = $this->esSuperAdmin() ? (int) $request->input('org_id', 0) : 0;
        if ($orgFiltro > 0) {
            $filtros['org_id'] = $orgFiltro;
        }

        $page = max(1, $request->int('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $rol = $this->rol();

        if ($rol === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            $citas = $this->model->searchByProfessional($profId, $filtros, $perPage, $offset);
            $total = $this->model->countByProfessional($profId, $filtros);
        } else {
            if ($this->esSuperAdmin() && $orgFiltro === 0) {
                $citas = $this->model->searchGlobal($filtros, $perPage, $offset);
                $total = $this->model->countGlobal($filtros);
            } else {
                $orgEf = $this->esSuperAdmin() ? $orgFiltro : $this->orgId();
                $citas = $this->model->searchByOrg($orgEf, $filtros, $perPage, $offset);
                $total = $this->model->countByOrg($orgEf, $filtros);
            }
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        $profesionales = [];
        if ($rol !== 'professional') {
            $profModel = new Profesional();
            if ($this->esSuperAdmin()) {
                $profesionales = $profModel->searchGlobal('', 0, 100, 0);
            } else {
                $profesionales = $profModel->search($this->orgId(), '', 100, 0);
            }
        }

        // Mapa cita_id => id de consulta (para botones de consulta medica)
        $consultaPorCita = [];
        if (!empty($citas)) {
            $ids = array_map(function ($c) { return (int) $c['id']; }, $citas);
            $dbTmp = Database::getInstance();
            $rows = $dbTmp->fetchAll(
                'SELECT cita_id, id FROM consultas WHERE cita_id IN (' . implode(',', $ids) . ')'
            );
            foreach ($rows as $r) {
                $consultaPorCita[(int) $r['cita_id']] = (int) $r['id'];
            }
        }
        // Bloqueos proximos para mostrar en la agenda
        $bloqueoModel = new BloqueoAgenda();
        if ($rol === 'professional') {
            $bloqueosProximos = $bloqueoModel->proximosActivos(0, $this->miProfesionalId() ?? 0);
        } elseif ($this->esSuperAdmin()) {
            $bloqueosProximos = $bloqueoModel->proximosActivos($orgFiltro > 0 ? $orgFiltro : 0, 0);
        } else {
            $bloqueosProximos = $bloqueoModel->proximosActivos($this->orgId(), 0);
        }
        $content = View::render('Pages/agenda/list', [
            'citas' => $citas,
            'filtros' => $filtros,
            'orgFiltro' => $orgFiltro,
            'organizaciones' => $this->esSuperAdmin() ? (new Organizacion())->getAll(100, 0) : [],
            'profesionales' => $profesionales,
            'estados' => Cita::estados(),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'rol' => $rol,
            'consultaPorCita' => $consultaPorCita,
            'bloqueosProximos' => $bloqueosProximos,
            'esSuperAdmin' => $this->esSuperAdmin(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => $rol === 'professional' ? 'Mi Agenda' : 'Agenda',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // CREATE: FORMULARIO CON FILTRO DINAMICO POR ORGANIZACION
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $rol = $this->rol();
        $orgPropia = $this->orgId();

        $errores = Session::getFlash('form_errors', []);
        $old = Session::getFlash('form_old', []);

        $pacModel = new Paciente();
        $profModel = new Profesional();
        $conModel = new Consultorio();

        $profesionalUnico = null;
        $organizaciones = [];
        $orgSeleccionada = $orgPropia;
        $requiereOrg = false;

        // SUPERADMIN: la organizacion se elige por GET o viene del old
        if ($this->esSuperAdmin()) {
            $organizaciones = (new Organizacion())->getAll(100, 0);
            $orgGet = (int) $request->input('organizacion_id', 0);
            $orgOld = (int) ($old['organizacion_id'] ?? 0);
            $orgSeleccionada = $orgGet > 0 ? $orgGet : $orgOld;
            $requiereOrg = $orgSeleccionada <= 0;
        }

        if ($rol === 'professional') {
            // Profesional: solo sus pacientes y su org
            $profId = $this->miProfesionalId() ?? 0;
            $pacientes = $pacModel->search($orgPropia, '', 200, 0, $profId);
            $profesionales = [];
            if ($profId > 0) {
                $profesionalUnico = $profModel->findById($profId);
            }
            $consultorios = $conModel->todosActivos($orgPropia);
        } elseif ($this->esSuperAdmin()) {
            // Superadmin: filtra TODO por la organizacion seleccionada
            if ($orgSeleccionada > 0) {
                $pacientes = $pacModel->search($orgSeleccionada, '', 200, 0);
                $profesionales = $profModel->search($orgSeleccionada, '', 100, 0);
                $consultorios = $conModel->todosActivos($orgSeleccionada);
            } else {
                $pacientes = [];
                $profesionales = [];
                $consultorios = [];
            }
        } else {
            // Clinic admin / receptionist: su organizacion
            $pacientes = $pacModel->search($orgPropia, '', 200, 0);
            $profesionales = $profModel->search($orgPropia, '', 100, 0);
            $consultorios = $conModel->todosActivos($orgPropia);
        }

        // Mapa de bloqueos por profesional (para mostrar al seleccionar)
        $orgParaMapa = $orgPropia;
        if ($this->esSuperAdmin()) {
            $orgGet2 = (int) $request->input('organizacion_id', 0);
            $orgOld2 = (int) ($old['organizacion_id'] ?? 0);
            $orgParaMapa = $orgGet2 > 0 ? $orgGet2 : $orgOld2;
        }
        $bloqueoModel2 = new BloqueoAgenda();
        $bloqueosMapa = $bloqueoModel2->mapaPorProfesional($orgParaMapa);
        $bloqueosProfUnico = ($profesionalUnico !== null)
            ? ($bloqueosMapa[(int) $profesionalUnico['id']] ?? [])
            : [];
        $content = View::render('Pages/agenda/form', [
            'cita' => null,
            'pacientes' => $pacientes,
            'profesionales' => $profesionales,
            'profesionalUnico' => $profesionalUnico,
            'consultorios' => $consultorios,
            'organizaciones' => $organizaciones,
            'orgSeleccionada' => $orgSeleccionada,
            'requiereOrg' => $requiereOrg,
            'esSuperAdmin' => $this->esSuperAdmin(),
            'esProfessional' => $rol === 'professional',
            'bloqueosMapa' => $bloqueosMapa,
            'bloqueosProfUnico' => $bloqueosProfUnico,
            'errores' => $errores,
            'old' => $old,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nueva Cita',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/panel/agenda');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/agenda/nueva');
        }

        $data = [
            'paciente_id' => (int) $request->input('paciente_id', 0),
            'profesional_id' => (int) $request->input('profesional_id', 0),
            'consultorio_id' => (int) $request->input('consultorio_id', 0),
            'fecha_cita' => trim((string) $request->input('fecha_cita', '')),
            'hora_inicio' => trim((string) $request->input('hora_inicio', '')),
            'motivo' => trim((string) $request->input('motivo', '')),
            'organizacion_id' => (int) $request->input('organizacion_id', 0),
        ];

        $validator = Validator::make($data, [
            'paciente_id' => 'required',
            'profesional_id' => 'required',
            'fecha_cita' => 'required|date',
            'hora_inicio' => 'required',
            'motivo' => 'max:500',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        // ============================================================
        // DETERMINAR ORGANIZACION DESTINO
        // ============================================================
        if ($this->esSuperAdmin()) {
            if ($data['organizacion_id'] <= 0) {
                Session::flash('form_errors', ['organizacion_id' => ['Selecciona la organizacion de la cita']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/agenda/nueva');
            }

            $orgObj = (new Organizacion())->findById($data['organizacion_id']);
            if ($orgObj === null || (int) $orgObj['activo'] !== 1) {
                Session::flash('form_errors', ['organizacion_id' => ['La organizacion seleccionada no es valida']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/agenda/nueva');
            }

            $orgDestino = (int) $orgObj['id'];
        } else {
            $orgDestino = $this->orgId();
        }

        // ============================================================
        // SEGURIDAD: paciente, profesional y consultorio deben
        // pertenecer a la organizacion destino (anti-manipulacion)
        // ============================================================
        $pacModel = new Paciente();
        $pac = $pacModel->findById($data['paciente_id']);
        if ($pac === null || (int) $pac['organizacion_id'] !== $orgDestino) {
            Session::flash('form_errors', ['paciente_id' => ['El paciente no pertenece a la organizacion seleccionada']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        $profModel = new Profesional();
        $prof = $profModel->findById($data['profesional_id']);
        if ($prof === null || (int) $prof['organizacion_id'] !== $orgDestino) {
            Session::flash('form_errors', ['profesional_id' => ['El profesional no pertenece a la organizacion seleccionada']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        if ($data['consultorio_id'] > 0) {
            $con = (new Consultorio())->findById($data['consultorio_id']);
            if ($con === null || (int) $con['organizacion_id'] !== $orgDestino) {
                Session::flash('form_errors', ['consultorio_id' => ['El consultorio no pertenece a la organizacion seleccionada']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/agenda/nueva');
            }
        }

        // Si es profesional, forzar su propio id
        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            if ($profId === 0 || $data['profesional_id'] !== $profId) {
                Session::flashError('Solo puedes crear citas para ti mismo.');
                $response->redirectTo('/panel/agenda/nueva');
            }
        }

        // Normalizar hora (agregar :00 si viene como HH:MM)
        $horaInicio = strlen($data['hora_inicio']) === 5 ? $data['hora_inicio'] . ':00' : $data['hora_inicio'];

        // Calcular hora_fin segun duracion del profesional
        $duracion = $this->model->duracionProfesional($data['profesional_id']);
        $horaFin = date('H:i:s', strtotime($data['fecha_cita'] . ' ' . $horaInicio . ' + ' . $duracion . ' minutes'));

        // Validar dia bloqueado por el profesional
        $bloqueoModel = new BloqueoAgenda();
        if ($bloqueoModel->hayBloqueo($data['profesional_id'], $data['fecha_cita'])) {
            $bloqueo = $bloqueoModel->bloqueoDeFecha($data['profesional_id'], $data['fecha_cita']);
            $motivo = ($bloqueo !== null && !empty($bloqueo['motivo'])) ? ' (' . $bloqueo['motivo'] . ')' : '';
            Session::flash('form_errors', ['fecha_cita' => ['El profesional no esta disponible ese dia' . $motivo . '. Elige otra fecha.']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        // Validar que la cita caiga dentro del horario de atencion del profesional
        $horarioModel = new Horario();
        if (!$horarioModel->dentroDeHorario($data['profesional_id'], $data['fecha_cita'], $horaInicio, $horaFin)) {
            $franjas = $horarioModel->franjasActivasDeFecha($data['profesional_id'], $data['fecha_cita']);
            if (empty($franjas)) {
                $msg = 'El profesional no atiende ese dia de la semana. Revisa sus horarios.';
            } else {
                $msg = 'Fuera del horario de atencion. Franjas de ese dia: ';
                foreach ($franjas as $fr) {
                    $msg .= substr($fr['hora_inicio'], 0, 5) . '-' . substr($fr['hora_fin'], 0, 5) . '  ';
                }
            }
            Session::flash('form_errors', ['hora_inicio' => [$msg]]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        // Validar empalme
        if ($this->model->hayEmpalme($data['profesional_id'], $data['fecha_cita'], $horaInicio, $horaFin)) {
            Session::flash('form_errors', ['hora_inicio' => ['El profesional ya tiene una cita en ese horario. Elige otro horario.']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/agenda/nueva');
        }

        $this->model->create([
            'organizacion_id' => $orgDestino,
            'paciente_id' => $data['paciente_id'],
            'profesional_id' => $data['profesional_id'],
            'consultorio_id' => $data['consultorio_id'] > 0 ? $data['consultorio_id'] : null,
            'fecha_cita' => $data['fecha_cita'],
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => 'pendiente',
            'motivo' => $data['motivo'] !== '' ? $data['motivo'] : null,
            'creado_por' => Session::getUserId(),
        ]);

        Session::flashSuccess('Cita creada correctamente.');
        $response->redirectTo('/panel/agenda');
    }

    // ========================================================================
    // CAMBIO DE ESTADO
    // ========================================================================

    public function cambiarEstado(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $nuevoEstado = trim((string) $request->routeParam('estado', ''));

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/agenda');
        }

        $estadosValidos = ['confirmada', 'completada', 'cancelada', 'no_asistio', 'en_progreso'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            Session::flashError('Estado no valido.');
            $response->redirectTo('/panel/agenda');
        }

        $cita = $this->buscarEnAlcance($id);

        if ($cita === null) {
            Session::flashError('Cita no encontrada o sin permiso.');
            $response->redirectTo('/panel/agenda');
        }

        $extra = [];
        if ($nuevoEstado === 'cancelada') {
            $extra['motivo_cancelacion'] = trim((string) $request->input('motivo_cancelacion', ''));
            $extra['cancelado_por'] = Session::getUserId();
        }

        // Congelar el monto de ganancia al completar la cita
        if ($nuevoEstado === 'completada') {
            $profModel = new Profesional();
            $prof = $profModel->findById((int) $cita['profesional_id']);
            $extra['monto_consulta'] = $prof !== null ? (float) ($prof['costo_consulta'] ?? 0) : 0;
        }

        $this->model->updateEstado($id, $nuevoEstado, $extra);

        Session::flashSuccess('Cita actualizada a "' . (Cita::estados()[$nuevoEstado] ?? $nuevoEstado) . '".');
        $response->redirectTo('/panel/agenda');
    }
}
