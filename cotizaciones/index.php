<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$cotizaciones = $pdo->query("
    SELECT co.*, o.id AS orden_id, c.nombre AS cliente, v.marca, v.modelo
    FROM cotizaciones co
    JOIN ordenes_servicio o ON o.id = co.orden_id
    JOIN vehiculos v ON v.id = o.vehiculo_id
    JOIN clientes c ON c.id = v.cliente_id
    ORDER BY co.fecha DESC
")->fetchAll();

$pagos = $pdo->query("
    SELECT p.*, o.id AS orden_id, c.nombre AS cliente
    FROM pagos p
    JOIN ordenes_servicio o ON o.id = p.orden_id
    JOIN vehiculos v ON v.id = o.vehiculo_id
    JOIN clientes c ON c.id = v.cliente_id
    ORDER BY p.fecha_pago DESC
")->fetchAll();

$tituloPagina = 'Cotizaciones y pagos';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <h2 class="mt-0">Cotizaciones registradas</h2>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Orden</th><th>Cliente</th><th>Vehículo</th><th>Fecha</th><th>Monto estimado</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if (empty($cotizaciones)): ?><tr><td colspan="6" class="text-centro">No hay cotizaciones registradas.</td></tr><?php endif; ?>
                <?php foreach ($cotizaciones as $c): ?>
                <tr>
                    <td><a href="../ordenes/ver.php?id=<?php echo $c['orden_id']; ?>">#<?php echo $c['orden_id']; ?></a></td>
                    <td><?php echo htmlspecialchars($c['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($c['marca'] . ' ' . $c['modelo']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></td>
                    <td>$<?php echo number_format($c['monto_estimado'], 2); ?></td>
                    <td><?php echo ucfirst($c['estado']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h2 class="mt-0">Pagos registrados</h2>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Orden</th><th>Cliente</th><th>Fecha</th><th>Monto</th><th>Método</th></tr></thead>
            <tbody>
                <?php if (empty($pagos)): ?><tr><td colspan="5" class="text-centro">No hay pagos registrados.</td></tr><?php endif; ?>
                <?php foreach ($pagos as $p): ?>
                <tr>
                    <td><a href="../ordenes/ver.php?id=<?php echo $p['orden_id']; ?>">#<?php echo $p['orden_id']; ?></a></td>
                    <td><?php echo htmlspecialchars($p['cliente']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pago'])); ?></td>
                    <td>$<?php echo number_format($p['monto'], 2); ?></td>
                    <td><?php echo ucfirst($p['metodo_pago']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p style="color:var(--gris); font-size:0.85rem;">Para registrar una nueva cotización o pago, ingresa a la orden de servicio correspondiente.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
