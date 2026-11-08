<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/funciones.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    match ($action) {
        'iniciar_sesion' => iniciar_sesion(),
        'registrar_usuario' => registrar_usuario(),
        'cerrar_sesion' => cerrar_sesion(),
        'actualizar_perfil' => actualizar_perfil(),
        'cambiar_contrasena' => cambiar_contrasena(),
        default => redireccionar('../inicio_sesion.php'),
    };
} catch (Throwable $exception) {
    mensaje_temporal('error', 'Ocurrio un error. Revisa que la base de datos este importada.');
    redireccionar('../inicio_sesion.php');
}

function iniciar_sesion(): never
{
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['contrasena'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
        mensaje_temporal('error', 'Ingresa un correo válido y tu contraseña.');
        redireccionar('../inicio_sesion.php');
    }

    $stmt = base_datos()->prepare('SELECT id, nombre, correo, contrasena, rol, estado FROM usuarios WHERE correo = ? LIMIT 1');
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['contrasena'])) {
        mensaje_temporal('error', 'Correo o contraseña incorrectos.');
        redireccionar('../inicio_sesion.php');
    }

    if ($user['estado'] !== 'activo') {
        mensaje_temporal('error', 'Tu usuario esta inactivo. Contacta al administrador.');
        redireccionar('../inicio_sesion.php');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['rol'];

    $now = date('Y-m-d H:i:s');
    $update = base_datos()->prepare('UPDATE usuarios SET ultimo_acceso = ? WHERE id = ?');
    $update->bind_param('si', $now, $user['id']);
    $update->execute();
    registrar_actividad_usuario((int) $user['id'], 'Inicio de sesión');

    redireccionar($user['rol'] === 'admin' ? '../administracion/panel_administrativo.php' : '../area_personal/panel_usuario.php');
}

function registrar_usuario(): never
{
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['contrasena'] ?? '';
    $confirm = $_POST['confirmar_contrasena'] ?? '';

    if ($nombre === '' || $usuario === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mensaje_temporal('error', 'Completa nombre, usuario y correo valido.');
        redireccionar('../registro_usuario.php');
    }

    if ($password !== $confirm || !validar_contrasena($password)) {
        mensaje_temporal('error', 'La contraseña debe coincidir y tener mínimo 8 caracteres.');
        redireccionar('../registro_usuario.php');
    }

    $stmt = base_datos()->prepare('SELECT id FROM usuarios WHERE usuario = ? OR correo = ? LIMIT 1');
    $stmt->bind_param('ss', $usuario, $correo);
    $stmt->execute();

    if ($stmt->get_result()->fetch_assoc()) {
        mensaje_temporal('error', 'El usuario o correo ya existe.');
        redireccionar('../registro_usuario.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = base_datos()->prepare('INSERT INTO usuarios (nombre, usuario, correo, contrasena) VALUES (?, ?, ?, ?)');
    $insert->bind_param('ssss', $nombre, $usuario, $correo, $hash);
    $insert->execute();

    $userId = base_datos()->insert_id;
    registrar_actividad_usuario((int) $userId, 'Registro');
    mensaje_temporal('success', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
    redireccionar('../inicio_sesion.php');
}

function cerrar_sesion(): never
{
    if (!empty($_SESSION['user_id'])) {
        registrar_actividad_usuario((int) $_SESSION['user_id'], 'Cierre de sesión');
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    redireccionar('../inicio.php');
}

function actualizar_perfil(): never
{
    $user = exigir_sesion();
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if ($nombre === '' || $usuario === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mensaje_temporal('error', 'Completa todos los datos del perfil correctamente.');
        redireccionar('../area_personal/perfil_usuario.php');
    }

    $exists = base_datos()->prepare('SELECT id FROM usuarios WHERE (usuario = ? OR correo = ?) AND id <> ? LIMIT 1');
    $exists->bind_param('ssi', $usuario, $correo, $user['id']);
    $exists->execute();
    if ($exists->get_result()->fetch_assoc()) {
        mensaje_temporal('error', 'Ese usuario o correo ya esta en uso.');
        redireccionar('../area_personal/perfil_usuario.php');
    }

    $stmt = base_datos()->prepare('UPDATE usuarios SET nombre = ?, usuario = ?, correo = ? WHERE id = ?');
    $stmt->bind_param('sssi', $nombre, $usuario, $correo, $user['id']);
    $stmt->execute();
    registrar_actividad_usuario((int) $user['id'], 'Edicion de perfil');

    mensaje_temporal('success', 'Perfil actualizado.');
    redireccionar('../area_personal/perfil_usuario.php');
}

function cambiar_contrasena(): never
{
    $user = exigir_sesion();
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirm = $_POST['confirmar'] ?? '';

    if ($nueva !== $confirm || !validar_contrasena($nueva)) {
        mensaje_temporal('error', 'La nueva contraseña debe coincidir y tener mínimo 8 caracteres.');
        redireccionar('../area_personal/perfil_usuario.php');
    }

    $stmt = base_datos()->prepare('SELECT contrasena FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($actual, $row['contrasena'])) {
        mensaje_temporal('error', 'La contraseña actual no es correcta.');
        redireccionar('../area_personal/perfil_usuario.php');
    }

    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $update = base_datos()->prepare('UPDATE usuarios SET contrasena = ? WHERE id = ?');
    $update->bind_param('si', $hash, $user['id']);
    $update->execute();
    registrar_actividad_usuario((int) $user['id'], 'Cambio de contraseña');

    mensaje_temporal('success', 'Contrasena actualizada.');
    redireccionar('../area_personal/perfil_usuario.php');
}
