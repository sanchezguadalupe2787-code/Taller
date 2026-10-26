<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);

$stmtOrden = $pdo->prepare("
    SELECT o.*, v.marca, v.modelo, v.anio, v.placas, v.color, c.nombre AS cliente, c.telefono AS cliente_tel, c.id AS cliente_id, v.id AS vehiculo_id
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    JOIN clientes c ON c.id = v.cliente_id
    WHERE o.id = ?
");
$stmtOrden->execute([$id]);
$orden = $stmtOrden->fetch();
if (!$orden) { header('Location: index.php'); exit; }

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_orden') {
    $estado = $_POST['estado'];
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $fecha_entrega_estimada = $_POST['fecha_entrega_estimada'] ?: null;
    $fechaEntregaReal = ($estado === 'entregado' && !$orden['fecha_entrega_real']) ? date('Y-m-d H:i:s') : $orden['fecha_entrega_real'];
    $stmt = $pdo->prepare('UPDATE ordenes_servicio SET estado=?, diagnostico=?, fecha_entrega_estimada=?, fecha_entrega_real=? WHERE id=?');
    $stmt->execute([$estado, $diagnostico, $fecha_entrega_estimada, $fechaEntregaReal, $id]);
    header('Location: ver.php?id=' . $id . '&mensaje=actualizado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_servicio') {
    $descripcion = trim($_POST['descripcion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $recomendaciones = trim($_POST['recomendaciones'] ?? '');
    $costo = (float) ($_POST['costo_mano_obra'] ?? 0);
    if ($descripcion !== '') {
        $stmt = $pdo->prepare('INSERT INTO servicios_reparaciones (orden_id, descripcion, observaciones, recomendaciones, costo_mano_obra) VALUES (?,?,?,?,?)');
        $stmt->execute([$id, $descripcion, $observaciones, $recomendaciones, $costo]);
    }
    header('Location: ver.php?id=' . $id . '&mensaje=actualizado#servicios');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_refaccion') {
    $refaccion_id = (int) $_POST['refaccion_id'];
    $cantidad = (int) $_POST['cantidad'];
    if ($refaccion_id && $cantidad > 0) {
        $ref = $pdo->prepare('SELECT * FROM refacciones WHERE id = ?');
        $ref->execute([$refaccion_id]);
        $refaccion = $ref->fetch();
        if ($refaccion && $refaccion['stock'] >= $cantidad) {
            $pdo->prepare('INSERT INTO refacciones_usadas (orden_id, refaccion_id, cantidad, precio_unitario) VALUES (?,?,?,?)')
                ->execute([$id, $refaccion_id, $cantidad, $refaccion['precio_unitario']]);
            $pdo->prepare('UPDATE refacciones SET stock = stock - ? WHERE id = ?')->execute([$cantidad, $refaccion_id]);
        } else {
            $error = 'Stock insuficiente para esa refacción.';
        }
    }
    if (!$error) {
        header('Location: ver.php?id=' . $id . '&mensaje=actualizado#refacciones');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_cotizacion') {
    $monto = (float) $_POST['monto_estimado'];
    $detalle = trim($_POST['detalle'] ?? '');
    $pdo->prepare('INSERT INTO cotizaciones (orden_id, monto_estimado, detalle) VALUES (?,?,?)')->execute([$id, $monto, $detalle]);
    header('Location: ver.php?id=' . $id . '&mensaje=actualizado#cotizaciones');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_pago') {
    $monto = (float) $_POST['monto'];
    $metodo = $_POST['metodo_pago'];
    $notas = trim($_POST['notas'] ?? '');
    if ($monto > 0) {
        $pdo->prepare('INSERT INTO pagos (orden_id, monto, metodo_pago, notas) VALUES (?,?,?,?)')->execute([$id, $monto, $metodo, $notas]);
    }
    header('Location: ver.php?id=' . $id . '&mensaje=actualizado#pagos');
    exit;
}

if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado') $mensaje = 'Orden creada correctamente.';
if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado') $mensaje = 'Información actualizada correctamente.';

$servicios = $pdo->prepare('SELECT * FROM servicios_reparaciones WHERE orden_id = ? ORDER BY fecha_registro');
$servicios->execute([$id]);
$servicios = $servicios->fetchAll();

$refUsadas = $pdo->prepare('SELECT ru.*, r.nombre FROM refacciones_usadas ru JOIN refacciones r ON r.id = ru.refaccion_id WHERE ru.orden_id = ?');
$refUsadas->execute([$id]);
$refUsadas = $refUsadas->fetchAll();

$refaccionesDisponibles = $pdo->query('SELECT * FROM refacciones ORDER BY nombre')->fetchAll();

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

$estados = ['recibido','en_diagnostico','en_reparacion','esperando_refacciones','listo','entregado','cancelado'];

$tituloPagina = 'Orden #' . $orden['id'];
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($mensaje): ?><div class="alerta alerta-exito"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Orden de servicio #<?php echo $orden['id']; ?></h2>
        <span class="badge badge-<?php echo $orden['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$orden['estado'])); ?></span>
    </div>
    <div class="form-fila">
        <div>
            <p><strong>Cliente:</strong> <a href="../clientes/ver.php?id=<?php echo $orden['cliente_id']; ?>"><?php echo htmlspecialchars($orden['cliente']); ?></a> (<?php echo htmlspecialchars($orden['cliente_tel']); ?>)</p>
            <p><strong>Vehículo:</strong> <a href="../vehiculos/ver.php?id=<?php echo $orden['vehiculo_id']; ?>"><?php echo htmlspecialchars($orden['marca'] . ' ' . $orden['modelo'] . ' ' . $orden['anio']); ?></a></p>
            <p><strong>Placas:</strong> <?php echo htmlspecialchars($orden['placas']); ?> &nbsp; <strong>Color:</strong> <?php echo htmlspecialchars($orden['color']); ?></p>
        </div>
        <div>
            <p><strong>Fecha de ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])); ?></p>
            <p><strong>Entrega estimada:</strong> <?php echo $orden['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($orden['fecha_entrega_estimada'])) : '-'; ?></p>
            <p><strong>Entrega real:</strong> <?php echo $orden['fecha_entrega_real'] ? date('d/m/Y H:i', strtotime($orden['fecha_entrega_real'])) : '-'; ?></p>
        </div>
    </div>
    <p><strong>Problema reportado:</strong><br><?php echo nl2br(htmlspecialchars($orden['problema_reportado'])); ?></p>
</div>

<div class="panel">
    <h3 class="mt-0">Actualizar estado y diagnóstico</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="actualizar_orden">
        <div class="form-fila">
            <div class="form-grupo">
                <label>Estado</label>
                <select name="estado">
                    <?php foreach ($estados as $e): ?>
                        <option value="<?php echo $e; ?>" <?php echo $orden['estado'] === $e ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$e)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label>Fecha de entrega estimada</label>
                <input type="date" name="fecha_entrega_estimada" value="<?php echo htmlspecialchars($orden['fecha_entrega_estimada']); ?>">
            </div>
        </div>
        <div class="form-grupo">
            <label>Diagnóstico</label>
            <textarea name="diagnostico" rows="3"><?php echo htmlspecialchars($orden['diagnostico']); ?></textarea>
        </div>
        <button type="submit" class="btn">Actualizar orden</button>
    </form>
</div>

<div class="panel" id="servicios">
    <h3 class="mt-0">Servicios y reparaciones realizadas</h3>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Descripción</th><th>Observaciones</th><th>Recomendaciones</th><th>Costo mano de obra</th></tr></thead>
            <tbody>
                <?php if (empty($servicios)): ?><tr><td colspan="4" class="text-centro">Sin servicios registrados.</td></tr><?php endif; ?>
                <?php foreach ($servicios as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['descripcion']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['observaciones'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['recomendaciones'])); ?></td>
                    <td>$<?php echo number_format($s['costo_mano_obra'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <h4>Agregar servicio</h4>
    <form method="POST">
        <input type="hidden" name="accion" value="agregar_servicio">
        <div class="form-grupo"><label>Descripción *</label><input type="text" name="descripcion" required></div>
        <div class="form-fila">
            <div class="form-grupo"><label>Observaciones</label><textarea name="observaciones" rows="2"></textarea></div>
            <div class="form-grupo"><label>Recomendaciones</label><textarea name="recomendaciones" rows="2"></textarea></div>
        </div>
        <div class="form-grupo" style="max-width:200px;"><label>Costo mano de obra</label><input type="number" step="0.01" name="costo_mano_obra" value="0"></div>
        <button type="submit" class="btn btn-sm">+ Agregar servicio</button>
    </form>
</div>

<div class="panel" id="refacciones">
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
    <h4>Agregar refacción</h4>
    <form method="POST" class="form-fila">
        <input type="hidden" name="accion" value="agregar_refaccion">
        <div class="form-grupo">
            <label>Refacción</label>
            <select name="refaccion_id" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($refaccionesDisponibles as $rf): ?>
                    <option value="<?php echo $rf['id']; ?>"><?php echo htmlspecialchars($rf['nombre']) . ' (stock: ' . $rf['stock'] . ', $' . number_format($rf['precio_unitario'],2) . ')'; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo" style="max-width:120px;">
            <label>Cantidad</label>
            <input type="number" name="cantidad" min="1" value="1">
        </div>
        <div class="form-grupo" style="align-self:flex-end;">
            <button type="submit" class="btn btn-sm">+ Agregar</button>
        </div>
    </form>
</div>

<div class="panel" id="cotizaciones">
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
    <h4>Registrar cotización</h4>
    <form method="POST" class="form-fila">
        <input type="hidden" name="accion" value="agregar_cotizacion">
        <div class="form-grupo" style="max-width:180px;"><label>Monto estimado</label><input type="number" step="0.01" name="monto_estimado" required></div>
        <div class="form-grupo"><label>Detalle</label><input type="text" name="detalle"></div>
        <div class="form-grupo" style="align-self:flex-end;"><button type="submit" class="btn btn-sm">+ Registrar</button></div>
    </form>
</div>

<div class="panel" id="pagos">
    <h3 class="mt-0">Pagos</h3>
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
    <h4>Registrar pago</h4>
    <form method="POST" class="form-fila">
        <input type="hidden" name="accion" value="agregar_pago">
        <div class="form-grupo" style="max-width:150px;"><label>Monto</label><input type="number" step="0.01" name="monto" required></div>
        <div class="form-grupo" style="max-width:180px;">
            <label>Método de pago</label>
            <select name="metodo_pago">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div class="form-grupo"><label>Notas</label><input type="text" name="notas"></div>
        <div class="form-grupo" style="align-self:flex-end;"><button type="submit" class="btn btn-sm">+ Registrar pago</button></div>
    </form>
</div>

<div class="panel">
    <h3 class="mt-0">Resumen de costos</h3>
    <p>Mano de obra: <strong>$<?php echo number_format($totalManoObra, 2); ?></strong></p>
    <p>Refacciones: <strong>$<?php echo number_format($totalRefacciones, 2); ?></strong></p>
    <p>Total general: <strong>$<?php echo number_format($totalGeneral, 2); ?></strong></p>
    <p>Total pagado: <strong>$<?php echo number_format($totalPagado, 2); ?></strong></p>
    <p>Saldo pendiente: <strong style="color:<?php echo $saldoPendiente > 0 ? 'var(--rojo)' : 'var(--verde)'; ?>">$<?php echo number_format($saldoPendiente, 2); ?></strong></p>
</div>

<a href="index.php" class="btn btn-secundario">&larr; Volver a órdenes</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
