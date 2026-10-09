<?php
/**
 * eliminar.php
 * DELETE: elimina un contacto. Solo acepta POST con token CSRF
 * (la confirmación visual se realiza en el navegador con SweetAlert2).
 */
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php');
}

verificarCsrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    flash('danger', 'Identificador de contacto inválido.');
    redirigir('index.php');
}

$pdo = obtenerConexion();

$stmt = $pdo->prepare('SELECT nombres FROM contacto WHERE id = :id');
$stmt->execute([':id' => $id]);
$nombre = $stmt->fetchColumn();

if ($nombre === false) {
    flash('danger', 'El contacto ya no existe o fue eliminado previamente.');
    redirigir('index.php');
}

$stmt = $pdo->prepare('DELETE FROM contacto WHERE id = :id');
$stmt->execute([':id' => $id]);

flash('success', 'Contacto "' . $nombre . '" eliminado correctamente.');
redirigir('index.php');
