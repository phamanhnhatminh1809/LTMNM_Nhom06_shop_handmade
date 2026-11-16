/**
 * Admin App Shell:
 * - Giữ nguyên thanh Admin Sidebar cố định (không reload khi chuyển tab)
 * - PJAX hoán đổi chỉ vùng #adminMainContent + nạp động scripts trang
 * - Hover prefetch vào RAM + AbortController dọn sạch listener tránh rò rỉ
 */
(function (window, document) {
    'use strict';

    const PREFETCH_TTL_MS = 25000;
    const PREFETCH_MAX_ENTRIES = 12;
    const NOTIFICATION_POLL_MS = 30000;

    const registeredModules = new Map();
    const inlineMountQueue = [];
    const loadedExternalScripts = new Set();
    const inFlightScripts = new Map();
    const pjaxCache = new Map();
    const inFlightPrefetches = new Map();

    let bootCompleted = false;
    let isEvaluatingPjaxScripts = false;
    let currentAbortController = new AbortController();
    let activeFetchController = null;
    let navSequence = 0;
    let notificationPollTimer = null;
    let activeNotificationFilter = 'all';
    let notificationPollInFlight = false;

    function normalizeScriptKey(rawSrc) {
        try {
            const u = new URL(rawSrc, window.location.origin);
            return u.origin + u.pathname;
        } catch (_) {
            return String(rawSrc || '').split('?')[0];
        }
    }

    function getMainEl() {
        return document.getElementById('adminMainContent') || document.querySelector('.admin-main-content');
    }

    function getCurrentContext() {
        return {
            main: getMainEl(),
            signal: currentAbortController.signal
        };
    }

    function runRegisteredModules(ctx) {
        registeredModules.forEach((fn) => {
            try {
                fn(ctx);
            } catch (err) {
                console.error('[AdminApp] Module init error:', err);
            }
        });
    }

    function flushInlineMounts(ctx) {
        while (inlineMountQueue.length > 0) {
            const fn = inlineMountQueue.shift();
            try {
                fn(ctx);
            } catch (err) {
                console.error('[AdminApp] Page mount error:', err);
            }
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function escapeHtml(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function updateBellBadges(count) {
        const safeCount = Math.max(0, Number(count) || 0);
        document.querySelectorAll('[data-admin-bell]').forEach((wrapper) => {
            wrapper.setAttribute('data-unread-count', String(safeCount));

            const badge = wrapper.querySelector('[data-bell-badge]');
            if (badge) {
                badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
                badge.classList.toggle('is-visible', safeCount > 0);
            }

            const pill = wrapper.querySelector('[data-bell-pill]');
            if (pill) {
                pill.textContent = `${safeCount} mới`;
                pill.classList.toggle('is-hidden', safeCount === 0);
            }

            const markAllBtn = wrapper.querySelector('[data-bell-read-all]');
            if (markAllBtn) {
                markAllBtn.disabled = safeCount === 0;
            }
        });
    }

    function renderBellItems(wrapper, items) {
        const listEl = wrapper.querySelector('[data-bell-list]');
        if (!listEl) return;

        if (!Array.isArray(items) || items.length === 0) {
            listEl.innerHTML = `
                <div class="bell-state-empty">
                    <i class="bi bi-bell-slash"></i>
                    <span>${activeNotificationFilter === 'unread' ? 'Không có thông báo chưa đọc.' : 'Chưa có thông báo nào.'}</span>
                </div>
            `;
            return;
        }

        listEl.innerHTML = items.map((item) => {
            const isOrder = item.type === 'new_order';
            const iconClass = isOrder
                ? 'bi-receipt-cutoff'
                : (item.severity === 'danger' ? 'bi-exclamation-octagon' : 'bi-box-seam');
            const toneClass = isOrder
                ? 'is-order'
                : (item.severity === 'danger' ? 'is-stock-danger' : 'is-stock-warning');
            const rawUrl = typeof item.action_url === 'string' && item.action_url.startsWith('/') ? item.action_url : '/admin';

            return `
                <a href="${escapeHtml(rawUrl)}"
                   class="notification-item ${item.is_read ? '' : 'is-unread'}"
                   data-notification-id="${escapeHtml(item.id)}"
                   data-action-url="${escapeHtml(rawUrl)}"
                   data-is-read="${item.is_read ? '1' : '0'}"
                   data-no-pjax>
                    <span class="notification-icon ${toneClass}">
                        <i class="bi ${iconClass}"></i>
                    </span>
                    <span class="notification-body">
                        <span class="notification-title">${escapeHtml(item.title)}</span>
                        <span class="notification-message">${escapeHtml(item.message)}</span>
                        <span class="notification-meta">
                            <span>${escapeHtml(item.time_ago || '')}</span>
                            ${item.is_resolved ? '<span class="notification-resolved-tag">Đã xử lý kho</span>' : ''}
                        </span>
                    </span>
                    ${item.is_read ? '' : '<span class="notification-unread-dot" aria-label="Chưa đọc"></span>'}
                </a>
            `;
        }).join('');
    }

    async function fetchAdminNotifications(options = {}) {
        const wrapper = document.querySelector('[data-admin-bell]');
        if (!wrapper || notificationPollInFlight) return;

        const fetchUrl = wrapper.getAttribute('data-fetch-url');
        if (!fetchUrl) return;

        const includeList = Boolean(options.includeList || wrapper.classList.contains('is-open'));
        const limit = includeList ? 15 : 1;
        const url = `${fetchUrl}?filter=${encodeURIComponent(activeNotificationFilter)}&limit=${limit}`;

        notificationPollInFlight = true;
        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!res.ok) return;

            const data = await res.json();
            updateBellBadges(data.unread_count ?? 0);
            if (includeList) {
                renderBellItems(wrapper, data.items || []);
            }
        } catch (err) {
            if (options.showError) {
                const listEl = wrapper.querySelector('[data-bell-list]');
                if (listEl) {
                    listEl.innerHTML = `
                        <div class="bell-state-empty">
                            <i class="bi bi-wifi-off"></i>
                            <span>Không thể tải thông báo. Vui lòng thử lại.</span>
                        </div>
                    `;
                }
            }
        } finally {
            notificationPollInFlight = false;
        }
    }

    function ensureNotificationPolling() {
        if (notificationPollTimer !== null) return;
        notificationPollTimer = window.setInterval(() => {
            if (document.visibilityState !== 'visible') return;
            if (!document.querySelector('[data-admin-bell]')) return;
            fetchAdminNotifications();
        }, NOTIFICATION_POLL_MS);
    }

    function mountNotificationBell(ctx) {
        const wrapper = document.querySelector('[data-admin-bell]');
        if (!wrapper) return;

        ensureNotificationPolling();

        const toggleBtn = wrapper.querySelector('[data-bell-toggle]');
        const listEl = wrapper.querySelector('[data-bell-list]');
        const markAllBtn = wrapper.querySelector('[data-bell-read-all]');
        const tabs = wrapper.querySelectorAll('[data-bell-filter]');

        const closeDropdown = () => {
            wrapper.classList.remove('is-open');
            toggleBtn?.setAttribute('aria-expanded', 'false');
        };

        tabs.forEach((tab) => {
            tab.classList.toggle('active', tab.getAttribute('data-bell-filter') === activeNotificationFilter);
            tab.setAttribute('aria-selected', tab.getAttribute('data-bell-filter') === activeNotificationFilter ? 'true' : 'false');

            tab.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                activeNotificationFilter = tab.getAttribute('data-bell-filter') || 'all';
                tabs.forEach((t) => {
                    const active = t === tab;
                    t.classList.toggle('active', active);
                    t.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                fetchAdminNotifications({ includeList: true, showError: true });
            }, { signal: ctx.signal });
        });

        toggleBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const willOpen = !wrapper.classList.contains('is-open');
            wrapper.classList.toggle('is-open', willOpen);
            toggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                fetchAdminNotifications({ includeList: true, showError: true });
            }
        }, { signal: ctx.signal });

        markAllBtn?.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const readAllUrl = wrapper.getAttribute('data-read-all-url');
            if (!readAllUrl) return;

            try {
                const res = await fetch(readAllUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                updateBellBadges(0);
                wrapper.querySelectorAll('.notification-item.is-unread').forEach((el) => {
                    el.classList.remove('is-unread');
                    el.setAttribute('data-is-read', '1');
                    el.querySelector('.notification-unread-dot')?.remove();
                });
                if (activeNotificationFilter === 'unread') {
                    renderBellItems(wrapper, []);
                }
            } catch (_) {
                // Giữ nguyên trạng thái nếu mất mạng tạm thời
            }
        }, { signal: ctx.signal });

        listEl?.addEventListener('click', (e) => {
            if (!(e.target instanceof Element)) return;
            const itemEl = e.target.closest('.notification-item');
            if (!itemEl) return;

            e.preventDefault();
            const id = itemEl.getAttribute('data-notification-id');
            const actionUrl = itemEl.getAttribute('data-action-url') || '/admin';
            const wasRead = itemEl.getAttribute('data-is-read') === '1';
            const template = wrapper.getAttribute('data-read-url-template') || '';

            if (!wasRead && id && template) {
                const readUrl = template.replace('__ID__', encodeURIComponent(id));
                const currentCount = Number(wrapper.getAttribute('data-unread-count') || '0');
                updateBellBadges(Math.max(0, currentCount - 1));
                itemEl.classList.remove('is-unread');
                itemEl.setAttribute('data-is-read', '1');
                itemEl.querySelector('.notification-unread-dot')?.remove();

                fetch(readUrl, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin',
                    keepalive: true
                }).catch(() => {});
            }

            closeDropdown();
            const resolvedUrl = new URL(actionUrl, window.location.origin).href;
            pjaxCache.delete(resolvedUrl);
            navigate(resolvedUrl, { pushHistory: true, bypassCache: true });
        }, { signal: ctx.signal });

        document.addEventListener('click', (e) => {
            if (!(e.target instanceof Node) || !wrapper.contains(e.target)) {
                closeDropdown();
            }
        }, { signal: ctx.signal });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown();
            }
        }, { signal: ctx.signal });
    }

    function runAllInitializers() {
        const ctx = getCurrentContext();
        mountNotificationBell(ctx);
        if (window.HandmadeUI && typeof window.HandmadeUI.initAll === 'function') {
            window.HandmadeUI.initAll(ctx.main || document, ctx.signal);
        }
        runRegisteredModules(ctx);
        flushInlineMounts(ctx);
    }

    function registerExistingScripts() {
        document.querySelectorAll('script[src]').forEach((el) => {
            const src = el.getAttribute('src');
            if (src) {
                loadedExternalScripts.add(normalizeScriptKey(src));
            }
        });
    }

    function loadExternalScript(rawSrc) {
        const key = normalizeScriptKey(rawSrc);
        if (!key || key.endsWith('/js/admin/sidebar.js') || loadedExternalScripts.has(key)) {
            return Promise.resolve();
        }
        if (inFlightScripts.has(key)) {
            return inFlightScripts.get(key);
        }

        const promise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = rawSrc;
            script.async = false;
            script.onload = () => {
                loadedExternalScripts.add(key);
                inFlightScripts.delete(key);
                resolve();
            };
            script.onerror = (err) => {
                inFlightScripts.delete(key);
                reject(err);
            };
            document.head.appendChild(script);
        });

        inFlightScripts.set(key, promise);
        return promise;
    }

    function canPjaxUrl(href) {
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }
        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return false;
            if (url.pathname === '/logout' || url.pathname.startsWith('/logout/')) return false;
            return url.pathname === '/admin' || url.pathname.startsWith('/admin/');
        } catch (_) {
            return false;
        }
    }

    function setCacheEntry(href, payload) {
        if (pjaxCache.has(href)) {
            pjaxCache.delete(href);
        } else if (pjaxCache.size >= PREFETCH_MAX_ENTRIES) {
            const oldestKey = pjaxCache.keys().next().value;
            if (oldestKey) pjaxCache.delete(oldestKey);
        }
        pjaxCache.set(href, {
            html: payload.html,
            finalUrl: payload.finalUrl,
            expiresAt: Date.now() + PREFETCH_TTL_MS
        });
    }

    function getValidCacheEntry(href) {
        const item = pjaxCache.get(href);
        if (!item) return null;
        if (Date.now() > item.expiresAt) {
            pjaxCache.delete(href);
            return null;
        }
        return item;
    }

    function fetchAdminPage(href, signal) {
        return fetch(href, {
            method: 'GET',
            credentials: 'same-origin',
            signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Admin-Pjax': '1'
            }
        }).then(async (res) => {
            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
            const html = await res.text();
            return {
                html,
                finalUrl: res.url || href
            };
        });
    }

    function prefetchUrl(href) {
        if (!canPjaxUrl(href)) return;
        if (href === window.location.href) return;
        if (getValidCacheEntry(href) || inFlightPrefetches.has(href)) return;

        const promise = fetchAdminPage(href)
            .then((payload) => {
                if (canPjaxUrl(payload.finalUrl)) {
                    setCacheEntry(href, payload);
                }
                return payload;
            })
            .catch(() => null)
            .finally(() => {
                inFlightPrefetches.delete(href);
            });

        inFlightPrefetches.set(href, promise);
    }

    function setOptimisticSidebarActive(targetPathname) {
        const sidebar = document.getElementById('adminSidebar');
        if (!sidebar) return;

        const links = sidebar.querySelectorAll('a.nav-link');
        const profileCard = sidebar.querySelector('.admin-profile-card');
        links.forEach((l) => l.classList.remove('active'));
        if (profileCard) profileCard.classList.remove('admin-profile-card--active');

        if (profileCard) {
            try {
                const pPath = new URL(profileCard.getAttribute('href'), window.location.origin).pathname;
                if (targetPathname === pPath || targetPathname.startsWith(pPath + '/')) {
                    profileCard.classList.add('admin-profile-card--active');
                    return;
                }
            } catch (_) {}
        }

        let bestMatch = null;
        let bestLength = -1;

        links.forEach((link) => {
            try {
                const lPath = new URL(link.getAttribute('href'), window.location.origin).pathname;
                if (lPath === '/admin' || lPath === '/admin/dashboard') {
                    if (targetPathname === '/admin' || targetPathname === '/admin/dashboard') {
                        bestMatch = link;
                        bestLength = lPath.length + 100;
                    }
                    return;
                }
                if ((targetPathname === lPath || targetPathname.startsWith(lPath + '/')) && lPath.length > bestLength) {
                    bestMatch = link;
                    bestLength = lPath.length;
                }
            } catch (_) {}
        });

        if (bestMatch) {
            bestMatch.classList.add('active');
        }
    }

    function syncSidebarActiveFromDoc(doc) {
        const currentSidebar = document.getElementById('adminSidebar');
        const nextSidebar = doc.getElementById('adminSidebar');
        if (!currentSidebar || !nextSidebar) return;

        const currentLinks = currentSidebar.querySelectorAll('a.nav-link');
        const nextActiveHrefs = new Set(
            Array.from(nextSidebar.querySelectorAll('a.nav-link.active')).map((a) => {
                try {
                    return new URL(a.getAttribute('href'), window.location.origin).pathname;
                } catch (_) {
                    return '';
                }
            })
        );

        currentLinks.forEach((link) => {
            try {
                const p = new URL(link.getAttribute('href'), window.location.origin).pathname;
                link.classList.toggle('active', nextActiveHrefs.has(p));
            } catch (_) {}
        });

        const currentProfile = currentSidebar.querySelector('.admin-profile-card');
        const nextProfile = nextSidebar.querySelector('.admin-profile-card');
        if (currentProfile && nextProfile) {
            currentProfile.classList.toggle(
                'admin-profile-card--active',
                nextProfile.classList.contains('admin-profile-card--active')
            );
        }
    }

    function destroyActiveCharts(mainEl) {
        if (!window.Chart || typeof window.Chart.getChart !== 'function' || !mainEl) return;
        mainEl.querySelectorAll('canvas').forEach((canvas) => {
            try {
                const instance = window.Chart.getChart(canvas);
                if (instance) instance.destroy();
            } catch (_) {}
        });
    }

    async function executePageScripts(doc) {
        const hostScriptsContainer = document.getElementById('adminPageScripts');
        const nextScriptsContainer = doc.getElementById('adminPageScripts');
        if (!nextScriptsContainer) return;

        if (hostScriptsContainer) {
            hostScriptsContainer.innerHTML = '';
        }

        const scriptEls = Array.from(nextScriptsContainer.querySelectorAll('script'));
        isEvaluatingPjaxScripts = true;

        try {
            for (const scriptEl of scriptEls) {
                const src = scriptEl.getAttribute('src');
                if (src) {
                    await loadExternalScript(src);
                } else {
                    const code = scriptEl.textContent || '';
                    if (code.trim().length > 0) {
                        const runnable = document.createElement('script');
                        runnable.textContent = code;
                        (hostScriptsContainer || document.body).appendChild(runnable);
                    }
                }
            }
        } finally {
            isEvaluatingPjaxScripts = false;
        }
    }

    // Thanh tiến trình điều hướng mảnh ở mép trên cùng
    let progressTimer = null;
    let navProgressBar = null;

    function ensureProgressBar() {
        if (navProgressBar) return navProgressBar;
        navProgressBar = document.createElement('div');
        navProgressBar.className = 'admin-nav-progress';
        document.body.appendChild(navProgressBar);
        return navProgressBar;
    }

    function startNavProgress(mainEl) {
        const bar = ensureProgressBar();
        clearTimeout(progressTimer);
        progressTimer = setTimeout(() => {
            if (mainEl) mainEl.classList.add('is-pjax-loading');
            bar.style.transform = 'scaleX(0.35)';
            bar.classList.add('is-active');
            requestAnimationFrame(() => {
                bar.style.transform = 'scaleX(0.78)';
            });
        }, 70);
    }

    function finishNavProgress(mainEl) {
        clearTimeout(progressTimer);
        if (mainEl) mainEl.classList.remove('is-pjax-loading');
        if (!navProgressBar || !navProgressBar.classList.contains('is-active')) return;
        navProgressBar.style.transform = 'scaleX(1)';
        setTimeout(() => {
            navProgressBar.classList.remove('is-active');
            navProgressBar.style.transform = 'scaleX(0)';
        }, 160);
    }

    async function navigate(rawUrl, options = {}) {
        const {
            pushHistory = true,
            bypassCache = false
        } = options;

        if (!canPjaxUrl(rawUrl)) {
            window.location.href = rawUrl;
            return;
        }

        const targetUrl = new URL(rawUrl, window.location.origin);
        const targetHref = targetUrl.href;
        const mainEl = getMainEl();

        if (!mainEl) {
            window.location.href = targetHref;
            return;
        }

        if (activeFetchController) {
            activeFetchController.abort();
            activeFetchController = null;
        }

        const currentSeq = ++navSequence;
        setOptimisticSidebarActive(targetUrl.pathname);

        let payload = !bypassCache ? getValidCacheEntry(targetHref) : null;
        const fromMemory = Boolean(payload);

        if (!payload && !bypassCache && inFlightPrefetches.has(targetHref)) {
            startNavProgress(mainEl);
            payload = await inFlightPrefetches.get(targetHref);
        }

        if (currentSeq !== navSequence) return;

        if (!payload) {
            activeFetchController = new AbortController();
            if (!fromMemory) startNavProgress(mainEl);
            try {
                payload = await fetchAdminPage(targetHref, activeFetchController.signal);
                setCacheEntry(targetHref, payload);
            } catch (err) {
                if (err && err.name === 'AbortError') return;
                window.location.href = targetHref;
                return;
            } finally {
                if (activeFetchController && currentSeq === navSequence) {
                    activeFetchController = null;
                }
            }
        }

        if (currentSeq !== navSequence) return;

        // Nếu session hết hạn hoặc bị redirect ra ngoài khu vực /admin -> chuyển hướng toàn trang
        if (!canPjaxUrl(payload.finalUrl)) {
            window.location.href = payload.finalUrl;
            return;
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString(payload.html, 'text/html');
        const nextMain = doc.getElementById('adminMainContent') || doc.querySelector('.admin-main-content');

        if (!nextMain) {
            window.location.href = payload.finalUrl;
            return;
        }

        // Hủy listener trang cũ & Chart.js trước khi thay DOM
        currentAbortController.abort();
        currentAbortController = new AbortController();
        destroyActiveCharts(mainEl);
        document.body.style.overflow = '';

        // Đồng bộ tiêu đề & trạng thái active chính xác từ server HTML
        if (doc.title) {
            document.title = doc.title;
        }
        syncSidebarActiveFromDoc(doc);

        // Thay chỉ vùng nội dung bên phải, giữ nguyên hoàn toàn #adminSidebar
        mainEl.classList.remove('is-pjax-loading', 'is-pjax-entering');
        mainEl.innerHTML = nextMain.innerHTML;
        void mainEl.offsetWidth;
        mainEl.classList.add('is-pjax-entering');

        const historyUrl = payload.finalUrl || targetHref;
        if (pushHistory && historyUrl !== window.location.href) {
            window.history.pushState({ adminPjaxUrl: historyUrl }, '', historyUrl);
        }

        window.scrollTo({ top: 0, behavior: 'auto' });

        try {
            await executePageScripts(doc);
        } catch (err) {
            console.error('[AdminApp] Script load error, falling back to full load:', err);
            window.location.href = historyUrl;
            return;
        }

        if (currentSeq !== navSequence) return;

        runAllInitializers();
        finishNavProgress(mainEl);
    }

    // API công khai cho các script module (dashboard.js, products.js) và inline views
    window.AdminApp = {
        registerModule(name, fn) {
            if (typeof fn !== 'function') return;
            registeredModules.set(String(name), fn);
            if (bootCompleted && !isEvaluatingPjaxScripts) {
                try {
                    fn(getCurrentContext());
                } catch (err) {
                    console.error('[AdminApp] Module run error:', err);
                }
            }
        },
        mount(fn) {
            if (typeof fn !== 'function') return;
            inlineMountQueue.push(fn);
            if (bootCompleted && !isEvaluatingPjaxScripts) {
                flushInlineMounts(getCurrentContext());
            }
        },
        onPageLoad(nameOrFn, maybeFn) {
            if (typeof nameOrFn === 'string') {
                this.registerModule(nameOrFn, maybeFn);
            } else {
                this.mount(nameOrFn);
            }
        },
        navigate,
        prefetch: prefetchUrl,
        clearCache() {
            pjaxCache.clear();
        }
    };

    registerExistingScripts();

    document.addEventListener('DOMContentLoaded', function () {
        const adminSidebar = document.getElementById('adminSidebar');
        const btnToggle = document.getElementById('btnSidebarToggle');
        const toggleIcon = document.getElementById('sidebarToggleIcon');

        // 1. Khởi tạo & giữ trạng thái thu gọn Sidebar (chỉ chạy 1 lần duy nhất)
        if (adminSidebar && btnToggle) {
            const savedState = localStorage.getItem('admin_sidebar_collapsed');
            if (savedState === 'true') {
                adminSidebar.classList.add('collapsed');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-right';
                btnToggle.setAttribute('title', 'Mở rộng sidebar');
            } else {
                adminSidebar.classList.remove('collapsed');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-left';
                btnToggle.setAttribute('title', 'Thu gọn sidebar');
            }

            setTimeout(() => {
                document.documentElement.classList.remove('sidebar-is-collapsed');
            }, 50);

            btnToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                const isCollapsed = adminSidebar.classList.toggle('collapsed');
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');

                if (toggleIcon) {
                    toggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
                }
                btnToggle.setAttribute('title', isCollapsed ? 'Mở rộng sidebar' : 'Thu gọn sidebar');

                if (!isCollapsed) {
                    const scrollContainer = adminSidebar.querySelector('.sidebar-scroll-container');
                    if (scrollContainer) {
                        scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            });
        }

        // 2. Hover / focus prefetch vào RAM (65ms debounce)
        let prefetchTimer = null;
        document.addEventListener('pointerenter', function (e) {
            if (!(e.target instanceof Element)) return;
            const anchor = e.target.closest('a[href]');
            if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-prefetch')) {
                return;
            }
            const href = anchor.getAttribute('href');
            if (!canPjaxUrl(href)) return;

            clearTimeout(prefetchTimer);
            prefetchTimer = setTimeout(() => {
                prefetchUrl(new URL(href, window.location.origin).href);
            }, 65);
        }, true);

        // 3. Chặn mọi điều hướng link nội bộ /admin/* để giữ Sidebar cố định
        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
            if (!(e.target instanceof Element)) return;

            const anchor = e.target.closest('a[href]');
            if (!anchor) return;
            if (anchor.target === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-pjax') || anchor.hasAttribute('onclick')) {
                return;
            }

            const rawHref = anchor.getAttribute('href');
            if (!canPjaxUrl(rawHref)) return;

            e.preventDefault();

            const targetHref = new URL(rawHref, window.location.origin).href;

            // Đổi sáng pill tức thì nếu là thanh tab trạng thái
            const tabContainer = anchor.closest('.status-tabs-bar');
            if (tabContainer && anchor.classList.contains('status-tab-pill')) {
                tabContainer.querySelectorAll('.status-tab-pill').forEach((t) => t.classList.remove('active'));
                anchor.classList.add('active');
            }

            // Nếu click lại đúng URL hiện tại thì xóa cache để làm mới vùng nội dung bên phải
            const isSameUrl = targetHref === window.location.href;
            if (isSameUrl) {
                pjaxCache.delete(targetHref);
            }

            navigate(targetHref, {
                pushHistory: !isSameUrl,
                bypassCache: isSameUrl
            });
        });

        // 4. Chặn GET form tìm kiếm/lọc trong #adminMainContent & xóa cache khi submit POST/PUT/DELETE
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            const method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method !== 'GET') {
                pjaxCache.clear();
                return;
            }

            const mainEl = getMainEl();
            if (!mainEl || !mainEl.contains(form) || form.hasAttribute('data-no-pjax')) return;

            const action = form.getAttribute('action') || window.location.href;
            if (!canPjaxUrl(action)) return;

            e.preventDefault();
            const targetUrl = new URL(action, window.location.origin);
            const params = new URLSearchParams();
            new FormData(form).forEach((value, key) => {
                if (typeof value === 'string' && value !== '') {
                    params.append(key, value);
                }
            });
            targetUrl.search = params.toString();

            pjaxCache.delete(targetUrl.href);
            navigate(targetUrl.href, { pushHistory: true, bypassCache: true });
        });

        // 5. Hỗ trợ Back / Forward trình duyệt không tải lại trang
        window.addEventListener('popstate', function () {
            if (canPjaxUrl(window.location.href)) {
                navigate(window.location.href, { pushHistory: false, bypassCache: false });
            } else {
                window.location.reload();
            }
        });

        // Khởi chạy module & mount cho lần tải trang đầu tiên
        bootCompleted = true;
        runAllInitializers();
    });
})(window, document);
