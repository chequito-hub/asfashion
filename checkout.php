<?php
require_once __DIR__ . '/config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: mi-cuenta.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    header('Location: carrito.php');
    exit;
}

$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$descuento = $_SESSION['cupon_aplicado']['descuento'] ?? 0;
$costoEnvio = ($subtotal >= 999) ? 0 : 150;
$total = max(0, $subtotal - $descuento + $costoEnvio);

// Generar CLABE simulada única para SPEI
$clabeSimulada = '012' . rand(100, 999) . rand(100000000, 999999999) . rand(10, 99);

include __DIR__ . '/includes/header.php';
?>

<div style="padding: 2.5rem 0; max-width: 1100px; margin: 0 auto;">
    <h1 style="text-align: center; font-size: 2.4rem; margin-bottom: 0.5rem;">Pasarela de Pago Segura</h1>
    <p style="text-align: center; color: var(--color-texto-mutado); margin-bottom: 2.5rem; font-size: 0.88rem;">Cifrado bancario de extremo a extremo (SSL 256-Bit)</p>

    <form id="form-checkout" action="procesar_pedido.php" method="POST" onsubmit="iniciarSimulacionPago(event)">
        <input type="hidden" name="token_csrf" value="<?= generarCSRFToken() ?>">
        <input type="hidden" id="metodo_pago_selected" name="metodo_pago" value="tarjeta">

        <div style="display: grid; grid-template-columns: 1fr 420px; gap: 2.5rem;">
            
            <!-- Columna Izquierda: Datos de Envío y Selección de Método de Pago -->
            <div>
                <!-- 1. Dirección de Envío -->
                <div style="background:#FFF; padding:2rem; border-radius:12px; border:1px solid var(--color-borde); margin-bottom:2rem; box-shadow:var(--sombra-elegante);">
                    <h3 style="margin-bottom:1.2rem; color:var(--color-espresso); border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem;">1. Dirección de Entrega</h3>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div style="grid-column: 1/-1;">
                            <label style="font-size:0.8rem; font-weight:700;">Calle y Número Exterior/Interior *</label>
                            <input type="text" name="calle" placeholder="Ej. Av. Reforma 123, Depto 4B" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                        </div>

                        <div>
                            <label style="font-size:0.8rem; font-weight:700;">Colonia / Fraccionamiento *</label>
                            <input type="text" name="colonia" placeholder="Ej. Polanco" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                        </div>

                        <div>
                            <label style="font-size:0.8rem; font-weight:700;">Código Postal *</label>
                            <input type="text" name="codigo_postal" placeholder="Ej. 11560" required maxlength="5" class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                        </div>

                        <div>
                            <label style="font-size:0.8rem; font-weight:700;">Ciudad / Municipio *</label>
                            <input type="text" name="ciudad" placeholder="Ej. Ciudad de México" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                        </div>

                        <div>
                            <label style="font-size:0.8rem; font-weight:700;">Teléfono de Contacto (Paquetería) *</label>
                            <input type="tel" name="telefono" placeholder="Ej. 5512345678" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                        </div>
                    </div>
                </div>

                <!-- 2. Métodos de Pago Simulados -->
                <div style="background:#FFF; padding:2rem; border-radius:12px; border:1px solid var(--color-borde); box-shadow:var(--sombra-elegante);">
                    <h3 style="margin-bottom:1.2rem; color:var(--color-espresso); border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem;">2. Método de Pago</h3>

                    <!-- Pestañas de Selección -->
                    <div style="display:flex; gap:10px; margin-bottom:1.5rem;">
                        <button type="button" class="tab-pay-btn active" onclick="seleccionarMetodo('tarjeta', this)">💳 Tarjeta</button>
                        <button type="button" class="tab-pay-btn" onclick="seleccionarMetodo('spei', this)">🏦 SPEI Transferencia</button>
                        <button type="button" class="tab-pay-btn" onclick="seleccionarMetodo('paypal', this)">🅿️ PayPal Express</button>
                    </div>

                    <!-- OPCIÓN 1: Tarjeta de Crédito/Débito con Visualizador Interactivo -->
                    <div id="pay-box-tarjeta">
                        <!-- Tarjeta Digital Animada en Tiempo Real -->
                        <div class="virtual-card">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:0.75rem; letter-spacing:0.15em; font-weight:800; color:#EAD3C3;">AS FASHION GOLD</span>
                                <span id="card-brand-logo" style="font-weight:bold; font-style:italic;">VISA</span>
                            </div>
                            <div id="card-num-display" style="font-family:monospace; font-size:1.25rem; letter-spacing:0.18em; margin:1.5rem 0 1rem 0;">•••• •••• •••• ••••</div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; text-transform:uppercase;">
                                <div>
                                    <div style="color:#A3A3A3; font-size:0.6rem;">TITULAR</div>
                                    <div id="card-name-display">NOMBRE TITULAR</div>
                                </div>
                                <div>
                                    <div style="color:#A3A3A3; font-size:0.6rem;">VENCE</div>
                                    <div id="card-exp-display">MM/AA</div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario Tarjeta -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1.5rem;">
                            <div style="grid-column:1/-1;">
                                <label style="font-size:0.78rem; font-weight:700;">Número de Tarjeta (Prueba) *</label>
                                <input type="text" id="card_number" name="card_number" placeholder="4532 8921 4012 8890" maxlength="19" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222; font-family:monospace;" oninput="actualizarTarjeta(this)">
                            </div>

                            <div style="grid-column:1/-1;">
                                <label style="font-size:0.78rem; font-weight:700;">Nombre en la Tarjeta *</label>
                                <input type="text" id="card_name" name="card_name" placeholder="Como aparece en el plástico" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;" oninput="document.getElementById('card-name-display').innerText = this.value.toUpperCase() || 'NOMBRE TITULAR'">
                            </div>

                            <div>
                                <label style="font-size:0.78rem; font-weight:700;">Expiración (MM/AA) *</label>
                                <input type="text" id="card_exp" name="card_exp" placeholder="08/28" maxlength="5" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;" oninput="document.getElementById('card-exp-display').innerText = this.value || 'MM/AA'">
                            </div>

                            <div>
                                <label style="font-size:0.78rem; font-weight:700;">CVV / CVC *</label>
                                <input type="password" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
                            </div>
                        </div>
                    </div>

                    <!-- OPCIÓN 2: SPEI Transferencia -->
                    <div id="pay-box-spei" style="display:none; background:var(--color-fondo-warm); padding:1.5rem; border-radius:8px; border:1px solid var(--color-borde-cobrizo);">
                        <h4 style="margin-bottom:0.5rem; color:var(--color-primario);">Transferencia Electrónica SPEI (STP)</h4>
                        <p style="font-size:0.82rem; color:var(--color-texto-mutado); margin-bottom:1rem;">Realiza tu pago desde la app de tu banco con la siguiente CLABE interbancaria autorizada al instante:</p>
                        
                        <div style="background:#FFF; padding:1rem; border-radius:6px; border:1px solid #DDD; font-family:monospace; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-size:0.7rem; color:#888;">CLABE INTERBANCARIA:</div>
                                <strong style="font-size:1.1rem; color:var(--color-espresso);"><?= $clabeSimulada ?></strong>
                            </div>
                            <button type="button" onclick="navigator.clipboard.writeText('<?= $clabeSimulada ?>'); mostrarToast('CLABE copiada al portapapeles', '📋');" class="btn-as btn-outline" style="font-size:0.7rem; padding:4px 10px;">Copiar</button>
                        </div>
                        <p style="font-size:0.75rem; color:#666;">Banco Destino: <strong>BBVA / STP</strong> | Beneficiario: <strong>AsFashion Boutique S.A. de C.V.</strong></p>
                    </div>

                    <!-- OPCIÓN 3: PayPal -->
                    <div id="pay-box-paypal" style="display:none; text-align:center; padding:1.5rem; background:#F8F9FA; border-radius:8px; border:1px solid #DDD;">
                        <p style="font-size:0.88rem; margin-bottom:1rem;">Serás redirigido a la ventana segura de <strong>PayPal Sandbox Express</strong> para autorizar la transacción.</p>
                        <div style="background:#0070BA; color:#FFF; padding:12px; border-radius:4px; font-weight:800; font-size:1.1rem;">PayPal Express Checkout</div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen Final y Botón de Pago -->
            <div>
                <div style="background:#FFF; padding:2rem; border-radius:12px; border:1px solid var(--color-borde-cobrizo); box-shadow:var(--sombra-seda); position:sticky; top:100px;">
                    <h3 style="margin-bottom:1.2rem; border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem; color:var(--color-espresso);">Resumen de Compra</h3>

                    <!-- Lista Mini prendas -->
                    <div style="max-height: 180px; overflow-y:auto; margin-bottom:1rem;">
                        <?php foreach ($carrito as $item): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.8rem; font-size:0.85rem;">
                                <span><?= $item['cantidad'] ?>x <?= htmlspecialchars($item['nombre']) ?></span>
                                <strong>$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--color-borde); padding-top:1rem; font-size:0.85rem; display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2) ?> MXN</span>
                    </div>

                    <?php if ($descuento > 0): ?>
                        <div style="font-size:0.85rem; display:flex; justify-content:space-between; margin-bottom:0.5rem; color:var(--color-primario);">
                            <span>Descuento:</span>
                            <span>-$<?= number_format($descuento, 2) ?> MXN</span>
                        </div>
                    <?php endif; ?>

                    <div style="font-size:0.85rem; display:flex; justify-content:space-between; margin-bottom:1rem;">
                        <span>Envío:</span>
                        <span><?= $costoEnvio == 0 ? 'GRATIS' : '$' . number_format($costoEnvio, 2) . ' MXN' ?></span>
                    </div>

                    <div style="border-top:2px solid var(--color-borde); padding-top:1rem; font-size:1.3rem; font-weight:800; display:flex; justify-content:space-between; margin-bottom:1.8rem; color:var(--color-espresso);">
                        <span>Total:</span>
                        <span style="color:var(--color-primario);">$<?= number_format($total, 2) ?> MXN</span>
                    </div>

                    <button type="submit" id="btn-pay-now" class="btn-as" style="width:100%; padding:1.1rem; font-size:0.9rem;">
                        💳 Pagar $<?= number_format($total, 2) ?> MXN
                    </button>
                    
                    <p style="text-align:center; font-size:0.7rem; color:#888; margin-top:1rem;">🔒 Al hacer clic aceptas las políticas de venta y envío asegurado de AsFashion.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de Carga de Simulación de Pago -->
<div id="modal-processing-pay" class="modal-backdrop">
    <div style="background:#FFF; padding:3rem; border-radius:12px; text-align:center; max-width:420px; box-shadow:var(--sombra-seda);">
        <div class="pay-spinner"></div>
        <h3 style="margin-top:1.5rem; color:var(--color-espresso);">Procesando Pago Seguro...</h3>
        <p style="font-size:0.82rem; color:var(--color-texto-mutado); margin-top:0.5rem;">Conectando con la pasarela bancaria. No cierres esta ventana.</p>
    </div>
</div>

<style>
.tab-pay-btn {
  flex: 1;
  padding: 0.8rem;
  background: #FFF;
  border: 1px solid var(--color-borde);
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
  transition: var(--transicion);
}
.tab-pay-btn.active {
  background: var(--color-espresso);
  color: #FFF;
  border-color: var(--color-espresso);
}

.virtual-card {
  background: linear-gradient(135deg, #221E1B 0%, #36322E 50%, #B57A55 100%);
  color: #FFF;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 12px 25px rgba(0,0,0,0.15);
  margin-bottom: 1rem;
}

.pay-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--color-borde);
  border-top-color: var(--color-primario);
  border-radius: 50%;
  animation: spinPay 1s linear infinite;
  margin: 0 auto;
}
@keyframes spinPay { to { transform: rotate(360deg); } }
</style>

<script>
function seleccionarMetodo(metodo, btn) {
    document.querySelectorAll('.tab-pay-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('metodo_pago_selected').value = metodo;

    document.getElementById('pay-box-tarjeta').style.display = (metodo === 'tarjeta') ? 'block' : 'none';
    document.getElementById('pay-box-spei').style.display = (metodo === 'spei') ? 'block' : 'none';
    document.getElementById('pay-box-paypal').style.display = (metodo === 'paypal') ? 'block' : 'none';

    // Ajustar campos requeridos según método
    const reqTarjeta = (metodo === 'tarjeta');
    document.getElementById('card_number').required = reqTarjeta;
    document.getElementById('card_name').required = reqTarjeta;
    document.getElementById('card_exp').required = reqTarjeta;
    document.getElementById('card_cvv').required = reqTarjeta;
}

function actualizarTarjeta(input) {
    let v = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let matches = v.match(/\d{4,16}/g);
    let match = matches && matches[0] || '';
    let parts = [];

    for (let i=0, len=match.length; i<len; i+=4) {
        parts.push(match.substring(i, i+4));
    }

    if (parts.length) {
        input.value = parts.join(' ');
    }

    // Marca de Tarjeta
    const brand = document.getElementById('card-brand-logo');
    if (v.startsWith('4')) brand.innerText = 'VISA';
    else if (v.startsWith('5')) brand.innerText = 'MASTERCARD';
    else if (v.startsWith('3')) brand.innerText = 'AMEX';
    else brand.innerText = 'CARD';

    document.getElementById('card-num-display').innerText = input.value || '•••• •••• •••• ••••';
}

function iniciarSimulacionPago(e) {
    e.preventDefault();
    const modal = document.getElementById('modal-processing-pay');
    modal.classList.add('active');

    // Simular retraso bancario de 2.5 segundos para máxima veracidad
    setTimeout(() => {
        document.getElementById('form-checkout').submit();
    }, 2500);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>