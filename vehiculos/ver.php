<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT v.*, c.nombre AS cliente_nombre, c.id AS cliente_id FROM vehiculos v JOIN clientes c ON c.id = v.cliente_id WHERE v.id = ?');
$stmt->execute([$id]);
$vehiculo = $stmt->fetch();
if (!$vehiculo) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("
    SELECT o.*
    FROM ordenes_servicio o
    WHERE o.vehiculo_id = ?
    ORDER BY o.fecha_ingreso DESC
");
$stmt->execute([$id]);
$ordenes = $stmt->fetchAll();

$tituloPagina = 'Historial del vehículo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <h2 class="mt-0"><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['anio'] . ')'); ?></h2>
    <p><strong>Cliente:</strong> <a href="../clientes/ver.php?id=<?php echo $vehiculo['cliente_id']; ?>"><?php echo htmlspecialchars($vehiculo['cliente_nombre']); ?></a></p>
    <p><strong>Placas:</strong> <?php echo htmlspecialchars($vehiculo['placas']); ?> &nbsp; <strong>Color:</strong> <?php echo htmlspecialchars($vehiculo['color']); ?></p>
    <p><strong>Número de serie:</strong> <?php echo htmlspecialchars($vehiculo['numero_serie']); ?></p>
    <?php if ($vehiculo['notas']): ?><p><strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($vehiculo['notas'])); ?></p><?php endif; ?>
</div>

<div class="panel">
    <div class="flex-entre">
        <h3 class="mt-0">Historial de mantenimiento y reparaciones</h3>
        <a href="../ordenes/crear.php?vehiculo_id=<?php echo $vehiculo['id']; ?>" class="btn btn-sm">+ Nueva orden</a>
    </div>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>#</th><th>Fecha ingreso</th><th>Problema reportado</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="5" class="text-centro">Este vehículo no tiene órdenes registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($o['fecha_ingreso'])); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($o['problema_reportado'], 0, 60, '...')); ?></td>
                    <td><span class="badge badge-<?php echo $o['estado']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['estado'])); ?></span></td>
                    <td><a href="../ordenes/ver.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-secundario">Ver orden</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<a href="index.php" class="btn btn-secundario">&larr; Volver a vehículos</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
