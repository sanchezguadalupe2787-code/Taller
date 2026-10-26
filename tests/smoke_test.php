<?php
/** Pruebas de humo del entorno. No se conecta ni escribe en la base de datos. */
$errores = [];
if (PHP_VERSION_ID < 80000) {
    $errores[] = 'Se requiere PHP 8.0 o superior; versión detectada: ' . PHP_VERSION;
}
foreach (['PDO', 'pdo_mysql'] as $extension) {
    if (!extension_loaded($extension)) {
        $errores[] = 'Falta la extensión PHP: ' . $extension;
    }
}
$archivosRequeridos = [
    __DIR__ . '/../index.php',
    __DIR__ . '/../config/db.php',
    __DIR__ . '/../includes/auth.php',
];
foreach ($archivosRequeridos as $archivo) {
    if (!is_file($archivo)) {
        $errores[] = 'No se encontró el archivo requerido: ' . $archivo;
    }
}
if ($errores) {
    fwrite(STDERR, "PRUEBAS FALLIDAS:\n- " . implode("\n- ", $errores) . "\n");
    exit(1);
}
echo "OK: PHP " . PHP_VERSION . "\n";
echo "OK: extensiones PDO y pdo_mysql disponibles\n";
echo "OK: archivos esenciales presentes\n";
echo "OK: prueba de humo completada; no se modificó ni consultó la base de datos\n";
