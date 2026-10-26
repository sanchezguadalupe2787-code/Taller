<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
$stmt->execute([$id]);
$cliente = $stmt->fetch();
if (!$cliente) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        $stmt = $pdo->prepare('UPDATE clientes SET nombre=?, telefono=?, email=?, direccion=? WHERE id=?');
        $stmt->execute([$nombre, $telefono, $email, $direccion, $id]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
    $cliente = array_merge($cliente, $_POST);
}

$tituloPagina = 'Editar cliente';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Editar cliente</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($cliente['nombre']); ?>">
        </div>
        <div class="form-fila">
            <div class="form-grupo">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
            </div>
            <div class="form-grupo">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
            </div>
        </div>
        <div class="form-grupo">
            <label>Dirección</label>
            <textarea name="direccion" rows="2"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
