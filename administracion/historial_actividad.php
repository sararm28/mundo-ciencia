<?php
require_once __DIR__ . '/../includes/funciones.php';
$viewer = exigir_administrador();
$pageTitle = 'Historial de Actividad';
$panelType = 'admin';
$db = base_datos();
asegurar_esquema_experimentos();
$userHistory = base_datos()->query('SELECT h.*, u.nombre, u.correo FROM historial_usuarios h INNER JOIN usuarios u ON u.id = h.usuario_id ORDER BY h.fecha DESC LIMIT 80')->fetch_all(MYSQLI_ASSOC);
$adminHistory = base_datos()->query('SELECT h.*, a.nombre AS admin_nombre, u.nombre AS afectado FROM historial_admin h INNER JOIN usuarios a ON a.id = h.admin_id LEFT JOIN usuarios u ON u.id = h.usuario_afectado_id ORDER BY h.fecha DESC LIMIT 80')->fetch_all(MYSQLI_ASSOC);
$experimentHistory = $db->query('SELECT h.*, u.nombre, e.titulo
    FROM historial_experimentos h
    INNER JOIN usuarios u ON u.id = h.usuario_id
    LEFT JOIN experimentos e ON e.id = h.experimento_id
    ORDER BY h.fecha DESC
    LIMIT 80')->fetch_all(MYSQLI_ASSOC);
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<section class="panel-grid">
    <article class="info-card">
        <h2>Historial de Experimentos</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Usuario</th><th>Accion</th><th>Experimento</th><th>ID</th><th>Fecha</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($experimentHistory as $row): ?>
                    <tr>
                        <td><?= e($row['nombre']) ?></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['titulo'] ?? 'Eliminado') ?></td>
                        <td><?= e((string) ($row['experimento_referencia_id'] ?? $row['experimento_id'] ?? 'No disponible')) ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <article class="info-card">
        <h2>Historial de Actividad de Usuarios</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Usuario</th><th>Accion</th><th>Fecha</th><th>IP</th><th>Navegador</th></tr></thead>
                <tbody>
                <?php foreach ($userHistory as $row): ?>
                    <tr>
                        <td><?= e($row['nombre']) ?><br><span><?= e($row['correo']) ?></span></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                        <td><?= e($row['navegador']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <article class="info-card">
        <h2>Historial Administrativo</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Administrador</th><th>Accion</th><th>Usuario afectado</th><th>Fecha</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($adminHistory as $row): ?>
                    <tr>
                        <td><?= e($row['admin_nombre']) ?></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['afectado'] ?? 'No aplica') ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
