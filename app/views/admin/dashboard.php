<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid admin-db">

        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title" style="font-size: 28px; font-weight: 700; color: #1b2533;">Tổng quan hệ thống
                    </h3>
                    <p class="text-muted mt-1 mb-0" style="font-size: 14px;">Theo dõi và phân tích các chỉ số quan trọng
                        của nền tảng.</p>
                </div>
                <div class="col-auto">
                    <span
                        style="font-size:13px;color:#0d4dc9;background:#e8f1ff;padding:.5rem 1rem;border-radius:99px;font-weight:600;">
                        <i class="fa fa-calendar-o" style="margin-right:.4rem;"></i>
                        <?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <style>
            .admin-db {
                padding: 24px;
                min-height: 100vh;
            }

            .ad-card {
                border: 1px solid #e8edf4;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 4px 15px rgba(16, 24, 40, 0.03);
                transition: transform 0.2s, box-shadow 0.2s;
                height: 100%;
            }

            .ad-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
            }

            .ad-card-body {
                padding: 20px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                height: 100%;
            }

            .ad-metric-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 16px;
            }

            .ad-icon-wrap {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .ad-icon-blue {
                background: #eff6ff;
                color: #2563eb;
            }

            .ad-icon-green {
                background: #f0fdf4;
                color: #16a34a;
            }

            .ad-icon-purple {
                background: #faf5ff;
                color: #9333ea;
            }

            .ad-icon-orange {
                background: #fff7ed;
                color: #ea580c;
            }

            .ad-icon-cyan {
                background: #ecfeff;
                color: #06b6d4;
            }

            .ad-metric-label {
                font-size: 13px;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .ad-metric-value {
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                margin-top: 8px;
                line-height: 1.2;
            }

            .ad-panel-header {
                padding: 16px 20px;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .ad-panel-title {
                font-size: 15px;
                font-weight: 700;
                color: #1e293b;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .ad-panel-body {
                padding: 20px;
            }

            .ad-chart-container {
                position: relative;
                height: 320px;
                width: 100%;
            }

            .ad-chart-container-sm {
                position: relative;
                height: 250px;
                width: 100%;
            }

            .ad-quick-action {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px;
                border-radius: 12px;
                color: #fff;
                text-decoration: none;
                transition: all 0.2s;
                height: 100%;
                gap: 12px;
            }

            .ad-quick-action:hover {
                transform: translateY(-3px);
                color: #fff;
            }

            .ad-qa-blue {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
            }

            .ad-qa-cyan {
                background: linear-gradient(135deg, #0ea5e9, #0284c7);
                box-shadow: 0 6px 15px rgba(14, 165, 233, 0.25);
            }

            .ad-qa-green {
                background: linear-gradient(135deg, #10b981, #059669);
                box-shadow: 0 6px 15px rgba(16, 185, 129, 0.25);
            }

            .ad-qa-orange {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                box-shadow: 0 6px 15px rgba(245, 158, 11, 0.25);
            }
        </style>

        <!-- ROW 1: Key Metrics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 col-12 mb-3 mb-xl-0">
                <div class="ad-card">
                    <div class="ad-card-body">
                        <div class="ad-metric-top">
                            <div class="ad-icon-wrap ad-icon-blue"><i class="fa fa-users"></i></div>
                            <span
                                style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">TOTAL</span>
                        </div>
                        <div>
                            <div class="ad-metric-label">Tổng người dùng</div>
                            <div class="ad-metric-value"><?= (int) ($stats['users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12 mb-3 mb-xl-0">
                <div class="ad-card">
                    <div class="ad-card-body">
                        <div class="ad-metric-top">
                            <div class="ad-icon-wrap ad-icon-purple"><i class="fa fa-id-badge"></i></div>
                            <span
                                style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">AGENT</span>
                        </div>
                        <div>
                            <div class="ad-metric-label">Tổng môi giới</div>
                            <div class="ad-metric-value"><?= (int) ($stats['agents'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12 mb-3 mb-sm-0">
                <div class="ad-card">
                    <div class="ad-card-body">
                        <div class="ad-metric-top">
                            <div class="ad-icon-wrap ad-icon-cyan"><i class="fa fa-home"></i></div>
                            <span
                                style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">PROPERTIES</span>
                        </div>
                        <div>
                            <div class="ad-metric-label">Tổng bất động sản</div>
                            <div class="ad-metric-value"><?= (int) ($stats['properties'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="ad-card">
                    <div class="ad-card-body">
                        <div class="ad-metric-top">
                            <div class="ad-icon-wrap ad-icon-green"><i class="fa fa-exchange"></i></div>
                            <span
                                style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">DEALS</span>
                        </div>
                        <div>
                            <div class="ad-metric-label">Tổng giao dịch</div>
                            <div class="ad-metric-value"><?= (int) ($stats['total_transactions'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: Main Charts -->
        <div class="row mb-4">
            <div class="col-xl-8 col-12 mb-3 mb-xl-0">
                <div class="ad-card h-100">
                    <div class="ad-panel-header">
                        <h4 class="ad-panel-title"><i class="fa fa-line-chart text-primary"></i> Giao dịch theo thời
                            gian (30 ngày)</h4>
                    </div>
                    <div class="ad-panel-body">
                        <?php 
                        $txRowsCount = isset($stats['chart_transactions_by_day']) ? count($stats['chart_transactions_by_day']) : 0; 
                        $txMaxWidth = ($txRowsCount > 0 && $txRowsCount <= 4) ? 'max-width: ' . max(300, $txRowsCount * 120) . 'px; margin: 0 auto;' : '';
                        ?>
                        <div class="ad-chart-container" style="<?= $txMaxWidth ?>">
                            <canvas id="txTimelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-12">
                <div class="ad-card h-100">
                    <div class="ad-panel-header">
                        <h4 class="ad-panel-title"><i class="fa fa-building text-info"></i> Cơ cấu loại hình BĐS</h4>
                    </div>
                    <div class="ad-panel-body">
                        <div class="ad-chart-container-sm mb-4">
                            <canvas id="propertyTypeDoughnut"></canvas>
                        </div>
                        <div class="row">
                            <?php
                            $ptCards = isset($stats['property_type_cards']) && is_array($stats['property_type_cards']) ? $stats['property_type_cards'] : [];
                            $colors = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#64748b'];
                            foreach ($ptCards as $index => $card):
                                $color = $colors[$index % count($colors)];
                                $label = htmlspecialchars((string) ($card['label'] ?? ''), ENT_QUOTES, 'UTF-8');
                                $count = (int) ($card['count'] ?? 0);
                                ?>
                                <div class="col-6 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div
                                            style="width: 12px; height: 12px; border-radius: 50%; background-color: <?= $color ?>; flex-shrink: 0;">
                                        </div>
                                        <div style="overflow: hidden;">
                                            <div style="font-size: 12px; color: #64748b; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"
                                                title="<?= $label ?>"><?= $label ?></div>
                                            <div
                                                style="font-size: 16px; font-weight: 700; color: #0f172a; line-height: 1; margin-top: 2px;">
                                                <?= $count ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: Secondary Charts -->
        <div class="row mb-4">
            <div class="col-xl-6 col-12 mb-3 mb-xl-0">
                <div class="ad-card h-100">
                    <div class="ad-panel-header">
                        <h4 class="ad-panel-title"><i class="fa fa-map-marker text-danger"></i> Bài đăng theo khu vực
                            (Top 10)</h4>
                    </div>
                    <div class="ad-panel-body">
                        <div class="ad-chart-container">
                            <canvas id="areaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-12">
                <div class="ad-card h-100">
                    <div class="ad-panel-header">
                        <h4 class="ad-panel-title"><i class="fa fa-money text-warning"></i> Giá trung bình theo loại BĐS
                        </h4>
                    </div>
                    <div class="ad-panel-body">
                        <?php 
                        $avgRowsCount = isset($stats['chart_avg_price_by_type']) ? count($stats['chart_avg_price_by_type']) : 0; 
                        $avgMaxWidth = ($avgRowsCount > 0 && $avgRowsCount <= 4) ? 'max-width: ' . max(300, $avgRowsCount * 120) . 'px; margin: 0 auto;' : '';
                        ?>
                        <div class="ad-chart-container" style="<?= $avgMaxWidth ?>">
                            <canvas id="priceTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 4: Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="ad-card">
                    <div class="ad-panel-header">
                        <h4 class="ad-panel-title"><i class="fa fa-bolt text-warning"></i> Thao tác nhanh</h4>
                    </div>
                    <div class="ad-panel-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <a href="<?= BASEURL ?>/adminProperty/add" class="ad-quick-action ad-qa-blue">
                                    <i class="fa fa-plus-circle" style="font-size: 28px;"></i>
                                    <span style="font-size: 13px; font-weight: 600; text-align: center;">Thêm BĐS</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASEURL ?>/adminProperty/index" class="ad-quick-action ad-qa-cyan">
                                    <i class="fa fa-list" style="font-size: 28px;"></i>
                                    <span style="font-size: 13px; font-weight: 600; text-align: center;">DS BĐS</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASEURL ?>/adminUser/user" class="ad-quick-action ad-qa-green">
                                    <i class="fa fa-users" style="font-size: 28px;"></i>
                                    <span style="font-size: 13px; font-weight: 600; text-align: center;">Người
                                        dùng</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASEURL ?>/adminFeedback/index" class="ad-quick-action ad-qa-orange">
                                    <i class="fa fa-star" style="font-size: 28px;"></i>
                                    <span style="font-size: 13px; font-weight: 600; text-align: center;">Phản hồi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- /Page Wrapper -->

<!-- Chart Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js is not loaded.');
            return;
        }

        // Common options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 14, weight: 'bold' },
                    cornerRadius: 8,
                    displayColors: true
                }
            }
        };

        // 1. Transaction Timeline Chart (Bar)
        <?php
        $txRows = isset($stats['chart_transactions_by_day']) && is_array($stats['chart_transactions_by_day']) ? $stats['chart_transactions_by_day'] : [];
        $txLabels = [];
        $txData = [];
        foreach ($txRows as $r) {
            $txLabels[] = $r['label'] ?? '';
            $txData[] = (int) ($r['total'] ?? 0);
        }
        ?>
        const txCtx = document.getElementById('txTimelineChart');
        if (txCtx) {
            new Chart(txCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($txLabels) ?>,
                    datasets: [{
                        label: 'Giao dịch',
                        data: <?= json_encode($txData) ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 40,
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { precision: 0 } },
                        x: { grid: { display: false }, border: { display: false } }
                    }
                }
            });
        }

        // 2. Transaction Type Chart (Doughnut)
        const typeCtx = document.getElementById('txTypeChart');
        if (typeCtx) {
            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Đang bán', 'Cho thuê'],
                    datasets: [{
                        data: [<?= (int) ($stats['sales'] ?? 0) ?>, <?= (int) ($stats['rents'] ?? 0) ?>],
                        backgroundColor: ['#10b981', '#3b82f6'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: commonOptions.plugins.tooltip
                    }
                }
            });
        }

        // 3. Area Chart (Horizontal Bar)
        <?php
        $areaRows = isset($stats['chart_properties_by_area']) && is_array($stats['chart_properties_by_area']) ? $stats['chart_properties_by_area'] : [];
        $areaRows = array_slice($areaRows, 0, 10);
        $areaLabels = [];
        $areaData = [];
        foreach ($areaRows as $r) {
            $areaLabels[] = $r['label'] ?? '';
            $areaData[] = (int) ($r['total'] ?? 0);
        }
        ?>
        const areaCtx = document.getElementById('areaChart');
        if (areaCtx) {
            new Chart(areaCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($areaLabels) ?>,
                    datasets: [{
                        label: 'Số bài đăng',
                        data: <?= json_encode($areaData) ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 30,
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    scales: {
                        y: { grid: { display: false }, border: { display: false } },
                        x: { beginAtZero: true, grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 4. Price by Type Chart (Bar)
        <?php
        $avgRows = isset($stats['chart_avg_price_by_type']) && is_array($stats['chart_avg_price_by_type']) ? $stats['chart_avg_price_by_type'] : [];
        $avgLabels = [];
        $avgData = [];
        foreach ($avgRows as $r) {
            $avgLabels[] = $r['label'] ?? '';
            $avgData[] = (float) ($r['avg_price'] ?? 0);
        }
        ?>
        const priceCtx = document.getElementById('priceTypeChart');
        if (priceCtx) {
            new Chart(priceCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($avgLabels) ?>,
                    datasets: [{
                        label: 'Giá TB',
                        data: <?= json_encode($avgData) ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderColor: 'rgb(245, 158, 11)',
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 40,
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            border: { display: false },
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000000) return (value / 1000000000).toFixed(1) + ' Tỷ';
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Tr';
                                    return value;
                                }
                            }
                        },
                        x: { grid: { display: false }, border: { display: false } }
                    },
                    plugins: {
                        tooltip: {
                            ...commonOptions.plugins.tooltip,
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 5. Property Type Doughnut
        <?php
        $ptLabels = [];
        $ptData = [];
        foreach ($ptCards as $card) {
            $ptLabels[] = $card['label'] ?? '';
            $ptData[] = (int) ($card['count'] ?? 0);
        }
        ?>
        const ptCtx = document.getElementById('propertyTypeDoughnut');
        if (ptCtx) {
            new Chart(ptCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($ptLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($ptData) ?>,
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: commonOptions.plugins.tooltip
                    }
                }
            });
        }
    });
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>