<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM vehiculos WHERE id = ? AND cliente_id = ?');
$stmt->execute([$id, $clienteId]);
$vehiculo = $stmt->fetch();
if (!$vehiculo) {
    header('Location: vehiculos.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM ordenes_servicio WHERE vehiculo_id = ? ORDER BY fecha_ingreso DESC');
$stmt->execute([$id]);
$ordenes = $stmt->fetchAll();

$tituloPagina = 'Mi vehículo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <h2 class="mt-0"><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['anio'] . ')'); ?></h2>
    <p><strong>Placas:</strong> <?php echo htmlspecialchars($vehiculo['placas']); ?> &nbsp; <strong>Color:</strong> <?php echo htmlspecialchars($vehiculo['color']); ?></p>
    <p><strong>Número de serie:</strong> <?php echo htmlspecialchars($vehiculo['numero_serie']); ?></p>
    <?php if ($vehiculo['notas']): ?><p><strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($vehiculo['notas'])); ?></p><?php endif; ?>
</div>

<div class="panel">
    <h3 class="mt-0">Historial de servicio</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>#</th><th>Fecha ingreso</th><th>Problema reportado</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="5" class="text-centro">Este vehículo no tiene órdenes de servicio registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($o['problema_reportado'], 0, 60, '...')); ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                    <td><a href="orden.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-secundario">Ver detalle</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<a href="vehiculos.php" class="btn btn-secundario">&larr; Volver a mis vehículos</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
