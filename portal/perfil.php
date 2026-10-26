<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
$stmt->execute([$clienteId]);
$cliente = $stmt->fetch();
if (!$cliente) {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar_datos') {
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $stmt = $pdo->prepare('UPDATE clientes SET telefono = ?, email = ?, direccion = ? WHERE id = ?');
    $stmt->execute([$telefono, $email, $direccion, $clienteId]);
    $cliente['telefono'] = $telefono;
    $cliente['email'] = $email;
    $cliente['direccion'] = $direccion;
    $mensaje = 'Tus datos de contacto se actualizaron correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_password') {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    if (!password_verify($actual, $cliente['password'] ?? '')) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'La confirmación no coincide con la nueva contraseña.';
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE clientes SET password = ? WHERE id = ?')->execute([$hash, $clienteId]);
        $mensaje = 'Tu contraseña se actualizó correctamente.';
    }
}

$tituloPagina = 'Mi perfil';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($mensaje): ?><div class="alerta alerta-exito"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Mis datos</h2>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre']); ?></p>
    <p style="color:var(--gris); font-size:0.85rem;">Para cambiar tu nombre, contacta al taller.</p>

    <form method="POST">
        <input type="hidden" name="accion" value="actualizar_datos">
        <div class="form-grupo">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
        </div>
        <div class="form-grupo">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
        </div>
        <div class="form-grupo">
            <label>Dirección</label>
            <textarea name="direccion" rows="2"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
    </form>
</div>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Cambiar contraseña</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="cambiar_password">
        <div class="form-grupo">
            <label>Contraseña actual</label>
            <input type="password" name="password_actual" required>
        </div>
        <div class="form-grupo">
            <label>Nueva contraseña</label>
            <input type="password" name="password_nueva" required minlength="6">
        </div>
        <div class="form-grupo">
            <label>Confirmar nueva contraseña</label>
            <input type="password" name="password_confirmar" required minlength="6">
        </div>
        <button type="submit" class="btn">Actualizar contraseña</button>
    </form>
</div>

<a href="index.php" class="btn btn-secundario">&larr; Volver a mi portal</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
