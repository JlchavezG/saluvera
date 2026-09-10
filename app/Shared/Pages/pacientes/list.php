<?php
/**
 * SALUVERA - Listado de Pacientes
 *
 * @version 2.6.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pacientes = $pacientes ?? [];
$q = $q ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Pacientes</h2>
        <p class="page-subtitle"><?= $total ?> paciente(s) registrado(s)</p>
    </div>
    <a href="<?= url('/panel/pacientes/nuevo') ?>" class="btn btn-primary">+ Nuevo Paciente</a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/pacientes') ?>" class="search-form">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, apellido o correo..." class="search-input">
        <button type="submit" class="btn btn-outline">Buscar</button>
        <?php if ($q !== ''): ?>
            <a href="<?= url('/panel/pacientes') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (empty($pacientes)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <h3>No hay pacientes</h3>
            <?php if ($q !== ''): ?>
                <p>No se encontraron resultados para "<?= htmlspecialchars($q) ?>"</p>
            <?php else: ?>
                <p>Registra tu primer paciente para comenzar.</p>
                <a href="<?= url('/panel/pacientes/nuevo') ?>" class="btn btn-primary">Registrar paciente</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Edad</th>
                        <th>Genero</th>
                        <th>T. Sangre</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                        <?php
                        $edad = null;
                        if (!empty($p['fecha_nacimiento'])) {
                            try {
                                $edad = (new DateTime($p['fecha_nacimiento']))->diff(new DateTime('now'))->y;
                            } catch (Exception $e) {
                                $edad = null;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="cell-name">
                                    <span class="avatar-mini"><?= strtoupper(substr($p['nombre'], 0, 1)) ?></span>
                                    <strong><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['correo'] ?? '—') ?>
                                <?php if (!empty($p['telefono'])): ?>
                                    <br><span class="cell-muted"><?= htmlspecialchars($p['telefono']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $edad !== null ? $edad . ' anos' : '—' ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $p['genero'] ?? '—'))) ?></td>
                            <td><?= htmlspecialchars($p['tipo_sangre'] ?? '—') ?></td>
                            <td><?= date('d/m/Y', strtotime($p['creado_en'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= url('/panel/pacientes?q=' . urlencode($q) . '&page=' . ($page - 1)) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= url('/panel/pacientes?q=' . urlencode($q) . '&page=' . ($page + 1)) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
