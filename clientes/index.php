<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $pdo->prepare('UPDATE clientes SET estado = "inactivo" WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: index.php?mensaje=eliminado');
    exit;
}

$busqueda = trim($_GET['q'] ?? '');
if ($busqueda !== '') {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE estado='activo' AND (nombre LIKE ? OR telefono LIKE ? OR email LIKE ?) ORDER BY nombre");
    $like = "%$busqueda%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM clientes WHERE estado='activo' ORDER BY nombre");
}
$clientes = $stmt->fetchAll();

$tituloPagina = 'Clientes';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
    <div class="alerta alerta-exito">Cliente guardado correctamente.</div>
<?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
    <div class="alerta alerta-exito">Cliente eliminado correctamente.</div>
<?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Clientes</h2>
        <a href="crear.php" class="btn">+ Nuevo cliente</a>
    </div>

    <form method="GET" class="form-grupo" style="max-width:320px; margin-bottom:16px;">
        <input type="text" name="q" placeholder="Buscar por nombre, teléfono o email..." value="<?php echo htmlspecialchars($busqueda); ?>">
    </form>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Dirección</th><th>Registro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="6" class="text-centro">No hay clientes registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                    <td><?php echo htmlspecialchars($c['direccion']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($c['fecha_registro'])); ?></td>
                    <td class="acciones">
                        <a href="ver.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secundario">Ver</a>
                        <a href="editar.php?id=<?php echo $c['id']; ?>" class="btn btn-sm">Editar</a>
                        <a href="index.php?eliminar=<?php echo $c['id']; ?>" class="btn btn-sm btn-peligro confirmar-eliminar" data-mensaje="¿Eliminar este cliente?">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
