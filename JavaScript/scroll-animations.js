(() => {
    'use strict';

    const READY_CLASS = 'scroll-animations-ready';
    const VISIBLE_CLASS = 'is-revealed';

    document.documentElement.classList.add(READY_CLASS);

    function prepararGrupos() {
        document.querySelectorAll('[data-reveal-group]').forEach((grupo) => {
            Array.from(grupo.children).forEach((elemento, indice) => {
                if (!elemento.hasAttribute('data-reveal')) {
                    elemento.dataset.reveal = 'up';
                }

                // Escalonado corto: da ritmo a las cards sin volver lenta la sección.
                const retraso = Math.min(indice % 4, 3) * 75;
                elemento.style.setProperty('--reveal-delay', `${retraso}ms`);
            });
        });
    }

    function mostrarSinAnimacion(elementos) {
        elementos.forEach((elemento) => elemento.classList.add(VISIBLE_CLASS));
    }

    function iniciarAnimaciones() {
        prepararGrupos();

        const elementos = document.querySelectorAll('[data-reveal]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduceMotion || !('IntersectionObserver' in window)) {
            mostrarSinAnimacion(elementos);
            return;
        }

        const pendientes = new Set(elementos);

        function revelar(elemento) {
            elemento.classList.add(VISIBLE_CLASS);
            pendientes.delete(elemento);
        }

        const observador = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (!entrada.isIntersecting) return;

                revelar(entrada.target);
                observador.unobserve(entrada.target);
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -8% 0px'
        });

        elementos.forEach((elemento) => observador.observe(elemento));

        // Si alguien baja muy rápido usando la barra lateral, también mostramos
        // los bloques que ya quedaron arriba de la ventana y no alcanzaron a
        // cruzar lentamente el área observada.
        let revisionPendiente = false;
        function revisarRecorrido() {
            revisionPendiente = false;
            const limite = window.innerHeight * .92;

            pendientes.forEach((elemento) => {
                if (elemento.offsetParent === null) return;
                if (elemento.getBoundingClientRect().top < limite) {
                    revelar(elemento);
                    observador.unobserve(elemento);
                }
            });

            if (pendientes.size === 0) {
                window.removeEventListener('scroll', solicitarRevision);
            }
        }

        function solicitarRevision() {
            if (revisionPendiente) return;
            revisionPendiente = true;
            window.requestAnimationFrame(revisarRecorrido);
        }

        window.addEventListener('scroll', solicitarRevision, { passive: true });
        revisarRecorrido();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarAnimaciones, { once: true });
    } else {
        iniciarAnimaciones();
    }
})();
