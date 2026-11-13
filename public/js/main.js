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

    // ===== QUANTITY INPUT (1 LISTENER DUY NHẤT) =====
    document.querySelectorAll('.qty').forEach(qty => {
        if (qty.dataset.qtyInitialized === 'true') return;
        qty.dataset.qtyInitialized = 'true';

        const input = qty.querySelector('input');
        const minus = qty.querySelector('[data-qty-action="minus"], button:first-of-type');
        const plus  = qty.querySelector('[data-qty-action="plus"], button:last-of-type');
        if (!input || !minus || !plus) return;

        const clampValue = () => {
            const min = parseInt(input.min, 10) || 1;
            const max = parseInt(input.max, 10) || 999;
            let val = parseInt(input.value, 10);
            if (Number.isNaN(val) || val < min) val = min;
            if (val > max) val = max;
            input.value = val;
            return val;
        };

        minus.addEventListener('click', () => {
            const min = parseInt(input.min, 10) || 1;
            const current = clampValue();
            if (current > min) input.value = current - 1;
        });

        plus.addEventListener('click', () => {
            const max = parseInt(input.max, 10) || 999;
            const current = clampValue();
            if (current < max) {
                input.value = current + 1;
            } else if (window.showShopToast) {
                window.showShopToast(`Số lượng tối đa hiện có là ${max} sản phẩm.`, 'warning');
            }
        });

        input.addEventListener('change', clampValue);
    });

    // ===== SHOP TOAST NOTIFICATION =====
    const toastContainer = document.getElementById('shopToastContainer');
    window.showShopToast = function (message, type = 'success', options = {}) {
        if (!toastContainer || !message) return;

        const toast = document.createElement('div');
        toast.className = `shop-toast is-${type}`;

        const iconMap = {
            success: 'bi-check-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            error: 'bi-x-circle-fill',
        };
        const icon = document.createElement('i');
        icon.className = `bi ${iconMap[type] || iconMap.success} shop-toast__icon`;

        const body = document.createElement('div');
        body.className = 'shop-toast__body';
        const msgText = document.createElement('div');
        msgText.textContent = message;
        body.appendChild(msgText);

        if (options.linkUrl && options.linkText) {
            const link = document.createElement('a');
            link.href = options.linkUrl;
            link.className = 'shop-toast__link';
            link.textContent = options.linkText;
            body.appendChild(link);
        }

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'shop-toast__close';
        closeBtn.setAttribute('aria-label', 'Đóng thông báo');
        closeBtn.innerHTML = '&times;';

        const removeToast = () => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 220);
        };
        closeBtn.addEventListener('click', removeToast);

        toast.appendChild(icon);
        toast.appendChild(body);
        toast.appendChild(closeBtn);
        toastContainer.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('is-visible'));
        setTimeout(removeToast, options.duration || 3600);
    };

    if (toastContainer) {
        const flashSuccess = toastContainer.getAttribute('data-flash-success');
        const flashError = toastContainer.getAttribute('data-flash-error');
        if (flashSuccess) window.showShopToast(flashSuccess, 'success');
        if (flashError) window.showShopToast(flashError, 'error');
    }

    // ===== AJAX ADD TO CART & CẬP NHẬT BADGE HEADER =====
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const defaultCartStoreUrl = document.body.getAttribute('data-cart-store-url') || '/cart';
    const defaultCartIndexUrl = document.body.getAttribute('data-cart-index-url') || '/cart';

    function updateHeaderCartBadge(totalQuantity) {
        const qty = Number(totalQuantity) || 0;
        document.querySelectorAll('.cart-count').forEach(badge => {
            badge.textContent = qty;
            badge.style.display = qty > 0 ? 'inline-flex' : 'none';
        });
        const cartBtn = document.getElementById('headerCartBtn');
        if (cartBtn) {
            cartBtn.setAttribute('aria-label', `Giỏ hàng (${qty} sản phẩm)`);
            cartBtn.classList.remove('is-bumping');
            void cartBtn.offsetWidth;
            cartBtn.classList.add('is-bumping');
        }
    }

    async function sendAddToCartRequest(url, productId, quantity) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
            }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
        }
        return data;
    }

    // Nút thêm nhanh trên product card
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-quick-add-cart]');
        if (!btn || btn.disabled) return;
        e.preventDefault();

        const productId = btn.getAttribute('data-product-id');
        if (!productId) return;

        btn.classList.add('is-loading');
        try {
            const data = await sendAddToCartRequest(defaultCartStoreUrl, productId, 1);
            updateHeaderCartBadge(data.total_quantity);
            window.showShopToast(data.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success', {
                linkUrl: data.cart_url || defaultCartIndexUrl,
                linkText: 'Xem giỏ hàng →',
            });
        } catch (err) {
            window.showShopToast(err.message, 'error');
        } finally {
            btn.classList.remove('is-loading');
        }
    });

    // Form Thêm vào giỏ tại trang chi tiết sản phẩm (trừ nút Mua ngay)
    document.querySelectorAll('[data-ajax-cart-form]').forEach(form => {
        form.addEventListener('submit', async function (e) {
            const submitter = e.submitter;
            if (submitter && submitter.name === 'buy_now') {
                return;
            }
            e.preventDefault();

            const productId = form.querySelector('input[name="product_id"]')?.value;
            const quantity = parseInt(form.querySelector('input[name="quantity"]')?.value || '1', 10) || 1;
            const actionUrl = form.getAttribute('action') || defaultCartStoreUrl;

            if (submitter) submitter.disabled = true;
            try {
                const data = await sendAddToCartRequest(actionUrl, productId, quantity);
                updateHeaderCartBadge(data.total_quantity);
                window.showShopToast(data.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success', {
                    linkUrl: data.cart_url || defaultCartIndexUrl,
                    linkText: 'Xem giỏ hàng →',
                });
            } catch (err) {
                window.showShopToast(err.message, 'error');
            } finally {
                if (submitter) submitter.disabled = false;
            }
        });
    });

    // ===== PREFETCH LINK KHI RÊ CHUỘT (CHUYỂN TAB MƯỢT HƠN) =====
    const prefetched = new Set();
    let prefetchTimer = null;
    document.addEventListener('pointerenter', function (e) {
        if (!(e.target instanceof Element)) return;
        const anchor = e.target.closest('a[href]');
        if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('data-no-prefetch')) return;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname.startsWith('/logout') || url.pathname.startsWith('/admin')) return;
            if (url.href === window.location.href || prefetched.has(url.href) || prefetched.size >= 8) return;

            clearTimeout(prefetchTimer);
            prefetchTimer = setTimeout(() => {
                prefetched.add(url.href);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.as = 'document';
                link.href = url.href;
                document.head.appendChild(link);
            }, 65);
        } catch (_) {}
    }, true);
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