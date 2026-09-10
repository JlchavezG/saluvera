<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class DashboardController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $user = Session::user() ?? [];

        $stats = [
            'pacientes' => (int) $db->fetchColumn("SELECT COUNT(*) FROM pacientes"),
            'citas' => (int) $db->fetchColumn("SELECT COUNT(*) FROM citas"),
            'profesionales' => (int) $db->fetchColumn("SELECT COUNT(*) FROM profesionales"),
            'usuarios' => (int) $db->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE activo = 1"),
        ];

        $content = View::render('Pages.dashboard', [
            'stats' => $stats,
            'user' => $user,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Dashboard',
            'content' => $content,
        ]);

        $response->html($html);
    }
}
