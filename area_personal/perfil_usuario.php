<?php
require_once __DIR__ . '/../includes/funciones.php';
$viewer = exigir_sesion();
$pageTitle = 'Informacion del Usuario';
$panelType = 'usuario';
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<section class="two-column gestion-form-grid">
    <form class="form-card" action="../controllers/gestion_sesion.php?action=actualizar_perfil" method="post">
        <h2>Datos personales</h2>
        <label>Nombre
            <input type="text" name="nombre" value="<?= e($viewer['nombre']) ?>" required>
        </label>
        <label>Usuario
            <input type="text" name="usuario" value="<?= e($viewer['usuario']) ?>" required>
        </label>
        <label>Correo
            <input type="email" name="correo" value="<?= e($viewer['correo']) ?>" required>
        </label>
        <button class="btn btn-primary" type="submit">Guardar cambios</button>
    </form>
    <form class="form-card" action="../controllers/gestion_sesion.php?action=cambiar_contrasena" method="post">
        <h2>Cambiar contraseña</h2>
        <label>Contrasena actual
            <input type="password" name="actual" required>
        </label>
        <label>Nueva contraseña
            <input type="password" name="nueva" required minlength="8">
        </label>
        <label>Confirmar nueva contraseña
            <input type="password" name="confirmar" required minlength="8">
        </label>
        <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
