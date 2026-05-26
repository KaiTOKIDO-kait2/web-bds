<?php
$overviewMetrics = isset($data['overviewMetrics']) && is_array($data['overviewMetrics']) ? $data['overviewMetrics'] : [];
$leadTrend = isset($data['leadTrend']) && is_array($data['leadTrend']) ? $data['leadTrend'] : [];
$transactionStatus = isset($data['transactionStatus']) && is_array($data['transactionStatus']) ? $data['transactionStatus'] : [];
$recentLeads = isset($data['recentLeads']) && is_array($data['recentLeads']) ? $data['recentLeads'] : [];
$recentTransactions = isset($data['recentTransactions']) && is_array($data['recentTransactions']) ? $data['recentTransactions'] : [];

$totalLeads = (int) ($overviewMetrics['totalLeads'] ?? 0);
$newLeadsToday = (int) ($overviewMetrics['newLeadsToday'] ?? 0);
$consultingTransactions = (int) ($overviewMetrics['consultingTransactions'] ?? 0);
$closedTransactions = (int) ($overviewMetrics['closedTransactions'] ?? 0);
$cancelledTransactions = (int) ($overviewMetrics['cancelledTransactions'] ?? 0);
$conversionRate = (float) ($overviewMetrics['conversionRate'] ?? 0);

$transactionTotal = max(
    1,
    (int) ($transactionStatus['consulting'] ?? 0) + (int) ($transactionStatus['closed'] ?? 0) + (int) ($transactionStatus['cancelled'] ?? 0)
);

$consultingPercent = (int) round(((int) ($transactionStatus['consulting'] ?? 0) / $transactionTotal) * 100);
$closedPercent = (int) round(((int) ($transactionStatus['closed'] ?? 0) / $transactionTotal) * 100);
$cancelledPercent = max(0, 100 - $consultingPercent - $closedPercent);
$transactionConsultingCount = (int) ($transactionStatus['consulting'] ?? 0);
$transactionClosedCount = (int) ($transactionStatus['closed'] ?? 0);
$transactionCancelledCount = (int) ($transactionStatus['cancelled'] ?? 0);
$transactionTotalCount = $transactionConsultingCount + $transactionClosedCount + $transactionCancelledCount;

$maxLeadCount = 1;
if (!empty($leadTrend)) {
    foreach ($leadTrend as $trendItem) {
        $maxLeadCount = max($maxLeadCount, (int) ($trendItem['count'] ?? 0));
    }
}

if (!function_exists('agentOverviewTimeLabel')) {
    function agentOverviewTimeLabel($value)
    {
        $timestamp = strtotime((string) $value);
        if (!$timestamp) {
            return '-';
        }
        return date('d/m/Y', $timestamp);
    }
}

if (!function_exists('agentOverviewStatusBadge')) {
    function agentOverviewStatusBadge($status)
    {
        $status = strtolower((string) $status);
        if ($status === 'completed') {
            return ['label' => 'Đã chốt', 'class' => 'badge badge-success'];
        }
        if ($status === 'cancelled') {
            return ['label' => 'Đã hủy', 'class' => 'badge badge-danger'];
        }
        if (in_array($status, ['contacted', 'scheduled'], true)) {
            return ['label' => 'Đang tư vấn', 'class' => 'badge badge-warning'];
        }
        return ['label' => 'Đang chờ', 'class' => 'badge badge-secondary'];
    }
}
?>

<style>
    /* Scoped styles: only affect overview section */
    .ao-overview {
        background: radial-gradient(circle at 20% 10%, #f5f8ff 0%, #f8f9fc 45%, #ffffff 100%);
        border-radius: 14px;
        padding: 20px;
    }

    .ao-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .ao-eyebrow {
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        color: #0d4dc9;
        margin-bottom: 6px;
    }

    .ao-title {
        font-size: 28px;
        line-height: 1.2;
        font-weight: 800;
        color: #1b2533;
        margin: 0;
    }

    .ao-subtitle {
        font-size: 14px;
        color: #5e6b79;
        margin: 8px 0 0;
    }

    .ao-metric-card {
        border: 1px solid #e8edf4;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(16, 24, 40, 0.04);
        min-height: 154px;
    }

    .ao-metric-card .card-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ao-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .ao-icon-chip {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .ao-chip-blue {
        background: #e8f1ff;
        color: #0040a1;
    }

    .ao-chip-orange {
        background: #fff1e8;
        color: #c65a00;
    }

    .ao-chip-purple {
        background: #f3ecff;
        color: #6a35c4;
    }

    .ao-chip-green {
        background: #eaf9ef;
        color: #238a4b;
    }

    .ao-chip-red {
        background: #feeced;
        color: #c33442;
    }

    .ao-chip-navy {
        background: #e8f1ff;
        color: #123a8f;
    }

    .ao-metric-hint {
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .ao-metric-label {
        font-size: 12px;
        color: #6b7583;
        line-height: 1.3;
        font-weight: 500;
    }

    .ao-metric-value {
        font-size: 25px;
        line-height: 1.1;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #111827;
        margin-top: 4px;
    }

    .ao-panel {
        border: 1px solid #e8edf4;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(16, 24, 40, 0.04);
    }

    .ao-panel .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f7;
        font-weight: 700;
        color: #1f2937;
    }

    .ao-panel .table thead th {
        border-top: 0;
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .ao-panel .table tbody tr:hover {
        background: #f8fbff;
    }

    .ao-panel-scroll {
        max-height: 320px;
        overflow-y: auto;
    }

    .ao-panel-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .ao-panel-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .ao-panel-scroll::-webkit-scrollbar-thumb {
        background: #cbd5f5;
        border-radius: 999px;
    }

    .ao-panel-scroll:hover::-webkit-scrollbar-thumb {
        background: #a5b4fc;
    }

    .ao-link {
        font-size: 12px;
        font-weight: 700;
        color: #0d4dc9;
    }

    .ao-donut-wrap {
        position: relative;
        width: 190px;
        height: 190px;
        margin: 6px auto 14px;
    }

    .ao-donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        pointer-events: none;
    }

    .ao-donut-total {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: #101828;
    }

    .ao-donut-sub {
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #8a93a1;
        margin-top: 4px;
    }

    .ao-donut-legend {
        margin-top: 8px;
    }

    .ao-donut-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        padding: 5px 0;
    }

    .ao-donut-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475467;
    }

    .ao-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .ao-dot-consulting {
        background: #f59e0b;
    }

    .ao-dot-closed {
        background: #22c55e;
    }

    .ao-dot-cancelled {
        background: #ef4444;
    }

    .ao-table-heading {
        white-space: nowrap;
    }

    .ao-table-name,
    .ao-table-contact {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 240px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #1f2937;
        font-weight: 600;
        font-size: 13px;
    }

    .ao-table-name i,
    .ao-table-contact i {
        color: #2563eb;
        font-size: 16px;
        flex-shrink: 0;
    }

    .ao-table-contact {
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        .ao-overview {
            padding: 14px;
        }

        .ao-title {
            font-size: 22px;
        }

        .ao-metric-card {
            min-height: 132px;
        }

        .ao-metric-value {
            font-size: 28px;
        }
    }
</style>

<section class="agent-section ao-overview">
    <div class="ao-head">
        <div>
            <div class="ao-eyebrow">Agent Workspace</div>
            <h3 class="ao-title">Tổng quan hiệu suất</h3>
            <p class="ao-subtitle">Theo dõi KPI lead, giao dịch và tiến độ xử lý theo thời gian thực.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-blue"><i class="fa fa-users" aria-hidden="true"></i></span>
                        <span class="ao-metric-hint">+0%</span>
                    </div>
                    <div class="ao-metric-label">Tổng lead</div>
                    <div class="ao-metric-value"><?= $totalLeads ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-orange"><i class="fa fa-calendar"
                                aria-hidden="true"></i></span>
                        <span class="ao-metric-hint">today</span>
                    </div>
                    <div class="ao-metric-label">Lead mới hôm nay</div>
                    <div class="ao-metric-value"><?= $newLeadsToday ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-purple"><i class="fa fa-handshake"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="ao-metric-label">Đang tư vấn</div>
                    <div class="ao-metric-value"><?= $consultingTransactions ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-green"><i class="fa fa-check-circle"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="ao-metric-label">Đã chốt</div>
                    <div class="ao-metric-value"><?= $closedTransactions ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-red"><i class="fa fa-times-circle"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="ao-metric-label">Đã hủy</div>
                    <div class="ao-metric-value"><?= $cancelledTransactions ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2 mb-3">
            <div class="card ao-metric-card h-100 border-primary">
                <div class="card-body">
                    <div class="ao-metric-top">
                        <span class="ao-icon-chip ao-chip-navy"><i class="fa fa-chart-line"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="ao-metric-label">Tỷ lệ chuyển đổi</div>
                    <div class="ao-metric-value text-primary"><?= number_format($conversionRate, 1) ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-lg-8 mb-3">
            <div class="card ao-panel h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="h6 mb-0">Lead theo thời gian (7 ngày gần nhất)</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($leadTrend)): ?>
                        <canvas id="leadTrendChart" style="height: 240px;"></canvas>
                    <?php else: ?>
                        <div class="text-muted">Chưa có dữ liệu biểu đồ lead.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card ao-panel h-100">
                <div class="card-header">
                    <h4 class="h6 mb-0">Yêu cầu theo trạng thái</h4>
                </div>
                <div class="card-body">
                    <div class="ao-donut-wrap">
                        <canvas id="transactionStatusChart" aria-label="Transaction status pie chart"
                            role="img"></canvas>
                        <div class="ao-donut-center">
                            <div class="ao-donut-total"><?= $transactionTotalCount ?></div>
                            <div class="ao-donut-sub">Tổng</div>
                        </div>
                    </div>

                    <div class="ao-donut-legend">
                        <div class="ao-donut-item">
                            <span class="ao-donut-label"><span class="ao-dot ao-dot-consulting"></span>Đang tư vấn</span>
                            <strong><?= $consultingPercent ?>%</strong>
                        </div>
                        <div class="ao-donut-item">
                            <span class="ao-donut-label"><span class="ao-dot ao-dot-closed"></span>Đã chốt</span>
                            <strong><?= $closedPercent ?>%</strong>
                        </div>
                        <div class="ao-donut-item">
                            <span class="ao-donut-label"><span class="ao-dot ao-dot-cancelled"></span>Đã hủy</span>
                            <strong><?= $cancelledPercent ?>%</strong>
                        </div>
                    </div>

                    <div class="small text-muted mt-2">Tổng transaction: <?= $transactionTotalCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-6 mb-3">
            <div class="card ao-panel h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="h6 mb-0">Lead mới nhất</h4>
                    <a href="<?= BASEURL ?>/agentWorkspace/index?section=leads" class="ao-link">Xem tất cả</a>
                </div>
                <div class="table-responsive ao-panel-scroll">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ao-table-heading">Khách hàng</th>
                                <th class="ao-table-heading">Số điện thoại</th>
                                <th>Bất động sản</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentLeads)): ?>
                                <?php foreach ($recentLeads as $lead): ?>
                                    <?php
                                    $leadNameRaw = trim((string)($lead['inquirer_name'] ?? ($lead['name'] ?? '')));
                                    $leadName = $leadNameRaw !== '' ? $leadNameRaw : 'Không rõ';
                                    $leadContactRaw = trim((string)($lead['phone'] ?? $lead['work_email'] ?? ''));
                                    $leadContact = $leadContactRaw !== '' ? $leadContactRaw : '--';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="ao-table-name" title="<?= htmlspecialchars($leadName) ?>">
                                                <i class="fa fa-user-circle" aria-hidden="true"></i>
                                                <span><?= htmlspecialchars($leadName) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="ao-table-contact" title="<?= htmlspecialchars($leadContact) ?>">
                                                <span><?= htmlspecialchars($leadContact) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($lead['property_title'] ?? $lead['title'] ?? 'N/A')) ?>
                                        </td>
                                        <td><?= agentOverviewTimeLabel($lead['created_at'] ?? $lead['date'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Chưa có lead mới.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6 mb-3">
            <div class="card ao-panel h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="h6 mb-0">Yêu cầu gần đây</h4>
                    <a href="<?= BASEURL ?>/agentWorkspace/index?section=transactions" class="ao-link">Xem tất cả</a>
                </div>
                <div class="table-responsive ao-panel-scroll">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Bất động sản</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th>Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentTransactions)): ?>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <?php $statusMeta = agentOverviewStatusBadge($transaction['case_status'] ?? 'new'); ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($transaction['property_title'] ?? $transaction['title'] ?? 'N/A')) ?>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($transaction['inquirer_name'] ?? ($transaction['name'] ?? 'Khách hàng'))) ?></td>
                                        <td><span class="<?= $statusMeta['class'] ?>"><?= $statusMeta['label'] ?></span></td>
                                        <td><?= agentOverviewTimeLabel($transaction['created_at'] ?? $transaction['date'] ?? '') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Chưa có transaction gần đây.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($leadTrend)): ?>
    <script>
        (function () {
            const ctx = document.getElementById('leadTrendChart');
            if (!ctx) return;

            const labels = <?= json_encode(array_map(function ($item) {
                return htmlspecialchars((string) ($item['label'] ?? '-'));
            }, $leadTrend)) ?>;
            const data = <?= json_encode(array_map(function ($item) {
                return (int) ($item['count'] ?? 0);
            }, $leadTrend)) ?>;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số lead',
                        data: data,
                        backgroundColor: 'rgba(0, 64, 161, 0.7)',
                        borderColor: 'rgba(0, 64, 161, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    datasets: {
                        bar: {
                            barPercentage: 0.65,
                            categoryPercentage: 0.9
                        }
                    }
                }
            });
        })();
    </script>
<?php endif; ?>

<script>
    (function () {
        const ctx = document.getElementById('transactionStatusChart');
        if (!ctx || typeof Chart === 'undefined') return;

        const transactionData = [
            <?= $transactionConsultingCount ?>,
            <?= $transactionClosedCount ?>,
            <?= $transactionCancelledCount ?>
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Đang tư vấn', 'Đã chốt', 'Đã hủy'],
                datasets: [{
                    data: transactionData,
                    backgroundColor: ['#f59e0b', '#22c55e', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.82)',
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    })();
</script>