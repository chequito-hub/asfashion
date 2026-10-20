<?php
require_once __DIR__ . '/../config/conexion.php';

// Generar token CSRF para seguridad
$tokenCSRF = generarCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AsFashion | Haute Couture & Luxury Store</title>
    <!-- Carga con versión de tiempo para forzar actualización de caché CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/estilos.css?v=<?= time() ?>">
</head>
<body>

<!-- Marquesina Superior Satinada -->
<div class="ticker-wrap">
    <div class="ticker-move">
        <div class="ticker-item">✨ COLECCIÓN OTOÑO / INVIERNO 2026</div>
        <div class="ticker-item">✦ ENVÍO GRATIS A TODO MÉXICO EN COMPRAS > $999 MXN</div>
        <div class="ticker-item">★ 3 Y 6 MESES SIN INTERESES EN TARJETAS PARTICIPANTES</div>
        <div class="ticker-item">✨ COLECCIÓN OTOÑO / INVIERNO 2026</div>
    </div>
</div>

<!-- Encabezado Principal -->
<header class="header-nav">
    <!-- Logotipo y Nombre de Marca -->
    <div class="logo-container">
        <a href="<?= BASE_URL ?>index.php" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="AsFashion Monogram" style="height:42px; max-height:42px; width:auto; object-fit:contain; display:block;">
            <span class="brand-title">AsFashion</span>
        </a>
    </div>

    <!-- Buscador Inteligente AJAX -->
    <div class="search-box-container">
        <input type="text" id="input-search-live" class="search-input" placeholder="Buscar vestidos, jeans, blusas..." autocomplete="off">
        <div id="search-results-dropdown" class="search-results-dropdown"></div>
    </div>

    <!-- Menú de Navegación -->
    <nav>
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>index.php">Inicio</a></li>
            <li><a href="<?= BASE_URL ?>catalogo.php">Colección</a></li>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li><a href="<?= BASE_URL ?>favoritos.php">Favoritos</a></li>
                <li><a href="<?= BASE_URL ?>mi-cuenta.php">Mi Cuenta</a></li>
                
                <!-- Enlace exclusivo para Administradores -->
                <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1): ?>
                    <li>
                        <a href="<?= BASE_URL ?>admin/pedidos.php" style="color: var(--color-primario); font-weight: 800;">
                            ⚙️ Admin
                        </a>
                    </li>
                <?php endif; ?>
                
                <li><a href="<?= BASE_URL ?>logout.php">Salir</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>mi-cuenta.php">Acceder</a></li>
            <?php endif; ?>

            <!-- Botón del Carrito / Bolsa de Compras -->
            <li>
                <a href="<?= BASE_URL ?>carrito.php" class="btn-as" style="padding: 0.6rem 1.3rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    Bolsa (<span id="cart-count"><?= isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0 ?></span>)
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- Modal Global de Vista Previa Rápida (Quick View) -->
<div id="modal-quickview" class="modal-backdrop">
    <div class="modal-content">
        <button class="modal-close" onclick="cerrarQuickView()">&times;</button>
        <div id="quickview-body" class="quickview-grid">
            <!-- Se carga dinámicamente mediante AJAX -->
        </div>
    </div>
</div>

<!-- Modal Guía de Tallas -->
<div id="modal-size-guide" class="modal-backdrop">
    <div class="modal-content" style="max-width: 600px;">
        <button class="modal-close" onclick="document.getElementById('modal-size-guide').classList.remove('active')">&times;</button>
        <h2 style="margin-bottom: 1rem; font-size: 1.6rem; text-align:center;">Guía Oficial de Tallas (cm)</h2>
        <table class="size-table">
            <thead>
                <tr>
                    <th>Talla</th>
                    <th>Busto (cm)</th>
                    <th>Cintura (cm)</th>
                    <th>Cadera (cm)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><strong>XS (28)</strong></td><td>80 - 84</td><td>60 - 64</td><td>88 - 92</td></tr>
                <tr><td><strong>S (30)</strong></td><td>85 - 89</td><td>65 - 69</td><td>93 - 97</td></tr>
                <tr><td><strong>M (32)</strong></td><td>90 - 94</td><td>70 - 74</td><td>98 - 102</td></tr>
                <tr><td><strong>L (34)</strong></td><td>95 - 99</td><td>75 - 79</td><td>103 - 107</td></tr>
                <tr><td><strong>XL (36)</strong></td><td>100 - 105</td><td>80 - 85</td><td>108 - 113</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Contenedor Principal de la Página -->
<main class="container"></main>