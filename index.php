<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (estaAutenticado()) {
    header('Location: ' . (esCliente() ? 'portal/index.php' : 'dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Por favor ingrese usuario y contraseña.';
    } else {
        $pdo = conectarDB();

        // 1) Intentar como personal del taller
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND estado = "activo" LIMIT 1');
        $stmt->execute([$usuario]);
        $datos = $stmt->fetch();

        if ($datos && password_verify($password, $datos['password'])) {
            $_SESSION['usuario_id'] = $datos['id'];
            $_SESSION['usuario_nombre'] = $datos['nombre'];
            $_SESSION['usuario_rol'] = $datos['rol'];
            header('Location: dashboard.php');
            exit;
        }

        // 2) Intentar como cliente del portal
        $stmt = $pdo->prepare('SELECT * FROM clientes WHERE usuario = ? AND portal_activo = 1 AND estado = "activo" LIMIT 1');
        $stmt->execute([$usuario]);
        $cliente = $stmt->fetch();

        if ($cliente && $cliente['password'] && password_verify($password, $cliente['password'])) {
            $_SESSION['usuario_id'] = $cliente['id'];
            $_SESSION['usuario_nombre'] = $cliente['nombre'];
            $_SESSION['usuario_rol'] = 'cliente';
            $_SESSION['cliente_id'] = $cliente['id'];
            $pdo->prepare('UPDATE clientes SET fecha_ultimo_acceso = NOW() WHERE id = ?')->execute([$cliente['id']]);
            header('Location: portal/index.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0a0a0c">
<meta name="description" content="D'Juniors — Sistema de gestión para taller mecánico, para personal y clientes.">
<link rel="manifest" href="manifest.json">
<link rel="icon" href="assets/icons/icon-32.png" sizes="32x32">
<link rel="apple-touch-icon" href="assets/icons/icon-180.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="D'Juniors">
<title>Iniciar sesión - D'Juniors</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-inner">

        <div class="login-brand">
            <img src="assets/img/logo-full.png" alt="D'Juniors Taller Mecánico" class="login-logo-img">
            <p class="lema">ORGANIZA &nbsp;·&nbsp; CONTROLA &nbsp;·&nbsp; MEJORA</p>
        </div>

        <div class="login-box">
            <h3>Bienvenido</h3>
            <p class="subtitulo">Inicia sesión para acceder al sistema</p>

            <?php if ($error): ?>
                <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="login-field">
                    <span class="icono">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </span>
                    <input type="text" id="usuario" name="usuario" placeholder="Usuario" required autofocus autocomplete="username">
                </div>
                <div class="login-field">
                    <span class="icono">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                    <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar u ocultar contraseña">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <button type="submit" class="btn">Iniciar sesión</button>
            </form>

            <p class="login-nota">
                Personal del taller: ingresa con tu cuenta interna.<br>
                Clientes: ingresa con el usuario y contraseña de tu portal.
            </p>

            <div class="login-shortcuts">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13l2-5a2 2 0 0 1 2-1h10a2 2 0 0 1 2 1l2 5"/><path d="M3 13v5a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1h12v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-5"/><circle cx="7.5" cy="16.5" r="1.3"/><circle cx="16.5" cy="16.5" r="1.3"/></svg>
                    Vehículos
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.7 6.3a4 4 0 0 1-5.4 5.4L4 17v3h3l5.3-5.3a4 4 0 0 1 5.4-5.4l-2.6 2.6-2-2z"/></svg>
                    Servicios
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
                    Órdenes
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l1.2 2.4 2.6-.6.6 2.6 2.6.6-.6 2.6L20 9l1.6 2.2L20 13.4l.6 2.6-2.6.6-.6 2.6-2.6-.6L13.2 20 12 22l-1.2-2.4-2.6.6-.6-2.6-2.6-.6.6-2.6L4 13.4 2.4 11.2 4 9l-.6-2.6 2.6-.6.6-2.6 2.6.6z"/><circle cx="12" cy="11" r="2.4"/></svg>
                    Refacciones
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                    Reportes
                </span>
            </div>
        </div>

    </div>
    <div class="login-default">
        Personal de prueba: <b>admin</b> / <b>admin123</b> &nbsp;·&nbsp;
        Cliente de prueba: <b>juan.perez</b> / <b>cliente123</b>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
