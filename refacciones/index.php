<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    try {
        $pdo->prepare('DELETE FROM refacciones WHERE id = ?')->execute([$id]);
        header('Location: index.php?mensaje=eliminado');
    } catch (PDOException $e) {
        header('Location: index.php?mensaje=error_uso');
    }
    exit;
}

$busqueda = trim($_GET['q'] ?? '');
if ($busqueda !== '') {
    $stmt = $pdo->prepare('SELECT * FROM refacciones WHERE nombre LIKE ? ORDER BY nombre');
    $stmt->execute(["%$busqueda%"]);
} else {
    $stmt = $pdo->query('SELECT * FROM refacciones ORDER BY nombre');
}
$refacciones = $stmt->fetchAll();

$tituloPagina = 'Refacciones';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
    <div class="alerta alerta-exito">Refacción guardada correctamente.</div>
<?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
    <div class="alerta alerta-exito">Refacción eliminada correctamente.</div>
<?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] === 'error_uso'): ?>
    <div class="alerta alerta-error">No se puede eliminar: la refacción ya fue utilizada en órdenes de servicio.</div>
<?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Refacciones e inventario</h2>
        <a href="crear.php" class="btn">+ Nueva refacción</a>
    </div>

    <form method="GET" class="form-grupo" style="max-width:320px; margin-bottom:16px;">
        <input type="text" name="q" placeholder="Buscar refacción..." value="<?php echo htmlspecialchars($busqueda); ?>">
    </form>

    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Nombre</th><th>Descripción</th><th>Stock</th><th>Precio unitario</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($refacciones)): ?><tr><td colspan="5" class="text-centro">No hay refacciones registradas.</td></tr><?php endif; ?>
                <?php foreach ($refacciones as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($r['descripcion']); ?></td>
                    <td>
                        <?php echo (int) $r['stock']; ?>
                        <?php if ($r['stock'] <= 5): ?><span class="badge badge-esperando_refacciones">Bajo</span><?php endif; ?>
                    </td>
                    <td>$<?php echo number_format($r['precio_unitario'], 2); ?></td>
                    <td class="acciones">
                        <a href="editar.php?id=<?php echo $r['id']; ?>" class="btn btn-sm">Editar</a>
                        <a href="index.php?eliminar=<?php echo $r['id']; ?>" class="btn btn-sm btn-peligro confirmar-eliminar" data-mensaje="¿Eliminar esta refacción?">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
