/**
 * Shared UI Component Runtime (Handmade House)
 * - Custom Select (<x-select>) with Keyboard Nav, Search, Dynamic MutationObserver & PJAX support
 * - Alert Auto-Dismiss & Close (<x-alert>)
 * - Safe Auto-Trim on Text Inputs & Textareas (on blur & form submit)
 */
(function (window, document) {
    'use strict';

    const TRIM_INPUT_TYPES = new Set(['text', 'email', 'search', 'tel', 'url', '']);

    function isTrimmableControl(el) {
        if (!el || el.disabled || el.readOnly) return false;
        if (el.hasAttribute('data-no-trim') || el.classList.contains('ui-select__search')) return false;

        if (el instanceof HTMLTextAreaElement) return true;
        if (!(el instanceof HTMLInputElement)) return false;

        const type = (el.getAttribute('type') || 'text').toLowerCase();
        if (type === 'hidden') return el.hasAttribute('data-trim');
        return TRIM_INPUT_TYPES.has(type);
    }

    function trimControlValue(el) {
        if (!isTrimmableControl(el)) return;
        const trimmed = el.value.trim();
        if (el.value !== trimmed) {
            el.value = trimmed;
        }
    }

    function trimContainerInputs(container) {
        if (!container || typeof container.querySelectorAll !== 'function') return;
        container.querySelectorAll('input, textarea').forEach(trimControlValue);
    }

    function bindGlobalInputTrimmer() {
        if (window.__handmadeInputTrimmerBound) return;
        window.__handmadeInputTrimmerBound = true;

        document.addEventListener('focusout', function (e) {
            if (e.target instanceof Element) {
                trimControlValue(e.target);
            }
        });

        // Capture phase ensures inputs are trimmed before PJAX or form handlers read FormData
        document.addEventListener('submit', function (e) {
            if (e.target instanceof HTMLFormElement) {
                trimContainerInputs(e.target);
            }
        }, true);
    }

    function dismissAlertEl(alertEl) {
        if (!alertEl || alertEl.dataset.dismissed === 'true') return;
        alertEl.dataset.dismissed = 'true';
        alertEl.classList.add('is-dismissing');
        window.setTimeout(() => {
            alertEl.remove();
        }, 280);
    }

    function initAlerts(root = document, signal = null) {
        const scope = root && typeof root.querySelectorAll === 'function' ? root : document;
        const alerts = scope.querySelectorAll('[data-ui-alert], .admin-alert.success:not([data-persistent])');

        alerts.forEach((alertEl) => {
            if (alertEl.dataset.alertBound === 'true') return;
            alertEl.dataset.alertBound = 'true';

            const closeBtn = alertEl.querySelector('[data-alert-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => dismissAlertEl(alertEl), signal ? { signal } : undefined);
            }

            const rawDelay = alertEl.getAttribute('data-auto-dismiss');
            const delayMs = rawDelay !== null
                ? parseInt(rawDelay, 10)
                : (alertEl.classList.contains('admin-alert') && alertEl.classList.contains('success') ? 4000 : 0);

            if (!Number.isFinite(delayMs) || delayMs <= 0) return;

            let remaining = delayMs;
            let startedAt = Date.now();
            let timerId = window.setTimeout(() => dismissAlertEl(alertEl), remaining);

            alertEl.addEventListener('mouseenter', () => {
                if (timerId !== null) {
                    window.clearTimeout(timerId);
                    timerId = null;
                    remaining = Math.max(400, remaining - (Date.now() - startedAt));
                }
            }, signal ? { signal } : undefined);

            alertEl.addEventListener('mouseleave', () => {
                if (timerId === null && alertEl.dataset.dismissed !== 'true') {
                    startedAt = Date.now();
                    timerId = window.setTimeout(() => dismissAlertEl(alertEl), remaining);
                }
            }, signal ? { signal } : undefined);
        });
    }

    function closeAllSelects(exceptEl = null) {
        document.querySelectorAll('[data-ui-select].is-open').forEach((sel) => {
            if (sel === exceptEl) return;
            sel.classList.remove('is-open');
            const trigger = sel.querySelector('[data-ui-select-trigger]');
            trigger?.setAttribute('aria-expanded', 'false');
        });
    }

    function bindGlobalSelectDismiss() {
        if (window.__handmadeSelectDismissBound) return;
        window.__handmadeSelectDismissBound = true;

        document.addEventListener('click', function (e) {
            const target = e.target instanceof Element ? e.target.closest('[data-ui-select]') : null;
            if (!target) closeAllSelects();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllSelects();
        });
    }

    function initSelects(root = document, signal = null) {
        bindGlobalSelectDismiss();
        const scope = root && typeof root.querySelectorAll === 'function' ? root : document;

        scope.querySelectorAll('[data-ui-select]').forEach((wrapper) => {
            if (wrapper.dataset.uiSelectBound === 'true') return;
            wrapper.dataset.uiSelectBound = 'true';

            const nativeSelect = wrapper.querySelector('select.ui-select__native');
            const trigger = wrapper.querySelector('[data-ui-select-trigger]');
            const labelEl = wrapper.querySelector('.ui-select__label');
            const listEl = wrapper.querySelector('[data-ui-select-list]');
            const searchInput = wrapper.querySelector('[data-ui-select-search]');
            if (!nativeSelect || !trigger || !labelEl || !listEl) return;

            const placeholder = wrapper.getAttribute('data-placeholder') || '-- Chọn --';
            const checkIcon = wrapper.getAttribute('data-check-icon') || 'bi-check2';
            const emptyText = wrapper.getAttribute('data-empty-text') || 'Không có lựa chọn phù hợp';
            const submitOnChange = wrapper.getAttribute('data-submit-on-change') === 'true';

            function renderOptions(filterQuery = '') {
                const options = Array.from(nativeSelect.options);
                const selectedOpt = options[nativeSelect.selectedIndex] || options.find((o) => o.selected);
                const hasValidVal = selectedOpt && (selectedOpt.value !== '' || options.some((o) => o.value === '' && o.selected));

                labelEl.textContent = hasValidVal
                    ? selectedOpt.textContent.trim()
                    : (selectedOpt ? selectedOpt.textContent.trim() : placeholder);

                wrapper.classList.toggle('is-disabled', nativeSelect.disabled);
                trigger.disabled = nativeSelect.disabled;
                trigger.setAttribute('aria-disabled', nativeSelect.disabled ? 'true' : 'false');

                const q = filterQuery.trim().toLowerCase();
                listEl.innerHTML = '';
                let visibleCount = 0;

                options.forEach((opt, idx) => {
                    const text = opt.textContent.trim();
                    if (q && !text.toLowerCase().includes(q)) return;
                    visibleCount++;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const isSel = idx === nativeSelect.selectedIndex;
                    btn.className = 'ui-select__option' + (isSel ? ' is-selected' : '');
                    btn.setAttribute('role', 'option');
                    btn.setAttribute('aria-selected', isSel ? 'true' : 'false');
                    btn.setAttribute('data-value', opt.value);
                    if (opt.disabled) btn.disabled = true;

                    const left = document.createElement('span');
                    left.className = 'ui-select__option-text';

                    const optIcon = opt.getAttribute('data-icon');
                    if (optIcon) {
                        const ic = document.createElement('i');
                        ic.className = `bi ${optIcon}`;
                        ic.setAttribute('aria-hidden', 'true');
                        left.appendChild(ic);
                    }

                    const txtSpan = document.createElement('span');
                    txtSpan.textContent = text;
                    left.appendChild(txtSpan);
                    btn.appendChild(left);

                    if (isSel) {
                        const chk = document.createElement('i');
                        chk.className = `bi ${checkIcon} ui-select__check`;
                        chk.setAttribute('aria-hidden', 'true');
                        btn.appendChild(chk);
                    }

                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (opt.disabled || nativeSelect.disabled) return;
                        const prevVal = nativeSelect.value;
                        nativeSelect.value = opt.value;
                        renderOptions('');
                        if (searchInput) searchInput.value = '';
                        closeAllSelects();
                        trigger.focus();

                        if (prevVal !== opt.value || submitOnChange) {
                            nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        if (submitOnChange && nativeSelect.form) {
                            if (typeof nativeSelect.form.requestSubmit === 'function') {
                                nativeSelect.form.requestSubmit();
                            } else {
                                nativeSelect.form.submit();
                            }
                        }
                    });

                    listEl.appendChild(btn);
                });

                if (visibleCount === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'ui-select__empty';
                    empty.textContent = emptyText;
                    listEl.appendChild(empty);
                }
            }

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (nativeSelect.disabled) return;

                const willOpen = !wrapper.classList.contains('is-open');
                closeAllSelects(wrapper);
                wrapper.classList.toggle('is-open', willOpen);
                trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

                if (willOpen) {
                    renderOptions('');
                    if (searchInput) {
                        searchInput.value = '';
                        window.setTimeout(() => searchInput.focus(), 10);
                    }
                }
            }, signal ? { signal } : undefined);

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    renderOptions(searchInput.value);
                }, signal ? { signal } : undefined);

                searchInput.addEventListener('click', (e) => e.stopPropagation(), signal ? { signal } : undefined);
            }

            wrapper.addEventListener('keydown', (e) => {
                if (!wrapper.classList.contains('is-open')) {
                    if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                        if (document.activeElement === trigger) {
                            e.preventDefault();
                            trigger.click();
                        }
                    }
                    return;
                }

                const items = Array.from(listEl.querySelectorAll('.ui-select__option:not(:disabled)'));
                if (!items.length) return;
                const activeIdx = items.indexOf(document.activeElement);

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    items[(activeIdx + 1) % items.length].focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    items[(activeIdx - 1 + items.length) % items.length].focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeAllSelects();
                    trigger.focus();
                }
            }, signal ? { signal } : undefined);

            nativeSelect.addEventListener('change', () => {
                renderOptions(searchInput ? searchInput.value : '');
            }, signal ? { signal } : undefined);

            const valueDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
            if (valueDesc && valueDesc.get && valueDesc.set) {
                Object.defineProperty(nativeSelect, 'value', {
                    configurable: true,
                    get() {
                        return valueDesc.get.call(this);
                    },
                    set(nextVal) {
                        valueDesc.set.call(this, nextVal);
                        renderOptions(searchInput ? searchInput.value : '');
                    },
                });
            }

            // Tự động đồng bộ khi JS bên ngoài thay đổi option hoặc disabled (VD: API tỉnh/phường)
            const observer = new MutationObserver(() => {
                renderOptions(searchInput ? searchInput.value : '');
            });
            observer.observe(nativeSelect, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['disabled', 'selected'],
            });

            if (signal) {
                signal.addEventListener('abort', () => observer.disconnect(), { once: true });
            }

            renderOptions('');
        });
    }

    const CONFIRM_VARIANTS = {
        primary: {
            icon: 'bi-truck',
            iconClass: 'is-primary',
            title: 'Xác nhận thao tác',
            label: 'Xác nhận',
            btnClass: 'btn-modal-save',
        },
        success: {
            icon: 'bi-check2-circle',
            iconClass: 'is-success',
            title: 'Xác nhận hoàn tất',
            label: 'Đồng ý',
            btnClass: 'btn-confirm-modal-success',
        },
        danger: {
            icon: 'bi-exclamation-triangle-fill',
            iconClass: 'is-danger',
            title: 'Xác nhận thao tác',
            label: 'Đồng ý thực hiện',
            btnClass: 'btn-confirm-modal-danger',
        },
    };

    let pendingConfirmForm = null;
    let pendingConfirmModal = null;

    function closeAdminConfirmModal() {
        if (!pendingConfirmModal) {
            pendingConfirmModal = document.getElementById('adminConfirmModal');
        }
        pendingConfirmModal?.classList.remove('active');
        document.body.style.overflow = '';
        pendingConfirmForm = null;
    }

    function openAdminConfirmModal(form) {
        const modal = document.getElementById('adminConfirmModal');
        if (!modal) return false;

        pendingConfirmModal = modal;
        pendingConfirmForm = form;

        const variantKey = form.getAttribute('data-confirm-variant') || 'primary';
        const preset = CONFIRM_VARIANTS[variantKey] || CONFIRM_VARIANTS.primary;
        const iconWrap = modal.querySelector('[data-confirm-icon-wrap]');
        const iconEl = modal.querySelector('[data-confirm-icon]');
        const titleEl = modal.querySelector('[data-confirm-title]');
        const msgEl = modal.querySelector('[data-confirm-message]');
        const acceptBtn = modal.querySelector('[data-confirm-accept]');

        if (iconWrap) {
            iconWrap.className = 'modal-confirm-icon ' + preset.iconClass;
        }
        if (iconEl) {
            const customIcon = form.getAttribute('data-confirm-icon');
            iconEl.className = 'bi ' + (customIcon || preset.icon);
        }
        if (titleEl) {
            titleEl.textContent = form.getAttribute('data-confirm-title') || preset.title;
        }
        if (msgEl) {
            msgEl.textContent = form.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';
        }
        if (acceptBtn) {
            acceptBtn.className = preset.btnClass;
            acceptBtn.textContent = form.getAttribute('data-confirm-label') || preset.label;
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        acceptBtn?.focus();
        return true;
    }

    function bindGlobalConfirmForms() {
        if (window.__handmadeConfirmBound) return;
        window.__handmadeConfirmBound = true;

        const modal = document.getElementById('adminConfirmModal');
        if (modal) {
            modal.querySelector('[data-confirm-cancel]')?.addEventListener('click', closeAdminConfirmModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeAdminConfirmModal();
            });
            modal.querySelector('[data-confirm-accept]')?.addEventListener('click', () => {
                const targetForm = pendingConfirmForm;
                if (!targetForm) {
                    closeAdminConfirmModal();
                    return;
                }
                closeAdminConfirmModal();
                targetForm.setAttribute('data-confirmed', 'true');
                if (typeof targetForm.requestSubmit === 'function') {
                    targetForm.requestSubmit();
                } else {
                    targetForm.submit();
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeAdminConfirmModal();
                }
            });
        }

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm')) return;

            if (form.getAttribute('data-confirmed') === 'true') {
                form.removeAttribute('data-confirmed');
                return;
            }

            if (openAdminConfirmModal(form)) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }, true);
    }

    function showAdminToast(message, type = 'warning', duration = 3600) {
        if (!message) return;
        let container = document.getElementById('adminToastContainer') || document.getElementById('shopToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `shop-toast is-${type}`;

        const iconMap = {
            success: 'bi-check-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            error: 'bi-x-circle-fill',
        };
        const icon = document.createElement('i');
        icon.className = `bi ${iconMap[type] || iconMap.warning} shop-toast__icon`;

        const body = document.createElement('div');
        body.className = 'shop-toast__body';
        body.textContent = String(message);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'shop-toast__close';
        closeBtn.setAttribute('aria-label', 'Đóng thông báo');
        closeBtn.innerHTML = '&times;';

        const removeToast = () => {
            toast.classList.remove('is-visible');
            window.setTimeout(() => toast.remove(), 220);
        };
        closeBtn.addEventListener('click', removeToast);

        toast.append(icon, body, closeBtn);
        container.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('is-visible'));
        window.setTimeout(removeToast, duration);
    }

    function initAll(root = document, signal = null) {
        bindGlobalInputTrimmer();
        bindGlobalConfirmForms();
        initSelects(root, signal);
        initAlerts(root, signal);
    }

    window.showAdminToast = showAdminToast;

    window.HandmadeUI = {
        initAll,
        initSelects,
        initAlerts,
        dismissAlert: dismissAlertEl,
        showToast: showAdminToast,
        trimValue: trimControlValue,
        trimForm: trimContainerInputs,
    };

    document.addEventListener('DOMContentLoaded', function () {
        initAll(document);
    });
})(window, document);
