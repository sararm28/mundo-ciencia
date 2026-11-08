<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/funciones.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user = exigir_sesion();
asegurar_esquema_experimentos();

try {
    match ($action) {
        'guardar_experimento' => guardar_experimento($user),
        'actualizar_experimento' => actualizar_experimento($user),
        'eliminar_experimento' => eliminar_experimento($user),
        default => redireccionar('../gestion_experimentos.php'),
    };
} catch (Throwable $exception) {
    mensaje_temporal('error', 'No se pudo procesar el experimento.');
    redireccionar('../gestion_experimentos.php');
}

function guardar_experimento(array $user): never
{
    $title = trim($_POST['titulo'] ?? '');
    $category = trim($_POST['categoria'] ?? '');
    $description = trim($_POST['descripcion'] ?? '');
    $status = estado_permitido($_POST['estado'] ?? 'publicado', $user);

    if (!datos_experimento_validos($title, $description, $category)) {
        mensaje_temporal('error', 'Completa titulo, descripcion y categoria antes de publicar.');
        redireccionar('../gestion_experimentos.php');
    }

    $fileName = subir_archivo();
    $stmt = base_datos()->prepare('INSERT INTO experimentos (usuario_id, titulo, categoria, descripcion, archivo, estado) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $user['id'], $title, $category, $description, $fileName, $status);
    $stmt->execute();
    $experimentId = (int) base_datos()->insert_id;
    registrar_actividad_usuario((int) $user['id'], 'Creo experimento');
    registrar_actividad_experimento((int) $user['id'], 'Creacion de experimento', $experimentId);

    mensaje_temporal('success', $status === 'publicado' ? 'Experimento publicado correctamente.' : 'Experimento guardado como borrador.');
    redireccionar('../gestion_experimentos.php');
}

function actualizar_experimento(array $user): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $experiment = obtener_experimento($id);

    if (!$experiment || !puede_modificar_experimento($user, $experiment)) {
        mensaje_temporal('error', 'No tienes permisos para editar este experimento.');
        redireccionar('../gestion_experimentos.php');
    }

    $title = trim($_POST['titulo'] ?? '');
    $category = trim($_POST['categoria'] ?? '');
    $description = trim($_POST['descripcion'] ?? '');
    $status = estado_permitido($_POST['estado'] ?? $experiment['estado'], $user);

    if (!datos_experimento_validos($title, $description, $category)) {
        mensaje_temporal('error', 'Completa titulo, descripcion y categoria antes de actualizar.');
        redireccionar('../gestion_experimentos.php?editar=' . $id);
    }

    $fileName = subir_archivo();
    if ($fileName !== null) {
        $stmt = base_datos()->prepare('UPDATE experimentos SET titulo = ?, categoria = ?, descripcion = ?, archivo = ?, estado = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $title, $category, $description, $fileName, $status, $id);
    } else {
        $stmt = base_datos()->prepare('UPDATE experimentos SET titulo = ?, categoria = ?, descripcion = ?, estado = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $title, $category, $description, $status, $id);
    }
    $stmt->execute();

    registrar_actividad_usuario((int) $user['id'], 'Edito experimento');
    registrar_actividad_experimento((int) $user['id'], $status !== $experiment['estado'] ? 'Edicion y cambio de estado' : 'Edicion de experimento', $id);

    mensaje_temporal('success', 'Experimento actualizado correctamente.');
    redireccionar('../gestion_experimentos.php');
}

function eliminar_experimento(array $user): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $experiment = obtener_experimento($id);

    if (!$experiment || !puede_modificar_experimento($user, $experiment)) {
        mensaje_temporal('error', 'No tienes permisos para eliminar este experimento.');
        redireccionar('../gestion_experimentos.php');
    }

    registrar_actividad_usuario((int) $user['id'], 'Elimino experimento');
    registrar_actividad_experimento((int) $user['id'], 'Eliminacion de experimento', $id);

    $stmt = base_datos()->prepare('DELETE FROM experimentos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    mensaje_temporal('success', 'Experimento eliminado.');
    redireccionar('../gestion_experimentos.php');
}

function obtener_experimento(int $id): ?array
{
    $stmt = base_datos()->prepare('SELECT * FROM experimentos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $experiment = $stmt->get_result()->fetch_assoc();
    return $experiment ?: null;
}

function puede_modificar_experimento(array $user, array $experiment): bool
{
    return $user['rol'] === 'admin' || (int) $experiment['usuario_id'] === (int) $user['id'];
}

function estado_permitido(string $status, array $user): string
{
    $status = strtolower(trim($status));
    $allowed = $user['rol'] === 'admin'
        ? ['publicado', 'borrador', 'oculto']
        : ['publicado', 'borrador'];

    return in_array($status, $allowed, true) ? $status : 'publicado';
}

function datos_experimento_validos(string $title, string $description, string $category): bool
{
    return $title !== ''
        && $description !== ''
        && $category !== ''
        && mb_strlen($title) <= 160
        && mb_strlen($category) <= 80
        && mb_strlen($description) <= 5000;
}

function subir_archivo(): ?string
{
    if (empty($_FILES['archivo']['name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
        mensaje_temporal('error', 'El archivo no debe superar 5 MB.');
        redireccionar('../gestion_experimentos.php');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) {
        mensaje_temporal('error', 'Solo se permiten imagenes o PDF.');
        redireccionar('../gestion_experimentos.php');
    }

    $mime = mime_content_type($_FILES['archivo']['tmp_name']);
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($mime, $allowedMime, true)) {
        mensaje_temporal('error', 'El tipo de archivo no es valido.');
        redireccionar('../gestion_experimentos.php');
    }

    $safeName = uniqid('exp_', true) . '.' . $extension;
    $destination = ruta_base('uploads/' . $safeName);
    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destination)) {
        mensaje_temporal('error', 'No se pudo guardar el archivo adjunto.');
        redireccionar('../gestion_experimentos.php');
    }

    return $safeName;
}
