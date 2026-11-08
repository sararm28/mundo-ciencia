<?php
require_once __DIR__ . '/includes/funciones.php';
$pageTitle = 'Mundo Ciencia';
$activePage = 'inicio';
require_once __DIR__ . '/includes/encabezado.php';
?>
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Feria escolar de ciencias</p>
        <h1>Mundo Ciencia</h1>
        <p>Registra tu cuenta, comparte experimentos y consulta tu actividad dentro de una plataforma organizada para estudiantes y administradores.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="registro_usuario.php">Crear cuenta</a>
            <a class="btn btn-outline" href="gestion_experimentos.php">Ver experimentos</a>
        </div>
    </div>
</section>
<section class="content-grid">
    <article class="info-card">
        <h2>Gestión de Usuarios</h2>
        <p>Perfil personal, cambio de contraseña, historial de actividad e inicio de sesión protegido.</p>
    </article>
    <article class="info-card">
        <h2>Administración del Sistema</h2>
        <p>Panel Administrativo, búsqueda, edición, eliminación de usuarios y auditoría de acciones.</p>
    </article>
    <article class="info-card">
        <h2>Seguridad</h2>
        <p>Consultas preparadas, sesiones regeneradas y datos escapados para reducir riesgos comunes.</p>
    </article>
</section>
<?php require_once __DIR__ . '/includes/pie_pagina.php'; ?>
