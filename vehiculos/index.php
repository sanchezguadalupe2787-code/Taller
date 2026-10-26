<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $pdo->prepare('DELETE FROM vehiculos WHERE id = ?')->execute([$id]);
    header('Location: index.php?mensaje=eliminado');
    exit;
}

$busqueda = trim($_GET['q'] ?? '');
$sql = "SELECT v.*, c.nombre AS cliente_nombre FROM vehiculos v JOIN clientes c ON c.id = v.cliente_id";
$params = [];
if ($busqueda !== '') {
    $sql .= " WHERE v.marca LIKE ? OR v.modelo LIKE ? OR v.placas LIKE ? OR c.nombre LIKE ?";
    $like = "%$busqueda%";
    $params = [$like, $like, $like, $like];
}
$sql .= " ORDER BY v.fecha_registro DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

$tituloPagina = 'Vehículos';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
    <div class="alerta alerta-exito">Vehículo guardado correctamente.</div>
<?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
    <div class="alerta alerta-exito">Vehículo eliminado correctamente.</div>
<?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Vehículos</h2>
        <a href="crear.php" class="btn">+ Nuevo vehículo</a>
    </div>

    <form method="GET" class="form-grupo" style="max-width:320px; margin-bottom:16px;">
        <input type="text" name="q" placeholder="Buscar por marca, modelo, placas o cliente..." value="<?php echo htmlspecialchars($busqueda); ?>">
    </form>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr><th>Cliente</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Placas</th><th>Color</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($vehiculos)): ?>
                    <tr><td colspan="7" class="text-centro">No hay vehículos registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($vehiculos as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['cliente_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($v['marca']); ?></td>
                    <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                    <td><?php echo htmlspecialchars($v['anio']); ?></td>
                    <td><?php echo htmlspecialchars($v['placas']); ?></td>
                    <td><?php echo htmlspecialchars($v['color']); ?></td>
                    <td class="acciones">
                        <a href="ver.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-secundario">Historial</a>
                        <a href="editar.php?id=<?php echo $v['id']; ?>" class="btn btn-sm">Editar</a>
                        <a href="index.php?eliminar=<?php echo $v['id']; ?>" class="btn btn-sm btn-peligro confirmar-eliminar" data-mensaje="¿Eliminar este vehículo?">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
