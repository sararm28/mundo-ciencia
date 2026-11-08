<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/base_datos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ruta_base(string $path = ''): string
{
    return __DIR__ . '/../' . ltrim($path, '/');
}

function redireccionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function mensaje_temporal(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function obtener_mensaje_temporal(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function usuario_actual(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = base_datos()->prepare('SELECT id, nombre, usuario, correo, rol, estado, fecha_registro, ultimo_acceso FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['estado'] !== 'activo') {
        $_SESSION = [];
        session_destroy();
        return null;
    }

    return $user;
}

function asegurar_esquema_experimentos(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $db = base_datos();
    $columns = [];
    $result = $db->query("SHOW COLUMNS FROM experimentos");
    while ($column = $result->fetch_assoc()) {
        $columns[$column['Field']] = true;
    }

    if (!isset($columns['estado'])) {
        $db->query("ALTER TABLE experimentos ADD estado ENUM('publicado','borrador','oculto') NOT NULL DEFAULT 'publicado' AFTER archivo");
    }

    if (!isset($columns['fecha_actualizacion'])) {
        $db->query("ALTER TABLE experimentos ADD fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion");
    }

    $db->query("CREATE TABLE IF NOT EXISTS historial_experimentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        experimento_id INT NULL,
        experimento_referencia_id INT NULL,
        accion VARCHAR(120) NOT NULL,
        fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ip VARCHAR(45) NOT NULL,
        CONSTRAINT fk_historial_experimentos_usuario
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            ON DELETE CASCADE,
        CONSTRAINT fk_historial_experimentos_experimento
            FOREIGN KEY (experimento_id) REFERENCES experimentos(id)
            ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $historyColumns = [];
    $historyResult = $db->query("SHOW COLUMNS FROM historial_experimentos");
    while ($column = $historyResult->fetch_assoc()) {
        $historyColumns[$column['Field']] = true;
    }

    if (!isset($historyColumns['experimento_referencia_id'])) {
        $db->query("ALTER TABLE historial_experimentos ADD experimento_referencia_id INT NULL AFTER experimento_id");
        $db->query("UPDATE historial_experimentos SET experimento_referencia_id = experimento_id WHERE experimento_referencia_id IS NULL");
    }
}

function exigir_sesion(): array
{
    $user = usuario_actual();
    if (!$user) {
        mensaje_temporal('error', 'Debes iniciar sesión para continuar.');
        redireccionar('../inicio_sesion.php');
    }
    return $user;
}

function exigir_administrador(): array
{
    $user = exigir_sesion();
    if ($user['rol'] !== 'admin') {
        mensaje_temporal('error', 'No tienes permisos para acceder al Panel Administrativo.');
        redireccionar('../area_personal/panel_usuario.php');
    }
    return $user;
}

function ip_cliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function navegador_cliente(): string
{
    return substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255);
}

function registrar_actividad_usuario(int $userId, string $action): void
{
    $stmt = base_datos()->prepare('INSERT INTO historial_usuarios (usuario_id, accion, ip, navegador) VALUES (?, ?, ?, ?)');
    $ip = ip_cliente();
    $agent = navegador_cliente();
    $stmt->bind_param('isss', $userId, $action, $ip, $agent);
    $stmt->execute();
}

function registrar_actividad_administrador(int $adminId, string $action, ?int $affectedUserId = null): void
{
    $stmt = base_datos()->prepare('INSERT INTO historial_admin (admin_id, accion, usuario_afectado_id, ip) VALUES (?, ?, ?, ?)');
    $ip = ip_cliente();
    $stmt->bind_param('isis', $adminId, $action, $affectedUserId, $ip);
    $stmt->execute();
}

function registrar_actividad_experimento(int $userId, string $action, ?int $experimentId): void
{
    asegurar_esquema_experimentos();
    $stmt = base_datos()->prepare('INSERT INTO historial_experimentos (usuario_id, experimento_id, experimento_referencia_id, accion, ip) VALUES (?, ?, ?, ?, ?)');
    $ip = ip_cliente();
    $stmt->bind_param('iiiss', $userId, $experimentId, $experimentId, $action, $ip);
    $stmt->execute();
}

function validar_contrasena(string $password): bool
{
    return strlen($password) >= 8;
}

function clase_activa(string $page, string $current): string
{
    return $page === $current ? ' class="activo"' : '';
}
