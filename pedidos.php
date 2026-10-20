<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1, 3]); // Admin o Encargado

$db = Conexion::getConexion();

// Actualizar Guía de Envío
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_guia') {
    $pedidoId = intval($_POST['pedido_id']);
    $paqueteria = trim($_POST['paqueteria']);
    $guia = trim($_POST['numero_guia']);
    
    $stmtUpd = $db->prepare("UPDATE pedidos SET paqueteria = ?, numero_guia = ?, estado_pedido = 'enviado' WHERE id = ?");
    $stmtUpd->execute([$paqueteria, $guia, $pedidoId]);
    registrarAuditoria($_SESSION['usuario_id'], 'ASIGNAR_GUIA', 'pedidos', $pedidoId, "Guía {$guia} asignada por {$paqueteria}");
}

$stmt = $db->query("SELECT p.*, u.nombre, u.apellidos FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.id DESC");
$pedidos = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2.5rem 0;">
    <!-- Sub-Menú Navegación del Admin -->
    <div style="display:flex; gap: 1rem; border-bottom: 2px solid var(--color-borde); padding-bottom: 1rem; margin-bottom: 2rem;">
        <a href="pedidos.php" class="btn-as" style="font-size:0.8rem;">📦 Gestión de Pedidos</a>
        <a href="productos.php" class="btn-as btn-outline" style="font-size:0.8rem;">👗 Catálogo e Inventario</a>
        <a href="exportar_ventas.php" class="btn-as btn-outline" style="font-size:0.8rem; margin-left: auto;">📊 Exportar Ventas a CSV</a>
    </div>

    <h2 style="margin-bottom: 1.5rem;">Gestión de Pedidos y Envíos</h2>

    <?php if (empty($pedidos)): ?>
        <div style="background:#FFF; padding: 3rem; text-align:center; border-radius:8px; border:1px solid var(--color-borde);">
            <p style="color:var(--color-texto-mutado); font-size:1.1rem;">Aún no se ha registrado ninguna venta en la plataforma.</p>
            <p style="font-size:0.9rem; color:#888; margin-top:0.5rem;">Prueba realizar una compra desde la tienda para ver cómo se genera la orden aquí en tiempo real.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; background:#FFF; border-collapse:collapse; border:1px solid var(--color-borde);">
            <thead>
                <tr style="background:var(--color-fondo-lino); text-align:left;">
                    <th style="padding:12px;">Orden #</th>
                    <th style="padding:12px;">Cliente</th>
                    <th style="padding:12px;">Total</th>
                    <th style="padding:12px;">Estado</th>
                    <th style="padding:12px;">Guía de Envío</th>
                    <th style="padding:12px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr style="border-bottom: 1px solid var(--color-borde);">
                        <td style="padding:12px;"><strong><?= htmlspecialchars($p['numero_pedido_unico']) ?></strong></td>
                        <td style="padding:12px;"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                        <td style="padding:12px; color:var(--color-primario); font-weight:bold;">$<?= number_format($p['total'], 2) ?> MXN</td>
                        <td style="padding:12px;"><span class="badge-tag"><?= strtoupper($p['estado_pedido']) ?></span></td>
                        <td style="padding:12px;">
                            <form method="POST" action="pedidos.php" style="display:flex; gap:6px;">
                                <input type="hidden" name="action" value="actualizar_guia">
                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                <input type="text" name="paqueteria" placeholder="DHL/FedEx" value="<?= htmlspecialchars($p['paqueteria'] ?? '') ?>" required style="width:90px; padding:4px;">
                                <input type="text" name="numero_guia" placeholder="# Guía" value="<?= htmlspecialchars($p['numero_guia'] ?? '') ?>" required style="width:110px; padding:4px;">
                                <button type="submit" class="btn-as" style="padding:4px 8px; font-size:0.7rem;">Guardar</button>
                            </form>
                        </td>
                        <td style="padding:12px;">
                            <a href="../generar_recibo.php?id=<?= $p['id'] ?>" target="_blank" style="text-decoration:underline; font-size:0.85rem;">🖨️ Ver Recibo PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>