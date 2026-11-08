<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/funciones.php';

$admin = exigir_administrador();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    match ($action) {
        'guardar_usuario' => guardar_usuario($admin),
        'eliminar_usuario' => eliminar_usuario($admin),
        default => redireccionar('../administracion/gestion_usuarios.php'),
    };
} catch (Throwable $exception) {
    mensaje_temporal('error', 'No se pudo completar la accion administrativa.');
    redireccionar('../administracion/gestion_usuarios.php');
}

function guardar_usuario(array $admin): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $rol = $_POST['rol'] === 'admin' ? 'admin' : 'usuario';
    $estado = $_POST['estado'] === 'inactivo' ? 'inactivo' : 'activo';

    if ($nombre === '' || $usuario === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mensaje_temporal('error', 'Datos de usuario invalidos.');
        redireccionar('../administracion/gestion_usuarios.php');
    }

    if ($id > 0) {
        $stmt = base_datos()->prepare('UPDATE usuarios SET nombre = ?, usuario = ?, correo = ?, rol = ?, estado = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $nombre, $usuario, $correo, $rol, $estado, $id);
        $stmt->execute();
        registrar_actividad_administrador((int) $admin['id'], 'Edito usuario', $id);
        mensaje_temporal('success', 'Usuario actualizado.');
    } else {
        $password = $_POST['contrasena'] ?? '';
        if (!validar_contrasena($password)) {
            mensaje_temporal('error', 'La contraseña debe tener mínimo 8 caracteres.');
            redireccionar('../administracion/gestion_usuarios.php');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = base_datos()->prepare('INSERT INTO usuarios (nombre, usuario, correo, contrasena, rol, estado) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $nombre, $usuario, $correo, $hash, $rol, $estado);
        $stmt->execute();
        $createdId = base_datos()->insert_id;
        registrar_actividad_administrador((int) $admin['id'], 'Creo usuario', (int) $createdId);
        mensaje_temporal('success', 'Usuario creado.');
    }

    redireccionar('../administracion/gestion_usuarios.php');
}

function eliminar_usuario(array $admin): never
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0 || $id === (int) $admin['id']) {
        mensaje_temporal('error', 'No puedes eliminar ese usuario.');
        redireccionar('../administracion/gestion_usuarios.php');
    }

    $stmt = base_datos()->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    registrar_actividad_administrador((int) $admin['id'], 'Elimino usuario', $id);

    mensaje_temporal('success', 'Usuario eliminado.');
    redireccionar('../administracion/gestion_usuarios.php');
}
