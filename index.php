<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1, 2, 3]); // Admin, Inventario, Pedidos

$db = Conexion::getConexion();

// Métricas principales
$ventasTotales = $db->query("SELECT SUM(total) as total FROM pedidos WHERE estado_pago = 'completado'")->fetch()['total'] ?? 0.00;
$pedidosTotales = $db->query("SELECT COUNT(*) as total FROM pedidos")->fetch()['total'];
$alertasStock = $db->query("SELECT COUNT(*) as total FROM variantes_producto WHERE stock_disponible < 5")->fetch()['total'];

// Productos bajo inventario
$stmtBajoStock = $db->query("
    SELECT p.nombre, v.talla, v.color_nombre, v.stock_disponible 
    FROM variantes_producto v 
    JOIN productos p ON v.producto_id = p.id 
    WHERE v.stock_disponible < 5 
    ORDER BY v.stock_disponible ASC
");
$itemsBajoStock = $stmtBajoStock->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h2>Dashboard de Gestión AsFashion</h2>
    <p style="color: var(--color-texto-mutado);">Rol activo: <strong><?= $_SESSION['rol_nombre'] ?? 'Personal' ?></strong></p>

    <!-- KPi Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <div style="background:#fff; padding:1.5rem; border-radius:8px; border-left: 5px solid var(--color-primario);">
            <h3>Ventas Completadas</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--color-primario);">$<?= number_format($ventasTotales, 2) ?></p>
        </div>
        <div style="background:#fff; padding:1.5rem; border-radius:8px; border-left: 5px solid #17a2b8;">
            <h3>Total Pedidos</h3>
            <p style="font-size: 1.8rem; font-weight: bold;"><?= $pedidosTotales ?></p>
        </div>
        <div style="background:#fff; padding:1.5rem; border-radius:8px; border-left: 5px solid var(--color-peligro);">
            <h3>Alertas de Inventario</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--color-peligro);"><?= $alertasStock ?></p>
        </div>
    </div>

    <!-- Tabla Alerta Stock -->
    <h3 style="margin-top:2rem;">⚠️ Alerta de Productos con Stock Critico (< 5 Unidades)</h3>
    <table style="width: 100%; background:#fff; border-collapse: collapse; margin-top: 1rem;">
        <thead>
            <tr style="background: var(--color-fondo-suave); text-align: left; border-bottom: 2px solid var(--color-borde);">
                <th style="padding: 10px;">Producto</th>
                <th style="padding: 10px;">Talla</th>
                <th style="padding: 10px;">Color</th>
                <th style="padding: 10px;">Stock Restante</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itemsBajoStock as $stock): ?>
            <tr style="border-bottom: 1px solid var(--color-borde);">
                <td style="padding: 10px;"><?= htmlspecialchars($stock['nombre']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($stock['talla']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($stock['color_nombre']) ?></td>
                <td style="padding: 10px; font-weight: bold; color: var(--color-peligro);"><?= $stock['stock_disponible'] ?> pcs</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>