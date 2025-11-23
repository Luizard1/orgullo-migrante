let currentIndex = 0;
const images = [
    'Media/Zapatos/BussinesMan/CarruselBM2.png',
    'Media/Zapatos/NocturneDandy/CarruselND1.png',
    'Media/hombreelegante.avif',
    'Media/zapatocapu.webp',
    'Media/Zapatos/ModernHeritage/CarruselMH1.png'
];
const heroImage = document.querySelector('.hero-image');

document.querySelector('.next-button').addEventListener('click', function() {
    currentIndex = (currentIndex + 1) % images.length;
    heroImage.style.opacity = 0; 
    setTimeout(() => {
        heroImage.src = images[currentIndex]; 
        heroImage.style.opacity = 1;
    }, 500); 
});

document.querySelector('.prev-button').addEventListener('click', function() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    heroImage.style.opacity = 0; 
    setTimeout(() => {
        heroImage.src = images[currentIndex]; 
        heroImage.style.opacity = 1; 
    }, 500);
});
