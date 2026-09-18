/**
 * Main JS - Xử lý tương tác FE
 */
document.addEventListener('DOMContentLoaded', function () {

    // ===== SIDEBAR DRAWER =====
    window.toggleSidebar = function () {
        const drawer  = document.getElementById('sidebarDrawer');
        const overlay = document.querySelector('.sidebar-drawer__overlay');
        if (!drawer || !overlay) return;
        drawer.classList.toggle('sidebar-drawer--open');
        overlay.classList.toggle('sidebar-drawer__overlay--open');
        document.body.classList.toggle('no-scroll');
    };

    // ===== BANNER SLIDER =====
    initBannerSlider();

    // ===== GALLERY THUMBNAILS =====
    const mainImage = document.getElementById('mainImage');
    const thumbs = document.querySelectorAll('.gallery-thumbs .thumb');
    if (mainImage && thumbs.length) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                mainImage.src = this.src;
                thumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // ===== QUANTITY INPUT =====
    document.querySelectorAll('.qty').forEach(qty => {
        const input = qty.querySelector('input');
        const minus = qty.querySelector('button:first-of-type');
        const plus  = qty.querySelector('button:last-of-type');
        if (!input || !minus || !plus) return;

        minus.addEventListener('click', () => {
            const min = parseInt(input.min) || 1;
            if (parseInt(input.value) > min) input.value = parseInt(input.value) - 1;
        });

        plus.addEventListener('click', () => {
            const max = parseInt(input.max) || 999;
            if (parseInt(input.value) < max) input.value = parseInt(input.value) + 1;
        });
    });

    console.log('✅ Shop Handmade FE loaded');
});

// ===== BANNER SLIDER =====
function initBannerSlider() {
    const slider = document.querySelector('.banner-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.banner-slide');
    const dots   = slider.querySelectorAll('.banner-dot');
    const prev   = slider.querySelector('.banner-prev');
    const next   = slider.querySelector('.banner-next');
    if (slides.length === 0) return;

    let current = 0;
    let autoTimer = null;

    function showSlide(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        current = index;

        slides.forEach((s, i) => {
            s.classList.toggle('banner-slide--active', i === current);
        });
        dots.forEach((d, i) => {
            d.classList.toggle('banner-dot--active', i === current);
        });
    }

    function nextSlide() { showSlide(current + 1); }
    function prevSlide() { showSlide(current - 1); }

    function startAuto() {
        stopAuto();
        autoTimer = setInterval(nextSlide, 5000);
    }
    function stopAuto() {
        if (autoTimer) clearInterval(autoTimer);
    }

    if (next) next.addEventListener('click', () => { nextSlide(); startAuto(); });
    if (prev) prev.addEventListener('click', () => { prevSlide(); startAuto(); });

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { showSlide(i); startAuto(); });
    });

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    showSlide(0);
    startAuto();
}