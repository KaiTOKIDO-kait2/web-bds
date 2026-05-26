<?php /** @var array $requests */ 
$userRequests = isset($requests) && is_array($requests) ? $requests : [];

$requestStatusCounts = [
    'all' => count($userRequests),
    'pending' => 0,
    'accepted' => 0,
    'rejected' => 0,
];

foreach ($userRequests as $item) {
    $status = strtolower((string) ($item['status'] ?? 'pending'));
    if (isset($requestStatusCounts[$status])) {
        $requestStatusCounts[$status]++;
    }
}

$requestDefaultPageSize = 10;
$requestTotalCount = count($userRequests);
$requestInitialEnd = min($requestDefaultPageSize, $requestTotalCount);
$requestInitialRange = $requestTotalCount > 0 ? ('1-' . $requestInitialEnd) : '0-0';
$requestInitialTotalPages = $requestTotalCount > 0
    ? (int) ceil($requestTotalCount / $requestDefaultPageSize)
    : 0;
?>

<style>
    .requests-page {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .requests-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        padding: 4px 0 0;
    }

    .requests-eyebrow {
        margin: 0 0 8px;
        color: #0040a1;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .requests-title {
        margin: 0;
        color: #191c1d;
        font-size: 32px;
        line-height: 1.3;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .requests-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .requests-stat-card {
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

    .requests-stat-card:hover,
    .requests-stat-card:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        border-color: #0056d2;
        outline: none;
    }

    .requests-stat-card.is-active {
        background: #f3f4f5;
        border-color: #0056d2;
    }

    .requests-stat-icon {
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

    .requests-stat-icon--all { background: #e8f1ff; color: #0040a1; }
    .requests-stat-icon--pending { background: #fff6e5; color: #c2410c; }
    .requests-stat-icon--accepted { background: #e6f9f0; color: #0f766e; }
    .requests-stat-icon--rejected { background: #fdebec; color: #b91c1c; }

    .requests-stat-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .requests-stat-heading {
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
    }

    .requests-stat-label {
        margin: 0;
        color: #424654;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .requests-stat-value {
        margin: 0;
        color: #191c1d;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .requests-stat-note {
        margin: 0;
        color: #424654;
        font-size: 13px;
        line-height: 1.4;
    }

    .requests-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) repeat(2, minmax(180px, 0.8fr));
        gap: 12px;
        align-items: center;
        padding: 16px;
        border-radius: 16px;
        background: #ffffff;
    }

    .requests-search-shell {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #c3c6d6;
        border-radius: 9999px;
        background: #faf8ff;
        padding: 10px 14px;
    }

    .requests-search-shell i { color: #737785; }

    .requests-search-input {
        width: 100%;
        border: 0;
        background: transparent;
        color: #191c1d;
        font-size: 16px;
        outline: none;
    }

    .requests-field {
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

    .requests-table-wrap {
        margin-top: 40px;
        border: 1px solid #c3c6d6;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .requests-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid #e1e3e4;
        background: #ffff;
    }

    .requests-table {
        margin: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .requests-table thead th {
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

    .requests-table tbody td {
        padding: 16px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
    }

    .requests-table tbody tr:hover { background: #f8fbff; }

    .requests-name {
        display: block;
        color: #191c1d;
        font-size: 14px;
        line-height: 1.2;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .requests-sub {
        display: block;
        color: #424654;
        font-size: 12px;
        line-height: 1.4;
    }
    
    .requests-btn-soft {
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

    .requests-btn-soft:hover,
    .requests-btn-soft:focus-visible {
        background: #eef2ff;
        border-color: #0040a1;
        color: #0040a1;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 64, 161, 0.12);
    }

    .requests-empty-state {
        padding: 28px 18px;
        text-align: center;
        color: #424654;
        font-size: 14px;
    }

    .requests-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-top: 1px solid #e1e3e4;
        background: #faf8ff;
        color: #424654;
        font-size: 12px;
        font-weight: 600;
    }

    .requests-footer strong { color: #191c1d; }

    .requests-footer-info,
    .requests-footer-controls,
    .requests-page-size,
    .requests-pagination {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .requests-viewport-note {
        color: #424654;
    }

    .requests-field--compact {
        margin-top: 10px;
        padding: 6px 12px;
        font-size: 12px;
        min-width: 92px;
        min-height: 36px;
    }

    .requests-page-btn {
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

    .requests-page-btn:hover:not(:disabled),
    .requests-page-btn:focus-visible:not(:disabled) {
        background: #eef2ff;
        border-color: #0040a1;
        color: #0040a1;
        outline: none;
    }

    .requests-page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .requests-page-indicator {
        font-weight: 600;
        color: #424654;
        min-width: 88px;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .requests-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .requests-toolbar { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .requests-hero { flex-direction: column; align-items: flex-start; }
        .requests-title { font-size: 24px; }
        .requests-stat-grid { grid-template-columns: 1fr; }
        .requests-stat-card { flex-direction: column; align-items: flex-start; }
        .requests-stat-heading { flex-direction: column; align-items: flex-start; gap: 4px; }
        .requests-table-head, .requests-footer { flex-direction: column; align-items: flex-start; }
    }
</style>

<section class="agent-section requests-page" data-requests-page>
    <div class="requests-hero">
        <div>
            <p class="requests-eyebrow">User Workspace</p>
            <h3 class="requests-title">Yêu cầu của tôi</h3>
        </div>
    </div>

    <div class="requests-stat-grid">
        <button type="button" class="requests-stat-card is-active" data-status-card="all" aria-pressed="true">
            <span class="requests-stat-icon requests-stat-icon--all" aria-hidden="true"><i class="fa fa-list"></i></span>
            <div class="requests-stat-body">
                <div class="requests-stat-heading">
                    <p class="requests-stat-label">Tổng yêu cầu</p>
                    <p class="requests-stat-value"><?= (int) ($requestStatusCounts['all'] ?? 0) ?></p>
                </div>
                <span class="requests-stat-note">Toàn bộ yêu cầu của bạn</span>
            </div>
        </button>

        <button type="button" class="requests-stat-card" data-status-card="pending" aria-pressed="false">
            <span class="requests-stat-icon requests-stat-icon--pending" aria-hidden="true"><i class="fa fa-hourglass-half"></i></span>
            <div class="requests-stat-body">
                <div class="requests-stat-heading">
                    <p class="requests-stat-label">Đang chờ</p>
                    <p class="requests-stat-value"><?= (int) ($requestStatusCounts['pending'] ?? 0) ?></p>
                </div>
                <span class="requests-stat-note">Yêu cầu chưa được môi giới tiếp nhận</span>
            </div>
        </button>

        <button type="button" class="requests-stat-card" data-status-card="accepted" aria-pressed="false">
            <span class="requests-stat-icon requests-stat-icon--accepted" aria-hidden="true"><i class="fa fa-check-circle"></i></span>
            <div class="requests-stat-body">
                <div class="requests-stat-heading">
                    <p class="requests-stat-label">Đã tiếp nhận</p>
                    <p class="requests-stat-value"><?= (int) ($requestStatusCounts['accepted'] ?? 0) ?></p>
                </div>
                <span class="requests-stat-note">Yêu cầu đang được xử lý</span>
            </div>
        </button>

        <button type="button" class="requests-stat-card" data-status-card="rejected" aria-pressed="false">
            <span class="requests-stat-icon requests-stat-icon--rejected" aria-hidden="true"><i class="fa fa-times-circle"></i></span>
            <div class="requests-stat-body">
                <div class="requests-stat-heading">
                    <p class="requests-stat-label">Bị từ chối</p>
                    <p class="requests-stat-value"><?= (int) ($requestStatusCounts['rejected'] ?? 0) ?></p>
                </div>
                <span class="requests-stat-note">Yêu cầu đã bị môi giới từ chối</span>
            </div>
        </button>
    </div>

    <div class="requests-table-wrap">
        <div class="requests-table-head">
            <div class="requests-toolbar">
                <label class="mb-0 w-100">
                    <span class="sr-only">Tìm theo tên BĐS</span>
                    <div class="requests-search-shell">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <input class="requests-search-input" type="text" placeholder="Tìm theo tên BĐS hoặc môi giới..." data-request-search autocomplete="off">
                    </div>
                </label>
                <select class="requests-field" data-request-status-select>
                    <option value="all">Tất cả trạng thái</option>
                    <option value="pending">Đang chờ</option>
                    <option value="accepted">Đã tiếp nhận</option>
                    <option value="rejected">Bị từ chối</option>
                </select>

                <select class="requests-field" data-request-sort-select>
                    <option value="newest">Sắp xếp: Mới nhất</option>
                    <option value="oldest">Sắp xếp: Cũ nhất</option>
                    <option value="name_asc">Sắp xếp: Tên A-Z</option>
                    <option value="name_desc">Sắp xếp: Tên Z-A</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="agent-table requests-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>BĐS quan tâm</th>
                        <th>Môi giới</th>
                        <th>Hồ sơ</th>
                        <th>Lịch hẹn</th>
                        <th>Kết quả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-requests-table-body>
                    <?php if (!empty($userRequests)): ?>
                        <?php foreach ($userRequests as $request): ?>
                            <?php
                            $statusValue = strtolower((string) ($request['status'] ?? 'pending'));
                            $timestamp = !empty($request['created_at']) ? strtotime((string) $request['created_at']) : false;
                            $sortTimestamp = $timestamp !== false ? (int) $timestamp : 0;
                            $propertyTitle = trim((string) ($request['property_title'] ?? 'BĐS Không xác định'));
                            $agentName = trim((string) ($request['agent_name'] ?? 'Chưa rõ môi giới'));
                            ?>
                            <tr data-request-row 
                                data-request-name="<?= htmlspecialchars(mb_strtolower($propertyTitle . ' ' . $agentName, 'UTF-8')) ?>"
                                data-request-status="<?= htmlspecialchars($statusValue) ?>"
                                data-request-sort-ts="<?= (int) $sortTimestamp ?>">
                                <td><?= $timestamp !== false ? date('d/m/Y H:i', $timestamp) : '--' ?></td>
                                <td>
                                    <span class="requests-name"><?= htmlspecialchars($propertyTitle) ?></span>
                                    <span class="requests-sub">ID Yêu cầu #<?= (int) ($request['id'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <span class="requests-name"><?= htmlspecialchars($agentName) ?></span>
                                </td>
                                <td><?= userStatusBadge((string)($request['status'] ?? 'pending')) ?></td>
                                <td><?= userAppointmentBadge((string)($request['appointment_status'] ?? 'none')) ?></td>
                                <td><?= userResultBadge((string)($request['case_status'] ?? 'new')) ?></td>
                                <td>
                                    <a class="requests-btn-soft" href="<?= BASEURL ?>/userWorkspace/requestDetail/<?= (int)($request['id'] ?? 0) ?>">Chi tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr data-requests-empty hidden>
                            <td colspan="7" class="requests-empty-state">Không tìm thấy yêu cầu phù hợp với bộ lọc hiện tại.</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="requests-empty-state">Bạn chưa có yêu cầu nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="requests-footer">
            <div class="requests-footer-info">
                <span class="requests-viewport-note">Hiển thị 
                    <strong data-request-visible-count-footer><?= (int) $requestInitialEnd ?></strong> yêu cầu</span>
            </div>
            <div class="requests-footer-controls">
                <label class="requests-page-size">
                    <select class="requests-field requests-field--compact" data-request-page-size>
                        <option value="10" <?= $requestDefaultPageSize === 10 ? 'selected' : '' ?>>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </label>
                <div class="requests-pagination" aria-label="Phân trang danh sách yêu cầu">
                    <button type="button" class="requests-page-btn" data-request-page-prev aria-label="Trang trước">&lsaquo;</button>
                    <span class="requests-page-indicator" data-request-page-indicator>Trang <?= $requestTotalCount > 0 ? 1 : 0 ?> / <?= (int) $requestInitialTotalPages ?></span>
                    <button type="button" class="requests-page-btn" data-request-page-next aria-label="Trang sau">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= BASEURL ?>/js/lead-table.js"></script>
