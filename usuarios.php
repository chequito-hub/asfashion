<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1]); // EXCLUSIVO SUPER ADMIN (ROL_ID = 1)

$db = Conexion::getConexion();
$msg = '';

// Cambiar Rol de Usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiar_rol') {
    $targetUser = intval($_POST['usuario_id']);
    $nuevoRol = intval($_POST['rol_id']);

    $stmtUpd = $db->prepare("UPDATE usuarios SET rol_id = ? WHERE id = ?");
    $stmtUpd->execute([$nuevoRol, $targetUser]);
    
    registrarAuditoria($_SESSION['usuario_id'], 'CAMBIAR_ROL', 'usuarios', $targetUser, "Rol modificado a ID {$nuevoRol}");
    $msg = "Rol de usuario actualizado correctamente.";
}

// Cargar Usuarios y Roles
$usuarios = $db->query("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.id ASC")->fetchAll();
$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2.5rem 0;">
    <!-- Menú Admin -->
    <div style="display:flex; gap: 1rem; border-bottom: 2px solid var(--color-borde); padding-bottom: 1rem; margin-bottom: 2.5rem; flex-wrap:wrap; align-items:center;">
        <a href="pedidos.php" class="btn-as btn-outline" style="font-size:0.8rem;">📦 Pedidos y Envíos</a>
        <a href="productos.php" class="btn-as btn-outline" style="font-size:0.8rem;">👗 Catálogo e Inventario</a>
        <a href="usuarios.php" class="btn-as" style="font-size:0.8rem;">👑 Usuarios y Roles (Super Admin)</a>
        <a href="exportar_ventas.php" class="btn-as btn-outline" style="font-size:0.8rem; margin-left: auto;">📊 Reporte CSV</a>
    </div>

    <h2>Panel de Control de Usuarios y Permisos</h2>
    <p style="color: var(--color-texto-mutado); margin-bottom: 2rem;">Gestión exclusiva de roles para el nivel Super Admin.</p>

    <?php if ($msg): ?>
        <div style="background: #E8F5E9; color: #2E7D32; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #A5D6A7;"><?= $msg ?></div>
    <?php endif; ?>

    <table style="width:100%; background:#FFF; border-collapse:collapse; border:1px solid var(--color-borde); border-radius:8px; overflow:hidden; box-shadow: var(--sombra-elegante);">
        <thead>
            <tr style="background:var(--color-fondo-lino); text-align:left; font-size:0.85rem;">
                <th style="padding:14px;">ID</th>
                <th style="padding:14px;">Nombre Completo</th>
                <th style="padding:14px;">Correo Electrónico</th>
                <th style="padding:14px;">Rol Actual</th>
                <th style="padding:14px;">Asignar Nuevo Rol</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr style="border-bottom:1px solid var(--color-borde);">
                    <td style="padding:14px; font-weight:bold;"><?= $u['id'] ?></td>
                    <td style="padding:14px;"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></td>
                    <td style="padding:14px; font-family:monospace;"><?= htmlspecialchars($u['correo']) ?></td>
                    <td style="padding:14px;">
                        <span class="badge-tag" style="<?= $u['rol_id'] == 1 ? 'background:var(--color-primario); color:#FFF;' : '' ?>">
                            <?= strtoupper($u['rol_nombre']) ?>
                        </span>
                    </td>
                    <td style="padding:14px;">
                        <form method="POST" action="usuarios.php" style="display:flex; gap:8px;">
                            <input type="hidden" name="action" value="cambiar_rol">
                            <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                            
                            <select name="rol_id" class="newsletter-input" style="padding: 4px 8px; font-size:0.8rem; border:1px solid var(--color-borde); color:#222; background:#FFF;">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $u['rol_id'] == $r['id'] ? 'selected' : '' ?>><?= $r['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="submit" class="btn-as" style="padding: 4px 12px; font-size:0.75rem;">Actualizar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>