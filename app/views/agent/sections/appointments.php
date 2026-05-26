<?php
$appointmentRows = [];
$appointmentStats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0,
];
$nextAppointmentTs = null;

foreach (($inquiries ?? []) as $inquiry) {
    $statusRaw = strtolower((string) ($inquiry['appointment_status'] ?? 'none'));
    if ($statusRaw === 'none') {
        continue;
    }

    $appointmentStats['total']++;
    if (isset($appointmentStats[$statusRaw])) {
        $appointmentStats[$statusRaw]++;
    }

    $requestedTs = !empty($inquiry['appointment_requested_at']) ? strtotime((string) $inquiry['appointment_requested_at']) : null;
    if ($requestedTs !== null && ($nextAppointmentTs === null || $requestedTs < $nextAppointmentTs)) {
        $nextAppointmentTs = $requestedTs;
    }

    $inquiry['_normalized_status'] = $statusRaw;
    $inquiry['_requested_iso'] = $requestedTs !== null ? date('c', $requestedTs) : '';
    $propertyTitle = trim((string) ($inquiry['property_title'] ?? ''));
    if ($propertyTitle !== '') {
        $propertyTitleLower = function_exists('mb_strtolower')
            ? mb_strtolower($propertyTitle, 'UTF-8')
            : strtolower($propertyTitle);
    } else {
        $propertyTitleLower = '';
    }
    $inquiry['_property_search'] = $propertyTitleLower;

    $appointmentRows[] = $inquiry;
}

$nextAppointmentLabel = $nextAppointmentTs !== null ? date('d/m/Y H:i', $nextAppointmentTs) : 'Chưa có';
$appointmentDefaultPageSize = 10;
$appointmentInitialEnd = min($appointmentDefaultPageSize, count($appointmentRows));
$appointmentInitialRange = count($appointmentRows) > 0 ? ('1-' . $appointmentInitialEnd) : '0-0';
$appointmentInitialTotalPages = count($appointmentRows) > 0
    ? (int) ceil(count($appointmentRows) / $appointmentDefaultPageSize)
    : 0;
?>

<section class="agent-section agent-appointments" data-appointments-page>
    <div class="agent-section-head">
        <div class="agent-section-copy">
            <h3 class="agent-section-title">Quản lý Lịch hẹn</h3>
            <p class="agent-section-copy"><small>Theo dõi và xử lý các cuộc hẹn gặp khách hàng của bạn</small></p>
        </div>
        <div class="agent-section-actions">
            <!-- <button type="button" class="agent-action-btn agent-action-btn--outline">
                <i class="fa fa-calendar"></i>
                Calendar view
            </button> -->
        </div>
    </div>

    <div class="agent-metric-grid">
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #0040a1, #0056d2);">
                <i class="fa fa-calendar-check"></i>
            </div>
            <div>
                <p class="agent-metric-label">Tổng lịch hẹn</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['total'] ?></p>
                <span class="agent-metric-hint">Tất cả cuộc hẹn đang được quản lý</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div>
                <p class="agent-metric-label">Đang chờ xác nhận</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['pending'] ?></p>
                <span class="agent-metric-hint">Cần phản hồi sớm</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <p class="agent-metric-label">Đã xác nhận</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['confirmed'] ?></p>
                <span class="agent-metric-hint">Sẵn sàng dẫn khách xem</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #6366f1, #4338ca);">
                <i class="fa fa-clock"></i>
            </div>
            <div>
                <p class="agent-metric-label">Lịch sắp tới</p>
                <p class="agent-metric-value"><?= htmlspecialchars($nextAppointmentLabel) ?></p>
                <span class="agent-metric-hint">Thời gian gần nhất trong danh sách</span>
            </div>
        </div>
    </div>

    <div class="agent-filter-panel">
        <div class="agent-filter-group">
            <div class="agent-filter-control">
                <label for="appointment-filter-status">Trạng thái</label>
                <select id="appointment-filter-status" class="form-control">
                    <option value="all">Tất cả</option>
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Hoàn tất</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div class="agent-filter-control">
                <label for="appointment-filter-date-from">Từ ngày</label>
                <input type="date" id="appointment-filter-date-from" class="form-control">
            </div>
            <div class="agent-filter-control">
                <label for="appointment-filter-date-to">Đến ngày</label>
                <input type="date" id="appointment-filter-date-to" class="form-control">
            </div>
            <div class="agent-filter-control agent-filter-control--search">
                <label for="appointment-filter-search">Tên bài đăng</label>
                <div class="agent-filter-search">
                    <input type="text" id="appointment-filter-search">
                </div>
            </div>
        </div>
        <div class="agent-filter-actions">
            <button type="button" class="agent-action-btn agent-action-btn--ghost" id="appointment-filter-reset">
                <i class="fa fa-undo"></i>
                Đặt lại
            </button>
        </div>
    </div>

    <div class="agent-table-wrap agent-table-wrap--elevated">
        <table class="agent-table agent-table--modern">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>BĐS</th>
                    <th>Đề xuất</th>
                    <th>Xác nhận</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Hành động</th>
                </tr>
            </thead>
            <tbody data-appointment-table-body>
                <?php if (!empty($appointmentRows)): ?>
                    <?php foreach ($appointmentRows as $inquiry): ?>
                        <?php
                        $appointmentBadge = agentWorkspaceLeadBadge($inquiry['appointment_status'] ?? 'none', 'appointment');
                        $statusKey = $inquiry['_normalized_status'];
                        $requestedLabel = !empty($inquiry['appointment_requested_at'])
                            ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_requested_at']))
                            : 'Chưa có';
                        $confirmedLabel = !empty($inquiry['appointment_confirmed_at'])
                            ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_confirmed_at']))
                            : 'Chưa có';
                        $clientName = trim((string) ($inquiry['inquirer_name'] ?? 'Khách hàng'));
                        if (function_exists('mb_substr')) {
                            $initials = mb_strtoupper(mb_substr($clientName, 0, 2, 'UTF-8'), 'UTF-8');
                        } else {
                            $initials = strtoupper(substr($clientName, 0, 2));
                        }
                        ?>
                        <tr data-appointment-row data-status="<?= htmlspecialchars($statusKey) ?>"
                            data-requested="<?= htmlspecialchars($inquiry['_requested_iso']) ?>"
                            data-property="<?= htmlspecialchars($inquiry['_property_search']) ?>">
                            <td>
                                <div class="agent-table-client">
                                    <div class="agent-table-avatar">
                                        <span><?= htmlspecialchars($initials) ?></span>
                                    </div>
                                    <div>
                                        <span class="agent-table-title"><?= htmlspecialchars($clientName) ?></span>
                                        <span
                                            class="agent-table-sub"><?= htmlspecialchars((string) ($inquiry['phone'] ?? ($inquiry['work_email'] ?? 'Chưa có thông tin'))) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="agent-table-title"><?= htmlspecialchars((string) ($inquiry['property_title'] ?? 'Không rõ bất động sản')) ?></span>
                                <span class="agent-table-sub">ID lead #<?= (int) ($inquiry['id'] ?? 0) ?></span>
                            </td>
                            <td><?= htmlspecialchars($requestedLabel) ?></td>
                            <td><?= htmlspecialchars($confirmedLabel) ?></td>
                            <td>
                                <span class="agent-badge <?= htmlspecialchars((string) $appointmentBadge[1]) ?>">
                                    <?= htmlspecialchars((string) $appointmentBadge[0]) ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <a class="agent-btn agent-btn--compact"
                                    href="<?= BASEURL ?>/agentWorkspace/leadDetail/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    Mở chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="appointment-empty-state" data-appointment-empty hidden>
                        <td colspan="6" class="agent-table-empty">Không tìm thấy lịch hẹn nào khớp với bộ lọc.</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="agent-table-empty">Chưa có lịch hẹn nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="agent-table-footer">
            <div class="agent-table-footer-info">
                <span class="agent-table-footer-note">Đã hiển thị
                    <strong data-appointment-visible-count><?= (int) $appointmentInitialEnd ?></strong> yêu cầu</span>
            </div>
            <div class="agent-table-footer-controls">
                <label class="agent-table-page-size">
                    <select class="form-control form-control-1" data-appointment-page-size>
                        <option value="10" <?= $appointmentDefaultPageSize === 10 ? 'selected' : '' ?>>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </label>
                <div class="agent-table-pagination" aria-label="Phân trang danh sách lịch hẹn">
                    <button type="button" class="agent-table-page-btn" data-appointment-page-prev
                        aria-label="Trang trước">&lsaquo;</button>
                    <span class="agent-table-page-indicator" data-appointment-page-indicator>Trang
                        <?= count($appointmentRows) > 0 ? 1 : 0 ?> / <?= (int) $appointmentInitialTotalPages ?></span>
                    <button type="button" class="agent-table-page-btn" data-appointment-page-next
                        aria-label="Trang sau">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .agent-appointments .agent-section-head {
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .agent-appointments .agent-section-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .agent-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        background: #f3f4f6;
        transition: all .2s ease;
    }

    .agent-action-btn i {
        font-size: 14px;
    }

    .agent-action-btn--outline {
        background: #fff;
        border-color: #d1d5db;
    }

    .agent-action-btn--primary {
        color: #fff;
        background: linear-gradient(135deg, #0040a1, #0056d2);
        box-shadow: 0 10px 20px rgba(0, 86, 210, 0.22);
    }

    .agent-action-btn--ghost {
        background: rgba(15, 23, 42, 0.05);
        color: #1f2937;
    }

    .agent-action-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .agent-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        /* margin-bottom: 24px; */
    }

    .agent-metric-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        border-radius: 20px;
        background: linear-gradient(160deg, #ffffff, #f5f7ff);
        border: 1px solid rgba(99, 102, 241, 0.08);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .agent-metric-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 16px;
        color: #fff;
        font-size: 22px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .agent-metric-label {
        margin: 0;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 700;
    }

    .agent-metric-value {
        font-size: 20px;
        font-weight: 600;
        /* border: 1px solid var(--dash-line); */
        /* background: #fff; */
        color: var(--dash-text);
    }

    .agent-metric-hint {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
    }

    .agent-filter-panel {
        margin-top: 80px;
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        justify-content: space-between;
        align-items: flex-end;
        padding: 18px 20px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 16px 32px rgba(148, 163, 184, 0.15);
        margin-bottom: 24px;
    }

    .agent-filter-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        flex: 1;
    }

    .agent-filter-control label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .agent-filter-control .form-control,
    .agent-filter-search input {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 14px;
        padding: 10px 12px;
        min-height: 40px;
    }

    .agent-filter-control .form-control:focus,
    .agent-filter-search input:focus {
        outline: none;
        border-color: #0040a1;
        box-shadow: 0 0 0 4px rgba(0, 86, 210, 0.12);
        background: #fff;
    }

    .agent-filter-search {
        display: flex;
        align-items: center;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 0;
    }

    .agent-btn--compact {
        font-size: 12px !important;
        padding: 6px 12px !important;
    }

    .agent-filter-search input {
        border: none;
        background: transparent;
        padding: 10px 12px;
        width: 100%;
    }

    .agent-table-wrap--elevated {
        border-radius: 20px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 24px 45px rgba(100, 116, 139, 0.18);
        overflow: hidden;
    }

    .agent-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px 0;
    }

    .agent-table-toolbar .agent-table-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #1f2937;
    }

    .agent-table--modern thead {
        background: linear-gradient(120deg, #f8fafc, #eef2ff);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 12px;
        color: #64748b;
    }

    .agent-table--modern th,
    .agent-table--modern td {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .agent-table--modern tbody tr:hover {
        background: rgba(226, 232, 240, 0.35);
    }

    .agent-table-client {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agent-table-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(0, 64, 161, 0.2), rgba(0, 86, 210, 0.35));
        color: #0040a1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
    }

    .agent-table-sub {
        display: block;
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .agent-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    .agent-table-footer strong {
        color: #0f172a;
    }

    .agent-table-footer-info,
    .agent-table-footer-controls,
    .agent-table-page-size,
    .agent-table-pagination {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .agent-table-footer-note {
        color: #64748b;
    }

    .agent-table-page-size .form-control {
        margin-top: 10px;
        min-width: 92px;
        min-height: 30px;
        padding: 6px 10px;
        font-size: 12px;
        border-radius: 10px;
    }

    .form-control-1 {
        height: 10px !important;
    }

    .agent-table-page-btn {
        width: 34px;
        height: 32px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-weight: 700;
        line-height: 1;
        transition: all .2s ease;
    }

    .agent-table-page-btn:hover:not(:disabled) {
        background: #eff6ff;
        border-color: #0056d2;
        color: #0056d2;
    }

    .agent-table-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .agent-table-page-indicator {
        min-width: 88px;
        text-align: center;
        color: #475569;
    }

    @media (max-width: 991.98px) {
        .agent-section-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .agent-filter-panel {
            padding: 18px;
        }

        .agent-table-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .agent-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .agent-metric-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .agent-table--modern th,
        .agent-table--modern td {
            padding: 14px 16px;
        }
    }
</style>

<script src="<?= BASEURL ?>/js/lead-table.js"></script>