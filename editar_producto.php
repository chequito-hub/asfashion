<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1, 2]); // Super Admin y Administrador

$db = Conexion::getConexion();
$id = intval($_GET['id'] ?? 0);
$msg = '';

if ($id <= 0) {
    header('Location: productos.php');
    exit;
}

// 1. Procesar Actualización de Datos Generales del Producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_producto') {
    $nombre = trim($_POST['nombre']);
    $sku = trim($_POST['sku']);
    $precio = floatval($_POST['precio_regular']);
    $catId = intval($_POST['categoria_id']);
    $marcaId = intval($_POST['marca_id']);
    $desc = trim($_POST['descripcion']);

    $stmtUpd = $db->prepare("UPDATE productos SET nombre = ?, sku = ?, precio_regular = ?, categoria_id = ?, marca_id = ?, descripcion = ? WHERE id = ?");
    $stmtUpd->execute([$nombre, $sku, $precio, $catId, $marcaId, $desc, $id]);

    // Nueva imagen si se subió una
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['nueva_imagen']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['nueva_imagen']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'])) {
            $dirSubida = __DIR__ . '/../assets/img/productos/';
            if (!file_exists($dirSubida)) mkdir($dirSubida, 0777, true);

            $nuevoNombre = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($fileTmp, $dirSubida . $nuevoNombre)) {
                $rutaFinal = 'assets/img/productos/' . $nuevoNombre;
                
                // Actualizar o insertar foto principal
                $db->prepare("UPDATE fotos_producto SET es_principal = 0 WHERE producto_id = ?")->execute([$id]);
                $db->prepare("INSERT INTO fotos_producto (producto_id, ruta_imagen, es_principal) VALUES (?, ?, 1)")->execute([$id, $rutaFinal]);
            }
        }
    }
    $msg = 'Información general del modelo actualizada.';
}

// 2. Procesar Agregar Variante (Talla / Color / Stock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'agregar_variante') {
    $talla = trim($_POST['talla']);
    $colorNombre = trim($_POST['color_nombre']);
    $colorHex = trim($_POST['color_hex']);
    $stock = intval($_POST['stock']);

    $stmtVar = $db->prepare("INSERT INTO variantes_producto (producto_id, talla, color_nombre, color_hex, stock_disponible) VALUES (?, ?, ?, ?, ?)");
    $stmtVar->execute([$id, $talla, $colorNombre, $colorHex, $stock]);
    $msg = 'Variante de talla/color añadida con éxito.';
}

// 3. Borrar Variante Individual
if (isset($_GET['borrar_var'])) {
    $varId = intval($_GET['borrar_var']);
    $db->prepare("DELETE FROM variantes_producto WHERE id = ? AND producto_id = ?")->execute([$varId, $id]);
    header("Location: editar_producto.php?id={$id}&msg=var_eliminada");
    exit;
}

// Cargar Producto
$stmt = $db->prepare("SELECT p.*, f.ruta_imagen FROM productos p LEFT JOIN fotos_producto f ON p.id = f.producto_id AND f.es_principal = 1 WHERE p.id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

// Cargar Variantes del Producto
$stmtVars = $db->prepare("SELECT * FROM variantes_producto WHERE producto_id = ? ORDER BY id ASC");
$stmtVars->execute([$id]);
$variantes = $stmtVars->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2.5rem 0; max-width: 1000px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
        <h2>✏️ Editar Modelo: <?= htmlspecialchars($producto['nombre']) ?></h2>
        <a href="productos.php" class="btn-as btn-outline">← Volver al Catálogo</a>
    </div>

    <?php if ($msg): ?>
        <div style="background:#E8F5E9; color:#2E7D32; padding:1rem; border-radius:6px; margin-bottom:1.5rem; font-weight:bold;"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Formulario 1: Datos Generales e Imagen -->
    <div style="background:#FFF; padding:2.5rem; border-radius:10px; border:1px solid var(--color-borde); margin-bottom:3rem; box-shadow: var(--sombra-seda);">
        <h3 style="margin-bottom:1.5rem;">Información del Modelo</h3>
        
        <form method="POST" action="editar_producto.php?id=<?= $id ?>" enctype="multipart/form-data" style="display:grid; grid-template-columns:220px 1fr; gap:2rem;">
            <input type="hidden" name="action" value="actualizar_producto">

            <div>
                <label style="font-weight:700; font-size:0.85rem; display:block; margin-bottom:0.5rem;">Foto Actual:</label>
                <img src="<?= BASE_URL . ($producto['ruta_imagen'] ?? 'assets/img/logo.png') ?>" style="width:100%; max-height:220px; object-fit:cover; border-radius:6px; border:1px solid var(--color-borde); margin-bottom:10px;">
                
                <label style="font-size:0.75rem; color:var(--color-primario); cursor:pointer; font-weight:bold; text-align:center; display:block;">
                    📷 Cambiar Foto (PC)
                    <input type="file" name="nueva_imagen" accept="image/*" style="display:none;" onchange="this.form.submit()">
                </label>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.2rem;">
                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Nombre del Modelo</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Código SKU</label>
                    <input type="text" name="sku" value="<?= htmlspecialchars($producto['sku']) ?>" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Precio ($ MXN)</label>
                    <input type="number" step="0.01" name="precio_regular" value="<?= $producto['precio_regular'] ?>" required class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222;">
                </div>

                <div>
                    <label style="font-size:0.8rem; font-weight:700;">Categoría</label>
                    <select name="categoria_id" class="newsletter-input" style="width:100%; border:1px solid var(--color-borde); color:#222; background:#FFF;">
                        <option value="1" <?= $producto['categoria_id'] == 1 ? 'selected' : '' ?>>Vestidos</option>
                        <option value="2" <?= $producto['categoria_id'] == 2 ? 'selected' : '' ?>>Jeans y Pantalones</option>
                        <option value="3" <?= $producto['categoria_id'] == 3 ? 'selected' : '' ?>>Blusas y Tops</option>
                    </select>
                </div>

                <div style="grid-column: 1/-1;">
                    <label style="font-size:0.8rem; font-weight:700;">Descripción Corta</label>
                    <textarea name="descripcion" class="newsletter-input" style="width:100%; height:70px; border:1px solid var(--color-borde); color:#222;"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-as" style="grid-column: 1/-1;">Guardar Cambios Generales</button>
            </div>
        </form>
    </div>

    <!-- Formulario 2: Gestión de Variantes de Tallas, Colores y Stock -->
    <div style="background:#FFF; padding:2.5rem; border-radius:10px; border:1px solid var(--color-borde); box-shadow: var(--sombra-seda);">
        <h3 style="margin-bottom:1.5rem;">Variantes de Tallas y Colores</h3>

        <!-- Formulario para agregar nueva variante -->
        <form method="POST" action="editar_producto.php?id=<?= $id ?>" style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:2rem; background:var(--color-fondo-warm); padding:1rem; border-radius:6px; border:1px solid var(--color-borde);">
            <input type="hidden" name="action" value="agregar_variante">
            
            <div>
                <label style="font-size:0.75rem; font-weight:700;">Talla:</label>
                <input type="text" name="talla" placeholder="S, M, L..." required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>

            <div>
                <label style="font-size:0.75rem; font-weight:700;">Nombre Color:</label>
                <input type="text" name="color_nombre" placeholder="Ej. Oro Rosa" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>

            <div>
                <label style="font-size:0.75rem; font-weight:700;">Color Hex (Bolita):</label>
                <input type="color" name="color_hex" value="#B57A55" style="width:100%; height:40px; border:none; cursor:pointer;">
            </div>

            <div>
                <label style="font-size:0.75rem; font-weight:700;">Stock (Piezas):</label>
                <input type="number" name="stock" value="10" min="0" required class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
            </div>

            <button type="submit" class="btn-as" style="padding:0.7rem 1.2rem; font-size:0.8rem;">+ Añadir Variante</button>
        </form>

        <!-- Tabla de Variantes -->
        <table style="width:100%; border-collapse:collapse; border:1px solid var(--color-borde);">
            <thead>
                <tr style="background:var(--color-fondo-lino); text-align:left; font-size:0.85rem;">
                    <th style="padding:10px;">Color Visual</th>
                    <th style="padding:10px;">Nombre Color</th>
                    <th style="padding:10px;">Talla</th>
                    <th style="padding:10px;">Stock Disponible</th>
                    <th style="padding:10px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variantes as $v): ?>
                    <tr style="border-bottom:1px solid var(--color-borde);">
                        <td style="padding:10px;">
                            <div style="width:24px; height:24px; border-radius:50%; background-color:<?= htmlspecialchars($v['color_hex'] ?: '#B57A55') ?>; border:1px solid #DDD;"></div>
                        </td>
                        <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($v['color_nombre']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($v['talla']) ?></td>
                        <td style="padding:10px;">
                            <strong style="color:<?= $v['stock_disponible'] > 0 ? 'green' : 'red' ?>;"><?= $v['stock_disponible'] ?> piezas</strong>
                        </td>
                        <td style="padding:10px;">
                            <a href="editar_producto.php?id=<?= $id ?>&borrar_var=<?= $v['id'] ?>" onclick="return confirm('¿Eliminar esta combinación de talla/color?')" style="color:#DC3545; font-size:0.8rem; font-weight:bold;">✕ Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>