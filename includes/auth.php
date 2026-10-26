<?php
/**
 * Manejo de sesión y control de acceso por rol
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaAutenticado() {
    return isset($_SESSION['usuario_id']);
}

function requerirLogin() {
    if (!estaAutenticado()) {
        header('Location: ' . rutaBase() . 'index.php');
        exit;
    }
}

function requerirRol(array $rolesPermitidos) {
    requerirLogin();
    if (!in_array($_SESSION['usuario_rol'], $rolesPermitidos, true)) {
        header('Location: ' . rutaBase() . 'dashboard.php?error=acceso_denegado');
        exit;
    }
}

/**
 * Indica si la sesión activa pertenece a un cliente del portal (no personal del taller).
 */
function esCliente() {
    return ($_SESSION['usuario_rol'] ?? '') === 'cliente';
}

/**
 * Restringe una página a personal del taller (administrador, recepcionista, mecánico).
 * Si quien ingresa es un cliente, lo redirige a su portal en vez de mostrar error.
 */
function requerirStaff() {
    requerirLogin();
    if (esCliente()) {
        header('Location: ' . rutaBase() . 'portal/index.php');
        exit;
    }
}

/**
 * Restringe una página al portal de clientes. Si quien ingresa es personal
 * del taller, lo regresa al dashboard administrativo.
 */
function requerirCliente() {
    requerirLogin();
    if (!esCliente()) {
        header('Location: ' . rutaBase() . 'dashboard.php');
        exit;
    }
}

/**
 * Calcula la ruta relativa a la raíz del proyecto según la profundidad
 * de la carpeta actual (para que los enlaces funcionen desde /clientes/, /vehiculos/, /portal/, etc.)
 */
function rutaBase() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $partes = array_filter(explode('/', $scriptDir));
    $subcarpetas = ['clientes','vehiculos','ordenes','servicios','refacciones','cotizaciones','usuarios','reportes','portal'];
    $ultima = end($partes);
    if (in_array($ultima, $subcarpetas, true)) {
        return '../';
    }
    return '';
}

function usuarioActual() {
    return [
        'id'         => $_SESSION['usuario_id'] ?? null,
        'nombre'     => $_SESSION['usuario_nombre'] ?? null,
        'rol'        => $_SESSION['usuario_rol'] ?? null,
        'cliente_id' => $_SESSION['cliente_id'] ?? null,
    ];
}
