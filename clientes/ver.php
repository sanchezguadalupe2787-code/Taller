<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
$stmt->execute([$id]);
$cliente = $stmt->fetch();
if (!$cliente) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM vehiculos WHERE cliente_id = ? ORDER BY marca');
$stmt->execute([$id]);
$vehiculos = $stmt->fetchAll();

$usuarioActivo = usuarioActual();
$esAdmin = $usuarioActivo['rol'] === 'administrador';
$errorPortal = '';
$mensajePortal = '';
$passwordGenerada = '';

if ($esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_portal'])) {
    if ($_POST['accion_portal'] === 'activar') {
        $usuarioPortal = trim($_POST['usuario_portal'] ?? '');
        $passwordPortal = trim($_POST['password_portal'] ?? '');

        if ($usuarioPortal === '') {
            $errorPortal = 'El usuario de acceso es obligatorio.';
        } else {
            if ($passwordPortal === '') {
                $passwordPortal = substr(bin2hex(random_bytes(4)), 0, 8);
                $passwordGenerada = $passwordPortal;
            }
            $existe = $pdo->prepare('SELECT id FROM clientes WHERE usuario = ? AND id <> ?');
            $existe->execute([$usuarioPortal, $id]);
            if ($existe->fetch()) {
                $errorPortal = 'Ese usuario ya está en uso por otro cliente.';
            } else {
                $hash = password_hash($passwordPortal, PASSWORD_BCRYPT);
                $pdo->prepare('UPDATE clientes SET usuario = ?, password = ?, portal_activo = 1 WHERE id = ?')
                    ->execute([$usuarioPortal, $hash, $id]);
                $cliente['usuario'] = $usuarioPortal;
                $cliente['portal_activo'] = 1;
                $mensajePortal = $passwordGenerada
                    ? "Acceso activado. Contraseña generada: {$passwordGenerada} (compártela con el cliente; no se mostrará de nuevo)."
                    : 'Acceso al portal activado correctamente.';
            }
        }
    } elseif ($_POST['accion_portal'] === 'desactivar') {
        $pdo->prepare('UPDATE clientes SET portal_activo = 0 WHERE id = ?')->execute([$id]);
        $cliente['portal_activo'] = 0;
        $mensajePortal = 'Acceso al portal desactivado.';
    }
}

$tituloPagina = 'Ficha del cliente';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <div class="flex-entre">
        <h2 class="mt-0"><?php echo htmlspecialchars($cliente['nombre']); ?></h2>
        <a href="editar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm">Editar</a>
    </div>
    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($cliente['direccion']); ?></p>
    <p><strong>Registrado el:</strong> <?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></p>
</div>

<div class="panel">
    <div class="flex-entre">
        <h3 class="mt-0">Vehículos del cliente</h3>
        <a href="../vehiculos/crear.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-sm">+ Agregar vehículo</a>
    </div>
    <div class="tabla-contenedor">
        <table>
            <thead><tr><th>Marca</th><th>Modelo</th><th>Año</th><th>Placas</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if (empty($vehiculos)): ?>
                <tr><td colspan="5" class="text-centro">Este cliente no tiene vehículos registrados.</td></tr>
            <?php endif; ?>
            <?php foreach ($vehiculos as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['marca']); ?></td>
                    <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                    <td><?php echo htmlspecialchars($v['anio']); ?></td>
                    <td><?php echo htmlspecialchars($v['placas']); ?></td>
                    <td class="acciones">
                        <a href="../vehiculos/ver.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-secundario">Ver historial</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($esAdmin): ?>
<div class="panel">
    <h3 class="mt-0">Acceso al portal del cliente</h3>
    <p style="color:var(--gris); font-size:0.88rem; margin-top:-6px;">
        Permite que este cliente entre a la app con su propio usuario y vea únicamente
        sus vehículos, órdenes de servicio y pagos.
    </p>

    <?php if ($mensajePortal): ?><div class="alerta alerta-exito"><?php echo htmlspecialchars($mensajePortal); ?></div><?php endif; ?>
    <?php if ($errorPortal): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($errorPortal); ?></div><?php endif; ?>

    <p>
        Estado actual:
        <?php if ((int) $cliente['portal_activo'] === 1): ?>
            <span class="saldo-chip al-dia">Portal activo</span>
            <?php if ($cliente['usuario']): ?> — usuario: <b><?php echo htmlspecialchars($cliente['usuario']); ?></b><?php endif; ?>
        <?php else: ?>
            <span class="saldo-chip pendiente">Sin acceso al portal</span>
        <?php endif; ?>
    </p>

    <form method="POST" class="form-fila" style="align-items:flex-end;">
        <input type="hidden" name="accion_portal" value="activar">
        <div class="form-grupo">
            <label>Usuario de acceso</label>
            <input type="text" name="usuario_portal" value="<?php echo htmlspecialchars($cliente['usuario'] ?? ''); ?>" placeholder="p.ej. juan.perez" required>
        </div>
        <div class="form-grupo">
            <label>Contraseña (opcional: se genera una si la dejas vacía)</label>
            <input type="text" name="password_portal" placeholder="Dejar en blanco para generar una">
        </div>
        <div class="form-grupo" style="flex:0;">
            <button type="submit" class="btn btn-exito">Activar / actualizar acceso</button>
        </div>
    </form>

    <?php if ((int) $cliente['portal_activo'] === 1): ?>
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="accion_portal" value="desactivar">
        <button type="submit" class="btn btn-peligro btn-sm confirmar-eliminar" data-mensaje="¿Desactivar el acceso de este cliente al portal?">Desactivar acceso al portal</button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<a href="index.php" class="btn btn-secundario">&larr; Volver a clientes</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
