(() => {
    'use strict';

    const carrusel = document.querySelector('.box');
    const anterior = document.querySelector('.prev');
    const siguiente = document.querySelector('.next');

    // Depuración: se consulta el carrusel una sola vez y el guard permite reutilizar el script sin errores.
    if (!carrusel || !anterior || !siguiente) return;

    siguiente.addEventListener('click', () => carrusel.append(carrusel.firstElementChild));
    anterior.addEventListener('click', () => carrusel.prepend(carrusel.lastElementChild));
})();
