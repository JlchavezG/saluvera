<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class DashboardController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $userModel = new User();
        $citaModel = new Cita();

        $sessionUser = Session::user() ?? [];
        $fullUser = $userModel->findById(Session::getUserId());
        $orgId = (int) ($fullUser['organizacion_id'] ?? 0);
        $rolSlug = $sessionUser['rol_slug'] ?? '';

        $esSuperAdmin = $rolSlug === 'superadmin';

        // Informacion completa del usuario para la bienvenida
        $datosBienvenida = [
            'nombre' => $sessionUser['nombre'] ?? 'Usuario',
            'apellidos' => $sessionUser['apellidos'] ?? '',
            'rol_nombre' => $sessionUser['rol_nombre'] ?? '',
            'rol_slug' => $rolSlug,
            'correo' => $sessionUser['correo'] ?? '',
            'organizacion_nombre' => $esSuperAdmin ? 'Plataforma Global' : ($this->getOrgNombre($db, $orgId) ?? ''),
        ];

        if ($esSuperAdmin) {
            // SUPERADMIN: metricas globales de TODA la plataforma
            $stats = [
                'organizaciones' => (int) $db->fetchColumn("SELECT COUNT(*) FROM organizaciones WHERE activo = 1"),
                'organizaciones_total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM organizaciones"),
                'usuarios' => (int) $db->fetchColumn("SELECT COUNT(*) FROM usuarios WHERE activo = 1"),
                'profesionales' => (int) $db->fetchColumn("SELECT COUNT(*) FROM profesionales WHERE activo = 1"),
                'pacientes' => (int) $db->fetchColumn("SELECT COUNT(*) FROM pacientes WHERE activo = 1"),
                'citas' => (int) $db->fetchColumn("SELECT COUNT(*) FROM citas"),
                'citas_hoy' => (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM citas WHERE fecha_cita = CURDATE()"
                ),
                'consultorios' => (int) $db->fetchColumn("SELECT COUNT(*) FROM consultorios WHERE activo = 1"),
            ];
            $scope = 'global';
        } else {
            // CLINIC_ADMIN: metricas solo de su organizacion
            $stats = [
                'pacientes' => (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM pacientes WHERE organizacion_id = ? AND activo = 1",
                    [$orgId]
                ),
                'citas_hoy' => $citaModel->countHoyOrg($orgId),
                'citas' => $citaModel->countByOrg($orgId),
                'profesionales' => (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM profesionales WHERE organizacion_id = ? AND activo = 1",
                    [$orgId]
                ),
                'usuarios' => (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM usuarios WHERE organizacion_id = ? AND activo = 1",
                    [$orgId]
                ),
                'consultorios' => (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM consultorios WHERE organizacion_id = ? AND activo = 1",
                    [$orgId]
                ),
            ];
            $scope = 'org';
        }

        // Ultimas 5 organizaciones creadas (solo superadmin)
        $ultimasOrgs = [];
        if ($esSuperAdmin) {
            $ultimasOrgs = $db->fetchAll(
                "SELECT o.id, o.nombre, o.plan_suscripcion, o.estado_suscripcion, o.creado_en,
                        (SELECT COUNT(*) FROM usuarios u WHERE u.organizacion_id = o.id AND u.activo = 1) as total_usuarios,
                        (SELECT COUNT(*) FROM pacientes p WHERE p.organizacion_id = o.id AND p.activo = 1) as total_pacientes
                 FROM organizaciones o
                 ORDER BY o.creado_en DESC
                 LIMIT 5"
            );
        }

        $content = View::render('Pages.dashboard', [
            'stats' => $stats,
            'scope' => $scope,
            'esSuperAdmin' => $esSuperAdmin,
            'user' => $datosBienvenida,
            'ultimasOrgs' => $ultimasOrgs,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Dashboard',
            'content' => $content,
        ]);

        $response->html($html);
    }

    private function getOrgNombre(Database $db, int $orgId): ?string
    {
        $org = $db->fetchOne("SELECT nombre FROM organizaciones WHERE id = ?", [$orgId]);
        return $org !== null ? $org['nombre'] : null;
    }
}
