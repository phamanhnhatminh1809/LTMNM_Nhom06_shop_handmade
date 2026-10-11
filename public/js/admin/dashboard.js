document.addEventListener('DOMContentLoaded', () => {
    // Dữ liệu mẫu biểu đồ Doanh thu
    const revenueCtx = document.getElementById('revenueChart');
    let revenueChartInstance = null;

    const dataWeek = {
        labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
        data: [2.5, 3.8, 2.1, 4.3, 3.6, 5.8, 4.9]
    };

    const dataMonth = {
        labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
        data: [18.2, 22.5, 20.1, 26.8]
    };

    if (revenueCtx) {
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: dataWeek.labels,
                datasets: [{
                    label: 'Doanh thu (triệu VNĐ)',
                    data: dataWeek.data,
                    borderColor: '#792a16',
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(121, 42, 22, 0.28)');
                        gradient.addColorStop(1, 'rgba(255, 248, 246, 0.02)');
                        return gradient;
                    },
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#792a16',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 4,
                    pointHitRadius: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onHover: (event, chartElement) => {
                    const canvas = event.native ? event.native.target : revenueCtx;
                    if (canvas) {
                        canvas.style.cursor = (chartElement && chartElement.length > 0) ? 'pointer' : 'default';
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.parsed.y + ' triệu VNĐ';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '700' }, color: '#55423e' }
                    },
                    y: {
                        display: false,
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Toggle chuyển đổi Tuần / Tháng
    const periodButtons = document.querySelectorAll('.btn-period');
    periodButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            periodButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const period = this.getAttribute('data-period');
            if (revenueChartInstance) {
                if (period === 'month') {
                    revenueChartInstance.data.labels = dataMonth.labels;
                    revenueChartInstance.data.datasets[0].data = dataMonth.data;
                } else {
                    revenueChartInstance.data.labels = dataWeek.labels;
                    revenueChartInstance.data.datasets[0].data = dataWeek.data;
                }
                revenueChartInstance.update();
            }
        });
    });

    // Biểu đồ tròn Doanh thu theo danh mục
    const categoryCtx = document.getElementById('categoryDonutChart');
    let categoryChartInstance = null;
    if (categoryCtx) {
        categoryChartInstance = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Gấu bông len', 'Móc khóa len', 'Bó hoa len'],
                datasets: [{
                    data: [45, 30, 25],
                    backgroundColor: [
                        '#792a16',
                        '#c49b4d',
                        '#49624e'
                    ],
                    borderWidth: 0,
                    hoverOffset: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                onHover: (event, chartElement) => {
                    event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(37, 25, 20, 0.96)',
                        titleFont: { size: 12, weight: '700' },
                        bodyFont: { size: 12, weight: '600' },
                        padding: 10,
                        cornerRadius: 8,
                        boxPadding: 4,
                        position: 'nearest',
                        yAlign: 'bottom',
                        callbacks: {
                            label: function (context) {
                                return ` ${context.label}: ${context.parsed}%`;
                            }
                        }
                    }
                }
            }
        });

        // Hover vào các legend-item sẽ trigger highlight trên biểu đồ tròn
        const legendItems = document.querySelectorAll('.legend-item');
        legendItems.forEach((item, index) => {
            item.addEventListener('mouseenter', () => {
                if (categoryChartInstance) {
                    categoryChartInstance.setActiveElements([{ datasetIndex: 0, index: index }]);
                    categoryChartInstance.tooltip.setActiveElements([{ datasetIndex: 0, index: index }]);
                    categoryChartInstance.update();
                }
            });
            item.addEventListener('mouseleave', () => {
                if (categoryChartInstance) {
                    categoryChartInstance.setActiveElements([]);
                    categoryChartInstance.tooltip.setActiveElements([]);
                    categoryChartInstance.update();
                }
            });
        });
    }



    // Xử lý Dropdown / Popover chọn khoảng thời gian
    const dateFilterContainer = document.querySelector('.date-filter-container');
    const btnDateFilter = document.getElementById('btnDateFilter');
    const btnCloseDatePopover = document.getElementById('btnCloseDatePopover');
    const btnCancelDateFilter = document.getElementById('btnCancelDateFilter');
    const btnApplyDateFilter = document.getElementById('btnApplyDateFilter');
    const selectedDateText = document.getElementById('selectedDateText');
    const presetButtons = document.querySelectorAll('.preset-btn');
    const startDateInput = document.getElementById('startDateInput');
    const endDateInput = document.getElementById('endDateInput');

    if (btnDateFilter && dateFilterContainer) {
        // Toggle mở / đóng popover
        btnDateFilter.addEventListener('click', (e) => {
            e.stopPropagation();
            dateFilterContainer.classList.toggle('show');
        });

        // Đóng popover khi bấm dấu X hoặc nút Hủy bỏ
        if (btnCloseDatePopover) {
            btnCloseDatePopover.addEventListener('click', () => {
                dateFilterContainer.classList.remove('show');
            });
        }
        if (btnCancelDateFilter) {
            btnCancelDateFilter.addEventListener('click', () => {
                dateFilterContainer.classList.remove('show');
            });
        }

        // Chặn đóng khi click bên trong popover
        const popoverEl = document.getElementById('dateRangePopover');
        if (popoverEl) {
            popoverEl.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Đóng popover khi click ra ngoài màn hình
        document.addEventListener('click', () => {
            dateFilterContainer.classList.remove('show');
        });

        // Chọn các nút có sẵn
        presetButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                presetButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const preset = this.getAttribute('data-preset');
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const formatDate = (d) => `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;

                if (preset === 'today') {
                    startDateInput.value = formatDate(now);
                    endDateInput.value = formatDate(now);
                } else if (preset === '7days') {
                    const past7 = new Date();
                    past7.setDate(now.getDate() - 7);
                    startDateInput.value = formatDate(past7);
                    endDateInput.value = formatDate(now);
                } else if (preset === 'month') {
                    const startMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                    startDateInput.value = formatDate(startMonth);
                    endDateInput.value = formatDate(now);
                } else if (preset === 'year') {
                    const startYear = new Date(now.getFullYear(), 0, 1);
                    startDateInput.value = formatDate(startYear);
                    endDateInput.value = formatDate(now);
                }
            });
        });

        // Bấm nút Áp dụng
        if (btnApplyDateFilter) {
            btnApplyDateFilter.addEventListener('click', () => {
                const activePreset = document.querySelector('.preset-btn.active');
                if (activePreset) {
                    const spanText = activePreset.querySelector('span').textContent.trim();
                    selectedDateText.textContent = spanText;
                } else if (startDateInput && endDateInput) {
                    selectedDateText.textContent = `${startDateInput.value} - ${endDateInput.value}`;
                }
                dateFilterContainer.classList.remove('show');
            });
        }
    }
});
