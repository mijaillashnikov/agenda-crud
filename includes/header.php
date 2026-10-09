<?php
/**
 * includes/header.php
 * Cabecera HTML común. Espera la variable opcional $titulo.
 */
$titulo = $titulo ?? 'Agenda de Contactos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> | Agenda de Contactos</title>
    <link rel="icon" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/person-lines-fill.svg">

    <!-- Bootstrap 5, Bootstrap Icons, Google Fonts y SweetAlert2 desde CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-agenda shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="index.php">
            <i class="bi bi-person-lines-fill fs-4"></i> Agenda de Contactos
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="badge rounded-pill text-bg-light d-none d-md-inline">
                <i class="bi bi-hdd-network"></i> HTTP 10.10.10.20 &rarr; BD 10.10.10.30
            </span>
            <a href="crear.php" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Nuevo contacto</span>
            </a>
        </div>
    </div>
</nav>

<main class="container py-4">
<?php if ($flash = obtenerFlash()): ?>
    <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi <?= $flash['tipo'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-1"></i>
        <?= e($flash['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>
