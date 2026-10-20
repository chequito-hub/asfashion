<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1, 2]); // Super Admin (1) y Administrador (2)

$db = Conexion::getConexion();
$msg = $_GET['msg'] ?? '';
$error = '';

// Borrar Producto
if (isset($_GET['borrar']) && esSuperAdmin()) {
    $borrarId = intval($_GET['borrar']);
    $stmtDel = $db->prepare("DELETE FROM productos WHERE id = ?");
    $stmtDel->execute([$borrarId]);
    header('Location: productos.php?msg=borrado');
    exit;
}

// Agregar Nuevo Producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'nuevo_producto') {
    $nombre = trim($_POST['nombre']);
    $sku = trim($_POST['sku']);
    $precio = floatval($_POST['precio_regular']);
    $catId = intval($_POST['categoria_id']);
    $marcaId = intval($_POST['marca_id']);
    $talla = trim($_POST['talla']);
    $colorNombre = trim($_POST['color_nombre']);
    $colorHex = trim($_POST['color_hex'] ?? '#B57A55');
    $stock = intval($_POST['stock']);
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)) . '-' . rand(100, 999);

    $rutaImagen = 'assets/img/logo.png';

    if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['imagen_archivo']['tmp_name'];
        $fileName = $_FILES['imagen_archivo']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'])) {
            $dirSubida = __DIR__ . '/../assets/img/productos/';
            if (!file_exists($dirSubida)) mkdir($dirSubida, 0777, true);
            
            $nuevoNombre = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($fileTmp, $dirSubida . $nuevoNombre)) {
                $rutaImagen = 'assets/img/productos/' . $nuevoNombre;
            }
        }
    }

    $stmtIns = $db->prepare("INSERT INTO productos (categoria_id, marca_id, sku, nombre, slug, precio_regular) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtIns->execute([$catId, $marcaId, $sku, $nombre, $slug, $precio]);
    $prodId = $db->lastInsertId();

    $stmtFoto = $db->prepare("INSERT INTO fotos_producto (producto_id, ruta_imagen, es_principal) VALUES (?, ?, 1)");
    $stmtFoto->execute([$prodId, $rutaImagen]);

    $stmtVar = $db->prepare("INSERT INTO variantes_producto (producto_id, talla, color_nombre, color_hex, stock_disponible) VALUES (?, ?, ?, ?, ?)");
    $stmtVar->execute([$prodId, $talla, $colorNombre, $colorHex, $stock]);

    header('Location: productos.php?msg=creado');
    exit;
}

// Cargar Productos
$stmtProd = $db->query("
    SELECT p.*, c.nombre as cat_nombre, f.ruta_imagen, SUM(v.stock_disponible) as stock_total
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN fotos_producto f ON p.id = f.producto_id AND f.es_principal = 1
    LEFT JOIN variantes_producto v ON p.id = v.producto_id
    GROUP BY p.id ORDER BY p.id DESC
");
$productos = $stmtProd->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2.5rem 0;">
    <!-- Menú Admin -->
    <div style="display:flex; gap: 1rem; border-bottom: 2px solid var(--color-borde); padding-bottom: 1rem; margin-bottom: 2.5rem; align-items:center;">
        <a href="pedidos.php" class="btn-as btn-outline" style="font-size:0.8rem;">📦 Pedidos y Envíos</a>
        <a href="productos.php" class="btn-as" style="font-size:0.8rem;">👗 Catálogo e Inventario</a>
        <?php if (esSuperAdmin()): ?>
            <a href="usuarios.php" class="btn-as btn-outline" style="font-size:0.8rem; border-color:var(--color-primario); color:var(--color-primario)!important;">👑 Usuarios</a>
        <?php endif; ?>
    </div>

    <h2>Catálogo e Inventario de Prendas</h2>
    <p style="color: var(--color-texto-mutado); margin-bottom: 2rem;">Agrega prendas seleccionando sus colores en paleta de bolitas y gestiona los modelos.</p>

    <?php if ($msg === 'creado'): ?>
        <div style="background:#E8F5E9; color:#2E7D32; padding:1rem; border-radius:6px; margin-bottom:1.5rem; font-weight:bold;">✨ Prenda agregada con éxito al catálogo.</div>
    <?php endif; ?>
    <?php if ($msg === 'borrado'): ?>
        <div style="background:#FFEBEE; color:#C62828; padding:1rem; border-radius:6px; margin-bottom:1.5rem; font-weight:bold;">🗑️ Modelo eliminado del sistema.</div>
    <?php endif; ?>

    <!-- Formulario de Nueva Prenda -->
    <div style="background: #FFF; padding: 2.5rem; border-radius: 10px; border: 1px solid var(--color-borde); margin-bottom: 3.5rem; box-shadow: var(--sombra-seda);">
        <h3 style="margin-bottom: 1.5rem;">➕ Agregar Nuevo Modelo</h3>
        
        <form method="POST" action="productos.php" enctype="multipart/form-data" style="display: grid; grid-template-columns: 280px 1fr; gap: 2.5rem;">
            <input type="hidden" name="action" value="nuevo_producto">

            <div>
                <label style="display:block; font-weight:700; margin-bottom:0.5rem; font-size:0.85rem;">Imagen de Prenda (desde tu PC):</label>
                <div id="dropzone-box" onclick="document.getElementById('imagen_archivo').click();" style="border: 2px dashed var(--color-borde-cobrizo); background: var(--color-fondo-warm); border-radius: 8px; padding: 1.5rem; text-align: center; cursor: pointer; min-height: 240px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <img id="preview-img" src="<?= BASE_URL ?>assets/img/logo.png" style="max-height: 150px; width: auto; object-fit: contain; margin-bottom: 10px;">
                    <span style="font-size: 0.8rem; color: var(--color-primario); font-weight: 700;">📁 Seleccionar Imagen</span>
                </div>
                <input type="file" id="imagen_archivo" name="imagen_archivo" accept="image/*" style="display: none;" onchange="const r=new FileReader(); r.onload=e=>document.getElementById('preview-img').src=e.target.result; r.readAsDataURL(this.files[0]);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem;">
                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Nombre del Modelo *</label>
                    <input type="text" name="nombre" placeholder="Ej. Vestido Gala Satin" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Código SKU *</label>
                    <input type="text" name="sku" placeholder="Ej. ASF-GAL-01" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Precio Regular ($ MXN) *</label>
                    <input type="number" step="0.01" name="precio_regular" placeholder="1290.00" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Categoría *</label>
                    <select name="categoria_id" class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222; background:#FFF;">
                        <option value="1">Vestidos</option>
                        <option value="2">Jeans y Pantalones</option>
                        <option value="3">Blusas y Tops</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Marca *</label>
                    <select name="marca_id" class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222; background:#FFF;">
                        <option value="1">AsFashion Studio</option>
                        <option value="2">Moda Presumida</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Talla Inicial *</label>
                    <input type="text" name="talla" placeholder="S, M, L o 28, 30" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <!-- Selección de Color con Paleta y Hex -->
                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Nombre de Color *</label>
                    <input type="text" id="input-color-nombre" name="color_nombre" value="Oro Rosa" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Color Visual (Bolita) *</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="color" id="input-color-hex" name="color_hex" value="#B57A55" style="width:50px; height:42px; border:none; cursor:pointer; background:none;">
                        <span style="font-size:0.8rem; color:var(--color-texto-mutado);">Selecciona el tono en la paleta</span>
                    </div>
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Stock Inicial *</label>
                    <input type="number" name="stock" value="10" min="1" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div style="grid-column: 1/-1; margin-top: 0.5rem;">
                    <button type="submit" class="btn-as" style="width:100%;">Guardar Modelo en Catálogo</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Modelos Existentes -->
    <h3>Modelos Registrados</h3>
    <table style="width:100%; background:#FFF; border-collapse:collapse; border:1px solid var(--color-borde); margin-top:1rem; border-radius:8px; overflow:hidden;">
        <thead>
            <tr style="background:var(--color-fondo-lino); text-align:left;">
                <th style="padding:12px;">Vista Previa</th>
                <th style="padding:12px;">SKU</th>
                <th style="padding:12px;">Modelo</th>
                <th style="padding:12px;">Categoría</th>
                <th style="padding:12px;">Precio</th>
                <th style="padding:12px;">Stock Total</th>
                <th style="padding:12px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr style="border-bottom:1px solid var(--color-borde);">
                    <td style="padding:10px;">
                        <img src="<?= BASE_URL . ($p['ruta_imagen'] ?? 'assets/img/logo.png') ?>" style="width:45px; height:55px; object-fit:cover; border-radius:4px;">
                    </td>
                    <td style="padding:12px; font-family:monospace;"><?= htmlspecialchars($p['sku']) ?></td>
                    <td style="padding:12px; font-weight:bold;"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td style="padding:12px;"><?= htmlspecialchars($p['cat_nombre'] ?? 'General') ?></td>
                    <td style="padding:12px; color:var(--color-primario); font-weight:bold;">$<?= number_format($p['precio_regular'], 2) ?></td>
                    <td style="padding:12px;"><span class="badge-tag"><?= intval($p['stock_total'] ?? 0) ?> pzs</span></td>
                    <td style="padding:12px; display:flex; gap:10px; align-items:center;">
                        <!-- Botón Editar Modelo -->
                        <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn-as btn-outline" style="padding: 4px 10px; font-size:0.75rem; color:var(--color-espresso)!important;">✏️ Editar Modelo</a>
                        
                        <!-- Botón Borrar Modelo -->
                        <?php if (esSuperAdmin()): ?>
                            <a href="productos.php?borrar=<?= $p['id'] ?>" onclick="return confirm('¿Confirmas que deseas eliminar permanentemente este modelo?')" style="color:#DC3545; font-weight:bold; font-size:0.8rem;">🗑️ Borrar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>