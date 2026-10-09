<?php
/**
 * conexion.php
 * Conexión centralizada (PDO) al servidor remoto MySQL/MariaDB.
 *
 * Nodo HTTP : 10.10.10.20 (Apache + PHP)  -> este archivo se ejecuta aquí
 * Nodo BD   : 10.10.10.30 (MySQL/MariaDB) -> destino de la conexión
 */

define('DB_HOST', '10.10.10.30');
define('DB_PORT', '3306');
define('DB_NAME', 'agenda_db');
define('DB_USER', 'sysadmin');
define('DB_PASS', 'Uac_123456');
define('DB_CHARSET', 'utf8mb4');

/**
 * Devuelve una única instancia PDO reutilizable durante la petición.
 */
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Resultados como arreglos asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Sentencias preparadas reales
            PDO::ATTR_TIMEOUT            => 5,                      // Segundos de espera para conectar
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            error_log('[Agenda] Error de conexión: ' . $e->getMessage());
            http_response_code(500);
            die(
                '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:24px;'
                . 'border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:12px">'
                . '<h2 style="margin-top:0">No se pudo conectar a la base de datos</h2>'
                . '<p>Verifique que el servidor <strong>' . DB_HOST . ':' . DB_PORT . '</strong> esté encendido, '
                . 'que MySQL/MariaDB acepte conexiones remotas y que el firewall permita el puerto 3306.</p>'
                . '</div>'
            );
        }
    }

    return $pdo;
}
