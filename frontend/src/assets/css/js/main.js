/**
 * Main JS - Xử lý tương tác FE
 */
document.addEventListener('DOMContentLoaded', function () {

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