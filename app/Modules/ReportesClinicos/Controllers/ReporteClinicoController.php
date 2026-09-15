<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ReporteClinicoController
{
    private PlantillaReporte $plantillas;
    private ReportePaciente $reportes;

    public function __construct()
    {
        $this->plantillas = new PlantillaReporte();
        $this->reportes = new ReportePaciente();
    }

    private function esProfesional(): bool
    {
        $user = Session::user() ?? [];
        return ($user['rol_slug'] ?? '') === 'professional';
    }

    private function miProfesionalId(): ?int
    {
        if (!$this->esProfesional()) {
            return null;
        }
        $db = Database::getInstance();
        $prof = $db->fetchOne("SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1", [Session::getUserId()]);
        return $prof ? (int) $prof['id'] : null;
    }

    private function miEspecialidadId(): ?int
    {
        $profId = $this->miProfesionalId();
        if (!$profId) {
            return null;
        }
        $db = Database::getInstance();
        $prof = $db->fetchOne("SELECT especialidad_id FROM profesionales WHERE id = ?", [$profId]);
        return $prof && $prof['especialidad_id'] ? (int) $prof['especialidad_id'] : null;
    }

    private function orgId(): int
    {
        // Primero intentar desde profesionales
        $profId = $this->miProfesionalId();
        if ($profId) {
            $db = Database::getInstance();
            $prof = $db->fetchOne("SELECT organizacion_id FROM profesionales WHERE id = ?", [$profId]);
            if ($prof && $prof['organizacion_id']) {
                return (int) $prof['organizacion_id'];
            }
        }
        
        // Si no es profesional (admin), obtener la org del usuario
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT organizacion_id FROM usuarios WHERE id = ?", [Session::getUserId()]);
        return $user && $user['organizacion_id'] ? (int) $user['organizacion_id'] : 0;
    }

    private function esAdmin(): bool
    {
        $user = Session::user() ?? [];
        return in_array($user['rol_slug'] ?? '', ['clinic_admin', 'superadmin']);
    }

    public function index(Request $request, Response $response): void
    {
        $userId = Session::getUserId();
        $orgId = $this->orgId();
        $admin = $this->esAdmin();

        // Admin ve todas (sin filtro de especialidad), profesional solo las de su especialidad
        $especialidadFiltro = $admin ? null : $this->miEspecialidadId();
        $plantillas = $this->plantillas->listarDisponibles($orgId, $userId, $especialidadFiltro);
        $tipos = $this->plantillas->tiposDisponibles();

        // Lista de especialidades para el filtro visual del admin
        $db = Database::getInstance();
        $especialidades = $db->fetchAll("SELECT id, nombre FROM especialidades WHERE activo = 1 ORDER BY nombre");

        $content = View::render('Pages/reportes_clinicos/plantillas', [
            'plantillas' => $plantillas,
            'tipos' => $tipos,
            'esProfesional' => $this->esProfesional(),
            'esAdmin' => $admin,
            'especialidades' => $especialidades,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Reportes Clínicos', 'content' => $content]);
        $response->html($html);
    }

    public function editarPlantilla(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $plantilla = $this->plantillas->buscar($id);

        if (!$plantilla) {
            Session::flashError('Plantilla no encontrada.');
            $response->redirect(url('/panel/reportes-clinicos'));
            return;
        }

        $estructura = json_decode($plantilla['estructura_json'], true) ?: [];
        $tipos = $this->plantillas->tiposDisponibles();

        $content = View::render('Pages/reportes_clinicos/editar_plantilla', [
            'plantilla' => $plantilla,
            'estructura' => $estructura,
            'tipos' => $tipos,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Editar Plantilla', 'content' => $content]);
        $response->html($html);
    }

    public function actualizarPlantilla(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $nombre = trim($request->input('nombre', ''));
        $tipo = $request->input('tipo', 'informe');
        $descripcion = trim($request->input('descripcion', ''));

        if (!$nombre) {
            Session::flashError('El nombre es requerido.');
            $response->redirect(url('/panel/reportes-clinicos/editar/' . $id));
            return;
        }

        $estructura = [
            'mostrar_datos_paciente' => true,
            'mostrar_datos_profesional' => true,
            'mostrar_firma' => true,
            'secciones' => []
        ];

        $titulos = $request->input('seccion_titulo', []);
        $tiposCampo = $request->input('seccion_tipo', []);
        $placeholders = $request->input('seccion_placeholder', []);
        $requeridos = $request->input('seccion_requerido', []);

        // Los checkboxes solo envian los marcados, asi que hay que mapear por posicion.
        // Creamos un mapa de indices requeridos basado en el orden de envio.
        // Como los checkboxes con [] solo envian los checked, necesitamos saber
        // cuales posiciones estaban marcadas. Usamos un hidden input por seccion.
        $requeridosMap = [];
        $totalSecciones = count($titulos);
        // Los requeridos vienen en orden pero solo los marcados.
        // Para saber cual seccion es requerida, comparamos con el orden.
        // Solucion: el frontend envia seccion_requerido[] solo para marcados,
        // pero necesitamos el indice. Usaremos un campo hidden con el indice.
        $requeridosIdx = $request->input('seccion_requerido_idx', []);
        foreach ($requeridosIdx as $idx) {
            $requeridosMap[(int) $idx] = true;
        }

        foreach ($titulos as $i => $titulo) {
            if (empty(trim($titulo))) continue;
            
            $estructura['secciones'][] = [
                'id' => 'campo_' . $i,
                'titulo' => trim($titulo),
                'tipo' => $tiposCampo[$i] ?? 'texto_largo',
                'placeholder' => $placeholders[$i] ?? '',
                'requerido' => isset($requeridosMap[$i])
            ];
        }

        $actualizado = $this->plantillas->actualizar($id, [
            'nombre' => $nombre,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'estructura' => $estructura
        ]);

        if ($actualizado) {
            Session::flashSuccess('Plantilla actualizada correctamente.');
        } else {
            Session::flashError('No se pudo actualizar la plantilla.');
        }

        $response->redirect(url('/panel/reportes-clinicos'));
    }

    public function duplicarPlantilla(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $nuevoId = $this->plantillas->duplicar($id, $this->orgId(), Session::getUserId());
        Session::flashSuccess( $nuevoId ? 'Plantilla duplicada correctamente. Ahora puedes personalizarla.' : 'No se pudo duplicar la plantilla.');
        $response->redirect(url('/panel/reportes-clinicos'));
    }

    public function eliminarPlantilla(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $eliminado = $this->plantillas->eliminar($id);
        Session::flash($eliminado ? 'exito' : 'error', $eliminado ? 'Plantilla eliminada.' : 'Solo puedes eliminar plantillas propias (no las del sistema).');
        $response->redirect(url('/panel/reportes-clinicos'));
    }

    public function crearReporte(Request $request, Response $response): void
    {
        $orgId = $this->orgId();
        $plantillaId = (int) $request->input('plantilla_id');
        $pacienteId = (int) $request->input('paciente_id');

        $plantilla = $plantillaId ? $this->plantillas->buscar($plantillaId) : null;
        $estructura = $plantilla ? (json_decode($plantilla['estructura_json'], true) ?: []) : [];

        $db = Database::getInstance();
        $pacientes = $db->fetchAll(
            "SELECT id, nombre, apellidos, fecha_nacimiento FROM pacientes WHERE organizacion_id = ? AND activo = 1 ORDER BY nombre, apellidos",
            [$orgId]
        );

        $paciente = null;
        if ($pacienteId) {
            $paciente = $db->fetchOne(
                "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad FROM pacientes WHERE id = ? AND organizacion_id = ?",
                [$pacienteId, $orgId]
            );
        }

        $content = View::render('Pages/reportes_clinicos/generador', [
            'plantilla' => $plantilla,
            'estructura' => $estructura,
            'paciente' => $paciente,
            'pacientes' => $pacientes,
            'plantillaId' => $plantillaId,
            'pacienteId' => $pacienteId,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Crear Reporte', 'content' => $content]);
        $response->html($html);
    }

    public function editarReporte(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $reporte = $this->reportes->buscar($id);

        if (!$reporte) {
            Session::flashError('Reporte no encontrado.');
            $response->redirect(url('/panel/reportes-clinicos/mis-reportes'));
            return;
        }

        if ($reporte['estado'] !== 'borrador') {
            Session::flashError('Solo se pueden editar reportes en borrador.');
            $response->redirect(url('/panel/reportes-clinicos/ver/' . $id));
            return;
        }

        $orgId = $this->orgId();
        $plantillaId = (int) $reporte['plantilla_id'];
        $pacienteId = (int) $reporte['paciente_id'];

        $plantilla = $plantillaId ? $this->plantillas->buscar($plantillaId) : null;
        $estructura = $plantilla ? (json_decode($plantilla['estructura_json'], true) ?: []) : [];
        $contenido = json_decode($reporte['contenido_json'], true) ?: [];

        $db = Database::getInstance();
        $pacientes = $db->fetchAll(
            "SELECT id, nombre, apellidos, fecha_nacimiento FROM pacientes WHERE organizacion_id = ? AND activo = 1 ORDER BY nombre, apellidos",
            [$orgId]
        );

        $paciente = $db->fetchOne(
            "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad FROM pacientes WHERE id = ? AND organizacion_id = ?",
            [$pacienteId, $orgId]
        );

        $content = View::render('Pages/reportes_clinicos/generador', [
            'plantilla' => $plantilla,
            'estructura' => $estructura,
            'paciente' => $paciente,
            'pacientes' => $pacientes,
            'plantillaId' => $plantillaId,
            'pacienteId' => $pacienteId,
            'reporteEditando' => $reporte,
            'contenidoExistente' => $contenido,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Editar Reporte', 'content' => $content]);
        $response->html($html);
    }

    public function guardarReporte(Request $request, Response $response): void
    {
        $profId = $this->miProfesionalId();
        $orgId = $this->orgId();

        $plantillaId = (int) $request->input('plantilla_id');
        $pacienteId = (int) $request->input('paciente_id');
        $titulo = trim($request->input('titulo', ''));
        $contenido = $request->input('contenido', []);
        $firmar = $request->input('firmar') === '1';

        if (!$plantillaId || !$pacienteId || !$titulo) {
            Session::flashError( 'Completa todos los campos requeridos.');
            $response->redirect(url('/panel/reportes-clinicos/crear?plantilla_id=' . $plantillaId . '&paciente_id=' . $pacienteId));
            return;
        }

        $reporteId = $this->reportes->crear([
            'paciente_id' => $pacienteId,
            'profesional_id' => $profId,
            'organizacion_id' => $orgId,
            'plantilla_id' => $plantillaId,
            'titulo' => $titulo,
            'contenido' => $contenido,
        ]);

        if ($firmar && $reporteId) {
            $this->reportes->firmar($reporteId);
            Session::flashSuccess( 'Reporte firmado correctamente. Ya no puede editarse.');
        } else {
            Session::flashSuccess( 'Reporte guardado como borrador.');
        }

        $response->redirect(url('/panel/reportes-clinicos/ver/' . $reporteId));
    }

    public function verReporte(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $reporte = $this->reportes->buscar($id);

        if (!$reporte) {
            Session::flashError( 'Reporte no encontrado.');
            $response->redirect(url('/panel/reportes-clinicos'));
            return;
        }

        $estructura = $reporte['estructura_json'] ? (json_decode($reporte['estructura_json'], true) ?: []) : [];
        $contenido = json_decode($reporte['contenido_json'], true) ?: [];

        $content = View::render('Pages/reportes_clinicos/ver', [
            'reporte' => $reporte,
            'estructura' => $estructura,
            'contenido' => $contenido,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => $reporte['titulo'], 'content' => $content]);
        $response->html($html);
    }

    public function firmarReporte(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $firmado = $this->reportes->firmar($id);
        Session::flash($firmado ? 'exito' : 'error', $firmado ? 'Reporte firmado. Ya no puede editarse.' : 'No se pudo firmar el reporte.');
        $response->redirect(url('/panel/reportes-clinicos/ver/' . $id));
    }

    public function eliminarReporte(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $reporte = $this->reportes->buscar($id);

        if (!$reporte) {
            Session::flashError('Reporte no encontrado.');
            $response->redirect(url('/panel/reportes-clinicos/mis-reportes'));
            return;
        }

        // Permitir eliminar cualquier reporte (borrador o firmado)
        $eliminado = $this->reportes->eliminar($id);
        
        if ($eliminado) {
            Session::flashSuccess('Reporte eliminado correctamente.');
        } else {
            Session::flashError('No se pudo eliminar el reporte.');
        }

        $response->redirect(url('/panel/reportes-clinicos/mis-reportes'));
    }

    public function toggleCompartir(Request $request, Response $response): void
    {
        $id = (int) $request->input('id');
        $compartir = $request->input('compartir') === '1';
        $this->reportes->compartirPortal($id, $compartir);
        Session::flashSuccess( $compartir ? 'Reporte compartido en el portal del paciente.' : 'Reporte retirado del portal del paciente.');
        $response->redirect(url('/panel/reportes-clinicos/ver/' . $id));
    }

    public function listarReportes(Request $request, Response $response): void
    {
        $profId = $this->miProfesionalId();
        $reportes = $profId ? $this->reportes->listarPorProfesional($profId) : [];

        $content = View::render('Pages/reportes_clinicos/listar', ['reportes' => $reportes]);
        $html = View::render('Layouts.panel', ['pageTitle' => 'Mis Reportes Generados', 'content' => $content]);
        $response->html($html);
    }
}
