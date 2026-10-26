<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();
$usuario = usuarioActual();

$vehiculos = $pdo->query("
    SELECT v.id, v.marca, v.modelo, v.placas, c.nombre AS cliente
    FROM vehiculos v JOIN clientes c ON c.id = v.cliente_id
    ORDER BY c.nombre
")->fetchAll();
$vehiculoSeleccionado = (int) ($_GET['vehiculo_id'] ?? 0);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = (int) $_POST['vehiculo_id'];
    $problema_reportado = trim($_POST['problema_reportado'] ?? '');
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $fecha_entrega_estimada = $_POST['fecha_entrega_estimada'] ?: null;

    if (!$vehiculo_id || $problema_reportado === '') {
        $error = 'Vehículo y problema reportado son obligatorios.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO ordenes_servicio (vehiculo_id, usuario_id, problema_reportado, diagnostico, fecha_entrega_estimada) VALUES (?,?,?,?,?)');
        $stmt->execute([$vehiculo_id, $usuario['id'], $problema_reportado, $diagnostico, $fecha_entrega_estimada]);
        $ordenId = $pdo->lastInsertId();
        header('Location: ver.php?id=' . $ordenId . '&mensaje=guardado');
        exit;
    }
}

$tituloPagina = 'Nueva orden de servicio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:700px;">
    <h2 class="mt-0">Nueva orden de servicio</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo">
            <label>Vehículo *</label>
            <select name="vehiculo_id" required>
                <option value="">-- Seleccione un vehículo --</option>
                <?php foreach ($vehiculos as $v): ?>
                    <option value="<?php echo $v['id']; ?>" <?php echo $vehiculoSeleccionado == $v['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['cliente'] . ' — ' . $v['marca'] . ' ' . $v['modelo'] . ' (' . $v['placas'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo">
            <label>Problema reportado por el cliente *</label>
            <textarea name="problema_reportado" rows="3" required></textarea>
        </div>
        <div class="form-grupo">
            <label>Diagnóstico inicial (opcional)</label>
            <textarea name="diagnostico" rows="3"></textarea>
        </div>
        <div class="form-grupo">
            <label>Fecha de entrega estimada</label>
            <input type="date" name="fecha_entrega_estimada">
        </div>
        <button type="submit" class="btn">Crear orden</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
