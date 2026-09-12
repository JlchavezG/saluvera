<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class MiPanelController
{
    private function miProfesionalId(): ?int
    {
        $db = Database::getInstance();
        $prof = $db->fetchOne(
            "SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1",
            [Session::getUserId()]
        );
        return $prof !== null ? (int) $prof['id'] : null;
    }

    public function index(Request $request, Response $response): void
    {
        $sessionUser = Session::user() ?? [];
        $rol = $sessionUser['rol_slug'] ?? '';

        // Solo profesionales ven Mi Panel
        if ($rol !== 'professional') {
            $response->redirectTo('/panel');
        }

        $profId = $this->miProfesionalId();

        if ($profId === null) {
            Session::flashError('Tu usuario no tiene registro de profesional activo.');
            $response->redirectTo('/logout');
        }

        $citaModel = new Cita();
        $pacienteModel = new Paciente();

        $userModel = new User();
        $fullUser = $userModel->findById(Session::getUserId());
        $orgId = (int) ($fullUser['organizacion_id'] ?? 0);

        // SOLO datos propios (Capa 2)
        $citasHoy = $citaModel->misCitasDeHoy($profId);
        $proximaCita = $citaModel->miProximaCita($profId);
        $citasHoyCount = $citaModel->countMisCitasHoy($profId);
        $misPacientes = $pacienteModel->countSearch($orgId, '', $profId);

        $content = View::render('Pages/mi_panel', [
            'citasHoy' => $citasHoy,
            'proximaCita' => $proximaCita,
            'citasHoyCount' => $citasHoyCount,
            'misPacientes' => $misPacientes,
            'user' => $sessionUser,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Mi Panel',
            'content' => $content,
        ]);

        $response->html($html);
    }
}
