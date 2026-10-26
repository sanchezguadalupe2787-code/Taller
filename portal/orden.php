<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$id = (int) ($_GET['id'] ?? 0);

$stmtOrden = $pdo->prepare("
    SELECT o.*, v.marca, v.modelo, v.anio, v.placas, v.color, v.id AS vehiculo_id
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    WHERE o.id = ? AND v.cliente_id = ?
");
$stmtOrden->execute([$id, $clienteId]);
$orden = $stmtOrden->fetch();
if (!$orden) {
    header('Location: ordenes.php');
    exit;
}

$servicios = $pdo->prepare('SELECT * FROM servicios_reparaciones WHERE orden_id = ? ORDER BY fecha_registro');
$servicios->execute([$id]);
$servicios = $servicios->fetchAll();

$refUsadas = $pdo->prepare('SELECT ru.*, r.nombre FROM refacciones_usadas ru JOIN refacciones r ON r.id = ru.refaccion_id WHERE ru.orden_id = ?');
$refUsadas->execute([$id]);
$refUsadas = $refUsadas->fetchAll();

$cotizaciones = $pdo->prepare('SELECT * FROM cotizaciones WHERE orden_id = ? ORDER BY fecha DESC');
$cotizaciones->execute([$id]);
$cotizaciones = $cotizaciones->fetchAll();

$pagos = $pdo->prepare('SELECT * FROM pagos WHERE orden_id = ? ORDER BY fecha_pago DESC');
$pagos->execute([$id]);
$pagos = $pagos->fetchAll();

$totalManoObra = array_sum(array_column($servicios, 'costo_mano_obra'));
$totalRefacciones = 0;
foreach ($refUsadas as $r) { $totalRefacciones += $r['cantidad'] * $r['precio_unitario']; }
$totalGeneral = $totalManoObra + $totalRefacciones;
$totalPagado = array_sum(array_column($pagos, 'monto'));
$saldoPendiente = $totalGeneral - $totalPagado;

$tituloPagina = 'Orden #' . $orden['id'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Orden de servicio #<?php echo $orden['id']; ?></h2>
        <span class="badge badge-<?php echo $orden['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$orden['estado'])); ?></span>
    </div>
    <div class="form-fila">
        <div>
            <p><strong>Vehículo:</strong> <a href="vehiculo.php?id=<?php echo $orden['vehiculo_id']; ?>"><?php echo htmlspecialchars($orden['marca'] . ' ' . $orden['modelo'] . ' ' . $orden['anio']); ?></a></p>
            <p><strong>Placas:</strong> <?php echo htmlspecialchars($orden['placas']); ?> &nbsp; <strong>Color:</strong> <?php echo htmlspecialchars($orden['color']); ?></p>
        </div>
        <div>
            <p><strong>Fecha de ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])); ?></p>
            <p><strong>Entrega estimada:</strong> <?php echo $orden['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($orden['fecha_entrega_estimada'])) : '-'; ?></p>
            <p><strong>Entrega real:</strong> <?php echo $orden['fecha_entrega_real'] ? date('d/m/Y H:i', strtotime($orden['fecha_entrega_real'])) : '-'; ?></p>
        </div>
    </div>
    <p><strong>Problema reportado:</strong><br><?php echo nl2br(htmlspecialchars($orden['problema_reportado'])); ?></p>
    <?php if ($orden['diagnostico']): ?>
        <p><strong>Diagnóstico del taller:</strong><br><?php echo nl2br(htmlspecialchars($orden['diagnostico'])); ?></p>
    <?php endif; ?>
</div>

<div class="panel">
    <h3 class="mt-0">Servicios y reparaciones realizadas</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Descripción</th><th>Recomendaciones</th><th>Costo mano de obra</th></tr></thead>
            <tbody>
                <?php if (empty($servicios)): ?><tr><td colspan="3" class="text-centro">Sin servicios registrados todavía.</td></tr><?php endif; ?>
                <?php foreach ($servicios as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['descripcion']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['recomendaciones'])); ?></td>
                    <td>$<?php echo number_format($s['costo_mano_obra'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Refacciones utilizadas</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Refacción</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php if (empty($refUsadas)): ?><tr><td colspan="4" class="text-centro">Sin refacciones registradas.</td></tr><?php endif; ?>
                <?php foreach ($refUsadas as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                    <td><?php echo $r['cantidad']; ?></td>
                    <td>$<?php echo number_format($r['precio_unitario'], 2); ?></td>
                    <td>$<?php echo number_format($r['cantidad'] * $r['precio_unitario'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Cotizaciones</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Fecha</th><th>Monto estimado</th><th>Detalle</th><th>Estado</th></tr></thead>
            <tbody>
                <?php if (empty($cotizaciones)): ?><tr><td colspan="4" class="text-centro">Sin cotizaciones registradas.</td></tr><?php endif; ?>
                <?php foreach ($cotizaciones as $c): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></td>
                    <td>$<?php echo number_format($c['monto_estimado'], 2); ?></td>
                    <td><?php echo htmlspecialchars($c['detalle']); ?></td>
                    <td><?php echo ucfirst($c['estado']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Pagos realizados</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Notas</th></tr></thead>
            <tbody>
                <?php if (empty($pagos)): ?><tr><td colspan="4" class="text-centro">Sin pagos registrados.</td></tr><?php endif; ?>
                <?php foreach ($pagos as $p): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pago'])); ?></td>
                    <td>$<?php echo number_format($p['monto'], 2); ?></td>
                    <td><?php echo ucfirst($p['metodo_pago']); ?></td>
                    <td><?php echo htmlspecialchars($p['notas']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h3 class="mt-0">Resumen de costos</h3>
    <p>Mano de obra: <strong>$<?php echo number_format($totalManoObra, 2); ?></strong></p>
    <p>Refacciones: <strong>$<?php echo number_format($totalRefacciones, 2); ?></strong></p>
    <p>Total general: <strong>$<?php echo number_format($totalGeneral, 2); ?></strong></p>
    <p>Total pagado: <strong>$<?php echo number_format($totalPagado, 2); ?></strong></p>
    <p>Saldo pendiente:
        <strong style="color:<?php echo $saldoPendiente > 0 ? 'var(--rojo)' : 'var(--verde)'; ?>">
            $<?php echo number_format($saldoPendiente, 2); ?>
        </strong>
    </p>
</div>

<a href="ordenes.php" class="btn btn-secundario">&larr; Volver a mis órdenes</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
