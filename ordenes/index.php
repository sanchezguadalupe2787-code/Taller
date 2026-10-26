<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$estadoFiltro = $_GET['estado'] ?? '';
$sql = "
    SELECT o.*, v.marca, v.modelo, v.placas, c.nombre AS cliente
    FROM ordenes_servicio o
    JOIN vehiculos v ON v.id = o.vehiculo_id
    JOIN clientes c ON c.id = v.cliente_id
";
$params = [];
if ($estadoFiltro !== '') {
    $sql .= " WHERE o.estado = ?";
    $params[] = $estadoFiltro;
}
$sql .= " ORDER BY o.fecha_ingreso DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ordenes = $stmt->fetchAll();

$estados = ['recibido','en_diagnostico','en_reparacion','esperando_refacciones','listo','entregado','cancelado'];

$tituloPagina = 'Órdenes de servicio';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
    <div class="alerta alerta-exito">Orden guardada correctamente.</div>
<?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Órdenes de servicio</h2>
        <a href="crear.php" class="btn">+ Nueva orden</a>
    </div>

    <form method="GET" class="form-grupo" style="max-width:280px; margin-bottom:16px;">
        <select name="estado" onchange="this.form.submit()">
            <option value="">-- Todos los estados --</option>
            <?php foreach ($estados as $e): ?>
                <option value="<?php echo $e; ?>" <?php echo $estadoFiltro === $e ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$e)); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Vehículo</th><th>Ingreso</th><th>Entrega estimada</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="7" class="text-centro">No hay órdenes registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($o['marca'] . ' ' . $o['modelo'] . ' - ' . $o['placas']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><?php echo $o['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($o['fecha_entrega_estimada'])) : '-'; ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                    <td class="acciones">
                        <a href="ver.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-secundario">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
