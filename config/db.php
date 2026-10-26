<?php
/**
 * Configuración de conexión a la base de datos
 * Sistema de Gestión para Taller Mecánico — D'Juniors
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'taller_mecanico');
define('DB_USER', 'root');
define('DB_PASS', 'flor');
define('DB_CHARSET', 'utf8mb4');

function conectarDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    return $pdo;
}
