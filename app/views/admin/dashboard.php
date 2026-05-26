<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Bảng điều khiển</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Tổng quan</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <span style="font-size:12.5px;color:#64748b;background:#f1f5f9;padding:.4rem .9rem;border-radius:99px;border:1px solid #e2e8f0;">
                        <i class="fa fa-calendar-o" style="margin-right:.4rem;"></i>
                        <?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- ======= ROW 1: KPIs chính ======= -->
        <div class="row">
            <!-- Người dùng -->
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-blue">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Người dùng</div>
                            <div class="stat-number"><?= $stats['users'] ?></div>
                            <span class="stat-trend neutral"><i class="fa fa-users"></i> Tổng</span>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Môi giới -->
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-green">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Môi giới</div>
                            <div class="stat-number"><?= $stats['agents'] ?></div>
                            <span class="stat-trend up"><i class="fa fa-id-badge"></i> Agent</span>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fa fa-id-badge"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chính chủ -->
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-orange">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Chính chủ</div>
                            <div class="stat-number"><?= $stats['owners'] ?? 0 ?></div>
                            <span class="stat-trend neutral"><i class="fa fa-home"></i> Owner</span>
                        </div>
                        <div class="stat-icon icon-orange">
                            <i class="fa fa-home"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tổng BĐS -->
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-cyan">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Tổng BĐS</div>
                            <div class="stat-number"><?= $stats['properties'] ?></div>
                            <span class="stat-trend neutral"><i class="fa fa-home"></i> Bất động sản</span>
                        </div>
                        <div class="stat-icon icon-cyan">
                            <i class="fa fa-home"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /ROW 1 -->

        <!-- ======= ROW 2: Phân loại BĐS + Hình thức ======= -->
        <div class="row">
            <div class="col-xl-8 col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fa fa-pie-chart" style="color:#2563eb;margin-right:.5rem;"></i>
                            Phân loại bất động sản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $typeCardTones = [
                                1 => ['bg' => '#eff6ff', 'bd' => '#dbeafe', 'ibg' => '#dbeafe', 'ic' => '#2563eb'],
                                2 => ['bg' => '#faf5ff', 'bd' => '#e9d5ff', 'ibg' => '#ede9fe', 'ic' => '#8b5cf6'],
                                3 => ['bg' => '#f0fdf4', 'bd' => '#bbf7d0', 'ibg' => '#dcfce7', 'ic' => '#10b981'],
                                4 => ['bg' => '#fff7ed', 'bd' => '#fed7aa', 'ibg' => '#ffedd5', 'ic' => '#ea580c'],
                                5 => ['bg' => '#fef2f2', 'bd' => '#fecaca', 'ibg' => '#fee2e2', 'ic' => '#dc2626'],
                                6 => ['bg' => '#fefce8', 'bd' => '#fef08a', 'ibg' => '#fef9c3', 'ic' => '#f59e0b'],
                                7 => ['bg' => '#ecfeff', 'bd' => '#a5f3fc', 'ibg' => '#cffafe', 'ic' => '#0891b2'],
                            ];
                            $ptCards = isset($stats['property_type_cards']) && is_array($stats['property_type_cards']) ? $stats['property_type_cards'] : [];
                            foreach ($ptCards as $card):
                                $tid = (int) ($card['id'] ?? 0);
                                $tone = $typeCardTones[$tid] ?? ['bg' => '#f8fafc', 'bd' => '#e2e8f0', 'ibg' => '#f1f5f9', 'ic' => '#64748b'];
                                $icon = htmlspecialchars((string) ($card['icon'] ?? 'fa-circle'), ENT_QUOTES, 'UTF-8');
                                ?>
                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                <div style="text-align:center;padding:1rem .5rem;background:<?= $tone['bg'] ?>;border-radius:10px;border:1px solid <?= $tone['bd'] ?>;">
                                    <div style="width:44px;height:44px;background:<?= $tone['ibg'] ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                                        <i class="fa <?= $icon ?>" style="color:<?= $tone['ic'] ?>;font-size:18px;"></i>
                                    </div>
                                    <div style="font-size:1.6rem;font-weight:700;color:#0f172a;line-height:1;"><?= (int) ($card['count'] ?? 0) ?></div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:.3rem;font-weight:500;"><?= htmlspecialchars((string) ($card['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hình thức giao dịch -->
            <div class="col-xl-4 col-12">
                <div class="card" style="height:calc(100% - 1.25rem);">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fa fa-exchange" style="color:#2563eb;margin-right:.5rem;"></i>
                            Hình thức giao dịch
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center" style="gap:1rem;">
                        <!-- Đang bán -->
                        <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                            <div style="width:48px;height:48px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa fa-tag" style="color:#10b981;font-size:20px;"></i>
                            </div>
                            <div>
                                <div style="font-size:11.5px;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.04em;">Đang bán</div>
                                <div style="font-size:1.8rem;font-weight:700;color:#0f172a;line-height:1.1;"><?= $stats['sales'] ?></div>
                            </div>
                            <div style="margin-left:auto;">
                                <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:.25rem .65rem;border-radius:99px;">BÁN</span>
                            </div>
                        </div>

                        <!-- Cho thuê -->
                        <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:#eff6ff;border-radius:10px;border:1px solid #bfdbfe;">
                            <div style="width:48px;height:48px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa fa-key" style="color:#2563eb;font-size:20px;"></i>
                            </div>
                            <div>
                                <div style="font-size:11.5px;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.04em;">Cho thuê</div>
                                <div style="font-size:1.8rem;font-weight:700;color:#0f172a;line-height:1.1;"><?= $stats['rents'] ?></div>
                            </div>
                            <div style="margin-left:auto;">
                                <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:600;padding:.25rem .65rem;border-radius:99px;">THUÊ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /ROW 2 -->

        <!-- ======= ROW 3: Lead/Transaction KPI ======= -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-blue">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Tổng giao dịch</div>
                            <div class="stat-number"><?= (int) ($stats['total_transactions'] ?? 0) ?></div>
                            <span class="stat-trend neutral"><i class="fa fa-exchange"></i> Lead/Transaction</span>
                        </div>
                        <div class="stat-icon icon-blue"><i class="fa fa-exchange"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-green">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Đã chốt</div>
                            <div class="stat-number"><?= (int) ($stats['accepted_transactions'] ?? 0) ?></div>
                            <span class="stat-trend up"><i class="fa fa-check-circle"></i> Accepted</span>
                        </div>
                        <div class="stat-icon icon-green"><i class="fa fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-orange">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Tỉ lệ chốt</div>
                            <div class="stat-number"><?= number_format((float) ($stats['close_rate'] ?? 0), 2) ?>%</div>
                            <span class="stat-trend neutral"><i class="fa fa-line-chart"></i> Conversion</span>
                        </div>
                        <div class="stat-icon icon-orange"><i class="fa fa-line-chart"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="stat-card color-cyan">
                    <div class="stat-card-body">
                        <div class="stat-info">
                            <div class="stat-label">Tổng bài đăng</div>
                            <div class="stat-number"><?= (int) ($stats['properties'] ?? 0) ?></div>
                            <span class="stat-trend neutral"><i class="fa fa-home"></i> Property</span>
                        </div>
                        <div class="stat-icon icon-cyan"><i class="fa fa-home"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /ROW 3 -->

        <!-- ======= ROW 4: Report charts ======= -->
        <div class="row">
            <div class="col-xl-4 col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-calendar" style="color:#2563eb;margin-right:.5rem;"></i>Giao dịch theo thời gian (30 ngày)</h5>
                    </div>
                    <div class="card-body">
                        <?php $txRows = isset($stats['chart_transactions_by_day']) && is_array($stats['chart_transactions_by_day']) ? $stats['chart_transactions_by_day'] : []; ?>
                        <?php if (!empty($txRows)): ?>
                            <?php $maxTx = 0; foreach ($txRows as $r) { $maxTx = max($maxTx, (int) ($r['total'] ?? 0)); } ?>
                            <?php foreach ($txRows as $row): ?>
                                <?php $val = (int) ($row['total'] ?? 0); $percent = $maxTx > 0 ? ($val / $maxTx) * 100 : 0; ?>
                                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;">
                                    <span style="width:74px;font-size:11px;color:#64748b;"><?= htmlspecialchars((string) ($row['label'] ?? '')) ?></span>
                                    <div style="flex:1;height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                                        <div style="height:10px;width:<?= max(2, (int) $percent) ?>%;background:#0ea5e9;"></div>
                                    </div>
                                    <span style="width:24px;text-align:right;font-size:12px;font-weight:600;color:#0f172a;"><?= $val ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">Chưa có dữ liệu.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-map-marker" style="color:#2563eb;margin-right:.5rem;"></i>Bài đăng theo khu vực</h5>
                    </div>
                    <div class="card-body">
                        <?php $areaRows = isset($stats['chart_properties_by_area']) && is_array($stats['chart_properties_by_area']) ? $stats['chart_properties_by_area'] : []; ?>
                        <?php if (!empty($areaRows)): ?>
                            <?php $maxArea = 0; foreach ($areaRows as $r) { $maxArea = max($maxArea, (int) ($r['total'] ?? 0)); } ?>
                            <?php foreach ($areaRows as $row): ?>
                                <?php $val = (int) ($row['total'] ?? 0); $percent = $maxArea > 0 ? ($val / $maxArea) * 100 : 0; ?>
                                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;">
                                    <span style="width:92px;font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars((string) ($row['label'] ?? 'N/A')) ?></span>
                                    <div style="flex:1;height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                                        <div style="height:10px;width:<?= max(2, (int) $percent) ?>%;background:#10b981;"></div>
                                    </div>
                                    <span style="width:24px;text-align:right;font-size:12px;font-weight:600;color:#0f172a;"><?= $val ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">Chưa có dữ liệu.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-money" style="color:#2563eb;margin-right:.5rem;"></i>Giá trung bình theo loại BĐS</h5>
                    </div>
                    <div class="card-body">
                        <?php $avgRows = isset($stats['chart_avg_price_by_type']) && is_array($stats['chart_avg_price_by_type']) ? $stats['chart_avg_price_by_type'] : []; ?>
                        <?php if (!empty($avgRows)): ?>
                            <?php $maxAvg = 0; foreach ($avgRows as $r) { $maxAvg = max($maxAvg, (float) ($r['avg_price'] ?? 0)); } ?>
                            <?php foreach ($avgRows as $row): ?>
                                <?php $val = (float) ($row['avg_price'] ?? 0); $percent = $maxAvg > 0 ? ($val / $maxAvg) * 100 : 0; ?>
                                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.45rem;">
                                    <span style="width:92px;font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars((string) ($row['label'] ?? 'N/A')) ?></span>
                                    <div style="flex:1;height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                                        <div style="height:10px;width:<?= max(2, (int) $percent) ?>%;background:#f59e0b;"></div>
                                    </div>
                                    <span style="width:68px;text-align:right;font-size:12px;font-weight:600;color:#0f172a;"><?= number_format($val, 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">Chưa có dữ liệu.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- /ROW 4 -->

        <!-- ======= ROW 5: Quick actions ======= -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fa fa-bolt" style="color:#2563eb;margin-right:.5rem;"></i>
                            Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" style="gap:0;">
                            <div class="col-6 col-md-3 mb-3">
                                <a href="<?= BASEURL ?>/adminProperty/add"
                                   style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;color:#fff;text-decoration:none;transition:all .2s;"
                                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(37,99,235,.35)'"
                                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                                    <i class="fa fa-plus-circle" style="font-size:24px;"></i>
                                    <span style="font-size:13px;font-weight:600;">Thêm BĐS</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <a href="<?= BASEURL ?>/adminProperty/index"
                                   style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;background:linear-gradient(135deg,#0ea5e9,#0284c7);border-radius:12px;color:#fff;text-decoration:none;transition:all .2s;"
                                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(14,165,233,.35)'"
                                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                                    <i class="fa fa-list" style="font-size:24px;"></i>
                                    <span style="font-size:13px;font-weight:600;">Danh sách BĐS</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <a href="<?= BASEURL ?>/adminUser/user"
                                   style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;background:linear-gradient(135deg,#10b981,#059669);border-radius:12px;color:#fff;text-decoration:none;transition:all .2s;"
                                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(16,185,129,.35)'"
                                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                                    <i class="fa fa-users" style="font-size:24px;"></i>
                                    <span style="font-size:13px;font-weight:600;">Người dùng</span>
                                </a>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <a href="<?= BASEURL ?>/adminFeedback/index"
                                   style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;color:#fff;text-decoration:none;transition:all .2s;"
                                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(245,158,11,.35)'"
                                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                                    <i class="fa fa-star" style="font-size:24px;"></i>
                                    <span style="font-size:13px;font-weight:600;">Phản hồi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /ROW 3 -->

    </div>
</div>
<!-- /Page Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
