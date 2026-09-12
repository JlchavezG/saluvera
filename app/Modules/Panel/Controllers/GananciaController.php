<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class GananciaController
{
    private Cita $citas;

    public function __construct()
    {
        $this->citas = new Cita();
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

    public function index(Request $request, Response $response): void
    {
        if ($this->rol() !== 'professional') {
            Session::flashError('Solo los profesionales ven su balance de ganancias.');
            $response->redirectTo('/panel');
            return;
        }

        $profId = $this->miProfesionalId();

        if ($profId === null) {
            Session::flashError('No tienes un perfil de profesional asignado.');
            $response->redirectTo('/panel');
            return;
        }

        // Rango filtrado (default: mes actual)
        $desde = trim((string) $request->input('desde', date('Y-m-01')));
        $hasta = trim((string) $request->input('hasta', date('Y-m-d')));

        $rango = $this->citas->gananciasProfesional($profId, $desde, $hasta);
        $mesActual = $this->citas->gananciasProfesional($profId, date('Y-m-01'), date('Y-m-t'));
        $historico = $this->citas->gananciasProfesional($profId);
        $citas = $this->citas->citasCompletadasProfesional($profId, $desde, $hasta);

        $promedio = $rango['total_citas'] > 0
            ? $rango['total_monto'] / $rango['total_citas']
            : 0;

        $content = View::render('Pages/ganancias/index', [
            'rango' => $rango,
            'mesActual' => $mesActual,
            'historico' => $historico,
            'citas' => $citas,
            'desde' => $desde,
            'hasta' => $hasta,
            'promedio' => $promedio,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Mis Ganancias',
            'content' => $content,
        ]);

        $response->html($html);
    }
}
