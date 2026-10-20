// Buscador Inteligente en Tiempo Real (AJAX)
document.addEventListener('DOMContentLoaded', () => {
    const inputSearch = document.getElementById('input-search-live');
    const dropdown = document.getElementById('search-results-dropdown');

    if (inputSearch) {
        inputSearch.addEventListener('input', async function() {
            const query = this.value.trim();
            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`api/buscar.php?q=${encodeURIComponent(query)}`);
                const productos = await res.json();

                if (productos.length === 0) {
                    dropdown.innerHTML = '<div class="search-item">No hay resultados</div>';
                } else {
                    dropdown.innerHTML = productos.map(p => `
                        <a href="producto.php?id=${p.id}" class="search-item">
                            <img src="${p.ruta_imagen || 'assets/img/logo.png'}" style="width:36px; height:48px; object-fit:cover;">
                            <div>
                                <strong>${p.nombre}</strong>
                                <div style="color:var(--color-primario); font-size:0.8rem;">$${parseFloat(p.precio_regular).toFixed(2)} MXN</div>
                            </div>
                        </a>
                    `).join('');
                }
                dropdown.style.display = 'block';
            } catch (e) {
                console.error("Error en búsqueda:", e);
            }
        });
    }
});

// Abrir Modal Quick View
async function abrirQuickView(productoId) {
    const modal = document.getElementById('modal-quickview');
    const body = document.getElementById('quickview-body');
    
    body.innerHTML = '<p style="text-align:center;">Cargando detalles...</p>';
    modal.classList.add('active');

    try {
        const res = await fetch(`api/quick_view.php?id=${productoId}`);
        const data = await res.json();

        if (data.status === 'success') {
            const p = data.producto;
            body.innerHTML = `
                <div>
                    <img src="${p.ruta_imagen || 'assets/img/logo.png'}" style="width:100%; max-height:350px; object-fit:cover; border-radius:6px;">
                </div>
                <div>
                    <h2>${p.nombre}</h2>
                    <p class="precio" style="font-size:1.5rem; margin:1rem 0;">$${parseFloat(p.precio_regular).toFixed(2)} MXN</p>
                    <p style="color:var(--color-texto-mutado); font-size:0.9rem;">${p.descripcion || ''}</p>
                    <a href="producto.php?id=${p.id}" class="btn-as" style="margin-top:1.5rem; display:inline-block;">Ver Ficha Completa</a>
                </div>
            `;
        }
    } catch (e) {
        body.innerHTML = '<p>Error al cargar la vista previa.</p>';
    }
}

function cerrarQuickView() {
    document.getElementById('modal-quickview').classList.remove('active');
}