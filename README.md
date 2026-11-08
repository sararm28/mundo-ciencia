# Mundo Ciencia — Plataforma para feria escolar de ciencias

Aplicación web donde los estudiantes se registran, publican sus experimentos y consultan su actividad. Los administradores gestionan usuarios y revisan el historial de acciones.

## Objetivo
Organizar la información de una feria escolar de ciencias en un solo lugar, en vez de manejar los experimentos en papel o en archivos sueltos.

## Tecnologías
- PHP 8 (lógica del servidor y sesiones)
- MySQL (base de datos)
- HTML5, CSS3 y JavaScript

## Funcionalidades
- Registro e inicio de sesión de usuarios con contraseñas cifradas.
- Publicación, edición y eliminación de experimentos con imagen.
- Perfil personal y cambio de contraseña.
- Panel administrativo: búsqueda, edición y eliminación de usuarios.
- Historial de actividad y auditoría de acciones.
- Consultas preparadas para prevenir inyección SQL.

## Mi aporte
Diseñé la base de datos (usuarios, experimentos e historiales) y programé el registro, el inicio de sesión y la gestión de experimentos. También construí las vistas con HTML y CSS.

## Aprendizaje
Aprendí a conectar PHP con MySQL, a manejar sesiones y roles de usuario y a proteger formularios con consultas preparadas. Como mejora, me gustaría agregar búsqueda de experimentos por categoría.

## Cómo ejecutarlo
1. Instala XAMPP (Apache + MySQL).
2. Copia la carpeta `mundo-ciencia` en `htdocs`.
3. Importa `base_datos_mundo_ciencia.sql` en phpMyAdmin.
4. Revisa los datos de conexión en `config/base_datos.php`.
5. Abre `http://localhost/mundo-ciencia/inicio.php`.

> Usuario de prueba: `admin@mundociencia.local`. La contraseña está en el archivo SQL como hash; cámbiala después del primer ingreso.

---
Sara Rios Muñoz · Grado 11 · Programación de Software · 2026
