<?php
/**
 * index.php
 * READ: listado de contactos con búsqueda y estadísticas.
 */
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

$pdo      = obtenerConexion();
$busqueda = trim((string) ($_GET['q'] ?? ''));

if ($busqueda !== '') {
    $sql = 'SELECT * FROM contacto
            WHERE nombres LIKE :q1 OR email LIKE :q2 OR telefono LIKE :q3
            ORDER BY nombres ASC';
    $stmt = $pdo->prepare($sql);
    $like = '%' . $busqueda . '%';
    $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM contacto ORDER BY nombres ASC');
    $stmt->execute();
}
$contactos = $stmt->fetchAll();

// Estadísticas generales
$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            SUM(genero = 'Masculino') AS masculino,
            SUM(genero = 'Femenino')  AS femenino,
            SUM(genero = 'Otro')      AS otro
     FROM contacto"
)->fetch();

$titulo = 'Contactos';
require __DIR__ . '/includes/header.php';
?>

<!-- Encabezado con imagen -->
<section class="hero-agenda rounded-4 shadow-sm mb-4 text-white">
    <div class="hero-overlay rounded-4 p-4 p-md-5">
        <h1 class="h2 fw-bold mb-1">Tus contactos, organizados</h1>
        <p class="mb-0 opacity-75">Administra la información de tu agenda de forma rápida y segura.</p>
    </div>
</section>

<!-- Tarjetas de estadísticas -->
<div class="row g-3 mb-4">
    <?php
    $tarjetas = [
        ['Total',      $stats['total'],          'bi-people-fill',       'primary'],
        ['Masculino',  $stats['masculino'] ?? 0, 'bi-gender-male',       'info'],
        ['Femenino',   $stats['femenino'] ?? 0,  'bi-gender-female',     'danger'],
        ['Otro',       $stats['otro'] ?? 0,      'bi-gender-ambiguous',  'secondary'],
    ];
    foreach ($tarjetas as [$etiqueta, $num, $icono, $color]): ?>
        <div class="col-6 col-lg-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-<?= $color ?>-subtle text-<?= $color ?>"><i class="bi <?= $icono ?>"></i></div>
                    <div>
                        <div class="small text-muted"><?= $etiqueta ?></div>
                        <div class="fs-4 fw-bold"><?= (int) $num ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
            <h2 class="h5 mb-0"><i class="bi bi-journal-text text-primary"></i> Listado de contactos</h2>
            <form class="d-flex gap-2" method="get" action="index.php" role="search">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" class="form-control" placeholder="Buscar por nombre, email o teléfono"
                           value="<?= e($busqueda) ?>" maxlength="100">
                </div>
                <button class="btn btn-primary" type="submit">Buscar</button>
                <?php if ($busqueda !== ''): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card-body p-0 p-md-2">
        <?php if (empty($contactos)): ?>
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="Sin contactos" width="110" class="mb-3 opacity-75">
                <p class="text-muted mb-3">
                    <?= $busqueda !== '' ? 'No se encontraron contactos para "' . e($busqueda) . '".' : 'Aún no hay contactos registrados.' ?>
                </p>
                <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Agregar contacto</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-contactos">
                    <thead>
                        <tr>
                            <th>Contacto</th>
                            <th class="d-none d-lg-table-cell">Género</th>
                            <th class="d-none d-md-table-cell">Nacimiento</th>
                            <th>Teléfono</th>
                            <th class="d-none d-xl-table-cell">LinkedIn</th>
                            <th class="text-center">Sangre</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contactos as $c): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e(urlAvatar($c['nombres'], $c['genero'])) ?>" alt="" class="avatar" loading="lazy">
                                    <div>
                                        <div class="fw-semibold"><?= e($c['nombres']) ?></div>
                                        <a href="mailto:<?= e($c['email']) ?>" class="small text-muted text-decoration-none">
                                            <i class="bi bi-envelope"></i> <?= e($c['email']) ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell"><span class="badge badge-genero genero-<?= e(strtolower($c['genero'])) ?>"><?= e($c['genero']) ?></span></td>
                            <td class="d-none d-md-table-cell">
                                <?= e(formatearFecha($c['fecha_nacimiento'])) ?>
                                <div class="small text-muted"><?= calcularEdad($c['fecha_nacimiento']) ?> años</div>
                            </td>
                            <td><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $c['telefono'])) ?>" class="text-decoration-none text-body"><?= e($c['telefono']) ?></a></td>
                            <td class="d-none d-xl-table-cell">
                                <?php if (!empty($c['linkedin'])): ?>
                                    <a href="<?= e($c['linkedin']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-linkedin"></i> Perfil
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge rounded-pill badge-sangre"><?= e($c['tipo_sangre']) ?></span></td>
                            <td class="text-end text-nowrap">
                                <a href="editar.php?id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="eliminar.php" method="post" class="d-inline form-eliminar"
                                      data-nombre="<?= e($c['nombres']) ?>">
                                    <?= campoCsrf() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($contactos)): ?>
        <div class="card-footer bg-white border-0 small text-muted px-4 pb-3">
            Mostrando <?= count($contactos) ?> de <?= (int) $stats['total'] ?> contacto(s)
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
