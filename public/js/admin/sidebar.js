/**
 * Xử lý thu gọn / kéo ra kéo vô cho Admin Sidebar
 * Và Tối ưu hóa chuyển tab không giật lag (Smooth AJAX Tab Switcher)
 */
document.addEventListener('DOMContentLoaded', function () {
    const adminSidebar = document.getElementById('adminSidebar');
    const btnToggle = document.getElementById('btnSidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');

    if (adminSidebar && btnToggle) {
        // Đọc trạng thái từ localStorage
        const savedState = localStorage.getItem('admin_sidebar_collapsed');
        if (savedState === 'true') {
            adminSidebar.classList.add('collapsed');
            if (toggleIcon) {
                toggleIcon.className = 'bi bi-chevron-right';
            }
            btnToggle.setAttribute('title', 'Mở rộng sidebar');
        } else {
            adminSidebar.classList.remove('collapsed');
            if (toggleIcon) {
                toggleIcon.className = 'bi bi-chevron-left';
            }
            btnToggle.setAttribute('title', 'Thu gọn sidebar');
        }

        // Bỏ class tạm thời trên html sau khi DOM đã sẵn sàng (để bật lại transition cho user click)
        setTimeout(() => {
            document.documentElement.classList.remove('sidebar-is-collapsed');
        }, 50);

        // Sự kiện bấm nút thu gọn / mở rộng
        btnToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isCollapsed = adminSidebar.classList.toggle('collapsed');
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');

            if (toggleIcon) {
                toggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
            }
            btnToggle.setAttribute('title', isCollapsed ? 'Mở rộng sidebar' : 'Thu gọn sidebar');

            // Cuộn lên đầu trang mượt mà khi mở rộng lại
            if (!isCollapsed) {
                const scrollContainer = adminSidebar.querySelector('.sidebar-scroll-container');
                if (scrollContainer) {
                    scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    // ================================================================
    // TỐI ƯU CHUYỂN TAB MƯỢT MÀ (SMOOTH AJAX TAB SWITCHER)
    // Triệt tiêu hiện tượng giật lag / tải lại trang khi bấm các tab lọc
    // ================================================================
    document.addEventListener('click', function (e) {
        const tab = e.target.closest('.status-tab-pill');
        if (!tab) return;

        const url = tab.getAttribute('href');
        if (!url || url.startsWith('#') || tab.target === '_blank') return;
        if (!url.includes('/admin/')) return;

        e.preventDefault();

        // Đánh dấu active tức thì cho tab được bấm
        const tabContainer = tab.closest('.status-tabs-bar');
        if (tabContainer) {
            tabContainer.querySelectorAll('.status-tab-pill').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        }

        // Hiệu ứng mờ nhẹ báo hiệu đang nạp
        const tableCard = document.querySelector('.table-card');
        if (tableCard) {
            tableCard.style.transition = 'opacity 0.12s ease';
            tableCard.style.opacity = '0.4';
            tableCard.style.pointerEvents = 'none';
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => {
                if (!res.ok) throw new Error('Network response not ok');
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Cập nhật bảng dữ liệu
                const newTableCard = doc.querySelector('.table-card');
                if (tableCard && newTableCard) {
                    tableCard.replaceWith(newTableCard);
                }

                // Cập nhật thanh tab (bao gồm số lượng badge)
                const newTabBar = doc.querySelector('.status-tabs-bar');
                if (tabContainer && newTabBar) {
                    tabContainer.replaceWith(newTabBar);
                }

                // Cập nhật hàng KPI metrics nếu có
                const currentMetrics = document.querySelector('.metrics-row');
                const newMetrics = doc.querySelector('.metrics-row');
                if (currentMetrics && newMetrics) {
                    currentMetrics.replaceWith(newMetrics);
                }

                window.history.pushState({ path: url }, '', url);
            })
            .catch(err => {
                // Fallback nếu có lỗi mạng
                window.location.href = url;
            });
    });

    window.addEventListener('popstate', function () {
        window.location.reload();
    });
});
