<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirRol(['administrador']);
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $rol = $_POST['rol'] ?? 'recepcionista';
    $password = $_POST['password'] ?? '';

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, rol=?, password=? WHERE id=?');
            $stmt->execute([$nombre, $rol, $hash, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, rol=? WHERE id=?');
            $stmt->execute([$nombre, $rol, $id]);
        }
        header('Location: index.php?mensaje=actualizado');
        exit;
    }
}

$tituloPagina = 'Editar usuario';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Editar usuario</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo"><label>Nombre completo *</label><input type="text" name="nombre" required value="<?php echo htmlspecialchars($user['nombre']); ?>"></div>
        <div class="form-grupo"><label>Usuario</label><input type="text" value="<?php echo htmlspecialchars($user['usuario']); ?>" disabled></div>
        <div class="form-grupo"><label>Nueva contraseña (dejar en blanco para no cambiar)</label><input type="password" name="password"></div>
        <div class="form-grupo">
            <label>Rol *</label>
            <select name="rol">
                <option value="administrador" <?php echo $user['rol']==='administrador'?'selected':''; ?>>Administrador</option>
                <option value="recepcionista" <?php echo $user['rol']==='recepcionista'?'selected':''; ?>>Recepcionista</option>
                <option value="mecanico" <?php echo $user['rol']==='mecanico'?'selected':''; ?>>Mecánico</option>
            </select>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
