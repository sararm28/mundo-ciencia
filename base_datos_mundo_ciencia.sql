CREATE DATABASE IF NOT EXISTS ciencia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ciencia;

DROP TABLE IF EXISTS historial_admin;
DROP TABLE IF EXISTS historial_experimentos;
DROP TABLE IF EXISTS historial_usuarios;
DROP TABLE IF EXISTS experimentos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(60) NOT NULL UNIQUE,
    correo VARCHAR(120) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME NULL,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE experimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    categoria VARCHAR(80) NULL,
    descripcion TEXT NOT NULL,
    archivo VARCHAR(255) NULL,
    estado ENUM('publicado','borrador','oculto') NOT NULL DEFAULT 'publicado',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_experimentos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_experimentos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(120) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45) NOT NULL,
    navegador VARCHAR(255) NOT NULL,
    CONSTRAINT fk_historial_usuarios_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    accion VARCHAR(120) NOT NULL,
    usuario_afectado_id INT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45) NOT NULL,
    CONSTRAINT fk_historial_admin_admin
        FOREIGN KEY (admin_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_historial_admin_afectado
        FOREIGN KEY (usuario_afectado_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (nombre, usuario, correo, contrasena, rol, estado)
VALUES (
    'Administrador Mundo Ciencia',
    'admin',
    'admin@mundociencia.local',
    '$2y$10$ds/xlbSRgJsqQjZYyG8MJO8L.DxhEwVHV1yOSV2kdDwM6dj2ldl0O',
    'admin',
    'activo'
);
