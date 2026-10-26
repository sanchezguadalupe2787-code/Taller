<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM refacciones WHERE id = ?');
$stmt->execute([$id]);
$refaccion = $stmt->fetch();
if (!$refaccion) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $stock = (int) ($_POST['stock'] ?? 0);
    $precio = (float) ($_POST['precio_unitario'] ?? 0);

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        $stmt = $pdo->prepare('UPDATE refacciones SET nombre=?, descripcion=?, stock=?, precio_unitario=? WHERE id=?');
        $stmt->execute([$nombre, $descripcion, $stock, $precio, $id]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
    $refaccion = array_merge($refaccion, $_POST);
}

$tituloPagina = 'Editar refacción';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Editar refacción</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo"><label>Nombre *</label><input type="text" name="nombre" required value="<?php echo htmlspecialchars($refaccion['nombre']); ?>"></div>
        <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="2"><?php echo htmlspecialchars($refaccion['descripcion']); ?></textarea></div>
        <div class="form-fila">
            <div class="form-grupo"><label>Stock</label><input type="number" name="stock" min="0" value="<?php echo (int) $refaccion['stock']; ?>"></div>
            <div class="form-grupo"><label>Precio unitario</label><input type="number" step="0.01" name="precio_unitario" min="0" value="<?php echo htmlspecialchars($refaccion['precio_unitario']); ?>"></div>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
