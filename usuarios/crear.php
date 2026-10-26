<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirRol(['administrador']);
$pdo = conectarDB();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'recepcionista';

    if ($nombre === '' || $usuario === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $existe = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ?');
        $existe->execute([$usuario]);
        if ($existe->fetch()) {
            $error = 'Ese nombre de usuario ya está en uso.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?,?,?,?)');
            $stmt->execute([$nombre, $usuario, $hash, $rol]);
            header('Location: index.php?mensaje=guardado');
            exit;
        }
    }
}

$tituloPagina = 'Nuevo usuario';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Nuevo usuario</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo"><label>Nombre completo *</label><input type="text" name="nombre" required></div>
        <div class="form-grupo"><label>Usuario *</label><input type="text" name="usuario" required></div>
        <div class="form-grupo"><label>Contraseña *</label><input type="password" name="password" required></div>
        <div class="form-grupo">
            <label>Rol *</label>
            <select name="rol">
                <option value="administrador">Administrador</option>
                <option value="recepcionista" selected>Recepcionista</option>
                <option value="mecanico">Mecánico</option>
            </select>
        </div>
        <button type="submit" class="btn">Guardar usuario</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
