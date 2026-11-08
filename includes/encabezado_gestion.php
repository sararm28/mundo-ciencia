<?php
$pageTitle = $pageTitle ?? 'Gestión del Sistema - Mundo Ciencia';
$panelType = $panelType ?? 'usuario';
$viewer = $viewer ?? usuario_actual();
$prefix = $panelType === 'admin' ? '../' : '../';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../css/estilos_mundo_ciencia.css">
</head>
<body class="gestion-body">
<aside class="sidebar">
    <a class="brand gestion-brand" href="../inicio.php">
        <span class="brand-mark">MC</span>
        <span>Mundo Ciencia</span>
    </a>
    <nav class="side-nav">
        <?php if ($panelType === 'admin'): ?>
            <a href="panel_administrativo.php">Panel Administrativo</a>
            <a href="gestion_usuarios.php">Gestión de Usuarios</a>
            <a href="../gestion_experimentos.php">Gestión de Experimentos</a>
            <a href="historial_actividad.php">Historial de Actividad</a>
        <?php else: ?>
            <a href="panel_usuario.php">Área Personal</a>
            <a href="perfil_usuario.php">Perfil</a>
            <a href="../gestion_experimentos.php">Gestión de Experimentos</a>
        <?php endif; ?>
        <a href="../inicio.php">Inicio</a>
        <a href="../controllers/gestion_sesion.php?action=cerrar_sesion">Cerrar Sesión</a>
    </nav>
</aside>
<section class="gestion-main">
    <header class="gestion-topbar">
        <div>
            <p class="eyebrow"><?= $panelType === 'admin' ? 'Administración del Sistema' : 'Área Personal' ?></p>
            <h1><?= e($pageTitle) ?></h1>
        </div>
        <span class="user-chip"><?= e($viewer['nombre'] ?? '') ?></span>
    </header>
    <?php if ($flash = obtener_mensaje_temporal()): ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
