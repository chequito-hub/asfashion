<?php
require_once __DIR__ . '/config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

$db = Conexion::getConexion();
$usuarioId = $_SESSION['usuario_id'];

// Validar Token CSRF
if (!validarCSRFToken($_POST['token_csrf'] ?? '')) {
    die("Error de validación de seguridad CSRF.");
}

// 1. Guardar Dirección de Envío
$calle = trim($_POST['calle'] ?? '');
$colonia = trim($_POST['colonia'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$cp = trim($_POST['codigo_postal'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$metodoPago = trim($_POST['metodo_pago'] ?? 'tarjeta');

$stmtDir = $db->prepare("INSERT INTO direcciones_envio (usuario_id, calle, numero, colonia, ciudad, estado, codigo_postal) VALUES (?, ?, 'S/N', ?, ?, 'México', ?)");
$stmtDir->execute([$usuarioId, $calle, $colonia, $ciudad, $cp]);
$direccionId = $db->lastInsertId();

// 2. Calcular Totales
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$descuento = $_SESSION['cupon_aplicado']['descuento'] ?? 0;
$costoEnvio = ($subtotal >= 999) ? 0 : 150;
$total = max(0, $subtotal - $descuento + $costoEnvio);

// 3. Generar Autorización Bancaria Simulada
$numeroOrden = 'ASF-' . date('Ym') . '-' . rand(1000, 9999);
$codigoAuth = 'AUTH-' . rand(100000, 999999) . '-ASF';
$ultimosDigitos = isset($_POST['card_number']) ? substr(str_replace(' ', '', $_POST['card_number']), -4) : '8890';

// 4. Crear Pedido en BD
$stmtPed = $db->prepare("
    INSERT INTO pedidos (numero_pedido_unico, usuario_id, direccion_envio_id, telefono_contacto, subtotal, descuento, costo_envio, total, metodo_pago, codigo_autorizacion, ultimos_digitos, estado_pago, estado_pedido)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aprobado', 'recibido')
");
$stmtPed->execute([$numeroOrden, $usuarioId, $direccionId, $telefono, $subtotal, $descuento, $costoEnvio, $total, $metodoPago, $codigoAuth, $ultimosDigitos]);
$pedidoId = $db->lastInsertId();

// 5. Guardar Detalles de Productos y Descontar Existencias
$stmtDet = $db->prepare("INSERT INTO detalles_pedido (pedido_id, variante_id, producto_nombre, talla, color, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtStock = $db->prepare("UPDATE variantes_producto SET stock_disponible = GREATEST(0, stock_disponible - ?) WHERE id = ?");

foreach ($_SESSION['carrito'] as $varId => $item) {
    $subItem = $item['precio'] * $item['cantidad'];
    $stmtDet->execute([$pedidoId, $varId, $item['nombre'], $item['talla'], $item['color'], $item['precio'], $item['cantidad'], $subItem]);
    $stmtStock->execute([$item['cantidad'], $varId]);
}

// Registrar en auditoría
registrarAuditoria($usuarioId, 'PAGO_COMPLETADO', 'pedidos', $pedidoId, "Pago simulado aprobado por $" . number_format($total, 2));

// Limpiar Carrito y Cupones
unset($_SESSION['carrito']);
unset($_SESSION['cupon_aplicado']);

header("Location: confirmacion.php?id={$pedidoId}");
exit;