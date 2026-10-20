CREATE DATABASE IF NOT EXISTS `asfashion_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `asfashion_db`;

-- 1. ROLES
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre_rol` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema y configuraciones'),
(2, 'Encargado de Inventario', 'Gestión exclusiva de existencias y variantes'),
(3, 'Encargado de Pedidos', 'Gestión de estados de compras y guías de envío'),
(4, 'Cliente', 'Usuario comprador final');

-- 2. USUARIOS
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `rol_id` INT NOT NULL DEFAULT 4,
  `nombre` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `telefono` VARCHAR(20) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `estado` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contraseña por defecto para admin/usuarios: Admin123! (hash generado con PASSWORD_BCRYPT)
INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `apellidos`, `correo`, `telefono`, `password_hash`, `estado`) VALUES
(1, 1, 'Admin', 'AsFashion', 'admin@asfashion.com', '5551234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 1),
(2, 2, 'Laura', 'Inventarios', 'inventario@asfashion.com', '5559876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 1),
(3, 3, 'Carlos', 'Envíos', 'pedidos@asfashion.com', '5555555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 1);

-- 3. DIRECCIONES DE ENVÍO
CREATE TABLE `direcciones_envio` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `alias` VARCHAR(50) DEFAULT 'Domicilio',
  `calle` VARCHAR(150) NOT NULL,
  `numero` VARCHAR(20) NOT NULL,
  `colonia` VARCHAR(100) NOT NULL,
  `ciudad` VARCHAR(100) NOT NULL,
  `estado` VARCHAR(100) NOT NULL,
  `codigo_postal` VARCHAR(10) NOT NULL,
  `referencias` TEXT NULL,
  `es_principal` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. CATEGORÍAS
CREATE TABLE `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `descripcion` TEXT NULL,
  `imagen` VARCHAR(255) NULL,
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorias` (`id`, `nombre`, `slug`, `descripcion`, `activo`) VALUES
(1, 'Vestidos', 'vestidos', 'Colección elegante de vestidos casuales y de noche', 1),
(2, 'Jeans y Pantalones', 'jeans-pantalones', 'Mezclilla de corte perfecto y pantalones de vestir', 1),
(3, 'Blusas y Tops', 'blusas-tops', 'Tops, blusas sofisticadas y camisetas urbanas', 1);

-- 5. MARCAS
CREATE TABLE `marcas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `logo` VARCHAR(255) NULL,
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `marcas` (`id`, `nombre`, `activo`) VALUES
(1, 'AsFashion Studio', 1),
(2, 'Moda Presumida', 1);

-- 6. PRODUCTOS
CREATE TABLE `productos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `categoria_id` INT NOT NULL,
  `marca_id` INT NOT NULL,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(180) NOT NULL UNIQUE,
  `descripcion` TEXT NULL,
  `precio_regular` DECIMAL(10,2) NOT NULL,
  `precio_oferta` DECIMAL(10,2) NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`marca_id`) REFERENCES `marcas`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. FOTOS PRODUCTO
CREATE TABLE `fotos_producto` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `producto_id` INT NOT NULL,
  `ruta_imagen` VARCHAR(255) NOT NULL,
  `es_principal` TINYINT(1) DEFAULT 0,
  `orden` INT DEFAULT 0,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. VARIANTES PRODUCTO (INVENTARIO POR TALLA/COLOR)
CREATE TABLE `variantes_producto` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `producto_id` INT NOT NULL,
  `talla` VARCHAR(20) NOT NULL,
  `color_nombre` VARCHAR(50) NOT NULL,
  `color_hex` VARCHAR(10) DEFAULT '#000000',
  `stock_disponible` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. CUPONES
CREATE TABLE `cupones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(30) NOT NULL UNIQUE,
  `tipo_descuento` ENUM('porcentaje', 'monto_fijo') NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `monto_minimo` DECIMAL(10,2) DEFAULT 0.00,
  `usos_permitidos` INT DEFAULT 100,
  `usos_actuales` INT DEFAULT 0,
  `fecha_inicio` DATETIME NOT NULL,
  `fecha_fin` DATETIME NOT NULL,
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cupones` (`codigo`, `tipo_descuento`, `valor`, `monto_minimo`, `usos_permitidos`, `fecha_inicio`, `fecha_fin`, `activo`) 
VALUES ('BIENVENIDA10', 'porcentaje', 10.00, 500.00, 50, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1);

-- 10. FAVORITOS
CREATE TABLE `favoritos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `producto_id` INT NOT NULL,
  `fecha_agregado` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_fav` (`usuario_id`, `producto_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. PEDIDOS
CREATE TABLE `pedidos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero_pedido_unico` VARCHAR(30) NOT NULL UNIQUE,
  `usuario_id` INT NOT NULL,
  `direccion_envio_id` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `descuento` DECIMAL(10,2) DEFAULT 0.00,
  `costo_envio` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `metodo_pago` VARCHAR(50) NOT NULL,
  `estado_pago` ENUM('pendiente', 'completado', 'fallido') DEFAULT 'pendiente',
  `metodo_envio` VARCHAR(50) NOT NULL DEFAULT 'Estandar',
  `paqueteria` VARCHAR(50) NULL,
  `numero_guia` VARCHAR(100) NULL,
  `url_seguimiento` VARCHAR(255) NULL,
  `estado_pedido` ENUM('recibido', 'pago_confirmado', 'preparando', 'enviado', 'entregado', 'cancelado') DEFAULT 'recibido',
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`direccion_envio_id`) REFERENCES `direcciones_envio`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. DETALLES PEDIDO
CREATE TABLE `detalles_pedido` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pedido_id` INT NOT NULL,
  `variante_id` INT NOT NULL,
  `producto_nombre` VARCHAR(150) NOT NULL,
  `talla` VARCHAR(20) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `cantidad` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variante_id`) REFERENCES `variantes_producto`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. BITÁCORA DE AUDITORÍA
CREATE TABLE `bitacora_auditoria` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `accion` VARCHAR(100) NOT NULL,
  `tabla_afectada` VARCHAR(50) NOT NULL,
  `registro_id` INT NOT NULL,
  `detalles` TEXT NULL,
  `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
USE `asfashion_db`;

-- Actualizar o insertar la jerarquía de roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Super Admin', 'Control total del sistema: gestión de usuarios, roles, catálogo, ventas y configuraciones críticas.'),
(2, 'Administrador', 'Gestión de catálogo de productos, inventario, cupones y visualización de ventas.'),
(3, 'Encargado de Pedidos', 'Acceso exclusivo a la gestión de pedidos, paquerías y emisión de guías de envío.'),
(4, 'Cliente', 'Usuario comprador de la tienda en línea.')
ON DUPLICATE KEY UPDATE 
  `nombre` = VALUES(`nombre`), 
  `descripcion` = VALUES(`descripcion`);