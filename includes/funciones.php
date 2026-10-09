<?php
/**
 * includes/funciones.php
 * Utilidades comunes: sesión, CSRF, mensajes flash, escape y validaciones.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const GENEROS      = ['Masculino', 'Femenino', 'Otro'];
const TIPOS_SANGRE = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

/** Escapa texto para imprimirlo de forma segura en HTML. */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Redirige y termina la ejecución. */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------------------------------------------------------------
 * Protección CSRF
 * ------------------------------------------------------------- */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(tokenCsrf()) . '">';
}

function verificarCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(tokenCsrf(), $token)) {
        http_response_code(403);
        die('Token de seguridad inválido. Recargue la página e intente de nuevo.');
    }
}

/* ---------------------------------------------------------------
 * Mensajes flash (se muestran una sola vez)
 * ------------------------------------------------------------- */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtenerFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------------------------------------------------------------
 * Datos y validación del formulario de contacto
 * ------------------------------------------------------------- */

/** Extrae y normaliza los campos del formulario desde $_POST. */
function datosDesdePost(): array
{
    $campo = fn(string $k): string => trim((string) ($_POST[$k] ?? ''));

    return [
        'nombres'          => preg_replace('/\s+/', ' ', $campo('nombres')),
        'genero'           => $campo('genero'),
        'fecha_nacimiento' => $campo('fecha_nacimiento'),
        'telefono'         => $campo('telefono'),
        'email'            => strtolower($campo('email')),
        'linkedin'         => $campo('linkedin'),
        'tipo_sangre'      => strtoupper($campo('tipo_sangre')),
    ];
}

/** Valida los datos; devuelve un arreglo campo => mensaje de error. */
function validarContacto(array $d): array
{
    $errores = [];

    // Nombres
    if ($d['nombres'] === '') {
        $errores['nombres'] = 'Los nombres son obligatorios.';
    } elseif (mb_strlen($d['nombres']) > 100) {
        $errores['nombres'] = 'Máximo 100 caracteres.';
    } elseif (!preg_match("/^[\p{L}\s'.-]+$/u", $d['nombres'])) {
        $errores['nombres'] = 'Solo se permiten letras, espacios y los signos . \' -';
    }

    // Género
    if (!in_array($d['genero'], GENEROS, true)) {
        $errores['genero'] = 'Seleccione un género válido.';
    }

    // Fecha de nacimiento
    $fecha = DateTime::createFromFormat('Y-m-d', $d['fecha_nacimiento']);
    if (!$fecha || $fecha->format('Y-m-d') !== $d['fecha_nacimiento']) {
        $errores['fecha_nacimiento'] = 'Ingrese una fecha válida.';
    } elseif ($fecha > new DateTime('today')) {
        $errores['fecha_nacimiento'] = 'La fecha no puede ser futura.';
    } elseif ($fecha < new DateTime('1900-01-01')) {
        $errores['fecha_nacimiento'] = 'La fecha debe ser posterior a 1900.';
    }

    // Teléfono
    if ($d['telefono'] === '') {
        $errores['telefono'] = 'El teléfono es obligatorio.';
    } elseif (!preg_match('/^\+?[0-9\s()-]{6,20}$/', $d['telefono'])) {
        $errores['telefono'] = 'Use solo dígitos, espacios, paréntesis, guiones y un + inicial (6 a 20 caracteres).';
    }

    // Email
    if ($d['email'] === '') {
        $errores['email'] = 'El correo es obligatorio.';
    } elseif (mb_strlen($d['email']) > 100 || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Ingrese un correo electrónico válido.';
    }

    // LinkedIn (opcional)
    if ($d['linkedin'] !== '') {
        if (mb_strlen($d['linkedin']) > 150) {
            $errores['linkedin'] = 'Máximo 150 caracteres.';
        } elseif (!filter_var($d['linkedin'], FILTER_VALIDATE_URL)
            || !preg_match('#^https?://([a-z]{2,3}\.)?linkedin\.com/#i', $d['linkedin'])) {
            $errores['linkedin'] = 'Debe ser una URL de LinkedIn (ej. https://www.linkedin.com/in/usuario).';
        }
    }

    // Tipo de sangre
    if (!in_array($d['tipo_sangre'], TIPOS_SANGRE, true)) {
        $errores['tipo_sangre'] = 'Seleccione un tipo de sangre válido.';
    }

    return $errores;
}

/* ---------------------------------------------------------------
 * Ayudas de presentación
 * ------------------------------------------------------------- */

/** Avatar generado externamente a partir del nombre. */
function urlAvatar(string $nombres, string $genero): string
{
    $fondos = ['Masculino' => '0d6efd', 'Femenino' => 'd63384', 'Otro' => '6f42c1'];
    $fondo  = $fondos[$genero] ?? '6c757d';
    return 'https://ui-avatars.com/api/?name=' . urlencode($nombres)
        . '&background=' . $fondo . '&color=fff&bold=true&size=128&rounded=true';
}

function calcularEdad(string $fecha): int
{
    return (new DateTime($fecha))->diff(new DateTime('today'))->y;
}

function formatearFecha(string $fecha): string
{
    return (new DateTime($fecha))->format('d/m/Y');
}
