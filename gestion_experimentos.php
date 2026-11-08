<?php
require_once __DIR__ . '/includes/funciones.php';
$pageTitle = 'Gestión de Experimentos - Mundo Ciencia';
$activePage = 'experimentos';
$viewer = usuario_actual();
asegurar_esquema_experimentos();

$page = max(1, (int) ($_GET['pagina'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;
$totalPublished = (int) base_datos()->query("SELECT COUNT(*) AS total FROM experimentos WHERE estado = 'publicado'")->fetch_assoc()['total'];
$totalPages = max(1, (int) ceil($totalPublished / $perPage));

$publishedStmt = base_datos()->prepare("SELECT e.*, u.nombre AS autor_nombre, u.rol AS autor_rol
    FROM experimentos e
    INNER JOIN usuarios u ON u.id = e.usuario_id
    WHERE e.estado = 'publicado'
    ORDER BY e.fecha_creacion DESC
    LIMIT ? OFFSET ?");
$publishedStmt->bind_param('ii', $perPage, $offset);
$publishedStmt->execute();
$publishedExperiments = $publishedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$managedExperiments = [];
$editingExperiment = null;
if ($viewer) {
    $stmt = base_datos()->prepare('SELECT e.*, u.nombre AS autor_nombre FROM experimentos e INNER JOIN usuarios u ON u.id = e.usuario_id WHERE e.usuario_id = ? OR ? = "admin" ORDER BY e.fecha_creacion DESC');
    $stmt->bind_param('is', $viewer['id'], $viewer['rol']);
    $stmt->execute();
    $managedExperiments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $editId = (int) ($_GET['editar'] ?? 0);
    if ($editId > 0) {
        foreach ($managedExperiments as $experiment) {
            if ((int) $experiment['id'] === $editId) {
                $editingExperiment = $experiment;
                break;
            }
        }
    }
}

function resumen_experimento(string $text, int $limit = 180): string
{
    $clean = trim(preg_replace('/\s+/', ' ', $text));
    return mb_strlen($clean) > $limit ? mb_substr($clean, 0, $limit - 3) . '...' : $clean;
}

function archivo_es_imagen(?string $file): bool
{
    return $file !== null && preg_match('/\.(jpe?g|png|gif)$/i', $file) === 1;
}

require_once __DIR__ . '/includes/encabezado.php';
?>
<section class="page-band">
    <div>
        <p class="eyebrow">Proyectos</p>
        <h1>Gestión de Experimentos</h1>
    </div>
</section>
<?php if ($viewer): ?>
    <section class="two-column">
        <form class="form-card" action="controllers/gestion_experimentos.php?action=<?= $editingExperiment ? 'actualizar_experimento' : 'guardar_experimento' ?>" method="post" enctype="multipart/form-data">
            <h2><?= $editingExperiment ? 'Editar Experimento' : 'Publicar Experimento' ?></h2>
            <?php if ($editingExperiment): ?>
                <input type="hidden" name="id" value="<?= (int) $editingExperiment['id'] ?>">
            <?php endif; ?>
            <label>Titulo
                <input type="text" name="titulo" required maxlength="160" value="<?= e($editingExperiment['titulo'] ?? '') ?>">
            </label>
            <label>Categoria
                <input type="text" name="categoria" required maxlength="80" value="<?= e($editingExperiment['categoria'] ?? '') ?>">
            </label>
            <label>Descripcion
                <textarea name="descripcion" rows="5" required maxlength="5000"><?= e($editingExperiment['descripcion'] ?? '') ?></textarea>
            </label>
            <label>Estado
                <select name="estado" required>
                    <?php $currentStatus = $editingExperiment['estado'] ?? 'publicado'; ?>
                    <option value="publicado" <?= $currentStatus === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                    <option value="borrador" <?= $currentStatus === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    <?php if ($viewer['rol'] === 'admin'): ?>
                        <option value="oculto" <?= $currentStatus === 'oculto' ? 'selected' : '' ?>>Oculto</option>
                    <?php endif; ?>
                </select>
            </label>
            <label>Imagen o archivo adjunto
                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.gif,.pdf">
            </label>
            <?php if (!empty($editingExperiment['archivo'])): ?>
                <a href="uploads/<?= e($editingExperiment['archivo']) ?>" target="_blank">Archivo actual</a>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit"><?= $editingExperiment ? 'Actualizar experimento' : 'Publicar' ?></button>
            <?php if ($editingExperiment): ?>
                <a class="btn btn-outline" href="gestion_experimentos.php">Cancelar edición</a>
            <?php endif; ?>
        </form>
        <div class="stack">
            <div class="section-head">
                <h2>Mis Experimentos<?= $viewer['rol'] === 'admin' ? ' / Todos' : '' ?></h2>
            </div>
            <?php if (!$managedExperiments): ?>
                <div class="empty-state compact">Aun no hay experimentos registrados.</div>
            <?php endif; ?>
            <?php foreach ($managedExperiments as $experiment): ?>
                <article class="info-card">
                    <p class="eyebrow"><?= e($experiment['categoria']) ?> · <?= e(ucfirst($experiment['estado'])) ?></p>
                    <h2><?= e($experiment['titulo']) ?></h2>
                    <p><?= e(resumen_experimento($experiment['descripcion'])) ?></p>
                    <p class="meta">Autor: <?= e($experiment['autor_nombre']) ?> | Publicado: <?= e($experiment['fecha_creacion']) ?> | Actualizado: <?= e($experiment['fecha_actualizacion']) ?></p>
                    <?php if ($experiment['archivo']): ?>
                        <a href="uploads/<?= e($experiment['archivo']) ?>" target="_blank">Ver archivo</a>
                    <?php endif; ?>
                    <div class="card-actions">
                        <a class="btn btn-outline" href="gestion_experimentos.php?editar=<?= (int) $experiment['id'] ?>">Editar</a>
                        <form action="controllers/gestion_experimentos.php?action=eliminar_experimento" method="post" data-confirm="Eliminar este experimento?">
                            <input type="hidden" name="id" value="<?= (int) $experiment['id'] ?>">
                            <button class="btn btn-danger" type="submit">Eliminar</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php else: ?>
    <section class="empty-state">
        <h2>Inicia sesión para publicar experimentos</h2>
        <p>Las publicaciones existentes se cargan desde MySQL y permanecen disponibles al volver a ingresar.</p>
        <a class="btn btn-primary" href="inicio_sesion.php">Iniciar sesión</a>
    </section>
<?php endif; ?>
<section class="published-section">
    <div class="section-head">
        <div>
            <p class="eyebrow">Comunidad</p>
            <h2>Experimentos Publicados</h2>
        </div>
        <span class="meta"><?= (int) $totalPublished ?> publicaciones</span>
    </div>
    <?php if (!$publishedExperiments): ?>
        <div class="empty-state compact">Todavia no hay experimentos publicados.</div>
    <?php else: ?>
        <div class="experiment-grid">
            <?php foreach ($publishedExperiments as $experiment): ?>
                <?php $canEdit = $viewer && ($viewer['rol'] === 'admin' || (int) $viewer['id'] === (int) $experiment['usuario_id']); ?>
                <article class="experiment-card">
                    <?php if (archivo_es_imagen($experiment['archivo'])): ?>
                        <img src="uploads/<?= e($experiment['archivo']) ?>" alt="<?= e($experiment['titulo']) ?>">
                    <?php else: ?>
                        <div class="experiment-placeholder"><?= e(mb_substr($experiment['categoria'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="experiment-body">
                        <p class="eyebrow"><?= e($experiment['categoria']) ?></p>
                        <h3><?= e($experiment['titulo']) ?></h3>
                        <p><?= e(resumen_experimento($experiment['descripcion'])) ?></p>
                        <p class="meta">Por <?= e($experiment['autor_nombre']) ?> | <?= e($experiment['fecha_creacion']) ?></p>
                        <div class="card-actions">
                            <?php if ($experiment['archivo']): ?>
                                <a class="btn btn-outline" href="uploads/<?= e($experiment['archivo']) ?>" target="_blank">Ver mas</a>
                            <?php else: ?>
                                <a class="btn btn-outline" href="#exp-<?= (int) $experiment['id'] ?>">Ver mas</a>
                            <?php endif; ?>
                            <?php if ($canEdit): ?>
                                <a class="btn btn-outline" href="gestion_experimentos.php?editar=<?= (int) $experiment['id'] ?>">Editar</a>
                                <form action="controllers/gestion_experimentos.php?action=eliminar_experimento" method="post" data-confirm="Eliminar este experimento?">
                                    <input type="hidden" name="id" value="<?= (int) $experiment['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Paginacion de experimentos">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="<?= $i === $page ? 'activo' : '' ?>" href="gestion_experimentos.php?pagina=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/pie_pagina.php'; ?>
