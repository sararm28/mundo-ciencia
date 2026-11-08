<?php
require_once __DIR__ . '/includes/funciones.php';
$pageTitle = 'Inicio de Sesion - Mundo Ciencia';
$activePage = 'inicio_sesion';
require_once __DIR__ . '/includes/encabezado.php';
?>
<section class="auth-shell">
    <form class="form-card" action="controllers/gestion_sesion.php?action=iniciar_sesion" method="post">
        <h1>Inicio de Sesion</h1>
        <label>Correo
            <input type="email" name="correo" required autocomplete="email">
        </label>
        <label>Contrasena
            <input type="password" name="contrasena" required autocomplete="current-password">
        </label>
        <button class="btn btn-primary" type="submit">Ingresar</button>
        <p>No tienes cuenta? <a href="registro_usuario.php">Crear una cuenta</a></p>
    </form>
</section>
<?php require_once __DIR__ . '/includes/pie_pagina.php'; ?>
