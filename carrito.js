// Sistema de Notificaciones Toasts Elegantes
function mostrarToast(mensaje, icono = '✨') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span>${icono}</span> <div>${mensaje}</div>`;
    
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        toast.style.transition = 'all 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3200);
}

// Reloj Cuenta Regresiva
function iniciarCuentaRegresiva() {
    const horasEl = document.getElementById('cd-hours');
    const minsEl = document.getElementById('cd-mins');
    const secsEl = document.getElementById('cd-secs');

    if (!horasEl) return;

    let totalSegundos = (14 * 3600) + (32 * 60) + 45; // 14 horas ficticias de oferta

    setInterval(() => {
        if (totalSegundos <= 0) return;
        totalSegundos--;

        const h = Math.floor(totalSegundos / 3600);
        const m = Math.floor((totalSegundos % 3600) / 60);
        const s = totalSegundos % 60;

        horasEl.innerText = String(h).padStart(2, '0');
        minsEl.innerText = String(m).padStart(2, '0');
        secsEl.innerText = String(s).padStart(2, '0');
    }, 1000);
}

// Filtro Dinámico de Productos por Categoría sin Recargar
document.addEventListener('DOMContentLoaded', () => {
    iniciarCuentaRegresiva();

    const tabs = document.querySelectorAll('.tab-btn');
    const productos = document.querySelectorAll('.card-producto');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const categoria = tab.getAttribute('data-category');

            productos.forEach(prod => {
                const prodCat = prod.getAttribute('data-category');
                if (categoria === 'all' || prodCat === categoria) {
                    prod.style.display = 'block';
                    prod.classList.add('fade-in-element');
                } else {
                    prod.style.display = 'none';
                }
            });
        });
    });

    // Toggle de Botón de Favoritos
    document.querySelectorAll('.btn-heart-svg').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            if (this.classList.contains('active')) {
                mostrarToast('Guardado en tus Favoritos', '❤️');
            } else {
                mostrarToast('Removido de Favoritos', '🤍');
            }
        });
    });

    // Muestrarios de Color (Swatches)
    document.querySelectorAll('.swatch').forEach(swatch => {
        swatch.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            const colorNombre = this.getAttribute('title');
            mostrarToast(`Color seleccionado: ${colorNombre}`, '🎨');
        });
    });
});