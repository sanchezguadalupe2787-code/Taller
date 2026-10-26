<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$totalVehiculos = $pdo->prepare('SELECT COUNT(*) FROM vehiculos WHERE cliente_id = ?');
$totalVehiculos->execute([$clienteId]);
$totalVehiculos = $totalVehiculos->fetchColumn();

$ordenesActivas = $pdo->prepare("
    SELECT COUNT(*) FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    WHERE v.cliente_id = ? AND o.estado NOT IN ('entregado','cancelado')
");
$ordenesActivas->execute([$clienteId]);
$ordenesActivas = $ordenesActivas->fetchColumn();

// Saldo pendiente total de todas las órdenes del cliente
$stmtSaldo = $pdo->prepare("
    SELECT o.id,
        COALESCE((SELECT SUM(costo_mano_obra) FROM servicios_reparaciones WHERE orden_id = o.id), 0) AS mano_obra,
        COALESCE((SELECT SUM(cantidad * precio_unitario) FROM refacciones_usadas WHERE orden_id = o.id), 0) AS refacciones,
        COALESCE((SELECT SUM(monto) FROM pagos WHERE orden_id = o.id), 0) AS pagado
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    WHERE v.cliente_id = ?
");
$stmtSaldo->execute([$clienteId]);
$saldoPendiente = 0;
foreach ($stmtSaldo->fetchAll() as $row) {
    $saldoPendiente += ($row['mano_obra'] + $row['refacciones']) - $row['pagado'];
}

$ordenesRecientes = $pdo->prepare("
    SELECT o.id, o.estado, o.fecha_ingreso, v.marca, v.modelo, v.placas
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    WHERE v.cliente_id = ?
    ORDER BY o.fecha_ingreso DESC
    LIMIT 5
");
$ordenesRecientes->execute([$clienteId]);
$ordenesRecientes = $ordenesRecientes->fetchAll();

$misVehiculos = $pdo->prepare('SELECT * FROM vehiculos WHERE cliente_id = ? ORDER BY marca LIMIT 4');
$misVehiculos->execute([$clienteId]);
$misVehiculos = $misVehiculos->fetchAll();

$tituloPagina = 'Mi portal';
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mt-0">Hola, <?php echo htmlspecialchars(explode(' ', $usuario['nombre'])[0]); ?></h2>
<p class="portal-saludo">Este es el resumen de tu cuenta en D'Juniors.</p>

<div class="grid-cards">
    <div class="card-stat">
        <div class="valor"><?php echo (int) $totalVehiculos; ?></div>
        <div class="etiqueta">Vehículos registrados</div>
    </div>
    <div class="card-stat">
        <div class="valor"><?php echo (int) $ordenesActivas; ?></div>
        <div class="etiqueta">Órdenes en proceso</div>
    </div>
    <div class="card-stat">
        <div class="valor">$<?php echo number_format($saldoPendiente, 2); ?></div>
        <div class="etiqueta">Saldo pendiente total</div>
    </div>
</div>

<div class="panel">
    <div class="flex-entre">
        <h3 class="mt-0">Mis vehículos</h3>
        <a href="vehiculos.php" class="btn btn-sm btn-secundario">Ver todos</a>
    </div>
    <?php if (empty($misVehiculos)): ?>
        <p class="text-centro" style="color:var(--gris);">Todavía no tienes vehículos registrados en el taller.</p>
    <?php endif; ?>
    <?php foreach ($misVehiculos as $v): ?>
        <a href="vehiculo.php?id=<?php echo $v['id']; ?>" class="vehiculo-card">
            <div class="veh-info">
                <b><?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo'] . ' ' . $v['anio']); ?></b>
                <span>Placas: <?php echo htmlspecialchars($v['placas']); ?></span>
            </div>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--gris);"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="flex-entre">
        <h3 class="mt-0">Órdenes de servicio recientes</h3>
        <a href="ordenes.php" class="btn btn-sm btn-secundario">Ver todas</a>
    </div>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>#</th><th>Vehículo</th><th>Ingreso</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if (empty($ordenesRecientes)): ?>
                    <tr><td colspan="4" class="text-centro">Todavía no tienes órdenes de servicio.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenesRecientes as $o): ?>
                <tr>
                    <td><a href="orden.php?id=<?php echo $o['id']; ?>">#<?php echo $o['id']; ?></a></td>
                    <td><?php echo htmlspecialchars($o['marca'] . ' ' . $o['modelo'] . ' (' . $o['placas'] . ')'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
