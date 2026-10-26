<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirRol(['administrador']);
$pdo = conectarDB();

if (isset($_GET['desactivar'])) {
    $id = (int) $_GET['desactivar'];
    if ($id !== (int) $_SESSION['usuario_id']) {
        $pdo->prepare('UPDATE usuarios SET estado = "inactivo" WHERE id = ?')->execute([$id]);
    }
    header('Location: index.php?mensaje=actualizado');
    exit;
}
if (isset($_GET['activar'])) {
    $id = (int) $_GET['activar'];
    $pdo->prepare('UPDATE usuarios SET estado = "activo" WHERE id = ?')->execute([$id]);
    header('Location: index.php?mensaje=actualizado');
    exit;
}

$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY nombre')->fetchAll();

$tituloPagina = 'Usuarios';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
    <div class="alerta alerta-exito">Usuario guardado correctamente.</div>
<?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
    <div class="alerta alerta-exito">Usuario actualizado correctamente.</div>
<?php endif; ?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0">Usuarios y niveles de acceso</h2>
        <a href="crear.php" class="btn">+ Nuevo usuario</a>
    </div>

    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                    <td><?php echo ucfirst($u['rol']); ?></td>
                    <td><?php echo $u['estado'] === 'activo' ? '<span class="badge badge-entregado">Activo</span>' : '<span class="badge badge-cancelado">Inactivo</span>'; ?></td>
                    <td class="acciones">
                        <a href="editar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm">Editar</a>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <?php if ($u['estado'] === 'activo'): ?>
                                <a href="index.php?desactivar=<?php echo $u['id']; ?>" class="btn btn-sm btn-peligro">Desactivar</a>
                            <?php else: ?>
                                <a href="index.php?activar=<?php echo $u['id']; ?>" class="btn btn-sm btn-exito">Activar</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
