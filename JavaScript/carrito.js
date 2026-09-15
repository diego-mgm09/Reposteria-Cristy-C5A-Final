(() => {
    'use strict';

    const STORAGE_KEY = 'cristy_carrito';
    const PRODUCTOS = {
        'croissant-eiffel': { nombre: 'Croissant-Eiffel', precio: 21.00 },
        'strawberry-cupcakes': { nombre: 'Strawberry Cupcakes', precio: 45.00 },
        'imperial-roll': { nombre: 'Imperial Roll', precio: 12.50 },
        'tartaleta-clasica': { nombre: 'Tartaleta Clásica', precio: 42.00 },
        'tiramisu-slice': { nombre: 'Tiramisú Slice', precio: 19.50 },
        'red-velvet-cake': { nombre: 'Red Velvet Cake', precio: 55.00 },
        'trenzas-rellenas': { nombre: 'Trenzas Rellenas', precio: 8.00 },
        'churros-glaseados': { nombre: 'Churros Glaseados', precio: 8.00 },
        'milhojas': { nombre: 'Milhojas', precio: 14.60 }
    };

    const formatoQuetzales = new Intl.NumberFormat('es-GT', {
        style: 'currency',
        currency: 'GTQ',
        minimumFractionDigits: 2
    });

    let carrito = leerCarrito();

    function leerCarrito() {
        try {
            const guardado = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            if (!Array.isArray(guardado)) return [];

            return guardado
                .filter((item) => PRODUCTOS[item.id] && Number.isInteger(item.cantidad) && item.cantidad > 0)
                .map((item) => ({ id: item.id, cantidad: Math.min(item.cantidad, 99) }));
        } catch {
            return [];
        }
    }

    function guardarCarrito() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(carrito));
        renderizarCarrito();
    }

    function agregarProducto(id) {
        if (!PRODUCTOS[id]) return;

        const item = carrito.find((producto) => producto.id === id);
        if (item) {
            item.cantidad = Math.min(item.cantidad + 1, 99);
        } else {
            carrito.push({ id, cantidad: 1 });
        }

        guardarCarrito();
        anunciar(`${PRODUCTOS[id].nombre} se agregó al carrito.`);
    }

    function cambiarCantidad(id, cambio) {
        const item = carrito.find((producto) => producto.id === id);
        if (!item) return;

        item.cantidad += cambio;
        if (item.cantidad <= 0) {
            carrito = carrito.filter((producto) => producto.id !== id);
        } else {
            item.cantidad = Math.min(item.cantidad, 99);
        }
        guardarCarrito();
    }

    function eliminarProducto(id) {
        carrito = carrito.filter((producto) => producto.id !== id);
        guardarCarrito();
    }

    function limpiarCarrito() {
        carrito = [];
        localStorage.removeItem(STORAGE_KEY);
        renderizarCarrito();
    }

    function anunciar(mensaje) {
        const aviso = document.querySelector('[data-cart-notice]');
        if (!aviso) return;

        aviso.textContent = mensaje;
        aviso.classList.add('is-visible');
        window.clearTimeout(anunciar.timeoutId);
        anunciar.timeoutId = window.setTimeout(() => aviso.classList.remove('is-visible'), 2200);
    }

    function crearFila(item) {
        const producto = PRODUCTOS[item.id];
        const subtotal = producto.precio * item.cantidad;

        return `
            <div class="cart-item">
                <div class="cart-item-info">
                    <strong>${producto.nombre}</strong>
                    <span>${formatoQuetzales.format(producto.precio)} c/u</span>
                </div>
                <div class="cart-item-actions">
                    <div class="cart-quantity" aria-label="Cantidad de ${producto.nombre}">
                        <button type="button" data-cart-action="decrease" data-product-id="${item.id}" aria-label="Quitar una unidad">−</button>
                        <span>${item.cantidad}</span>
                        <button type="button" data-cart-action="increase" data-product-id="${item.id}" aria-label="Agregar una unidad">+</button>
                    </div>
                    <span class="cart-item-subtotal">${formatoQuetzales.format(subtotal)}</span>
                    <button type="button" class="cart-remove" data-cart-action="remove" data-product-id="${item.id}" aria-label="Eliminar ${producto.nombre}">
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                    </button>
                </div>
            </div>`;
    }

    function renderizarCarrito() {
        const cantidad = carrito.reduce((suma, item) => suma + item.cantidad, 0);
        const total = carrito.reduce((suma, item) => suma + (PRODUCTOS[item.id].precio * item.cantidad), 0);
        const hayProductos = carrito.length > 0;

        document.querySelectorAll('[data-cart-count]').forEach((elemento) => {
            elemento.textContent = cantidad;
            elemento.classList.toggle('d-none', cantidad === 0);
        });

        document.querySelectorAll('[data-cart-items]').forEach((contenedor) => {
            contenedor.innerHTML = carrito.map(crearFila).join('');
        });

        document.querySelectorAll('[data-cart-total]').forEach((elemento) => {
            elemento.textContent = formatoQuetzales.format(total);
        });

        document.querySelectorAll('[data-cart-empty]').forEach((elemento) => {
            elemento.classList.toggle('d-none', hayProductos);
        });

        document.querySelectorAll('[data-cart-content]').forEach((elemento) => {
            elemento.classList.toggle('d-none', !hayProductos);
        });

        document.querySelectorAll('[data-cart-submit]').forEach((boton) => {
            boton.disabled = !hayProductos;
        });

        const campoCarrito = document.querySelector('#cart_json');
        if (campoCarrito) {
            campoCarrito.value = JSON.stringify(carrito);
        }
    }

    document.addEventListener('click', (event) => {
        const botonAgregar = event.target.closest('[data-add-to-cart]');
        if (botonAgregar) {
            agregarProducto(botonAgregar.dataset.productId);
            botonAgregar.classList.add('is-added');
            window.setTimeout(() => botonAgregar.classList.remove('is-added'), 450);
            return;
        }

        const control = event.target.closest('[data-cart-action]');
        if (!control) return;

        const id = control.dataset.productId;
        const accion = control.dataset.cartAction;
        if (accion === 'increase') cambiarCantidad(id, 1);
        if (accion === 'decrease') cambiarCantidad(id, -1);
        if (accion === 'remove') eliminarProducto(id);
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (document.body.dataset.clearCart === 'true') {
            limpiarCarrito();
        } else {
            renderizarCarrito();
        }
    });
})();
