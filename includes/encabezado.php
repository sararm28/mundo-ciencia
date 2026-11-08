<?php
$pageTitle = $pageTitle ?? 'Mundo Ciencia';
$activePage = $activePage ?? '';
$viewer = usuario_actual();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="css/estilos_mundo_ciencia.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="inicio.php">
        <span class="brand-mark">MC</span>
        <span>Mundo Ciencia</span>
    </a>
    <nav class="top-nav">
        <a<?= clase_activa('inicio', $activePage) ?> href="inicio.php">Inicio</a>
        <a<?= clase_activa('experimentos', $activePage) ?> href="gestion_experimentos.php">Gestión de Experimentos</a>
        <?php if ($viewer && $viewer['rol'] === 'admin'): ?>
            <a<?= clase_activa('admin', $activePage) ?> href="administracion/panel_administrativo.php">Panel Administrativo</a>
        <?php elseif ($viewer): ?>
            <a<?= clase_activa('usuario', $activePage) ?> href="area_personal/panel_usuario.php">Área Personal</a>
        <?php endif; ?>
    </nav>
    <div class="session-actions">
        <?php if ($viewer): ?>
            <span><?= e($viewer['nombre']) ?></span>
            <a class="btn btn-outline" href="controllers/gestion_sesion.php?action=cerrar_sesion">Cerrar Sesión</a>
        <?php else: ?>
            <a class="btn btn-outline" href="inicio_sesion.php">Inicio de Sesión</a>
            <a class="btn btn-primary" href="registro_usuario.php">Registro de Usuario</a>
        <?php endif; ?>
    </div>
</header>
<main>
<?php if ($flash = obtener_mensaje_temporal()): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
