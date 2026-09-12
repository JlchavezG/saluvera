<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class NotificacionController
{
    private Notificacion $model;

    public function __construct()
    {
        $this->model = new Notificacion();
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

    private function notificacionEnAlcance(int $id): ?array
    {
        $not = $this->model->findById($id);
        if ($not === null) return null;
        if ($this->esSuperAdmin()) return $not;
        if ((int) $not['organizacion_id'] !== $this->orgId()) return null;
        return $not;
    }

    private function telefonoDe(array $not): string
    {
        if ($not['tipo_destinatario'] === 'paciente') {
            $p = (new Paciente())->findById((int) $not['destinatario_id']);
            return (string) ($p['telefono'] ?? '');
        }
        $u = (new User())->findById((int) $not['destinatario_id']);
        return (string) ($u['telefono'] ?? '');
    }

    // ========================================================================
    // CENTRO DE NOTIFICACIONES
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $orgId = $this->esSuperAdmin()
            ? (int) $request->input('org_id', 0)
            : $this->orgId();

        $recordatoriosCreados = 0;
        if ($orgId > 0) {
            $recordatoriosCreados = $this->model->generarRecordatoriosManana($orgId);
        }

        if ($this->esSuperAdmin() && $orgId === 0) {
            $notificaciones = $this->model->findAll();
            $pendientes = $this->model->countPendientesTodas();
        } else {
            $notificaciones = $orgId > 0 ? $this->model->findByOrg($orgId) : [];
            $pendientes = $orgId > 0 ? $this->model->countPendientesOrg($orgId) : 0;
        }

        $content = View::render('Pages/notificaciones/list', [
            'notificaciones' => $notificaciones,
            'pendientes' => $pendientes,
            'orgId' => $orgId,
            'organizaciones' => $this->esSuperAdmin() ? (new Organizacion())->getAll(100, 0) : [],
            'esSuperAdmin' => $this->esSuperAdmin(),
            'recordatoriosCreados' => $recordatoriosCreados,
            'tipos' => Notificacion::tipos(),
            'estados' => Notificacion::estados(),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Notificaciones', 'content' => $content]);
        $response->html($html);
    }

    // ========================================================================
    // ENVIAR POR WHATSAPP (click-to-WhatsApp o Cloud API)
    // ========================================================================

    public function enviarWhatsApp(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $not = $this->notificacionEnAlcance($id);

        if ($not === null) {
            Session::flashError('Notificacion no encontrada o sin permiso.');
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $tel = $this->telefonoDe($not);

        if ($tel === '') {
            Session::flashError('El destinatario no tiene telefono registrado.');
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $mensaje = $not['titulo'] . "\n" . ($not['cuerpo'] ?? '');

        // Modo cloud: envio automatico por API oficial
        // Modo manual: marca enviada y abre WhatsApp con el mensaje listo
        $this->model->marcar($id, 'enviado');
        header('Location: https://wa.me/' . preg_replace('/\D/', '', $tel) . '?text=' . rawurlencode($mensaje));
        exit;
    }

    // ========================================================================
    // MARCAR ENVIADA / ELIMINAR
    // ========================================================================

    public function marcarEnviada(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $not = $this->notificacionEnAlcance($id);
        if ($not === null) {
            Session::flashError('Notificacion no encontrada.');
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $this->model->marcar($id, 'enviado');
        Session::flashSuccess('Notificacion marcada como enviada.');
        $response->redirectTo('/panel/notificaciones');
    }

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $not = $this->notificacionEnAlcance($id);
        if ($not === null) {
            Session::flashError('Notificacion no encontrada.');
            $response->redirectTo('/panel/notificaciones');
            return;
        }

        $this->model->delete($id);
        Session::flashSuccess('Notificacion eliminada.');
        $response->redirectTo('/panel/notificaciones');
    }
}
