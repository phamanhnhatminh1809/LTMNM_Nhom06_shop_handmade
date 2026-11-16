window.AdminApp.registerModule('dashboard', ({ signal }) => {
    const revenueCtx = document.getElementById('revenueChart');
    const categoryCtx = document.getElementById('categoryDonutChart');
    if (!revenueCtx && !categoryCtx) return;
    if (typeof window.Chart === 'undefined') return;

    if (revenueCtx) {
        const prevRevChart = window.Chart.getChart(revenueCtx);
        if (prevRevChart) prevRevChart.destroy();
    }
    if (categoryCtx) {
        const prevCatChart = window.Chart.getChart(categoryCtx);
        if (prevCatChart) prevCatChart.destroy();
    }

    const chartData = window.dashboardChartData || {};
    const revenueSubLabel = document.getElementById('revenueChartSubLabel');
    let revenueChartInstance = null;

    const dataWeek = (chartData.revenueWeek && Array.isArray(chartData.revenueWeek.labels) && chartData.revenueWeek.labels.length > 0)
        ? chartData.revenueWeek
        : {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            data: [2.5, 3.8, 2.1, 4.3, 3.6, 5.8, 4.9]
        };

    const dataMonth = (chartData.revenueMonth && Array.isArray(chartData.revenueMonth.labels) && chartData.revenueMonth.labels.length > 0)
        ? chartData.revenueMonth
        : {
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
                    pointHoverRadius: 5,
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
                    if (revenueSubLabel) revenueSubLabel.textContent = '4 tuần gần nhất (Đơn vị: triệu VNĐ)';
                } else {
                    revenueChartInstance.data.labels = dataWeek.labels;
                    revenueChartInstance.data.datasets[0].data = dataWeek.data;
                    if (revenueSubLabel) revenueSubLabel.textContent = '7 ngày gần nhất (Đơn vị: triệu VNĐ)';
                }
                revenueChartInstance.update();
            }
        });
    });

    // Biểu đồ tròn Tỷ trọng theo danh mục
    let categoryChartInstance = null;
    if (categoryCtx) {
        const palette = ['#792a16', '#c49b4d', '#49624e', '#98412b', '#72594f', '#89726d'];
        const hasDynamicCat = chartData.categoryShare
            && Array.isArray(chartData.categoryShare.labels)
            && chartData.categoryShare.labels.length > 0;

        const donutLabels = hasDynamicCat
            ? chartData.categoryShare.labels
            : ['Gấu bông len', 'Móc khóa len', 'Bó hoa len'];
        const donutPercentages = hasDynamicCat
            ? chartData.categoryShare.percentages
            : [45, 30, 25];
        const donutColors = donutLabels.map((_, idx) => palette[idx % palette.length]);

        categoryChartInstance = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutPercentages,
                    backgroundColor: donutColors,
                    borderWidth: 0,
                    hoverOffset: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                onHover: (event, chartElement) => {
                    if (event.native && event.native.target) {
                        event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                    }
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
                                const soldCounts = hasDynamicCat && Array.isArray(chartData.categoryShare.sold_counts)
                                    ? chartData.categoryShare.sold_counts[context.dataIndex]
                                    : null;
                                const suffix = soldCounts !== null ? ` (${soldCounts} đã bán)` : '';
                                return ` ${context.label}: ${context.parsed}%${suffix}`;
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
                if (categoryChartInstance && index < donutLabels.length) {
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
    const btnExportRevenueXlsx = document.getElementById('btnExportRevenueXlsx');

    const padDate = (n) => String(n).padStart(2, '0');
    const formatDisplayDate = (d) => `${padDate(d.getDate())}/${padDate(d.getMonth() + 1)}/${d.getFullYear()}`;
    const toIsoDate = (raw) => {
        const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(raw || '').trim());
        return match ? `${match[3]}-${match[2]}-${match[1]}` : '';
    };

    if (btnDateFilter && dateFilterContainer) {
        const todayNow = new Date();
        if (startDateInput && startDateInput.value === '01/10/2023') {
            startDateInput.value = formatDisplayDate(todayNow);
        }
        if (endDateInput && endDateInput.value === '31/10/2023') {
            endDateInput.value = formatDisplayDate(todayNow);
        }

        const handleCustomDateInput = () => {
            presetButtons.forEach(b => b.classList.remove('active'));
        };
        startDateInput?.addEventListener('input', handleCustomDateInput, { signal });
        endDateInput?.addEventListener('input', handleCustomDateInput, { signal });

        btnDateFilter.addEventListener('click', (e) => {
            e.stopPropagation();
            dateFilterContainer.classList.toggle('show');
        });

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

        const popoverEl = document.getElementById('dateRangePopover');
        if (popoverEl) {
            popoverEl.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        document.addEventListener('click', () => {
            dateFilterContainer.classList.remove('show');
        }, { signal });

        presetButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                presetButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const preset = this.getAttribute('data-preset');
                const now = new Date();

                if (preset === 'today') {
                    startDateInput.value = formatDisplayDate(now);
                    endDateInput.value = formatDisplayDate(now);
                } else if (preset === '7days') {
                    const past7 = new Date();
                    past7.setDate(now.getDate() - 6);
                    startDateInput.value = formatDisplayDate(past7);
                    endDateInput.value = formatDisplayDate(now);
                } else if (preset === 'month') {
                    const startMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                    startDateInput.value = formatDisplayDate(startMonth);
                    endDateInput.value = formatDisplayDate(now);
                } else if (preset === 'year') {
                    const startYear = new Date(now.getFullYear(), 0, 1);
                    startDateInput.value = formatDisplayDate(startYear);
                    endDateInput.value = formatDisplayDate(now);
                }
            });
        });

        if (btnApplyDateFilter) {
            btnApplyDateFilter.addEventListener('click', () => {
                const activePreset = document.querySelector('.preset-btn.active');
                const exportBaseUrl = btnExportRevenueXlsx?.getAttribute('data-export-base') || '';
                const params = new URLSearchParams();

                if (activePreset) {
                    const spanText = activePreset.querySelector('span').textContent.trim();
                    const presetKey = activePreset.getAttribute('data-preset') || '';
                    selectedDateText.textContent = spanText;
                    if (presetKey) {
                        params.set('preset', presetKey);
                    }
                } else if (startDateInput && endDateInput) {
                    selectedDateText.textContent = `${startDateInput.value} - ${endDateInput.value}`;
                    const isoStart = toIsoDate(startDateInput.value);
                    const isoEnd = toIsoDate(endDateInput.value);
                    if (isoStart) params.set('start_date', isoStart);
                    if (isoEnd) params.set('end_date', isoEnd);
                }

                if (btnExportRevenueXlsx && exportBaseUrl) {
                    const qs = params.toString();
                    btnExportRevenueXlsx.href = qs ? `${exportBaseUrl}?${qs}` : exportBaseUrl;
                }

                dateFilterContainer.classList.remove('show');
            });
        }
    }
});
