<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$clientes = $pdo->query("SELECT id, nombre FROM clientes WHERE estado='activo' ORDER BY nombre")->fetchAll();
$clienteSeleccionado = (int) ($_GET['cliente_id'] ?? 0);

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
        $stmt = $pdo->prepare('INSERT INTO vehiculos (cliente_id, marca, modelo, anio, placas, color, numero_serie, notas) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$cliente_id, $marca, $modelo, $anio, $placas, $color, $numero_serie, $notas]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
}

$tituloPagina = 'Nuevo vehículo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:650px;">
    <h2 class="mt-0">Nuevo vehículo</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo">
            <label>Cliente *</label>
            <select name="cliente_id" required>
                <option value="">-- Seleccione un cliente --</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $clienteSeleccionado == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-fila">
            <div class="form-grupo"><label>Marca *</label><input type="text" name="marca" required></div>
            <div class="form-grupo"><label>Modelo *</label><input type="text" name="modelo" required></div>
            <div class="form-grupo"><label>Año</label><input type="number" name="anio" min="1900" max="2100"></div>
        </div>
        <div class="form-fila">
            <div class="form-grupo"><label>Placas</label><input type="text" name="placas"></div>
            <div class="form-grupo"><label>Color</label><input type="text" name="color"></div>
            <div class="form-grupo"><label>Número de serie</label><input type="text" name="numero_serie"></div>
        </div>
        <div class="form-grupo">
            <label>Notas</label>
            <textarea name="notas" rows="2"></textarea>
        </div>
        <button type="submit" class="btn">Guardar vehículo</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
