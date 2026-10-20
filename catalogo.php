<?php
require_once __DIR__ . '/config/conexion.php';
include __DIR__ . '/includes/header.php';

$db = Conexion::getConexion();

// Parámetros de Filtrado y Paginación
$catSlug = $_GET['cat'] ?? '';
$precioMax = floatval($_GET['precio_max'] ?? 5000);
$orden = $_GET['orden'] ?? 'recientes';
$pagina = max(1, intval($_GET['p'] ?? 1));
$limit = 9; // 9 productos por página
$offset = ($pagina - 1) * $limit;

// Construir Consulta SQL Dinámica con Sentencias Preparadas
$where = ["p.activo = 1 AND p.precio_regular <= ?"];
$params = [$precioMax];

if (!empty($catSlug)) {
    $where[] = "c.slug = ?";
    $params[] = $catSlug;
}

$sqlWhere = implode(" AND ", $where);

// Ordenamiento
$orderBy = "ORDER BY p.id DESC";
if ($orden === 'precio_asc') $orderBy = "ORDER BY COALESCE(p.precio_oferta, p.precio_regular) ASC";
if ($orden === 'precio_desc') $orderBy = "ORDER BY COALESCE(p.precio_oferta, p.precio_regular) DESC";

// Total de registros para paginación
$stmtCount = $db->prepare("SELECT COUNT(*) FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE {$sqlWhere}");
$stmtCount->execute($params);
$totalProductos = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalProductos / $limit);

// Consulta Final con LIMIT y OFFSET
$stmt = $db->prepare("
    SELECT p.*, f.ruta_imagen, c.nombre as categoria_nombre
    FROM productos p
    LEFT JOIN fotos_producto f ON p.id = f.producto_id AND f.es_principal = 1
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE {$sqlWhere}
    {$orderBy}
    LIMIT {$limit} OFFSET {$offset}
");
$stmt->execute($params);
$productos = $stmt->fetchAll();
?>

<div style="padding: 2.5rem 0;">
    <h1 style="text-align: center; font-size: 2.8rem; margin-bottom: 0.5rem;">Colección AsFashion</h1>
    <p style="text-align: center; color: var(--color-texto-mutado); margin-bottom: 3rem;">Descubre la prenda ideal con nuestros filtros avanzados</p>

    <div class="catalogo-layout">
        <!-- Sidebar de Filtros -->
        <aside class="sidebar-filtros">
            <h3>Filtrar Colección</h3>
            <form method="GET" action="catalogo.php">
                <?php if (!empty($catSlug)): ?>
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>">
                <?php endif; ?>

                <div class="filtro-grupo">
                    <label>Precio Máximo: <strong>$<span id="val-precio"><?= $precioMax ?></span> MXN</strong></label>
                    <input type="range" name="precio_max" min="300" max="5000" step="100" value="<?= $precioMax ?>" oninput="document.getElementById('val-precio').innerText = this.value">
                </div>

                <div class="filtro-grupo">
                    <label>Ordenar Por:</label>
                    <select name="orden" class="newsletter-input" style="width:100%; border:1px solid var(--color-borde);">
                        <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Lo más reciente</option>
                        <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                        <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                    </select>
                </div>

                <button type="submit" class="btn-as" style="width: 100%;">Aplicar Filtros</button>
            </form>
        </aside>

        <!-- Grid de Productos -->
        <main class="catalogo-content">
            <div class="grid-productos">
                <?php if (empty($productos)): ?>
                    <p style="grid-column: 1/-1; text-align:center; color: var(--color-texto-mutado);">No se encontraron prendas con los filtros seleccionados.</p>
                <?php else: ?>
                    <?php foreach ($productos as $prod): ?>
                        <div class="card-producto">
                            <span class="badge-tag">Exclusivo</span>
                            <button class="btn-heart-svg">🤍</button>
                            <div class="img-wrapper">
                                <img src="<?= (!empty($prod['ruta_imagen']) && $prod['ruta_imagen'] !== 'assets/img/logo.png') ? htmlspecialchars($prod['ruta_imagen']) : 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=600&q=80' ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" loading="lazy">
                                <div class="card-quick-actions">
                                    <button class="btn-as" onclick="abrirQuickView(<?= $prod['id'] ?>)" style="width:100%; font-size:0.75rem;">Vista Rápida</button>
                                </div>
                            </div>
                            <div class="card-info">
                                <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                                <p class="precio">$<?= number_format($prod['precio_regular'], 2) ?> MXN</p>
                                <a href="producto.php?id=<?= $prod['id'] ?>" style="font-size: 0.8rem; text-decoration:underline; color: var(--color-primario);">Ver Detalle Completo</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Paginación SQL -->
            <?php if ($totalPaginas > 1): ?>
                <div class="paginacion">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="catalogo.php?p=<?= $i ?>&precio_max=<?= $precioMax ?>&orden=<?= $orden ?>" class="page-link <?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>