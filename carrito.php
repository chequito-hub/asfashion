<?php
require_once __DIR__ . '/config/conexion.php';
include __DIR__ . '/includes/header.php';

$carrito = $_SESSION['carrito'] ?? [];
$subtotal = 0;

foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$descuento = $_SESSION['cupon_aplicado']['descuento'] ?? 0;
$costoEnvio = ($subtotal >= 999 || $subtotal == 0) ? 0 : 150;
$total = max(0, $subtotal - $descuento + $costoEnvio);
?>

<div style="padding: 3rem 0; max-width: 1100px; margin: 0 auto;">
    <h1 style="font-size: 2.5rem; text-align: center; margin-bottom: 0.5rem;">Tu Bolsa de Compras</h1>
    <p style="text-align: center; color: var(--color-texto-mutado); margin-bottom: 3rem; font-size: 0.9rem;">Prendas reservadas en tu sesión actual</p>

    <?php if (empty($carrito)): ?>
        <div style="text-align: center; background: #FFF; padding: 4rem 2rem; border-radius: 12px; border: 1px solid var(--color-borde); box-shadow: var(--sombra-seda);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🛍️</div>
            <p style="color: var(--color-texto-mutado); font-size: 1.1rem; margin-bottom: 1.5rem;">Tu bolsa de compras está vacía.</p>
            <a href="catalogo.php" class="btn-as">Explorar la Colección</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2.5rem;">
            
            <!-- Lista de Artículos -->
            <div style="background: #FFF; border-radius: 12px; border: 1px solid var(--color-borde); padding: 2rem; box-shadow: var(--sombra-elegante);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--color-borde); text-align: left; color: var(--color-texto-mutado); font-size: 0.8rem; text-transform: uppercase; letter-spacing:0.1em;">
                            <th style="padding-bottom: 1rem;">Prenda</th>
                            <th style="padding-bottom: 1rem;">Detalles</th>
                            <th style="padding-bottom: 1rem; text-align:center;">Cantidad</th>
                            <th style="padding-bottom: 1rem; text-align:right;">Subtotal</th>
                            <th style="padding-bottom: 1rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $varId => $item): ?>
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 1.2rem 0; display:flex; align-items:center; gap:12px;">
                                    <img src="<?= BASE_URL . (htmlspecialchars($item['imagen'] ?? 'assets/img/logo.png')) ?>" style="width:55px; height:70px; object-fit:cover; border-radius:6px; border:1px solid var(--color-borde);">
                                    <strong style="font-size:0.95rem; color:var(--color-espresso);"><?= htmlspecialchars($item['nombre']) ?></strong>
                                </td>
                                <td style="padding: 1.2rem 0; font-size: 0.82rem; color: var(--color-texto-mutado);">
                                    <div>Talla: <strong><?= htmlspecialchars($item['talla']) ?></strong></div>
                                    <div>Color: <strong><?= htmlspecialchars($item['color']) ?></strong></div>
                                </td>
                                <td style="padding: 1.2rem 0; text-align:center;">
                                    <span style="background:var(--color-fondo-lino); padding:4px 12px; border-radius:4px; font-weight:bold; font-size:0.9rem; border:1px solid var(--color-borde);"><?= $item['cantidad'] ?></span>
                                </td>
                                <td style="padding: 1.2rem 0; text-align:right; font-weight: 800; color: var(--color-primario); font-size:1.05rem;">
                                    $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                                </td>
                                <td style="padding: 1.2rem 0; text-align: right;">
                                    <button onclick="eliminarDelCarrito(<?= $varId ?>)" title="Eliminar ítem" style="background:none; border:none; color: #C62828; cursor:pointer; font-size:1.2rem; padding:4px;">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 1.5rem; display:flex; justify-content:space-between; align-items:center;">
                    <a href="catalogo.php" style="font-size:0.85rem; color:var(--color-primario); font-weight:700; text-decoration:underline;">← Seguir Comprando</a>
                </div>
            </div>

            <!-- Caja de Resumen -->
            <div style="background: #FFF; border-radius: 12px; border: 1px solid var(--color-borde-cobrizo); padding: 2rem; box-shadow: var(--sombra-seda); height: fit-content;">
                <h3 style="margin-bottom: 1.2rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 0.6rem; color:var(--color-espresso);">Resumen de Pedido</h3>
                
                <div style="display:flex; justify-content:space-between; margin-bottom: 0.8rem; font-size:0.9rem;">
                    <span style="color:var(--color-texto-mutado);">Subtotal Prendas:</span>
                    <strong>$<?= number_format($subtotal, 2) ?> MXN</strong>
                </div>

                <?php if ($descuento > 0): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 0.8rem; font-size:0.9rem; color: var(--color-primario);">
                        <span>Cupón (<?= htmlspecialchars($_SESSION['cupon_aplicado']['codigo']) ?>):</span>
                        <strong>-$<?= number_format($descuento, 2) ?> MXN</strong>
                    </div>
                <?php endif; ?>

                <div style="display:flex; justify-content:space-between; margin-bottom: 0.8rem; font-size:0.9rem;">
                    <span style="color:var(--color-texto-mutado);">Envío Asegurado:</span>
                    <?php if ($costoEnvio == 0): ?>
                        <strong style="color:green;">¡GRATIS!</strong>
                    <?php else: ?>
                        <strong>$<?= number_format($costoEnvio, 2) ?> MXN</strong>
                    <?php endif; ?>
                </div>

                <?php if ($subtotal < 999): ?>
                    <div style="background:var(--color-fondo-warm); padding:8px 12px; border-radius:6px; font-size:0.75rem; color:var(--color-primario); margin-bottom:1.2rem; border:1px solid var(--color-borde-cobrizo);">
                        💡 Agrega <strong>$<?= number_format(999 - $subtotal, 2) ?> MXN</strong> más para obtener <strong>Envío Gratis</strong>.
                    </div>
                <?php endif; ?>

                <div style="display:flex; justify-content:space-between; margin: 1.2rem 0; font-size: 1.3rem; font-weight: 800; border-top: 2px solid var(--color-borde); padding-top: 1rem; color:var(--color-espresso);">
                    <span>Total a Pagar:</span>
                    <span style="color: var(--color-primario);">$<?= number_format($total, 2) ?> MXN</span>
                </div>

                <!-- Cupón Promocional -->
                <form id="form-cupon" style="margin-bottom: 1.5rem; display: flex; gap: 6px;">
                    <input type="text" id="codigo-cupon" placeholder="Código de Descuento" class="newsletter-input" style="background:#FFF; color:#222; border: 1px solid #CCC; text-transform: uppercase; font-size:0.8rem;">
                    <button type="button" onclick="aplicarCupón(<?= $subtotal ?>)" class="btn-as" style="padding: 0.6rem 1rem; font-size: 0.75rem;">Aplicar</button>
                </form>

                <a href="checkout.php" class="btn-as" style="width: 100%; text-align: center; font-size:0.85rem; padding:1.1rem;">🔒 Proceder al Pago Seguro</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function eliminarDelCarrito(varianteId) {
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('variante_id', varianteId);

    const res = await fetch('api/carrito.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.status === 'success') location.reload();
}

async function aplicarCupón(subtotal) {
    const codigo = document.getElementById('codigo-cupon').value.trim();
    if (!codigo) return;

    const formData = new FormData();
    formData.append('codigo', codigo);
    formData.append('subtotal', subtotal);

    const res = await fetch('api/cupones.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
        mostrarToast(data.message, '🎉');
        setTimeout(() => location.reload(), 800);
    } else {
        mostrarToast(data.message, '⚠️');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>