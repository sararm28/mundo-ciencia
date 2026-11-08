<?php
require_once __DIR__ . '/../includes/funciones.php';
$viewer = exigir_sesion();
$pageTitle = 'Area Personal';
$panelType = 'usuario';
$loginHistory = base_datos()->prepare('SELECT accion, fecha, ip, navegador FROM historial_usuarios WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 8');
$loginHistory->bind_param('i', $viewer['id']);
$loginHistory->execute();
$history = $loginHistory->get_result()->fetch_all(MYSQLI_ASSOC);
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<section class="cards-row">
    <article class="stat-card"><span>Usuario</span><strong><?= e($viewer['usuario']) ?></strong></article>
    <article class="stat-card"><span>Registro</span><strong><?= e($viewer['fecha_registro']) ?></strong></article>
    <article class="stat-card"><span>Ultimo acceso</span><strong><?= e($viewer['ultimo_acceso'] ?? 'Sin registro') ?></strong></article>
</section>
<section class="panel-grid">
    <article class="info-card">
        <h2>Informacion Personal</h2>
        <p><strong>Nombre:</strong> <?= e($viewer['nombre']) ?></p>
        <p><strong>Correo:</strong> <?= e($viewer['correo']) ?></p>
        <p><strong>Estado:</strong> <?= e($viewer['estado']) ?></p>
        <a class="btn btn-primary" href="perfil_usuario.php">Editar perfil</a>
    </article>
    <article class="info-card">
        <h2>Actividad reciente</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Accion</th><th>Fecha</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($history as $row): ?>
                    <tr><td><?= e($row['accion']) ?></td><td><?= e($row['fecha']) ?></td><td><?= e($row['ip']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
