<?php
require_once __DIR__ . '/../config/conexion.php';
verificarAccesoRol([1]); // Exclusivo Administrador

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Reporte_Ventas_AsFashion_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID Orden', 'Numero Pedido', 'Cliente', 'Subtotal', 'Descuento', 'Total', 'Metodo Pago', 'Estado', 'Fecha']);

$db = Conexion::getConexion();
$stmt = $db->query("
    SELECT p.id, p.numero_pedido_unico, CONCAT(u.nombre, ' ', u.apellidos) as cliente, p.subtotal, p.descuento, p.total, p.metodo_pago, p.estado_pedido, p.fecha_creacion
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.id DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
exit;