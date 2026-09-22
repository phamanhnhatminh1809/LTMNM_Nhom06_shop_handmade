<?php
/**
 * Component: Popover / Modal Chọn Khoảng Thời Gian Lọc Dữ Liệu
 * Tái sử dụng cho Dashboard, Báo cáo, Đơn hàng, Kho hàng,...
 */
?>
<div class="date-filter-container">
    <button type="button" class="dropdown-date-btn" id="btnDateFilter">
        <i class="bi bi-calendar3"></i>
        <span id="selectedDateText">Hôm nay</span>
        <i class="bi bi-chevron-down date-chevron" style="font-size: 11px;"></i>
    </button>

    <!-- Popover Modal chọn khoảng thời gian chuẩn hình thiết kế -->
    <div class="date-range-popover" id="dateRangePopover">
        <div class="popover-header">
            <div class="popover-title">
                <i class="bi bi-calendar-event"></i>
                <span>Chọn khoảng thời gian</span>
            </div>
            <button type="button" class="btn-close-popover" id="btnCloseDatePopover">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="popover-presets">
            <button type="button" class="preset-btn active" data-preset="today">
                <i class="bi bi-calendar-check"></i>
                <span>Hôm nay</span>
            </button>
            <button type="button" class="preset-btn" data-preset="7days">
                <i class="bi bi-clock-history"></i>
                <span>7 ngày qua</span>
            </button>
            <button type="button" class="preset-btn" data-preset="month">
                <i class="bi bi-calendar3"></i>
                <span>Tháng này</span>
            </button>
            <button type="button" class="preset-btn" data-preset="year">
                <i class="bi bi-calendar4"></i>
                <span>Năm nay</span>
            </button>
        </div>

        <div class="popover-divider"></div>

        <div class="popover-custom-section">
            <div class="custom-section-title">
                <i class="bi bi-sliders"></i>
                <span>Tùy chỉnh</span>
            </div>
            <div class="custom-date-inputs">
                <div class="date-input-group">
                    <label for="startDateInput">TỪ NGÀY</label>
                    <div class="date-input-box">
                        <i class="bi bi-calendar-event"></i>
                        <input type="text" id="startDateInput" value="01/10/2023" placeholder="dd/mm/yyyy">
                    </div>
                </div>
                <div class="date-arrow-icon">
                    <i class="bi bi-arrow-right"></i>
                </div>
                <div class="date-input-group">
                    <label for="endDateInput">ĐẾN NGÀY</label>
                    <div class="date-input-box">
                        <i class="bi bi-calendar-check"></i>
                        <input type="text" id="endDateInput" value="31/10/2023" placeholder="dd/mm/yyyy">
                    </div>
                </div>
            </div>
        </div>

        <div class="popover-footer">
            <button type="button" class="btn-popover-cancel" id="btnCancelDateFilter">Hủy bỏ</button>
            <button type="button" class="btn-popover-apply" id="btnApplyDateFilter">
                <span>Áp dụng</span>
                <i class="bi bi-check2"></i>
            </button>
        </div>
    </div>
</div>
