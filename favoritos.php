<?php
require_once __DIR__ . '/config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: mi-cuenta.php');
    exit;
}

include __DIR__ . '/includes/header.php';

$db = Conexion::getConexion();
$stmt = $db->prepare("
    SELECT p.*, f.ruta_imagen
    FROM favoritos fav
    JOIN productos p ON fav.producto_id = p.id
    LEFT JOIN fotos_producto f ON p.id = f.producto_id AND f.es_principal = 1
    WHERE fav.usuario_id = ?
");
$stmt->execute([$_SESSION['usuario_id']]);
$favoritos = $stmt->fetchAll();
?>

<div style="padding: 3rem 0;">
    <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 0.5rem;">Mis Favoritos</h1>
    <p style="text-align: center; color: var(--color-texto-mutado); margin-bottom: 3rem;">Tus prendas seleccionadas para compras posteriores</p>

    <div class="grid-productos">
        <?php if (empty($favoritos)): ?>
            <p style="grid-column: 1/-1; text-align: center; color: var(--color-texto-mutado);">Aún no has guardado productos en tus favoritos.</p>
        <?php else: ?>
            <?php foreach ($favoritos as $prod): ?>
                <div class="card-producto">
                    <div class="img-wrapper">
                        <img src="<?= (!empty($prod['ruta_imagen']) && $prod['ruta_imagen'] !== 'assets/img/logo.png') ? htmlspecialchars($prod['ruta_imagen']) : 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=600&q=80' ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                    </div>
                    <div class="card-info">
                        <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                        <p class="precio">$<?= number_format($prod['precio_regular'], 2) ?> MXN</p>
                        <a href="producto.php?id=<?= $prod['id'] ?>" class="btn-as" style="width: 100%; margin-top: 0.8rem;">Ver Producto</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>