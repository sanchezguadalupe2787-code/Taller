<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $stock = (int) ($_POST['stock'] ?? 0);
    $precio = (float) ($_POST['precio_unitario'] ?? 0);

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO refacciones (nombre, descripcion, stock, precio_unitario) VALUES (?,?,?,?)');
        $stmt->execute([$nombre, $descripcion, $stock, $precio]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
}

$tituloPagina = 'Nueva refacción';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Nueva refacción</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo"><label>Nombre *</label><input type="text" name="nombre" required></div>
        <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="2"></textarea></div>
        <div class="form-fila">
            <div class="form-grupo"><label>Stock inicial</label><input type="number" name="stock" min="0" value="0"></div>
            <div class="form-grupo"><label>Precio unitario</label><input type="number" step="0.01" name="precio_unitario" min="0" value="0"></div>
        </div>
        <button type="submit" class="btn">Guardar refacción</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
