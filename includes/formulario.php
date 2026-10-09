<?php
/**
 * includes/formulario.php
 * Formulario reutilizable para crear y editar contactos.
 *
 * Variables esperadas:
 *   $contacto  array  valores actuales de los campos
 *   $errores   array  campo => mensaje de error
 *   $accion    string URL destino del formulario
 *   $textoBoton string texto del botón de envío
 */

$valor   = fn(string $k): string => e($contacto[$k] ?? '');
$invalid = fn(string $k): string => isset($errores[$k]) ? ' is-invalid' : '';
$error   = fn(string $k): string => isset($errores[$k])
    ? '<div class="invalid-feedback">' . e($errores[$k]) . '</div>'
    : '';
?>
<form action="<?= e($accion) ?>" method="post" class="needs-validation" novalidate>
    <?= campoCsrf() ?>
    <?php if (!empty($contacto['id'])): ?>
        <input type="hidden" name="id" value="<?= (int) $contacto['id'] ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12">
            <label for="nombres" class="form-label">Nombres completos <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control<?= $invalid('nombres') ?>" id="nombres" name="nombres"
                       maxlength="100" required placeholder="Ej. María Fernanda López" value="<?= $valor('nombres') ?>">
                <?= $error('nombres') ?: '<div class="invalid-feedback">Ingrese los nombres.</div>' ?>
            </div>
        </div>

        <div class="col-md-6">
            <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
            <select class="form-select<?= $invalid('genero') ?>" id="genero" name="genero" required>
                <option value="">Seleccione...</option>
                <?php foreach (GENEROS as $g): ?>
                    <option value="<?= e($g) ?>" <?= ($contacto['genero'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $error('genero') ?: '<div class="invalid-feedback">Seleccione un género.</div>' ?>
        </div>

        <div class="col-md-6">
            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
            <input type="date" class="form-control<?= $invalid('fecha_nacimiento') ?>" id="fecha_nacimiento"
                   name="fecha_nacimiento" required min="1900-01-01" max="<?= date('Y-m-d') ?>"
                   value="<?= $valor('fecha_nacimiento') ?>">
            <?= $error('fecha_nacimiento') ?: '<div class="invalid-feedback">Ingrese una fecha válida.</div>' ?>
        </div>

        <div class="col-md-6">
            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="tel" class="form-control<?= $invalid('telefono') ?>" id="telefono" name="telefono"
                       maxlength="20" required pattern="\+?[0-9\s()\-]{6,20}" placeholder="Ej. +51 987 654 321"
                       value="<?= $valor('telefono') ?>">
                <?= $error('telefono') ?: '<div class="invalid-feedback">Ingrese un teléfono válido.</div>' ?>
            </div>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control<?= $invalid('email') ?>" id="email" name="email"
                       maxlength="100" required placeholder="Ej. nombre@correo.com" value="<?= $valor('email') ?>">
                <?= $error('email') ?: '<div class="invalid-feedback">Ingrese un correo válido.</div>' ?>
            </div>
        </div>

        <div class="col-md-8">
            <label for="linkedin" class="form-label">LinkedIn <small class="text-muted">(opcional)</small></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-linkedin"></i></span>
                <input type="url" class="form-control<?= $invalid('linkedin') ?>" id="linkedin" name="linkedin"
                       maxlength="150" placeholder="https://www.linkedin.com/in/usuario" value="<?= $valor('linkedin') ?>">
                <?= $error('linkedin') ?: '<div class="invalid-feedback">Ingrese una URL válida.</div>' ?>
            </div>
        </div>

        <div class="col-md-4">
            <label for="tipo_sangre" class="form-label">Tipo de sangre <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-droplet-fill text-danger"></i></span>
                <select class="form-select<?= $invalid('tipo_sangre') ?>" id="tipo_sangre" name="tipo_sangre" required>
                    <option value="">Seleccione...</option>
                    <?php foreach (TIPOS_SANGRE as $t): ?>
                        <option value="<?= e($t) ?>" <?= ($contacto['tipo_sangre'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $error('tipo_sangre') ?: '<div class="invalid-feedback">Seleccione el tipo de sangre.</div>' ?>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= e($textoBoton) ?></button>
    </div>
</form>
