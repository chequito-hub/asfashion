</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Columna 1: Marca -->
            <div class="footer-col brand-col">
                <div class="footer-logo" style="margin-bottom: 0.8rem;">
                    <span class="brand-title" style="font-size: 1.8rem;">AsFashion</span>
                </div>
                <p class="footer-desc">
                    Alta costura y moda femenina contemporánea. Diseños atemporales confeccionados con textiles de la más alta calidad para destacar la elegancia femenina.
                </p>
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" title="Instagram" aria-label="Instagram">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="https://tiktok.com" target="_blank" title="TikTok" aria-label="TikTok">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.98-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.56-1.31 1.54-1.29 2.53.01.8.42 1.58 1.07 2.05.74.55 1.72.71 2.58.46.99-.27 1.83-1.06 2.13-2.03.11-.42.15-.86.15-1.3V.02z"/></svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" title="Facebook" aria-label="Facebook">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Columna 2: Navegación -->
            <div class="footer-col">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php">Inicio</a></li>
                    <li><a href="<?= BASE_URL ?>catalogo.php">Catálogo Completo</a></li>
                    <li><a href="<?= BASE_URL ?>catalogo.php?cat=vestidos">Vestidos de Noche</a></li>
                    <li><a href="<?= BASE_URL ?>catalogo.php?cat=jeans-pantalones">Jeans & Denim</a></li>
                    <li><a href="<?= BASE_URL ?>favoritos.php">Mi Lista de Deseos</a></li>
                </ul>
            </div>

            <!-- Columna 3: Políticas -->
            <div class="footer-col">
                <h4>Políticas y Servicio</h4>
                <ul>
                    <li><a href="javascript:void(0)" onclick="mostrarToast('Envíos asegurados. Entrega estimada de 2 a 4 días hábiles.', '📦')">Política de Envíos</a></li>
                    <li><a href="javascript:void(0)" onclick="mostrarToast('Devoluciones garantizadas durante los primeros 30 días.', '🔄')">Devoluciones y Cambios</a></li>
                    <li><a href="javascript:void(0)" onclick="mostrarToast('Tus datos están protegidos bajo cifrado de 256 bits.', '🔒')">Aviso de Privacidad</a></li>
                    <li><a href="javascript:void(0)" onclick="mostrarToast('Consulta las condiciones del servicio de venta en línea.', '📜')">Términos y Condiciones</a></li>
                    <li><a href="javascript:void(0)" onclick="mostrarToast('Soporte vía WhatsApp y correo las 24 horas.', '💬')">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <!-- Columna 4: Pagos -->
            <div class="footer-col">
                <h4>Pasarela de Pago Segura</h4>
                <p style="font-size: 0.82rem; color: var(--color-texto-mutado); margin-bottom: 1rem;">
                    Procesamiento de pagos cifrado de punto a punto.
                </p>
                <div class="payment-methods">
                    <span class="pay-chip">VISA</span>
                    <span class="pay-chip">MC</span>
                    <span class="pay-chip">AMEX</span>
                    <span class="pay-chip">PAYPAL</span>
                    <span class="pay-chip">SPEI</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <strong>AsFashion Boutique</strong>. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>assets/js/app.js"></script>
<script src="<?= BASE_URL ?>assets/js/carrito.js"></script>
</body>
</html>