// ========================================
// HERO SLIDER FUNCTIONALITY
// ========================================

let currentSlide = 0;
const totalSlides = 5;
const slidesWrapper = document.getElementById('slidesWrapper');
const indicators = document.querySelectorAll('.indicator');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

// Auto-slide timer
let autoSlideTimer;

function updateSlider() {
    // Move slides
    const translateX = -currentSlide * 20; // 20% per slide
    slidesWrapper.style.transform = `translateX(${translateX}%)`;

    // Update indicators
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlider();
}

function goToSlide(slideIndex) {
    currentSlide = slideIndex;
    updateSlider();
}

function startAutoSlide() {
    autoSlideTimer = setInterval(nextSlide, 4000); // Change slide every 4 seconds
}

function stopAutoSlide() {
    clearInterval(autoSlideTimer);
}

// Event Listeners
nextBtn.addEventListener('click', () => {
    nextSlide();
    stopAutoSlide();
    startAutoSlide(); // Restart timer
});

prevBtn.addEventListener('click', () => {
    prevSlide();
    stopAutoSlide();
    startAutoSlide(); // Restart timer
});

// Indicator clicks
indicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
        goToSlide(index);
        stopAutoSlide();
        startAutoSlide(); // Restart timer
    });
});

// Slide clicks for navigation
document.querySelectorAll('.slide').forEach((slide, index) => {
    slide.addEventListener('click', () => {
        nextSlide(); // Click to go to next slide
        stopAutoSlide();
        startAutoSlide(); // Restart timer
    });
});

// Pause auto-slide on hover
const heroSlider = document.querySelector('.hero-slider');
heroSlider.addEventListener('mouseenter', stopAutoSlide);
heroSlider.addEventListener('mouseleave', startAutoSlide);

// Start auto-slide when page loads
startAutoSlide();

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        prevSlide();
        stopAutoSlide();
        startAutoSlide();
    } else if (e.key === 'ArrowRight') {
        nextSlide();
        stopAutoSlide();
        startAutoSlide();
    }
});