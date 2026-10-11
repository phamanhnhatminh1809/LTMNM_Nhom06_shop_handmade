/**
 * Xử lý thu gọn / kéo ra kéo vô cho Admin Sidebar
 * Lấy cảm hứng và tương thích hoàn toàn theo logic KLCN
 */
document.addEventListener('DOMContentLoaded', function () {
    const adminSidebar = document.getElementById('adminSidebar');
    const btnToggle = document.getElementById('btnSidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');

    if (!adminSidebar || !btnToggle) return;

    // Đọc trạng thái từ localStorage (mặc định ban đầu mở rộng như KLCN)
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
});
