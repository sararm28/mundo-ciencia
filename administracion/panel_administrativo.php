<?php
require_once __DIR__ . '/../includes/funciones.php';
$viewer = exigir_administrador();
$pageTitle = 'Panel Administrativo';
$panelType = 'admin';
$stats = [
    'total' => base_datos()->query('SELECT COUNT(*) AS total FROM usuarios')->fetch_assoc()['total'],
    'today' => base_datos()->query('SELECT COUNT(*) AS total FROM usuarios WHERE DATE(fecha_registro) = CURDATE()')->fetch_assoc()['total'],
    'active' => base_datos()->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['total'],
];
$recentUsers = base_datos()->query('SELECT id, nombre, correo, rol, estado, fecha_registro, ultimo_acceso FROM usuarios ORDER BY fecha_registro DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<section class="cards-row">
    <article class="stat-card"><span>Total usuarios</span><strong><?= (int) $stats['total'] ?></strong></article>
    <article class="stat-card"><span>Registrados hoy</span><strong><?= (int) $stats['today'] ?></strong></article>
    <article class="stat-card"><span>Usuarios activos</span><strong><?= (int) $stats['active'] ?></strong></article>
</section>
<section class="info-card">
    <div class="section-head">
        <h2>Registros recientes</h2>
        <a class="btn btn-primary" href="gestion_usuarios.php">Gestionar Usuarios</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Registro</th><th>Ultimo acceso</th></tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $user): ?>
                <tr>
                    <td><?= e($user['nombre']) ?></td>
                    <td><?= e($user['correo']) ?></td>
                    <td><?= e($user['rol']) ?></td>
                    <td><?= e($user['estado']) ?></td>
                    <td><?= e($user['fecha_registro']) ?></td>
                    <td><?= e($user['ultimo_acceso'] ?? 'Sin registro') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
