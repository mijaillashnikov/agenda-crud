<?php
/**
 * editar.php
 * UPDATE: carga el contacto (GET) y guarda los cambios (POST).
 */
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

$pdo     = obtenerConexion();
$errores = [];

$id = filter_var($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null),
    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$id) {
    flash('danger', 'Identificador de contacto inválido.');
    redirigir('index.php');
}

// Verificar que el contacto exista
$stmt = $pdo->prepare('SELECT * FROM contacto WHERE id = :id');
$stmt->execute([':id' => $id]);
$contacto = $stmt->fetch();

if (!$contacto) {
    flash('danger', 'El contacto solicitado no existe.');
    redirigir('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $contacto       = datosDesdePost();
    $contacto['id'] = $id;
    $errores        = validarContacto($contacto);

    if (!$errores) {
        // Evitar correo duplicado en otro contacto
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM contacto WHERE email = :email AND id <> :id');
        $stmt->execute([':email' => $contacto['email'], ':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $errores['email'] = 'Otro contacto ya utiliza este correo.';
        }
    }

    if (!$errores) {
        $sql = 'UPDATE contacto SET
                    nombres = :nombres,
                    genero = :genero,
                    fecha_nacimiento = :fecha_nacimiento,
                    telefono = :telefono,
                    email = :email,
                    linkedin = :linkedin,
                    tipo_sangre = :tipo_sangre
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombres'          => $contacto['nombres'],
            ':genero'           => $contacto['genero'],
            ':fecha_nacimiento' => $contacto['fecha_nacimiento'],
            ':telefono'         => $contacto['telefono'],
            ':email'            => $contacto['email'],
            ':linkedin'         => $contacto['linkedin'] !== '' ? $contacto['linkedin'] : null,
            ':tipo_sangre'      => $contacto['tipo_sangre'],
            ':id'               => $id,
        ]);

        flash('success', 'Contacto "' . $contacto['nombres'] . '" actualizado correctamente.');
        redirigir('index.php');
    }
}

$titulo     = 'Editar contacto';
$accion     = 'editar.php';
$textoBoton = 'Actualizar contacto';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="<?= e(urlAvatar($contacto['nombres'] ?: 'Contacto', $contacto['genero'])) ?>" alt="" class="avatar avatar-lg">
                    <div>
                        <h1 class="h4 mb-0"><i class="bi bi-pencil-square text-warning"></i> Editar contacto</h1>
                        <p class="text-muted small mb-0">ID #<?= (int) $id ?> &middot; Los campos con <span class="text-danger">*</span> son obligatorios.</p>
                    </div>
                </div>
                <?php if ($errores): ?>
                    <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle"></i> Corrija los errores indicados.</div>
                <?php endif; ?>
                <?php require __DIR__ . '/includes/formulario.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
