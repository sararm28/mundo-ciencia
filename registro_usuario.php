<?php
require_once __DIR__ . '/includes/funciones.php';
$pageTitle = 'Registro de Usuario - Mundo Ciencia';
$activePage = 'registro';
require_once __DIR__ . '/includes/encabezado.php';
?>
<section class="auth-shell">
    <form class="form-card" action="controllers/gestion_sesion.php?action=registrar_usuario" method="post">
        <h1>Registro de Usuario</h1>
        <label>Nombre completo
            <input type="text" name="nombre" required maxlength="120">
        </label>
        <label>Usuario
            <input type="text" name="usuario" required maxlength="60">
        </label>
        <label>Correo
            <input type="email" name="correo" required maxlength="120">
        </label>
        <label>Contrasena
            <input type="password" name="contrasena" required minlength="8">
        </label>
        <label>Confirmar contraseña
            <input type="password" name="confirmar_contrasena" required minlength="8">
        </label>
        <button class="btn btn-primary" type="submit">Registrar</button>
        <p>¿Ya tienes cuenta? <a href="inicio_sesion.php">Iniciar sesión</a></p>
    </form>
</section>
<?php require_once __DIR__ . '/includes/pie_pagina.php'; ?>
