<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$stmt = $pdo->prepare("
    SELECT o.*, v.marca, v.modelo, v.placas
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    WHERE v.cliente_id = ?
    ORDER BY o.fecha_ingreso DESC
");
$stmt->execute([$clienteId]);
$ordenes = $stmt->fetchAll();

$tituloPagina = 'Mis órdenes de servicio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <h2 class="mt-0">Mis órdenes de servicio</h2>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>#</th><th>Vehículo</th><th>Ingreso</th><th>Problema reportado</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="5" class="text-centro">Todavía no tienes órdenes de servicio.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $o): ?>
                <tr>
                    <td><a href="orden.php?id=<?php echo $o['id']; ?>">#<?php echo $o['id']; ?></a></td>
                    <td><?php echo htmlspecialchars($o['marca'] . ' ' . $o['modelo'] . ' (' . $o['placas'] . ')'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($o['problema_reportado'], 0, 50, '...')); ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
