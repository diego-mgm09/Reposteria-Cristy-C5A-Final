
document.addEventListener('DOMContentLoaded', () => {
 
    // Aquí defines tus pasteles: título, descripción y color propio.
    // El "color" se aplica al título cuando ese pastel está activo.
    // El orden debe coincidir con el data-index de cada .cake-slide en el HTML
     const cakes = [
        {
            title: 'Tiramisú de Temporada',
            desc: 'Esponjoso bizcocho con notas de café y suave crema. Un clásico indispensable',
            color: '#1a8fa8'
        },
        {
            title: 'Red Velvet Clásico',
            desc: 'Esponjoso con acentos de frutos rojos y un glaseado cremoso irresistible.',
            color: '#E271A1'
        },
        {
            title: 'Tartaleta de la Casa',
            desc: 'Base crujiente y crema pastelera coronada con la mejor selección de fruta fresca.',
            color: '#9D3E6C'
        }
    ];
 
    const track   = document.querySelector('.cake-track');
    if (!track) return; // Si esta sección no existe en la página, no hace nada.
 
    const slides  = document.querySelectorAll('.cake-slide');
    const dots    = document.querySelectorAll('.cake-dot');
    const titleEl = document.querySelector('.cake-title');
    const descEl  = document.querySelector('.cake-desc');
    const infoEl  = document.querySelector('.carousel-info');
    const btnPrev = document.querySelector('.cake-arrow--left');
    const btnNext = document.querySelector('.cake-arrow--right');
 
    const total = slides.length;
    let current = 0;
    let isAnimating = false;
 
    // Coloca cada slide en su posición (activo, anterior o siguiente)
    // según qué tan lejos está del pastel "current".
    function positionSlides() {
        slides.forEach((slide, i) => {
            slide.classList.remove('is-active', 'is-prev', 'is-next');
 
            if (i === current) {
                slide.classList.add('is-active');
            } else if (i === (current - 1 + total) % total) {
                slide.classList.add('is-prev');
            } else if (i === (current + 1) % total) {
                slide.classList.add('is-next');
            }
        });
 
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === current);
        });
    }
 
    // Cambia el título, la descripción y el color con un pequeño fade.
    function updateInfo() {
        infoEl.classList.add('is-fading');
        window.setTimeout(() => {
            titleEl.textContent = cakes[current].title;
            descEl.textContent  = cakes[current].desc;
            titleEl.style.color = cakes[current].color;
            infoEl.classList.remove('is-fading');
        }, 250);
    }
 
    function goTo(index) {
        if (isAnimating) return; // Evita clics rápidos que rompan la animación.
        isAnimating = true;
 
        current = (index + total) % total;
        positionSlides();
        updateInfo();
 
        window.setTimeout(() => { isAnimating = false; }, 700);
    }
 
    btnPrev.addEventListener('click', () => goTo(current - 1));
    btnNext.addEventListener('click', () => goTo(current + 1));
 
    dots.forEach((dot) => {
        dot.addEventListener('click', () => goTo(Number(dot.dataset.index)));
    });
 
 
    positionSlides();
    titleEl.style.color = cakes[current].color; // Color inicial, sin esperar al primer clic.
});


