<?php
require_once __DIR__ . '/config/conexion.php';

$id = intval($_GET['id'] ?? 1);$db = Conexion::getConexion();

// Cargar Producto
$stmt =$db->prepare("SELECT p.*, f.ruta_imagen, c.nombre as cat_nombre FROM productos p LEFT JOIN fotos_producto f ON p.id = f.producto_id AND f.es_principal = 1 LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ? AND p.activo = 1");
$stmt->execute([$id]);
$producto =$stmt->fetch();

if (!$producto) {
    header('Location: catalogo.php');
    exit;
}

// Cargar Variantes con Colores Hex
$stmtVar =$db->prepare("SELECT * FROM variantes_producto WHERE producto_id = ? ORDER BY color_nombre ASC, talla ASC");
$stmtVar->execute([$id]);
$variantes =$stmtVar->fetchAll();

// Agrupar variantes por color
$coloresAgrupados = [];
foreach ($variantes as$v) {
    $cNombre =$v['color_nombre'];
    if (!isset($coloresAgrupados[$cNombre])) {
        $coloresAgrupados[$cNombre] = [
            'hex' => $v['color_hex'] ?: '#B57A55',
            'variantes' => []
        ];
    }
    $coloresAgrupados[$cNombre]['variantes'][] =$v;
}

include __DIR__ . '/includes/header.php';
?>

<div style="padding: 3rem 0;">
    <div class="producto-detalle-grid">
        <!-- Lupa / Zoom de Imagen Principal -->
        <div class="img-zoom-container">
            <img id="main-product-img" src="<?= BASE_URL . ((!empty($producto['ruta_imagen']) &&$producto['ruta_imagen'] !== 'assets/img/logo.png') ? htmlspecialchars($producto['ruta_imagen']) : 'assets/img/logo.png') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>

        <!-- Información y Selección de Talla y Colores en "Bolitas" -->
        <div class="producto-info-box">
            <span class="badge-tag"><?= htmlspecialchars($producto['cat_nombre'] ?? 'Boutique') ?></span>
            <h1 style="margin-top: 0.8rem; font-size: 2.2rem;"><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p class="precio" style="font-size: 1.8rem; margin: 0.8rem 0;">$<?= number_format($producto['precio_regular'], 2) ?> MXN</p>
            <p style="color: var(--color-texto-mutado); margin-bottom: 1.5rem; font-size:0.95rem;"><?= htmlspecialchars($producto['descripcion'] ?? 'Confección de alta costura diseñada para siluetas elegantes.') ?></p>

            <button type="button" class="btn-outline" onclick="document.getElementById('modal-size-guide').classList.add('active')" style="margin-bottom: 1.8rem; font-size: 0.75rem;">📏 Ver Guía Oficial de Tallas</button>

            <!-- 1. Selección de Color con Bolitas (Swatches) -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-weight:800; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.6rem;">
                    1. Elige Color: <span id="lbl-color-seleccionado" style="color: var(--color-primario);">Selecciona un color</span>
                </label>
                <div class="color-swatches-selector">
                    <?php $firstColor = true; foreach ($coloresAgrupados as $nombreColor =>$datosColor): ?>
                        <div class="swatch-circle <?= $firstColor ? 'active' : '' ?>" 
                             style="background-color: <?= htmlspecialchars($datosColor['hex']) ?>;" 
                             title="<?= htmlspecialchars($nombreColor) ?>"
                             onclick="seleccionarColor('<?= htmlspecialchars($nombreColor) ?>', this)">
                        </div>
                    <?php $firstColor = false; endforeach; ?>
                </div>
            </div>

            <!-- 2. Selección de Talla con Chips -->
            <div style="margin-bottom: 1.8rem;">
                <label style="display:block; font-weight:800; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.6rem;">
                    2. Elige Talla:
                </label>
                <div id="contenedor-tallas" class="size-chips-container">
                    <!-- Cargado mediante JavaScript según el color -->
                </div>
            </div>

            <!-- Formulario de Agregar a la Bolsa -->
            <form id="form-add-to-cart" onsubmit="agregarAlCarritoForm(event)">
                <input type="hidden" id="selected_variante_id" name="variante_id" value="">

                <div style="margin-bottom: 1.2rem; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.85rem; font-weight: 700;">Cantidad:</span>
                    <input type="number" id="input-cantidad" name="cantidad" value="1" min="1" max="1" class="newsletter-input" style="width: 90px; text-align: center; border: 1px solid var(--color-borde-cobrizo); color:#222;" onchange="validarCantidadMax()">
                    <span id="lbl-stock-info" style="font-size: 0.8rem; color: var(--color-texto-mutado);"></span>
                </div>

                <button type="submit" id="btn-submit-cart" class="btn-as" style="width: 100%;" disabled>
                    Añadir a la Bolsa
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const variantesData = <?= json_encode($variantes) ?>;

function seleccionarColor(colorNombre, elemento) {
    document.querySelectorAll('.swatch-circle').forEach(el => el.classList.remove('active'));
    elemento.classList.add('active');
    document.getElementById('lbl-color-seleccionado').innerText = colorNombre;

    // Filtrar tallas para este color
    const tallasContenedor = document.getElementById('contenedor-tallas');
    tallasContenedor.innerHTML = '';

    const varsDelColor = variantesData.filter(v => v.color_nombre === colorNombre);

    varsDelColor.forEach((v, index) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'size-chip' + (v.stock_disponible <= 0 ? ' disabled' : '');
        btn.innerText = v.talla + (v.stock_disponible <= 0 ? ' (Agotado)' : '');
        
        if (v.stock_disponible > 0) {
            btn.onclick = () => seleccionarTalla(v, btn);
        }

        tallasContenedor.appendChild(btn);

        // Seleccionar la primera talla disponible automáticamente
        if (index === 0 && v.stock_disponible > 0) {
            seleccionarTalla(v, btn);
        }
    });
}

function seleccionarTalla(variante, elementoBtn) {
    document.querySelectorAll('.size-chip').forEach(el => el.classList.remove('active'));
    elementoBtn.classList.add('active');

    document.getElementById('selected_variante_id').value = variante.id;
    const inputCant = document.getElementById('input-cantidad');
    inputCant.max = variante.stock_disponible;
    inputCant.value = 1;

    document.getElementById('lbl-stock-info').innerText = `(${variante.stock_disponible} disponibles)`;
    document.getElementById('btn-submit-cart').disabled = false;
}

function validarCantidadMax() {
    const inputCant = document.getElementById('input-cantidad');
    const max = parseInt(inputCant.max) || 1;
    if (parseInt(inputCant.value) > max) {
        inputCant.value = max;
        mostrarToast(`El límite disponible para esta talla es de ${max} piezas.`, '⚠️');
    }
}

async function agregarAlCarritoForm(e) {
    e.preventDefault();
    const varId = document.getElementById('selected_variante_id').value;
    const cant = document.getElementById('input-cantidad').value;

    if (!varId) {
        mostrarToast('Por favor selecciona un color y una talla.', '⚠️');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'agregar');
    formData.append('variante_id', varId);
    formData.append('cantidad', cant);

    const res = await fetch('api/carrito.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.status === 'success') {
        document.getElementById('cart-count').innerText = data.cart_count;
        mostrarToast(data.message, '🛍️');
    } else {
        mostrarToast(data.message, '⚠️');
    }
}

// Inicializar selección
document.addEventListener('DOMContentLoaded', () => {
    const primerSwatch = document.querySelector('.swatch-circle');
    if (primerSwatch) primerSwatch.click();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>