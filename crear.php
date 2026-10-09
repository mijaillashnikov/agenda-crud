<?php
/**
 * crear.php
 * CREATE: muestra el formulario (GET) e inserta el contacto (POST).
 */
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

$contacto = [];
$errores  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $contacto = datosDesdePost();
    $errores  = validarContacto($contacto);

    if (!$errores) {
        $pdo  = obtenerConexion();

        // Evitar correos duplicados
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM contacto WHERE email = :email');
        $stmt->execute([':email' => $contacto['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errores['email'] = 'Ya existe un contacto con este correo.';
        }
    }

    if (!$errores) {
        $sql = 'INSERT INTO contacto (nombres, genero, fecha_nacimiento, telefono, email, linkedin, tipo_sangre)
                VALUES (:nombres, :genero, :fecha_nacimiento, :telefono, :email, :linkedin, :tipo_sangre)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombres'          => $contacto['nombres'],
            ':genero'           => $contacto['genero'],
            ':fecha_nacimiento' => $contacto['fecha_nacimiento'],
            ':telefono'         => $contacto['telefono'],
            ':email'            => $contacto['email'],
            ':linkedin'         => $contacto['linkedin'] !== '' ? $contacto['linkedin'] : null,
            ':tipo_sangre'      => $contacto['tipo_sangre'],
        ]);

        flash('success', 'Contacto "' . $contacto['nombres'] . '" registrado correctamente.');
        redirigir('index.php');
    }
}

$titulo     = 'Nuevo contacto';
$accion     = 'crear.php';
$textoBoton = 'Guardar contacto';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="row g-0">
                <div class="col-md-4 d-none d-md-block form-side-img"
                     style="background-image:url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80')"></div>
                <div class="col-md-8">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-1"><i class="bi bi-person-plus text-primary"></i> Nuevo contacto</h1>
                        <p class="text-muted small mb-4">Los campos marcados con <span class="text-danger">*</span> son obligatorios.</p>
                        <?php if ($errores): ?>
                            <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle"></i> Corrija los errores indicados.</div>
                        <?php endif; ?>
                        <?php require __DIR__ . '/includes/formulario.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
