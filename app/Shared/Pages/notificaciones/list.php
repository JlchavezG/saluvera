<?php
/**
 * SALUVERA - Centro de Notificaciones (click-to-WhatsApp + portal)
 *
 * @version 2.34.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$notificaciones = $notificaciones ?? [];
$pendientes = $pendientes ?? 0;
$orgId = $orgId ?? 0;
$organizaciones = $organizaciones ?? [];
$esSuperAdmin = $esSuperAdmin ?? false;
$recordatoriosCreados = $recordatoriosCreados ?? 0;
$tipos = $tipos ?? [];
$estados = $estados ?? [];

$badgeEstado = [
    'pendiente' => 'badge-pendiente',
    'enviado' => 'badge-confirmada',
    'entregado' => 'badge-completada',
    'fallido' => 'badge-cancelada',
    'leido' => 'badge-completada',
];
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Notificaciones</h2>
        <p class="page-subtitle"><?= (int) $pendientes ?> pendiente(s) de envio</p>
    </div>
</div>

<?php if ($recordatoriosCreados > 0): ?>
    <div class="form-error-box" style="background:var(--color-success-soft);color:var(--color-success);border-color:var(--color-success)">
        Se generaron <?= (int) $recordatoriosCreados ?> recordatorio(s) automatico(s) para las citas de manana.
    </div>
<?php endif; ?>

<?php if ($esSuperAdmin): ?>
<div class="table-card">
    <form method="GET" action="<?= url('/panel/notificaciones') ?>" class="search-form">
        <select name="org_id" class="search-select" onchange="this.form.submit()">
            <option value="0">Todas las organizaciones</option>
            <?php foreach ($organizaciones as $o): ?>
                <option value="<?= (int) $o['id'] ?>" <?= (int) $orgId === (int) $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php endif; ?>

<?php if (empty($notificaciones)): ?>
    <div class="table-card empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <h3>Sin notificaciones</h3>
        <p>Se crean automaticamente al confirmar/cancelar citas, subir documentos y como recordatorios de las citas de manana.</p>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Destinatario</th>
                        <th>Tipo</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $n): ?>
                        <?php
                        $destNombre = $n['tipo_destinatario'] === 'paciente'
                            ? trim(($n['pac_nombre'] ?? '') . ' ' . ($n['pac_apellidos'] ?? ''))
                            : trim(($n['usu_nombre'] ?? '') . ' ' . ($n['usu_apellidos'] ?? ''));
                        $telefono = $n['tipo_destinatario'] === 'paciente' ? ($n['pac_telefono'] ?? '') : ($n['usu_telefono'] ?? '');
                        $cuerpo = trim(($n['titulo'] ?? '') . '. ' . ($n['cuerpo'] ?? ''));
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($destNombre ?: 'Sin nombre') ?></strong>
                                <?php if ($telefono !== ''): ?>
                                    <br><span class="cell-muted"><?= htmlspecialchars($telefono) ?></span>
                                <?php else: ?>
                                    <br><span class="cell-muted">sin telefono</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($tipos[$n['tipo_notificacion']] ?? ucfirst($n['tipo_notificacion'])) ?></td>
                            <td title="<?= htmlspecialchars($cuerpo) ?>">
                                <?= htmlspecialchars(mb_strimwidth($cuerpo, 0, 70, '...')) ?>
                            </td>
                            <td><span class="badge-estado <?= $badgeEstado[$n['estado']] ?? 'badge-pendiente' ?>"><?= htmlspecialchars($estados[$n['estado']] ?? ucfirst($n['estado'])) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($n['creado_en'])) ?></td>
                            <td>
                                <div class="row-actions">
                                    <?php if ($telefono !== ''): ?>
                                        <a class="action-btn action-view"
                                           href="<?= url('/panel/notificaciones/' . (int) $n['id'] . '/whatsapp') ?>"
                                           target="_blank" rel="noopener"
                                           title="Abrir WhatsApp (enviar o reenviar)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($n['estado'] === 'pendiente'): ?>
                                        <form method="POST" action="<?= url('/panel/notificaciones/' . (int) $n['id'] . '/enviada') ?>" class="inline-form" title="Marcar como enviada">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= url('/panel/notificaciones/' . (int) $n['id'] . '/eliminar') ?>" class="inline-form" onsubmit="return confirm('Eliminar esta notificacion?');">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="action-btn action-delete" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
