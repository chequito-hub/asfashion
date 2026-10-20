<?php
require_once __DIR__ . '/config/conexion.php';

$pedidoId = intval($_GET['id'] ?? 0);
$db = Conexion::getConexion();

$stmt = $db->prepare("SELECT p.*, u.nombre, u.correo FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div style="padding: 4rem 0; text-align: center; max-width: 680px; margin: 0 auto;">
    <div style="width:80px; height:80px; background:#E8F5E9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem auto; border:2px solid #A5D6A7;">
        <span style="font-size:2.5rem; color:#2E7D32;">✓</span>
    </div>

    <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem; color:var(--color-espresso);">¡Transacción Aprobada!</h1>
    <p style="color: var(--color-texto-mutado); margin-bottom: 2rem; font-size: 0.95rem;">Hemos enviado la nota de compra a tu correo <strong><?= htmlspecialchars($pedido['correo']) ?></strong></p>

    <!-- Caja Ficha de Pago -->
    <div style="background:#FFF; padding: 2.5rem; border-radius: 12px; border: 1px solid var(--color-borde-cobrizo); box-shadow: var(--sombra-seda); text-align:left; margin-bottom: 2.5rem;">
        <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-borde); padding-bottom:1rem; margin-bottom:1rem;">
            <div>
                <span style="font-size:0.75rem; color:#888;">NÚMERO DE ORDEN:</span>
                <div style="font-weight:800; font-size:1.1rem; color:var(--color-primario);"><?= htmlspecialchars($pedido['numero_pedido_unico']) ?></div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:0.75rem; color:#888;">CÓDIGO DE AUTORIZACIÓN:</span>
                <div style="font-family:monospace; font-weight:bold; color:var(--color-espresso);"><?= htmlspecialchars($pedido['codigo_autorizacion']) ?></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; font-size:0.88rem;">
            <div>
                <span style="color:#888; font-size:0.75rem;">Método de Pago:</span>
                <div><strong><?= strtoupper($pedido['metodo_pago']) ?></strong> (•••• <?= $pedido['ultimos_digitos'] ?>)</div>
            </div>
            <div>
                <span style="color:#888; font-size:0.75rem;">Estado de Pago:</span>
                <div><span class="badge-tag" style="background:#2E7D32; color:#FFF;">PAGO APROBADO</span></div>
            </div>
            <div>
                <span style="color:#888; font-size:0.75rem;">Entrega Estimada:</span>
                <div><strong>2 a 4 Días Hábiles</strong></div>
            </div>
            <div>
                <span style="color:#888; font-size:0.75rem;">Total Transaccionado:</span>
                <div style="font-size:1.2rem; font-weight:800; color:var(--color-primario);">$<?= number_format($pedido['total'], 2) ?> MXN</div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="generar_recibo.php?id=<?= $pedidoId ?>" target="_blank" class="btn-as">🖨️ Descargar Comprobante PDF</a>
        <a href="mi-cuenta.php" class="btn-as btn-outline">Rastrear en Mi Cuenta</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>