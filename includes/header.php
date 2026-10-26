<?php
require_once __DIR__ . '/auth.php';
$base = rutaBase();
$usuario = usuarioActual();
$cliente = esCliente();

function navActiva($patron) {
    $ruta = $_SERVER['SCRIPT_NAME'];
    foreach ((array) $patron as $p) {
        if (strpos($ruta, $p) !== false) {
            return ' activo';
        }
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0a0a0c">
<link rel="manifest" href="<?php echo $base; ?>manifest.json">
<link rel="icon" href="<?php echo $base; ?>assets/icons/icon-32.png" sizes="32x32">
<link rel="apple-touch-icon" href="<?php echo $base; ?>assets/icons/icon-180.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="D'Juniors">
<title><?php echo isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' - ' : ''; ?>D'Juniors</title>
<link rel="stylesheet" href="<?php echo $base; ?>css/style.css">
</head>
<body data-base="<?php echo htmlspecialchars($base); ?>">
<?php if (estaAutenticado()): ?>
<header class="topbar">
    <div class="topbar-brand">
        <img src="<?php echo $base; ?>assets/img/logo-header.png" alt="D'Juniors" class="logo-img">
    </div>

    <?php if ($cliente): ?>
    <nav class="topbar-nav" id="navPrincipal">
        <a class="<?php echo navActiva(['/portal/index.php']); ?>" href="<?php echo $base; ?>portal/index.php">Inicio</a>
        <a class="<?php echo navActiva(['/portal/vehiculos.php','/portal/vehiculo.php']); ?>" href="<?php echo $base; ?>portal/vehiculos.php">Mis vehículos</a>
        <a class="<?php echo navActiva(['/portal/ordenes.php','/portal/orden.php']); ?>" href="<?php echo $base; ?>portal/ordenes.php">Mis órdenes</a>
        <a class="<?php echo navActiva(['/portal/perfil.php']); ?>" href="<?php echo $base; ?>portal/perfil.php">Mi perfil</a>
    </nav>
    <?php else: ?>
    <nav class="topbar-nav" id="navPrincipal">
        <a class="<?php echo navActiva(['/dashboard.php']); ?>" href="<?php echo $base; ?>dashboard.php">Inicio</a>
        <a class="<?php echo navActiva(['/clientes/']); ?>" href="<?php echo $base; ?>clientes/index.php">Clientes</a>
        <a class="<?php echo navActiva(['/vehiculos/']); ?>" href="<?php echo $base; ?>vehiculos/index.php">Vehículos</a>
        <a class="<?php echo navActiva(['/ordenes/']); ?>" href="<?php echo $base; ?>ordenes/index.php">Órdenes</a>
        <a class="<?php echo navActiva(['/refacciones/']); ?>" href="<?php echo $base; ?>refacciones/index.php">Refacciones</a>
        <a class="<?php echo navActiva(['/cotizaciones/']); ?>" href="<?php echo $base; ?>cotizaciones/index.php">Cotiz. / Pagos</a>
        <a class="<?php echo navActiva(['/reportes/']); ?>" href="<?php echo $base; ?>reportes/index.php">Reportes</a>
        <?php if ($usuario['rol'] === 'administrador'): ?>
        <a class="<?php echo navActiva(['/usuarios/']); ?>" href="<?php echo $base; ?>usuarios/index.php">Usuarios</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <button type="button" class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-controls="navPrincipal" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>

    <div class="topbar-user">
        <span><?php echo htmlspecialchars($usuario['nombre']); ?> <small>(<?php echo htmlspecialchars($cliente ? 'cliente' : $usuario['rol']); ?>)</small></span>
        <a href="<?php echo $base; ?>logout.php" class="btn-logout">Salir</a>
    </div>
</header>

<?php if ($cliente): ?>
<nav class="tabbar" aria-label="Navegación del portal">
    <div class="tabbar-inner">
        <a class="<?php echo navActiva(['/portal/index.php']); ?>" href="<?php echo $base; ?>portal/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-7 9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
            <span>Inicio</span>
        </a>
        <a class="<?php echo navActiva(['/portal/vehiculos.php','/portal/vehiculo.php']); ?>" href="<?php echo $base; ?>portal/vehiculos.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13l2-5a2 2 0 0 1 2-1h10a2 2 0 0 1 2 1l2 5"/><path d="M3 13v5a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1h12v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-5"/><circle cx="7.5" cy="16.5" r="1.3"/><circle cx="16.5" cy="16.5" r="1.3"/></svg>
            <span>Vehículos</span>
        </a>
        <a class="<?php echo navActiva(['/portal/ordenes.php','/portal/orden.php']); ?>" href="<?php echo $base; ?>portal/ordenes.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
            <span>Órdenes</span>
        </a>
        <a class="<?php echo navActiva(['/portal/perfil.php']); ?>" href="<?php echo $base; ?>portal/perfil.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            <span>Perfil</span>
        </a>
    </div>
</nav>
<?php else: ?>
<nav class="tabbar" aria-label="Navegación del taller">
    <div class="tabbar-inner">
        <a class="<?php echo navActiva(['/dashboard.php']); ?>" href="<?php echo $base; ?>dashboard.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-7 9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
            <span>Inicio</span>
        </a>
        <a class="<?php echo navActiva(['/clientes/']); ?>" href="<?php echo $base; ?>clientes/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            <span>Clientes</span>
        </a>
        <a class="<?php echo navActiva(['/ordenes/']); ?>" href="<?php echo $base; ?>ordenes/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
            <span>Órdenes</span>
        </a>
        <a class="<?php echo navActiva(['/refacciones/']); ?>" href="<?php echo $base; ?>refacciones/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l1.2 2.4 2.6-.6.6 2.6 2.6.6-.6 2.6L20 9l1.6 2.2L20 13.4l.6 2.6-2.6.6-.6 2.6-2.6-.6L13.2 20 12 22l-1.2-2.4-2.6.6-.6-2.6-2.6-.6.6-2.6L4 13.4 2.4 11.2 4 9l-.6-2.6 2.6-.6.6-2.6 2.6.6z"/><circle cx="12" cy="11" r="2.4"/></svg>
            <span>Refacc.</span>
        </a>
        <a class="<?php echo navActiva(['/reportes/']); ?>" href="<?php echo $base; ?>reportes/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
            <span>Reportes</span>
        </a>
    </div>
</nav>
<?php endif; ?>
<?php endif; ?>
<main class="contenido">
