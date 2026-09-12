<?php
/**
 * SALUVERA - Error de acceso al portal
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$mensaje = $mensaje ?? 'El enlace no es valido.';
?>
<div class="auth-card" style="max-width:520px;margin:60px auto;text-align:center">
    <h2 class="auth-title">Acceso no valido</h2>
    <p class="auth-subtitle"><?= htmlspecialchars($mensaje) ?></p>
    <a href="<?= url('/') ?>" class="btn btn-primary">Ir al inicio</a>
</div>
