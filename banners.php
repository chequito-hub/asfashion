<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1, 2]); // Super Admin y Administrador

$db = Conexion::getConexion();
$msg = '';
$error = '';

// Procesar Subida de Imagen para Banners de Index
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_banner') {
    $seccion = trim($_POST['seccion']);
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    
    // Obtener imagen actual
    $stmtActual = $db->prepare("SELECT ruta_imagen FROM banners_home WHERE seccion = ?");
    $stmtActual->execute([$seccion]);
    $rutaFinal = $stmtActual->fetchColumn() ?: 'assets/img/logo.png';

    // Subida desde PC
    if (isset($_FILES['imagen_banner']) && $_FILES['imagen_banner']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['imagen_banner']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['imagen_banner']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'])) {
            $dirSubida = __DIR__ . '/../assets/img/banners/';
            if (!file_exists($dirSubida)) mkdir($dirSubida, 0777, true);

            $nuevoNombre = 'banner_' . $seccion . '_' . time() . '.' . $ext;
            if (move_uploaded_file($fileTmp, $dirSubida . $nuevoNombre)) {
                $rutaFinal = 'assets/img/banners/' . $nuevoNombre;
            } else {
                $error = 'Error al mover el archivo subido al servidor.';
            }
        } else {
            $error = 'Formato no permitido. Usa JPG, PNG, WEBP, GIF o AVIF.';
        }
    }

    if (empty($error)) {
        $stmtUpd = $db->prepare("
            INSERT INTO banners_home (seccion, titulo, subtitulo, ruta_imagen) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE titulo = VALUES(titulo), subtitulo = VALUES(subtitulo), ruta_imagen = VALUES(ruta_imagen)
        ");
        $stmtUpd->execute([$seccion, $titulo, $subtitulo, $rutaFinal]);
        $msg = "✨ Banner de la sección '{$seccion}' actualizado con éxito.";
    }
}

// Cargar Banners Existentes
$stmtBanners = $db->query("SELECT * FROM banners_home");
$bannersRaw = $stmtBanners->fetchAll();
$banners = [];
foreach ($bannersRaw as $b) {
    $banners[$b['seccion']] = $b;
}

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2.5rem 0;">
    <!-- Menú de Navegación Admin -->
    <div style="display:flex; gap: 1rem; border-bottom: 2px solid var(--color-borde); padding-bottom: 1rem; margin-bottom: 2.5rem; flex-wrap:wrap; align-items:center;">
        <a href="pedidos.php" class="btn-as btn-outline" style="font-size:0.8rem;">📦 Pedidos y Envíos</a>
        <a href="productos.php" class="btn-as btn-outline" style="font-size:0.8rem;">👗 Catálogo e Inventario</a>
        <a href="banners.php" class="btn-as" style="font-size:0.8rem;">🖼️ Banners del Index</a>
        <?php if (esSuperAdmin()): ?>
            <a href="usuarios.php" class="btn-as btn-outline" style="font-size:0.8rem; border-color:var(--color-primario); color:var(--color-primario)!important;">👑 Usuarios</a>
        <?php endif; ?>
    </div>

    <h2>Personalización Visual de la Página de Inicio (Index)</h2>
    <p style="color: var(--color-texto-mutado); margin-bottom: 2rem;">Sube fotos desde tu computadora para cambiar el Banner Principal, los Círculos de Categorías y el Lookbook de redes sociales.</p>

    <?php if ($msg): ?>
        <div style="background:#E8F5E9; color:#2E7D32; padding:1rem; border-radius:6px; margin-bottom:1.5rem; font-weight:bold;"><?= $msg ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background:#FFEBEE; color:#C62828; padding:1rem; border-radius:6px; margin-bottom:1.5rem; font-weight:bold;"><?= $error ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- 1. Banner Principal Hero -->
        <div style="background:#FFF; padding:2rem; border-radius:10px; border:1px solid var(--color-borde); box-shadow: var(--sombra-seda); grid-column: 1 / -1;">
            <h3 style="margin-bottom:1rem; border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem;">1. Banner Principal (Hero Principal)</h3>
            <form method="POST" action="banners.php" enctype="multipart/form-data" style="display:grid; grid-template-columns: 280px 1fr; gap:2rem; align-items:center;">
                <input type="hidden" name="action" value="actualizar_banner">
                <input type="hidden" name="seccion" value="hero">

                <div>
                    <img src="<?= BASE_URL . ($banners['hero']['ruta_imagen'] ?? 'assets/img/logo.png') ?>" style="width:100%; max-height:180px; object-fit:cover; border-radius:6px; border:1px solid var(--color-borde); margin-bottom:8px;">
                    <input type="file" name="imagen_banner" accept="image/*" class="newsletter-input" style="width:100%; font-size:0.75rem;">
                </div>

                <div style="display:grid; gap:10px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:700;">Título del Banner:</label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($banners['hero']['titulo'] ?? 'Elegancia O/I 2026') ?>" class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:700;">Subtítulo / Texto Corto:</label>
                        <input type="text" name="subtitulo" value="<?= htmlspecialchars($banners['hero']['subtitulo'] ?? 'Textiles seleccionados a mano...') ?>" class="newsletter-input" style="width:100%; border:1px solid #CCC; color:#222;">
                    </div>
                    <button type="submit" class="btn-as" style="margin-top:5px;">💾 Guardar Banner Hero</button>
                </div>
            </form>
        </div>

        <!-- 2. Círculos de Categorías (Stories) -->
        <div style="background:#FFF; padding:2rem; border-radius:10px; border:1px solid var(--color-borde); box-shadow: var(--sombra-seda);">
            <h3 style="margin-bottom:1.5rem; border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem;">2. Círculos de Categorías (Stories)</h3>
            
            <?php
            $stories = [
                'story_todos' => 'Círculo: TODOS',
                'story_vestidos' => 'Círculo: VESTIDOS',
                'story_jeans' => 'Círculo: PANTALONES',
                'story_blusas' => 'Círculo: BLUSAS'
            ];
            foreach ($stories as $secKey => $secNombre):
                $imgActual = $banners[$secKey]['ruta_imagen'] ?? 'assets/img/logo.png';
            ?>
                <form method="POST" action="banners.php" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center; margin-bottom:1.2rem; background:var(--color-fondo-warm); padding:10px; border-radius:6px;">
                    <input type="hidden" name="action" value="actualizar_banner">
                    <input type="hidden" name="seccion" value="<?= $secKey ?>">

                    <img src="<?= BASE_URL . $imgActual ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid var(--color-primario);">
                    
                    <div style="flex:1;">
                        <strong style="font-size:0.85rem; display:block;"><?= $secNombre ?></strong>
                        <input type="file" name="imagen_banner" accept="image/*" style="font-size:0.7rem;">
                    </div>

                    <button type="submit" class="btn-as" style="padding:4px 10px; font-size:0.75rem;">Actualizar</button>
                </form>
            <?php endforeach; ?>
        </div>

        <!-- 3. Fotos del Lookbook (#AsFashion.Mx) -->
        <div style="background:#FFF; padding:2rem; border-radius:10px; border:1px solid var(--color-borde); box-shadow: var(--sombra-seda);">
            <h3 style="margin-bottom:1.5rem; border-bottom:1px solid var(--color-borde); padding-bottom:0.5rem;">3. Fotos de Lookbook (#AsFashion.Mx)</h3>
            
            <?php
            for ($i = 1; $i <= 4; $i++):
                $secKey = 'lookbook_' . $i;
                $imgActual = $banners[$secKey]['ruta_imagen'] ?? 'assets/img/logo.png';
                $tagActual = $banners[$secKey]['titulo'] ?? '@asfashion.mx';
            ?>
                <form method="POST" action="banners.php" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center; margin-bottom:1rem; background:var(--color-fondo-warm); padding:8px; border-radius:6px;">
                    <input type="hidden" name="action" value="actualizar_banner">
                    <input type="hidden" name="seccion" value="<?= $secKey ?>">

                    <img src="<?= BASE_URL . $imgActual ?>" style="width:45px; height:55px; object-fit:cover; border-radius:4px;">
                    
                    <div style="flex:1;">
                        <input type="text" name="titulo" value="<?= htmlspecialchars($tagActual) ?>" placeholder="@usuario_ig" class="newsletter-input" style="padding:2px 6px; font-size:0.75rem; width:100%; border:1px solid #CCC;">
                        <input type="file" name="imagen_banner" accept="image/*" style="font-size:0.7rem; margin-top:3px;">
                    </div>

                    <button type="submit" class="btn-as" style="padding:4px 10px; font-size:0.75rem;">Guardar</button>
                </form>
            <?php endfor; ?>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>