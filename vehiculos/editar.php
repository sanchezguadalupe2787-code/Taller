<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM vehiculos WHERE id = ?');
$stmt->execute([$id]);
$vehiculo = $stmt->fetch();
if (!$vehiculo) { header('Location: index.php'); exit; }

$clientes = $pdo->query("SELECT id, nombre FROM clientes WHERE estado='activo' ORDER BY nombre")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = (int) $_POST['cliente_id'];
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $anio = $_POST['anio'] !== '' ? (int) $_POST['anio'] : null;
    $placas = trim($_POST['placas'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $numero_serie = trim($_POST['numero_serie'] ?? '');
    $notas = trim($_POST['notas'] ?? '');

    if (!$cliente_id || $marca === '' || $modelo === '') {
        $error = 'Cliente, marca y modelo son obligatorios.';
    } else {
        $stmt = $pdo->prepare('UPDATE vehiculos SET cliente_id=?, marca=?, modelo=?, anio=?, placas=?, color=?, numero_serie=?, notas=? WHERE id=?');
        $stmt->execute([$cliente_id, $marca, $modelo, $anio, $placas, $color, $numero_serie, $notas, $id]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
    $vehiculo = array_merge($vehiculo, $_POST);
}

$tituloPagina = 'Editar vehículo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:650px;">
    <h2 class="mt-0">Editar vehículo</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo">
            <label>Cliente *</label>
            <select name="cliente_id" required>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $vehiculo['cliente_id'] == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-fila">
            <div class="form-grupo"><label>Marca *</label><input type="text" name="marca" required value="<?php echo htmlspecialchars($vehiculo['marca']); ?>"></div>
            <div class="form-grupo"><label>Modelo *</label><input type="text" name="modelo" required value="<?php echo htmlspecialchars($vehiculo['modelo']); ?>"></div>
            <div class="form-grupo"><label>Año</label><input type="number" name="anio" value="<?php echo htmlspecialchars($vehiculo['anio']); ?>"></div>
        </div>
        <div class="form-fila">
            <div class="form-grupo"><label>Placas</label><input type="text" name="placas" value="<?php echo htmlspecialchars($vehiculo['placas']); ?>"></div>
            <div class="form-grupo"><label>Color</label><input type="text" name="color" value="<?php echo htmlspecialchars($vehiculo['color']); ?>"></div>
            <div class="form-grupo"><label>Número de serie</label><input type="text" name="numero_serie" value="<?php echo htmlspecialchars($vehiculo['numero_serie']); ?>"></div>
        </div>
        <div class="form-grupo">
            <label>Notas</label>
            <textarea name="notas" rows="2"><?php echo htmlspecialchars($vehiculo['notas']); ?></textarea>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
