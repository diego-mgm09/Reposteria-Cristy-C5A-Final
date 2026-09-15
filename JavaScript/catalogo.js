document.addEventListener('DOMContentLoaded', () => {
 
    const botones   = document.querySelectorAll('.filter-btn');
    const productos = document.querySelectorAll('.producto');
    const mensajeVacio = document.querySelector('.filter-empty');
 
    if (!botones.length || !mensajeVacio) return; // Si falta la sección o su mensaje, el script no hace nada.
 
    botones.forEach((boton) => {
        boton.addEventListener('click', () => {
 
            // Quita el estado activo de todos los botones y se lo pone solo al que clickearon
            botones.forEach((b) => b.classList.remove('is-active'));
            boton.classList.add('is-active');
 
            const filtro = boton.dataset.filtro;
            let visibles = 0;
 
            productos.forEach((producto) => {
                const coincide = filtro === 'todos' || producto.dataset.categoria === filtro;
                producto.classList.toggle('d-none', !coincide);
                if (coincide) visibles++;
            });
 
            // Si el filtro no tiene ningún producto todavía, muestra el mensaje.
            mensajeVacio.classList.toggle('d-none', visibles > 0);
        });
    });
 
});
 
 
