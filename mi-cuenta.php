<?php
require_once __DIR__ . '/config/conexion.php';

$mensaje = '';
$db = Conexion::getConexion();

// Procesar Registro de Usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registro') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmtIns = $db->prepare("INSERT INTO usuarios (rol_id, nombre, apellidos, correo, password_hash) VALUES (4, ?, ?, ?, ?)");
        $stmtIns->execute([$nombre, $apellidos, $correo, $password]);
        $mensaje = 'registro_exitoso';
    } catch (PDOException $e) {
        $mensaje = 'correo_duplicado';
    }
}

// Procesar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = ? AND estado = 1");
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['rol_id'] = $user['rol_id'];

        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header("Location: {$redirect}");
        exit;
    } else {
        $mensaje = 'credenciales_incorrectas';
    }
}

include __DIR__ . '/includes/header.php';

// Si la sesión está activa, mostrar Panel del Cliente e Historial de Pedidos
if (isset($_SESSION['usuario_id'])):
    $stmtPed = $db->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC");
    $stmtPed->execute([$_SESSION['usuario_id']]);
    $misPedidos = $stmtPed->fetchAll();
?>

<div style="padding: 3rem 0; max-width: 900px; margin: 0 auto;">
    <h2>Mi Cuenta</h2>
    <p style="color: var(--color-texto-mutado); margin-bottom: 2rem;">Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></p>

    <h3>Historial de Pedidos</h3>
    <?php if (empty($misPedidos)): ?>
        <p style="color: var(--color-texto-mutado); margin-top: 1rem;">Aún no has realizado compras.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:1rem; background:#FFF; border:1px solid var(--color-borde);">
            <thead>
                <tr style="background:var(--color-fondo-lino); text-align:left;">
                    <th style="padding:10px;">Orden</th>
                    <th style="padding:10px;">Total</th>
                    <th style="padding:10px;">Estado</th>
                    <th style="padding:10px;">Seguimiento</th>
                    <th style="padding:10px;">Comprobante</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($misPedidos as $ped): ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($ped['numero_pedido_unico']) ?></td>
                        <td style="padding:10px;">$<?= number_format($ped['total'], 2) ?></td>
                        <td style="padding:10px;"><span class="badge-tag"><?= strtoupper($ped['estado_pedido']) ?></span></td>
                        <td style="padding:10px;">
                            <?php if (!empty($ped['numero_guia'])): ?>
                                🚚 <?= htmlspecialchars($ped['paqueteria']) ?>: <strong><?= htmlspecialchars($ped['numero_guia']) ?></strong>
                            <?php else: ?>
                                <span style="color:#999;">Procesando envío</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px;">
                            <a href="generar_recibo.php?id=<?= $ped['id'] ?>" target="_blank" style="text-decoration:underline;">PDF Recibo</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php else: ?>

<!-- Formulario de Login / Registro -->
<div style="padding: 3rem 0; max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
    <!-- Formulario Login -->
    <div style="background:#FFF; padding:2.5rem; border-radius:8px; border:1px solid var(--color-borde);">
        <h3 style="margin-bottom: 1.5rem;">Iniciar Sesión</h3>
        <?php if ($mensaje === 'credenciales_incorrectas'): ?>
            <p style="color:red; font-size:0.85rem; margin-bottom:1rem;">Correo o contraseña incorrectos.</p>
        <?php endif; ?>
        <form method="POST" action="mi-cuenta.php">
            <input type="hidden" name="action" value="login">
            <div style="margin-bottom:1rem;">
                <label>Correo Electrónico:</label>
                <input type="email" name="correo" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label>Contraseña:</label>
                <input type="password" name="password" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <button type="submit" class="btn-as" style="width:100%;">Entrar</button>
        </form>
    </div>

    <!-- Formulario Registro -->
    <div style="background:#FFF; padding:2.5rem; border-radius:8px; border:1px solid var(--color-borde);">
        <h3 style="margin-bottom: 1.5rem;">Crear Cuenta Nueva</h3>
        <?php if ($mensaje === 'registro_exitoso'): ?>
            <p style="color:green; font-size:0.85rem; margin-bottom:1rem;">¡Cuenta creada con éxito! Ya puedes iniciar sesión.</p>
        <?php endif; ?>
        <form method="POST" action="mi-cuenta.php">
            <input type="hidden" name="action" value="registro">
            <div style="margin-bottom:1rem;">
                <label>Nombre(s):</label>
                <input type="text" name="nombre" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <div style="margin-bottom:1rem;">
                <label>Apellidos:</label>
                <input type="text" name="apellidos" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <div style="margin-bottom:1rem;">
                <label>Correo Electrónico:</label>
                <input type="email" name="correo" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label>Contraseña:</label>
                <input type="password" name="password" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>
            <button type="submit" class="btn-as" style="width:100%;">Registrarme</button>
        </form>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>