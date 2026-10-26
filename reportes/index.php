<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$porEstado = $pdo->query("SELECT estado, COUNT(*) AS total FROM ordenes_servicio GROUP BY estado")->fetchAll();

$topClientes = $pdo->query("
    SELECT c.nombre, COUNT(o.id) AS total_ordenes
    FROM clientes c
    JOIN vehiculos v ON v.cliente_id = c.id
    JOIN ordenes_servicio o ON o.vehiculo_id = v.id
    GROUP BY c.id
    ORDER BY total_ordenes DESC
    LIMIT 10
")->fetchAll();

$topRefacciones = $pdo->query("
    SELECT r.nombre, SUM(ru.cantidad) AS total_usado
    FROM refacciones_usadas ru
    JOIN refacciones r ON r.id = ru.refaccion_id
    GROUP BY r.id
    ORDER BY total_usado DESC
    LIMIT 10
")->fetchAll();

$ingresosMensuales = $pdo->query("
    SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS mes, SUM(monto) AS total
    FROM pagos
    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes
")->fetchAll();

$porMarca = $pdo->query("SELECT marca, COUNT(*) AS total FROM vehiculos GROUP BY marca ORDER BY total DESC")->fetchAll();

$tituloPagina = 'Reportes';
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mt-0">Reportes básicos</h2>
<p style="color:var(--gris);">Resumen de la actividad del taller para apoyar la toma de decisiones.</p>

<div class="panel">
    <h3 class="mt-0">Órdenes de servicio por estado</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Estado</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($porEstado as $r): ?>
                <tr><td><span class="badge badge-<?php echo $r['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$r['estado'])); ?></span></td><td><?php echo $r['total']; ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($porEstado)): ?><tr><td colspan="2" class="text-centro">Sin datos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Clientes con más órdenes de servicio</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Cliente</th><th>Total de órdenes</th></tr></thead>
            <tbody>
                <?php foreach ($topClientes as $r): ?>
                <tr><td><?php echo htmlspecialchars($r['nombre']); ?></td><td><?php echo $r['total_ordenes']; ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($topClientes)): ?><tr><td colspan="2" class="text-centro">Sin datos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Refacciones más utilizadas</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Refacción</th><th>Cantidad utilizada</th></tr></thead>
            <tbody>
                <?php foreach ($topRefacciones as $r): ?>
                <tr><td><?php echo htmlspecialchars($r['nombre']); ?></td><td><?php echo $r['total_usado']; ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($topRefacciones)): ?><tr><td colspan="2" class="text-centro">Sin datos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Ingresos por mes (últimos 6 meses)</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Mes</th><th>Total ingresado</th></tr></thead>
            <tbody>
                <?php foreach ($ingresosMensuales as $r): ?>
                <tr><td><?php echo $r['mes']; ?></td><td>$<?php echo number_format($r['total'], 2); ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($ingresosMensuales)): ?><tr><td colspan="2" class="text-centro">Sin pagos registrados en este periodo.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Vehículos atendidos por marca</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Marca</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($porMarca as $r): ?>
                <tr><td><?php echo htmlspecialchars($r['marca']); ?></td><td><?php echo $r['total']; ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($porMarca)): ?><tr><td colspan="2" class="text-centro">Sin datos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
