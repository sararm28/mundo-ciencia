<?php
require_once __DIR__ . '/../includes/funciones.php';
$viewer = exigir_administrador();
$pageTitle = 'Gestión de Usuarios';
$panelType = 'admin';
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = base_datos()->prepare('SELECT * FROM usuarios WHERE nombre LIKE ? OR usuario LIKE ? OR correo LIKE ? ORDER BY fecha_registro DESC');
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = base_datos()->query('SELECT * FROM usuarios ORDER BY fecha_registro DESC')->fetch_all(MYSQLI_ASSOC);
}

$edit = null;
if (!empty($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = base_datos()->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<section class="two-column gestion-form-grid">
    <form class="form-card" action="../controllers/gestion_administracion.php?action=guardar_usuario" method="post">
        <h2><?= $edit ? 'Editar usuario' : 'Crear usuario' ?></h2>
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <label>Nombre
            <input type="text" name="nombre" value="<?= e($edit['nombre'] ?? '') ?>" required>
        </label>
        <label>Usuario
            <input type="text" name="usuario" value="<?= e($edit['usuario'] ?? '') ?>" required>
        </label>
        <label>Correo
            <input type="email" name="correo" value="<?= e($edit['correo'] ?? '') ?>" required>
        </label>
        <?php if (!$edit): ?>
            <label>Contrasena inicial
                <input type="password" name="contrasena" minlength="8" required>
            </label>
        <?php endif; ?>
        <label>Rol
            <select name="rol">
                <option value="usuario" <?= (($edit['rol'] ?? '') === 'usuario') ? 'selected' : '' ?>>Usuario</option>
                <option value="admin" <?= (($edit['rol'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
            </select>
        </label>
        <label>Estado
            <select name="estado">
                <option value="activo" <?= (($edit['estado'] ?? '') === 'activo') ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= (($edit['estado'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Guardar usuario</button>
    </form>
    <section class="info-card">
        <div class="section-head">
            <h2>Busqueda de Usuarios</h2>
        </div>
        <form class="search-form" method="get">
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Nombre, usuario o correo">
            <button class="btn btn-outline" type="submit">Buscar</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= e($user['nombre']) ?></strong><br><span><?= e($user['usuario']) ?></span></td>
                        <td><?= e($user['correo']) ?></td>
                        <td><?= e($user['rol']) ?></td>
                        <td><?= e($user['estado']) ?></td>
                        <td class="actions-cell">
                            <a class="btn btn-outline" href="gestion_usuarios.php?edit=<?= (int) $user['id'] ?>">Editar</a>
                            <?php if ((int) $user['id'] !== (int) $viewer['id']): ?>
                                <form action="../controllers/gestion_administracion.php?action=eliminar_usuario" method="post" data-confirm="Eliminar usuario?">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
