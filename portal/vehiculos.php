<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirCliente();
$pdo = conectarDB();
$usuario = usuarioActual();
$clienteId = $usuario['cliente_id'];

$stmt = $pdo->prepare('SELECT * FROM vehiculos WHERE cliente_id = ? ORDER BY marca');
$stmt->execute([$clienteId]);
$vehiculos = $stmt->fetchAll();

$tituloPagina = 'Mis vehículos';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <h2 class="mt-0">Mis vehículos</h2>
    <?php if (empty($vehiculos)): ?>
        <p class="text-centro" style="color:var(--gris);">Todavía no tienes vehículos registrados en el taller.</p>
    <?php endif; ?>
    <?php foreach ($vehiculos as $v): ?>
        <a href="vehiculo.php?id=<?php echo $v['id']; ?>" class="vehiculo-card">
            <div class="veh-info">
                <b><?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo'] . ' ' . $v['anio']); ?></b>
                <span>Placas: <?php echo htmlspecialchars($v['placas']); ?> &middot; Color: <?php echo htmlspecialchars($v['color'] ?? '-'); ?></span>
            </div>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--gris);"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
