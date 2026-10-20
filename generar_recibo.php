<?php
require_once __DIR__ . '/config/conexion.php';

$pedidoId = intval($_GET['id'] ?? 0);
$db = Conexion::getConexion();

$stmt = $db->prepare("
    SELECT p.*, u.nombre, u.apellidos, u.correo, d.calle, d.colonia, d.ciudad, d.codigo_postal
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    JOIN direcciones_envio d ON p.direccion_envio_id = d.id
    WHERE p.id = ?
");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die("Pedido no encontrado.");
}

$stmtDet = $db->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
$stmtDet->execute([$pedidoId]);
$detalles = $stmtDet->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Compra - <?= htmlspecialchars($pedido['numero_pedido_unico']) ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .receipt-header { text-align: center; border-bottom: 2px solid #B57A55; padding-bottom: 15px; }
        .receipt-header h1 { color: #B57A55; margin: 0; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table th, .details-table td { border: 1px solid #DDD; padding: 10px; text-align: left; }
        .details-table th { background: #FAF8F5; }
        .btn-print { background: #B57A55; color: #FFF; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>

<div style="max-width: 700px; margin: 0 auto; border: 1px solid #DDD; padding: 30px; background: #FFF;">
    <div class="receipt-header">
        <h1>AsFashion Boutique</h1>
        <p>Comprobante Oficial de Orden de Compra</p>
    </div>

    <div style="margin-top: 20px; display: flex; justify-content: space-between;">
        <div>
            <p><strong>Número de Pedido:</strong> <?= htmlspecialchars($pedido['numero_pedido_unico']) ?></p>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidos']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($pedido['correo']) ?></p>
        </div>
        <div>
            <p><strong>Fecha:</strong> <?= $pedido['fecha_creacion'] ?></p>
            <p><strong>Estado:</strong> <?= strtoupper($pedido['estado_pedido']) ?></p>
        </div>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Talla / Color</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['producto_nombre']) ?></td>
                    <td><?= htmlspecialchars($d['talla']) ?> - <?= htmlspecialchars($d['color']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td>$<?= number_format($d['precio_unitario'], 2) ?></td>
                    <td>$<?= number_format($d['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="text-align: right; margin-top: 20px;">
        <h3>Total Pagado: $<?= number_format($pedido['total'], 2) ?> MXN</h3>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>
</div>

</body>
</html>