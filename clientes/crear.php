<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requerirStaff();
$pdo = conectarDB();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $aceptaAviso = isset($_POST['acepta_aviso_privacidad']);

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } elseif (!$aceptaAviso) {
        $error = 'Debe aceptar el aviso de privacidad para registrar al cliente.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO clientes (nombre, telefono, email, direccion, acepta_aviso_privacidad, fecha_aceptacion_privacidad) VALUES (?, ?, ?, ?, 1, NOW())');
        $stmt->execute([$nombre, $telefono, $email, $direccion]);
        header('Location: index.php?mensaje=guardado');
        exit;
    }
}

$tituloPagina = 'Nuevo cliente';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel" style="max-width:600px;">
    <h2 class="mt-0">Nuevo cliente</h2>
    <?php if ($error): ?><div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-grupo">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
        </div>
        <div class="form-fila">
            <div class="form-grupo">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
            </div>
            <div class="form-grupo">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-grupo">
            <label>Dirección</label>
            <textarea name="direccion" rows="2"><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
        </div>

        <div class="aviso-privacidad">
            <p><strong>Aviso de privacidad.</strong> Los datos personales recabados en este formulario
            (nombre, teléfono, correo electrónico y dirección) se utilizarán únicamente para la
            gestión de servicios del taller: identificación del cliente, contacto sobre el estado de
            su vehículo y emisión de cotizaciones y comprobantes de pago. No serán compartidos con
            terceros sin su consentimiento, salvo requerimiento legal. Usted puede solicitar el
            acceso, rectificación o eliminación de sus datos en cualquier momento.</p>
            <label class="checkbox-privacidad">
                <input type="checkbox" name="acepta_aviso_privacidad" value="1" required
                    <?php echo isset($_POST['acepta_aviso_privacidad']) ? 'checked' : ''; ?>>
                He leído y acepto el aviso de privacidad *
            </label>
        </div>

        <button type="submit" class="btn">Guardar cliente</button>
        <a href="index.php" class="btn btn-secundario">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
