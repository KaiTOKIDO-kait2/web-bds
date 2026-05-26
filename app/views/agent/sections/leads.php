<?php
$leadInquiries = isset($inquiries) && is_array($inquiries) ? $inquiries : [];

$leadStatusCounts = [
    'all' => count($leadInquiries),
    'pending' => 0,
    'accepted' => 0,
    'rejected' => 0,
];

foreach ($leadInquiries as $item) {
    $status = strtolower((string) ($item['status'] ?? 'pending'));
    if (isset($leadStatusCounts[$status])) {
        $leadStatusCounts[$status]++;
    }
}

$leadTotalCount = count($leadInquiries);
$leadDefaultPageSize = 10;
$leadInitialEnd = min($leadDefaultPageSize, $leadTotalCount);
$leadInitialRange = $leadTotalCount > 0
    ? '1-' . $leadInitialEnd
    : '0-0';
$leadInitialTotalPages = $leadTotalCount > 0
    ? (int) ceil($leadTotalCount / $leadDefaultPageSize)
    : 0;
?>

<style>
    .leads-page {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .leads-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        padding: 4px 0 0;
    }

    .leads-eyebrow {
        margin: 0 0 8px;
        color: #0040a1;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .leads-title {
        margin: 0;
        color: #191c1d;
        font-size: 32px;
        line-height: 1.3;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .leads-subtitle {
        margin: 8px 0 0;
        color: #424654;
        font-size: 16px;
        line-height: 1.6;
        max-width: 72ch;
    }

    .leads-hero-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .leads-meta-pill {
        border: 1px solid #c3c6d6;
        border-radius: 9999px;
        background: #ffffff;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #424654;
    }

    .leads-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .leads-stat-card {
        width: 100%;
        min-height: 132px;
        border: 1px solid #c3c6d6;
        border-radius: 16px;
        background: #ffffff;
        padding: 18px;
        text-align: left;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 180ms ease-out, box-shadow 180ms ease-out, border-color 180ms ease-out;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .leads-stat-card:hover,
    .leads-stat-card:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        border-color: #0056d2;
        outline: none;
    }

    .leads-stat-card.is-active {
        background: #f3f4f5;
        border-color: #0056d2;
    }

    .leads-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        background: #e8f1ff;
        color: #0040a1;
    }

    .leads-stat-icon--all {
        background: #e8f1ff;
        color: #0040a1;
    }

    .leads-stat-icon--pending {
        background: #fff6e5;
        color: #c2410c;
    }

    .leads-stat-icon--accepted {
        background: #e6f9f0;
        color: #0f766e;
    }

    .leads-stat-icon--rejected {
        background: #fdebec;
        color: #b91c1c;
    }

    .leads-stat-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .leads-stat-heading {
        /* display: flex; */
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
    }

    .leads-stat-label {
        margin: 0;
        color: #424654;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .leads-stat-value {
        margin: 0;
        color: #191c1d;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .leads-stat-note {
        margin: 0;
        color: #424654;
        font-size: 13px;
        line-height: 1.4;
    }

    .leads-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) repeat(2, minmax(180px, 0.8fr));
        gap: 12px;
        align-items: center;
        padding: 16px;
        border-radius: 16px;
        background: #ffffff;
    }

    .leads-search-shell {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #c3c6d6;
        border-radius: 9999px;
        background: #faf8ff;
        padding: 10px 14px;
    }

    .leads-search-shell i {
        color: #737785;
    }

    .leads-search-input {
        width: 100%;
        border: 0;
        background: transparent;
        color: #191c1d;
        font-size: 16px;
        outline: none;
    }

    .leads-field {
        width: 100%;
        border: 1px solid #c3c6d6;
        border-radius: 12px;
        background: #ffffff;
        color: #191c1d;
        font-size: 14px;
        font-weight: 600;
        padding: 11px 14px;
        outline: none;
    }

    /* 
    .leads-field:focus,
    .leads-search-input:focus {
        box-shadow: 0 0 0 3px rgba(0, 86, 210, 0.12);
    } */

    .leads-table-wrap {
        margin-top: 40px;
        border: 1px solid #c3c6d6;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .leads-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid #e1e3e4;
        background: #ffff;
    }

    .leads-table-title {
        margin: 0;
        color: #191c1d;
        font-size: 24px;
        line-height: 1.4;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .leads-table-subtitle {
        margin: 4px 0 0;
        color: #424654;
        font-size: 14px;
        line-height: 1.6;
    }

    .leads-table {
        margin: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .leads-table thead th {
        background: #f3f4f5;
        color: #424654;
        font-size: 10px;
        line-height: 1.2;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 600;
        padding: 16px 18px;
        border-bottom: 1px solid #e1e3e4;
    }

    .leads-table tbody td {
        padding: 16px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
    }

    .leads-table tbody tr:hover {
        background: #f8fbff;
    }

    .leads-name {
        display: block;
        color: #191c1d;
        font-size: 14px;
        line-height: 1.2;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .leads-sub {
        display: block;
        color: #424654;
        font-size: 12px;
        line-height: 1.4;
    }

    .leads-note {
        max-width: 24ch;
    }

    .leads-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .leads-actions .btn {
        white-space: nowrap;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        padding: 0 14px;
        height: 32px;
        min-width: 90px;
        line-height: 32px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 180ms ease, box-shadow 180ms ease, transform 120ms ease;
    }

    .leads-actions .btn-success {
        background: linear-gradient(135deg, #17c964 0%, #12a053 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(23, 201, 100, 0.30);
    }

    .leads-actions .btn-success:hover {
        background: linear-gradient(135deg, #12a053 0%, #0e7a3e 100%);
        box-shadow: 0 4px 12px rgba(23, 201, 100, 0.40);
        transform: translateY(-1px);
    }

    .leads-actions .btn-danger {
        background: linear-gradient(135deg, #f31260 0%, #c20e4d 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(243, 18, 96, 0.28);
    }

    .leads-actions .btn-danger:hover {
        background: linear-gradient(135deg, #c20e4d 0%, #960b3b 100%);
        box-shadow: 0 4px 12px rgba(243, 18, 96, 0.38);
        transform: translateY(-1px);
    }

    .leads-btn-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1.5px solid #d0d4e8;
        border-radius: 10px;
        background: #ffffff;
        color: #0040a1;
        padding: 0 14px;
        height: 32px;
        min-width: 90px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
    }

    .leads-btn-soft:hover,
    .leads-btn-soft:focus-visible {
        background: #eef2ff;
        border-color: #0040a1;
        color: #0040a1;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 64, 161, 0.12);
    }

    .leads-empty-state {
        padding: 28px 18px;
        text-align: center;
        color: #424654;
        font-size: 14px;
    }

    .leads-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 38px;
        border-top: 1px solid #e1e3e4;
        background: #faf8ff;
        color: #424654;
        font-size: 12px;
        font-weight: 600;
    }

    .leads-footer strong {
        color: #191c1d;
    }

    .leads-footer-info {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .leads-footer-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .leads-page-size {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .leads-pagination {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .leads-page-btn {
        border: 1px solid #c3c6d6;
        background: #ffffff;
        border-radius: 8px;
        width: 34px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: #424654;
        transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .leads-page-btn:hover:not(:disabled),
    .leads-page-btn:focus-visible:not(:disabled) {
        background: #eef2ff;
        border-color: #0040a1;
        color: #0040a1;
        outline: none;
    }

    .leads-page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .leads-page-indicator {
        font-weight: 600;
        color: #424654;
    }

    .leads-field--compact {
        margin-top: 9px;
        padding: 7px 12px;
        font-size: 12px;
        height: auto;
        min-width: 92px;
    }

    .leads-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .leads-badge.show {
        background: #eaf9ef;
        color: #238a4b;
    }

    .leads-badge.wait {
        background: #fff1e8;
        color: #c65a00;
    }

    .leads-badge.danger {
        background: #feeced;
        color: #c33442;
    }

    .leads-badge.muted {
        background: #e7e8e9;
        color: #424654;
    }

    .leads-viewport-note {
        font-size: 12px;
        color: #424654;
        font-weight: 600;
    }

    @media (max-width: 1199.98px) {
        .leads-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .leads-toolbar {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .leads-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .leads-title {
            font-size: 24px;
        }

        .leads-stat-grid {
            grid-template-columns: 1fr;
        }

        .leads-stat-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .leads-stat-heading {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .leads-table-head,
        .leads-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<section class="agent-section leads-page" data-leads-page
    data-lead-action-base="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow">
    <div class="leads-hero">
        <div>
            <p class="leads-eyebrow">Agent Workspace</p>
            <h3 class="leads-title">Danh sách lead cần theo sát</h3>
        </div>
        <div class="leads-hero-meta">

        </div>
    </div>

    <div class="leads-stat-grid">
        <button type="button" class="leads-stat-card is-active" data-status-card="all" aria-pressed="true">
            <span class="leads-stat-icon leads-stat-icon--all" aria-hidden="true"><i class="fa fa-users"></i></span>
            <div class="leads-stat-body">
                <div class="leads-stat-heading">
                    <p class="leads-stat-label">Tổng lead</p>
                    <p class="leads-stat-value"><?= (int) ($leadStatusCounts['all'] ?? 0) ?></p>
                </div>
                <span class="leads-stat-note">Toàn bộ lead trong danh sách hiện tại</span>
            </div>
        </button>

        <button type="button" class="leads-stat-card" data-status-card="pending" aria-pressed="false">
            <span class="leads-stat-icon leads-stat-icon--pending" aria-hidden="true"><i
                    class="fa fa-hourglass-half"></i></span>
            <div class="leads-stat-body">
                <div class="leads-stat-heading">
                    <p class="leads-stat-label">Chờ xác nhận</p>
                    <p class="leads-stat-value"><?= (int) ($leadStatusCounts['pending'] ?? 0) ?></p>
                </div>
                <span class="leads-stat-note">Lead chưa được tiếp nhận</span>
            </div>
        </button>

        <button type="button" class="leads-stat-card" data-status-card="accepted" aria-pressed="false">
            <span class="leads-stat-icon leads-stat-icon--accepted" aria-hidden="true"><i
                    class="fa fa-check-circle"></i></span>
            <div class="leads-stat-body">
                <div class="leads-stat-heading">
                    <p class="leads-stat-label">Đã tiếp nhận</p>
                    <p class="leads-stat-value"><?= (int) ($leadStatusCounts['accepted'] ?? 0) ?></p>
                </div>
                <span class="leads-stat-note">Lead đang được xử lý</span>
            </div>
        </button>

        <button type="button" class="leads-stat-card" data-status-card="rejected" aria-pressed="false">
            <span class="leads-stat-icon leads-stat-icon--rejected" aria-hidden="true"><i
                    class="fa fa-times-circle"></i></span>
            <div class="leads-stat-body">
                <div class="leads-stat-heading">
                    <p class="leads-stat-label">Đã từ chối</p>
                    <p class="leads-stat-value"><?= (int) ($leadStatusCounts['rejected'] ?? 0) ?></p>
                </div>
                <span class="leads-stat-note">Lead không tiếp tục xử lý</span>
            </div>
        </button>
    </div>



    <div class="leads-table-wrap">
        <div class="leads-table-head">
            <div class="leads-toolbar">
                <label class="mb-0 w-100">
                    <span class="sr-only">Tìm theo tên lead</span>
                    <div class="leads-search-shell">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <input class="leads-search-input" type="text" data-lead-search autocomplete="off">
                    </div>
                </label>
                <select class="leads-field" data-lead-status-select>
                    <option value="all">Tất cả trạng thái</option>
                    <option value="pending">Chờ xác nhận</option>
                    <option value="accepted">Đã tiếp nhận</option>
                    <option value="rejected">Đã từ chối</option>
                </select>

                <select class="leads-field" data-lead-sort-select>
                    <option value="newest">Sắp xếp: Mới nhất</option>
                    <option value="oldest">Sắp xếp: Cũ nhất</option>
                    <option value="name_asc">Sắp xếp: Tên A-Z</option>
                    <option value="name_desc">Sắp xếp: Tên Z-A</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="agent-table leads-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Tin liên quan</th>
                        <th>Trạng thái hồ sơ</th>
                        <th>Lịch hẹn</th>
                        <th>Kết quả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-leads-table-body>
                    <?php if (!empty($leadInquiries)): ?>
                        <?php foreach ($leadInquiries as $inquiry): ?>
                            <?php
                            $statusValue = strtolower((string) ($inquiry['status'] ?? 'pending'));
                            $statusBadge = ($statusValue === 'accepted')
                                ? ['Đã tiếp nhận', 'show']
                                : (($statusValue === 'rejected')
                                    ? ['Đã từ chối', 'danger']
                                    : ['Chờ xác nhận', 'wait']);
                            $appointmentBadge = agentWorkspaceLeadBadge($inquiry['appointment_status'] ?? 'none', 'appointment');
                            $caseStatus = strtolower((string) ($inquiry['case_status'] ?? 'new'));
                            $resultBadge = ($caseStatus === 'completed')
                                ? ['Thành công', 'show']
                                : (($caseStatus === 'cancelled')
                                    ? ['Thất bại', 'danger']
                                    : ['', 'muted']);
                            $timestamp = !empty($inquiry['created_at']) ? strtotime((string) $inquiry['created_at']) : false;
                            $sortTimestamp = $timestamp !== false ? (int) $timestamp : 0;
                            $leadName = trim((string) ($inquiry['inquirer_name'] ?? 'Khách hàng'));
                            ?>
                            <tr data-lead-row data-lead-name="<?= htmlspecialchars(mb_strtolower($leadName, 'UTF-8')) ?>"
                                data-lead-status="<?= htmlspecialchars($statusValue) ?>"
                                data-lead-sort-ts="<?= (int) $sortTimestamp ?>">
                                <td><?= $timestamp !== false ? date('d/m/Y H:i', $timestamp) : '--' ?></td>
                                <td>
                                    <span class="leads-name"><?= htmlspecialchars($leadName) ?></span>
                                    <span
                                        class="leads-sub"><?= htmlspecialchars((string) ($inquiry['phone'] ?? ($inquiry['work_email'] ?? 'Chưa có thông tin'))) ?></span>
                                </td>
                                <td>
                                    <span
                                        class="leads-name"><?= htmlspecialchars((string) ($inquiry['property_title'] ?? 'Không rõ bất động sản')) ?></span>
                                    <span class="leads-sub">ID lead #<?= (int) ($inquiry['id'] ?? 0) ?></span>
                                </td>
                                <td><span
                                        class="agent-badge <?= htmlspecialchars((string) $statusBadge[1]) ?> leads-badge <?= htmlspecialchars((string) $statusBadge[1]) ?>"><?= htmlspecialchars((string) $statusBadge[0]) ?></span>
                                </td>
                                <td><span
                                        class="agent-badge <?= htmlspecialchars((string) $appointmentBadge[1]) ?> leads-badge <?= htmlspecialchars((string) $appointmentBadge[1]) ?>"><?= htmlspecialchars((string) $appointmentBadge[0]) ?></span>
                                </td>
                                <td><?= !empty($resultBadge[0]) ? '<span class="agent-badge ' . htmlspecialchars((string) $resultBadge[1]) . ' leads-badge ' . htmlspecialchars((string) $resultBadge[1]) . '">' . htmlspecialchars((string) $resultBadge[0]) . '</span>' : '--' ?>
                                </td>
                                <td>
                                    <div class="leads-actions">
                                        <button class="btn btn-success btn-sm"
                                            onclick="AppPopup.show({type:'success',message:'Bạn có chắc chắn muốn chấp nhận lead này không?',confirmText:'Chấp nhận',cancelText:'Hủy',onConfirm:function(){handleLeadAction(<?= (int) ($inquiry['id'] ?? 0) ?>,'accept_lead');}})">Chấp
                                            nhận
                                        </button>
                                        <a class="leads-btn-soft"
                                            href="<?= BASEURL ?>/agentWorkspace/leadDetail/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                            Mở chi tiết</a>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="AppPopup.show({type:'error',message:'Bạn có chắc chắn muốn từ chối lead này không?',confirmText:'Từ chối',cancelText:'Hủy',onConfirm:function(){handleLeadAction(<?= (int) ($inquiry['id'] ?? 0) ?>,'reject_lead');}})">Từ
                                            chối
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr data-leads-empty hidden>
                            <td colspan="7" class="leads-empty-state">Không tìm thấy lead phù hợp với bộ lọc hiện tại.</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="leads-empty-state">Chưa có dữ liệu khách hàng.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="leads-footer">
            <div class="leads-footer-info">

                <span class="leads-viewport-note">Đã hiển thị <strong
                        data-lead-visible-count-footer><?= (int) $leadInitialEnd ?></strong> yêu cầu</span>
            </div>
            <div class="leads-footer-controls">
                <label class="leads-page-size">
                    <select class="leads-field leads-field--compact" data-lead-page-size>
                        <option value="10" <?= $leadDefaultPageSize === 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $leadDefaultPageSize === 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $leadDefaultPageSize === 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </label>
                <div class="leads-pagination" aria-label="Phân trang danh sách lead">
                    <button type="button" class="leads-page-btn" data-lead-page-prev
                        aria-label="Trang trước">&lsaquo;</button>
                    <span class="leads-page-indicator" data-lead-page-indicator>Trang <?= $leadTotalCount > 0 ? 1 : 0 ?>
                        / <?= (int) $leadInitialTotalPages ?></span>
                    <button type="button" class="leads-page-btn" data-lead-page-next
                        aria-label="Trang sau">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= BASEURL ?>/js/lead-table.js"></script>
