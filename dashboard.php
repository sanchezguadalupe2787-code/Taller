<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requerirStaff();
$pdo = conectarDB();

$totalClientes  = $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
$totalVehiculos = $pdo->query('SELECT COUNT(*) FROM vehiculos')->fetchColumn();
$ordenesActivas = $pdo->query("SELECT COUNT(*) FROM ordenes_servicio WHERE estado NOT IN ('entregado','cancelado')")->fetchColumn();
$ordenesHoy     = $pdo->query('SELECT COUNT(*) FROM ordenes_servicio WHERE DATE(fecha_ingreso) = CURDATE()')->fetchColumn();
$stockBajo      = $pdo->query('SELECT COUNT(*) FROM refacciones WHERE stock <= 5')->fetchColumn();
$ingresosMes    = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())")->fetchColumn();

$stmt = $pdo->query("
    SELECT o.id, o.estado, o.fecha_ingreso, v.marca, v.modelo, v.placas, c.nombre AS cliente
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    JOIN clientes c ON c.id = v.cliente_id
    ORDER BY o.fecha_ingreso DESC
    LIMIT 8
");
$ordenesRecientes = $stmt->fetchAll();

$tituloPagina = 'Inicio';
require_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado'): ?>
    <div class="alerta alerta-error">No tiene permisos para acceder a esa sección.</div>
<?php endif; ?>

<h2 class="mt-0">Panel principal</h2>
<p style="color:var(--gris);">Resumen general de la actividad del taller.</p>

<div class="grid-cards">
    <div class="card-stat">
        <div class="valor"><?php echo $totalClientes; ?></div>
        <div class="etiqueta">Clientes registrados</div>
    </div>
    <div class="card-stat">
        <div class="valor"><?php echo $totalVehiculos; ?></div>
        <div class="etiqueta">Vehículos registrados</div>
    </div>
    <div class="card-stat">
        <div class="valor"><?php echo $ordenesActivas; ?></div>
        <div class="etiqueta">Órdenes en proceso</div>
    </div>
    <div class="card-stat">
        <div class="valor"><?php echo $ordenesHoy; ?></div>
        <div class="etiqueta">Órdenes ingresadas hoy</div>
    </div>
    <div class="card-stat">
        <div class="valor"><?php echo $stockBajo; ?></div>
        <div class="etiqueta">Refacciones con stock bajo</div>
    </div>
    <div class="card-stat">
        <div class="valor">$<?php echo number_format($ingresosMes, 2); ?></div>
        <div class="etiqueta">Ingresos del mes</div>
    </div>
</div>

<div class="panel">
    <div class="flex-entre">
        <h3>Órdenes de servicio recientes</h3>
        <a href="ordenes/index.php" class="btn btn-sm">Ver todas</a>
    </div>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Cliente</th><th>Vehículo</th><th>Placas</th><th>Ingreso</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordenesRecientes)): ?>
                    <tr><td colspan="6" class="text-centro">Sin órdenes registradas todavía.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenesRecientes as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($o['marca'] . ' ' . $o['modelo']); ?></td>
                    <td><?php echo htmlspecialchars($o['placas']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
