<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$request = isset($data['request']) && is_array($data['request']) ? $data['request'] : [];
$logs = isset($data['logs']) && is_array($data['logs']) ? $data['logs'] : [];
$busySlotsRaw = isset($data['busySlots']) && is_array($data['busySlots']) ? $data['busySlots'] : [];
$peerContacts = isset($data['peerContacts']) && is_array($data['peerContacts']) ? $data['peerContacts'] : [];
$busySlots = [];
foreach ($busySlotsRaw as $slot) {
    $timestamp = strtotime((string) $slot);
    if ($timestamp === false) {
        continue;
    }
    $local = date('Y-m-d\TH:i', $timestamp);
    if (!in_array($local, $busySlots, true)) {
        $busySlots[] = $local;
    }
}
$status = strtolower((string) ($request['status'] ?? 'pending'));
$caseStatus = strtolower((string) ($request['case_status'] ?? 'new'));
$appointmentStatus = strtolower((string) ($request['appointment_status'] ?? 'none'));

$statusMap = [
    'pending' => ['Chờ tiếp nhận', 'uw-badge-wait'],
    'accepted' => ['Đã tiếp nhận', 'uw-badge-ok'],
    'rejected' => ['Đã từ chối', 'uw-badge-danger'],
];

$caseMap = [
    'new' => ['Vừa tạo', 'uw-badge-muted'],
    'scheduled' => ['Đang hẹn', 'uw-badge-info'],
    'viewed' => ['Đã xem nhà', 'uw-badge-info'],
    'completed' => ['Hoàn tất', 'uw-badge-ok'],
    'cancelled' => ['Đã hủy', 'uw-badge-danger'],
];

$apptMap = [
    'none' => ['Chưa đặt lịch', 'uw-badge-muted'],
    'pending' => ['Chờ xác nhận', 'uw-badge-wait'],
    'confirmed' => ['Đã xác nhận', 'uw-badge-info'],
    'completed' => ['Hoàn thành', 'uw-badge-ok'],
    'cancelled' => ['Đã hủy', 'uw-badge-danger'],
];

[$statusLabel, $statusClass] = $statusMap[$status] ?? ['Không rõ', 'uw-badge-muted'];
[$caseLabel, $caseClass] = $caseMap[$caseStatus] ?? ['Không rõ', 'uw-badge-muted'];
[$apptLabel, $apptClass] = $apptMap[$appointmentStatus] ?? ['Không rõ', 'uw-badge-muted'];

$t1Done = in_array($status, ['accepted', 'rejected'], true);
$t2Done = in_array($appointmentStatus, ['confirmed', 'completed'], true);
$t3Done = in_array($caseStatus, ['viewed', 'completed'], true) || !empty($request['viewed_at']);
$t4Done = $caseStatus === 'completed';

$isCancelled = ($status === 'rejected' || $caseStatus === 'cancelled' || $appointmentStatus === 'cancelled');
$cancelStep = 0;
if ($isCancelled) {
    if (!$t1Done) {
        $cancelStep = 1;
    } elseif (!$t2Done) {
        $cancelStep = 2;
    } elseif (!$t3Done) {
        $cancelStep = 3;
    } else {
        $cancelStep = 4;
    }
}

$createdAt = !empty($request['created_at']) ? date('d/m/Y H:i', strtotime((string) $request['created_at'])) : '';
$appointmentRequestedAt = !empty($request['appointment_requested_at']) ? date('d/m/Y H:i', strtotime((string) $request['appointment_requested_at'])) : 'Chưa có';
$appointmentConfirmedAt = !empty($request['appointment_confirmed_at']) ? date('d/m/Y H:i', strtotime((string) $request['appointment_confirmed_at'])) : 'Chưa có';
$workflowUpdatedAt = !empty($request['workflow_updated_at']) ? date('d/m/Y H:i', strtotime((string) $request['workflow_updated_at'])) : 'Chưa có';
$agentName = trim((string) ($request['agent_name'] ?? 'Chưa phân công'));
$agentPhone = trim((string) ($request['agent_phone'] ?? ''));
$agentEmail = trim((string) ($request['agent_email'] ?? ''));
$agentContactParts = array_filter([$agentPhone, $agentEmail], static function ($value) {
    return trim((string) $value) !== '';
});
$customerName = trim((string) ($request['inquirer_name'] ?? 'Bạn'));
$customerPhone = trim((string) ($request['phone'] ?? ''));
$customerEmail = trim((string) ($request['work_email'] ?? ''));
$customerContactParts = array_filter([$customerPhone, $customerEmail], static function ($value) {
    return trim((string) $value) !== '';
});

$canSubmitAppointment = ($status === 'accepted') && in_array($appointmentStatus, ['none', 'cancelled'], true);
?>

<style>
    .uw-wrap {
        max-width: 1280px;
        margin: 0 auto;
        padding: 32px 24px 64px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .uw-header {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .uw-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #737785;
        font-weight: 600;
    }

    .uw-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .uw-breadcrumb a:hover {
        color: #0040a1;
    }

    .uw-headline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .uw-headline h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #191c1d;
        letter-spacing: -0.01em;
    }

    .uw-headline h1 span {
        color: #0040a1;
    }

    .uw-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .uw-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 1px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .uw-badge-ok {
        background: #eaf9ef;
        color: #238a4b;
    }

    .uw-badge-wait {
        background: #fff1e8;
        color: #c65a00;
    }

    .uw-badge-danger {
        background: #feeced;
        color: #c33442;
    }

    .uw-badge-muted {
        background: #e7e8e9;
        color: #424654;
    }

    .uw-badge-info {
        background: #e8f1ff;
        color: #0040a1;
    }

    .uw-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }

    .uw-card {
        background: #fff;
        border: 1px solid #c3c6d6;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .uw-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #e1e3e4;
        background: #fafbff;
    }

    .uw-card-label {
        margin: 0;
        font-size: 11px;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: black;
        font-weight: 700;
    }

    .uw-card-body {
        padding: 24px;
    }
    .uw-card-body-1 {
        padding: 0px !important;
    }

    .uw-property-image {
        width: 100%;
        height: 280px;
        overflow: hidden;
        background: #f3f4f5;
        margin-bottom: 16px;
    }

    .uw-property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .uw-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .uw-peer-table {
        border: 1px solid #dce2f3;
        border-radius: 12px;
        overflow: hidden;
    }

    .uw-peer-scroll {
        overflow-x: auto;
    }

    .uw-peer-grid {
        min-width: 720px;
    }

    .uw-peer-header,
    .uw-peer-row {
        display: grid;
        grid-template-columns: 160px 200px 160px 200px 150px;
        gap: 12px;
        padding: 12px 18px;
        align-items: center;
    }

    .uw-peer-header {
        background: #f4f6fb;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #5b6173;
    }

    .uw-peer-body {
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
    }

    .uw-peer-row {
        font-size: 13px;
        font-weight: 600;
        color: #1f2a44;
        border-top: 1px solid #e8ecf7;
    }

    .uw-peer-row:nth-child(odd) {
        background: #f9fbff;
    }

    .uw-peer-row.is-primary {
        box-shadow: inset 2px 0 0 #0040a1;
        /* background: #eef3ff; */
    }

    .uw-peer-meta {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-weight: 500;
    }

    .uw-peer-meta span:last-child,
    .uw-peer-meta small,
    .uw-peer-title small {
        font-size: 11px;
        /* color: #6c7285; */
        font-weight: 500;
    }

    .uw-peer-title {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .uw-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .uw-timeline {
        position: relative;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .uw-timeline-track {
        position: absolute;
        left: 52px;
        right: 52px;
        top: 44px;
        height: 3px;
        background: #e1e3e4;
        border-radius: 999px;
    }

    .uw-timeline-fill {
        height: 100%;
        background: #0040a1;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .uw-timeline-fill.is-cancelled {
        background: #c33442;
    }

    .uw-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .uw-step-dot {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #e1e3e4;
        background: #fff;
        font-weight: 700;
        color: #a0a5b9;
    }

    .uw-step.done .uw-step-dot {
        background: #0040a1;
        border-color: #0040a1;
        color: #fff;
    }

    .uw-step.active .uw-step-dot {
        border-color: #0040a1;
        color: #0040a1;
        box-shadow: 0 0 0 4px rgba(0, 64, 161, 0.12);
    }

    .uw-step.cancelled .uw-step-dot {
        background: #feeced;
        border-color: #f31260;
        color: #f31260;
    }

    .uw-step-label {
        font-size: 12px;
        font-weight: 700;
        color: #737785;
        text-align: center;
    }

    .uw-step-sub {
        font-size: 10px;
        color: #a0a5b9;
        font-weight: 600;
        text-align: center;
    }

    .uw-step.done .uw-step-label,
    .uw-step.done .uw-step-sub,
    .uw-step.active .uw-step-label,
    .uw-step.active .uw-step-sub {
        color: #0040a1;
    }

    .uw-step.cancelled .uw-step-label,
    .uw-step.cancelled .uw-step-sub {
        color: #c33442;
    }

    .uw-info-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .uw-info-list-1{
        padding: 16px;
    }
    .uw-info-row {
        display: flex;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #f0f1f2;
        align-items: baseline;
    }

    .uw-info-row:last-child {
        border-bottom: none;
    }

    .uw-info-label {
        min-width: 90px;
        font-size: 11px;
        font-weight: 700;
        color: #737785;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .uw-info-value {
        font-size: 14px;
        font-weight: 500;
        color: #191c1d;
    }

    .uw-req-block {
        background: #f3f4f5;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 13px;
        color: #424654;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    .uw-summary-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .uw-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .uw-summary-key {
        font-size: 12px;
        font-weight: 700;
        color: #737785;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .uw-summary-val {
        font-size: 13px;
        font-weight: 600;
        color: #191c1d;
    }

    .uw-form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
    }

    .uw-form-label {
        font-size: 12px;
        font-weight: 700;
        color: #424654;
    }

    .uw-form-control {
        width: 100%;
        padding: 10px 13px;
        border-radius: 12px;
        border: 1.5px solid #c3c6d6;
        background: #fafbff;
        font-size: 14px;
        color: #191c1d;
    }

    .uw-form-control:focus {
        outline: none;
        border-color: #0040a1;
        box-shadow: 0 0 0 3px rgba(0, 64, 161, 0.12);
    }

    .uw-submit-btn {
        width: 100%;
        padding: 11px;
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #17c964 0%, #12a053 100%);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .uw-submit-btn:disabled {
        background: #e5e7eb;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }

    .uw-submit-btn:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(23, 201, 100, 0.3);
    }

    .uw-back-btn {
        display: inline-flex;
        width: 100%;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1.5px solid #c3c6d6;
        font-weight: 600;
        color: #0040a1;
        text-decoration: none;
        transition: background 0.18s ease;
    }

    .uw-back-btn:hover {
        background: #eef2ff;
    }

    .uw-log-table {
        width: 100%;
        border-collapse: collapse;
    }

    .uw-log-table th,
    .uw-log-table td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
    }

    .uw-log-table thead {
        background: linear-gradient(120deg, #f8fafc, #eef2ff);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 11px;
        color: #64748b;
    }

    @media (max-width: 1024px) {
        .uw-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .uw-wrap {
            padding: 20px 16px 48px;
        }

        .uw-timeline {
            padding: 24px 18px;
        }

        .uw-timeline-track {
            left: 44px;
            right: 44px;
        }
    }
</style>

<div class="full-row" style="background:#f3f4f5; min-height: calc(100vh - 80px);">
    <div class="uw-wrap">
        <?php if (!empty($data['msg'])): ?>
            <div class="uw-card" style="border-radius:12px;border:1px solid #c3c6d6;background:#fff8ec;">
                
            </div>
        <?php endif; ?>

        <?php if ($status === 'rejected'): ?>
            <div style="background:#feeced;border:1.5px solid #f31260;border-radius:12px;padding:14px 20px;color:#c33442;">
                Yêu cầu này đã bị từ chối bởi môi giới. Bạn có thể gửi yêu cầu mới hoặc liên hệ trực tiếp để được hỗ trợ.
            </div>
        <?php endif; ?>

        <div class="uw-header">
            <div class="uw-breadcrumb">
                <a href="<?= BASEURL ?>/home/index">Trang chủ</a>
                <span>›</span>
                <a href="<?= BASEURL ?>/userWorkspace/index?section=requests">Yêu cầu của tôi</a>
                <span>›</span>
                <span style="color:#0040a1;">Yêu cầu #<?= (int) ($request['id'] ?? 0) ?></span>
            </div>
            <div class="uw-headline">
                <h1>Chi tiết yêu cầu <span>#<?= (int) ($request['id'] ?? 0) ?></span></h1>
                <div class="uw-badges">
                    <span class="uw-badge <?= $statusClass ?>">● <?= $statusLabel ?></span>
                    <span class="uw-badge <?= $caseClass ?>"><?= $caseLabel ?></span>
                    <span class="uw-badge <?= $apptClass ?>"><?= $apptLabel ?></span>
                    <?php if ($createdAt): ?>
                        <span style="font-size:12px;color:#737785;">Tạo ngày <?= $createdAt ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="uw-layout">
            <div class="uw-main">
                <div class="uw-card">
                    <div class="uw-card-header">
                        <p class="uw-card-label">Tiến trình xử lý</p>
                    </div>
                    <div class="uw-timeline">
                        <div class="uw-timeline-track">
                            <div class="uw-timeline-fill <?= $isCancelled ? 'is-cancelled' : '' ?>"
                                style="width:<?= $t4Done ? '100' : ($t3Done ? '75' : ($t2Done ? '50' : ($t1Done ? '25' : '5'))) ?>%;">
                            </div>
                        </div>
                        <div class="uw-step <?= $t1Done ? 'done' : ($cancelStep === 1 ? 'cancelled' : 'active') ?>">
                            <div class="uw-step-dot"><?= $t1Done ? '✓' : ($cancelStep === 1 ? '✕' : '1') ?></div>
                            <span class="uw-step-label">Tiếp nhận</span>
                            <span
                                class="uw-step-sub"><?= $t1Done ? $statusLabel : ($cancelStep === 1 ? 'Đã hủy' : 'Đang chờ') ?></span>
                        </div>
                        <div
                            class="uw-step <?= $t2Done ? 'done' : ($cancelStep === 2 ? 'cancelled' : ($t1Done ? 'active' : '')) ?>">
                            <div class="uw-step-dot"><?= $t2Done ? '✓' : ($cancelStep === 2 ? '✕' : '2') ?></div>
                            <span class="uw-step-label">Lịch hẹn</span>
                            <span class="uw-step-sub"><?= $apptLabel ?></span>
                        </div>
                        <div
                            class="uw-step <?= $t3Done ? 'done' : ($cancelStep === 3 ? 'cancelled' : ($t2Done ? 'active' : '')) ?>">
                            <div class="uw-step-dot"><?= $t3Done ? '✓' : ($cancelStep === 3 ? '✕' : '3') ?></div>
                            <span class="uw-step-label">Xem nhà</span>
                            <span
                                class="uw-step-sub"><?= $t3Done ? 'Đã xem' : ($cancelStep === 3 ? 'Đã hủy' : 'Đang chờ') ?></span>
                        </div>
                        <div
                            class="uw-step <?= $t4Done ? 'done' : ($cancelStep === 4 ? 'cancelled' : ($t3Done ? 'active' : '')) ?>">
                            <div class="uw-step-dot"><?= $t4Done ? '✓' : ($cancelStep >= 1 && !$t4Done ? '✕' : '4') ?>
                            </div>
                            <span class="uw-step-label">Kết quả</span>
                            <span
                                class="uw-step-sub"><?= $t4Done ? $caseLabel : ($cancelStep === 4 ? 'Đã hủy' : 'Chờ cập nhật') ?></span>
                        </div>
                    </div>
                </div>

                <?php
                $peerRows = [];
                if (!empty($request)) {
                    $peerRows[] = ['data' => $request, 'is_primary' => true];
                }
                $seenIds = [];
                if (!empty($request['id'])) {
                    $seenIds[(int) $request['id']] = true;
                }
                foreach ($peerContacts as $peer) {
                    $peerId = isset($peer['id']) ? (int) $peer['id'] : 0;
                    if ($peerId > 0 && isset($seenIds[$peerId])) {
                        continue;
                    }
                    $peerRows[] = ['data' => $peer, 'is_primary' => false];
                    if ($peerId > 0) {
                        $seenIds[$peerId] = true;
                    }
                }
                ?>

                
                <div class="uw-info-grid"
                    style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
                   
                    <div class="uw-card">
                        <div class="uw-card-header">
                            <p class="uw-card-label">Nhu cầu của bạn</p>
                        </div>
                        <div class="uw-card-body">
                            <div class="uw-info-list">
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Ngân sách</span>
                                    <span
                                        class="uw-info-value"><?= htmlspecialchars((string) ($request['desired_budget'] ?? 'Chưa có')) ?></span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Khu vực</span>
                                    <span
                                        class="uw-info-value"><?= htmlspecialchars((string) ($request['desired_area'] ?? 'Chưa có')) ?></span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Dọn vào</span>
                                    <span
                                        class="uw-info-value"><?= htmlspecialchars((string) ($request['desired_move_in_time'] ?? 'Chưa rõ')) ?></span>
                                </div>
                            </div>
                            <?php if (!empty($request['requirement'])): ?>
                                <div style="margin-top:16px;">
                                    <div
                                        style="font-size:10px;color:#737785;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px;">
                                        Yêu cầu chi tiết
                                    </div>
                                    <div class="uw-req-block"><?= htmlspecialchars((string) $request['requirement']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $propertyImage = trim((string) ($request['property_image'] ?? ''));
                    $propertyImagePath = $propertyImage !== '' ? __DIR__ . '/../../../admin/property/' . $propertyImage : '';
                    $hasPropertyImage = $propertyImage !== '' && file_exists($propertyImagePath);
                    ?>
                     <div class="uw-card">
                        <div class="uw-card-body uw-card-body-1">
                            <?php if ($hasPropertyImage): ?>
                                <div class="uw-property-image">
                                    <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($propertyImage) ?>" alt="Ảnh bất động sản">
                                </div>
                            <?php endif; ?>

                            <div class="uw-info-list uw-info-list-1 ">
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Tên BĐS</span>
                                    <span class="uw-info-value" style="font-weight:700;">
                                        <?= htmlspecialchars((string) ($request['property_title'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Vị trí</span>
                                    <span
                                        class="uw-info-value"><?= htmlspecialchars((string) ($request['property_location'] ?? 'Chưa có')) ?></span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Giá</span>
                                    <span class="uw-info-value">
                                        <?= isset($request['property_price']) && (float) $request['property_price'] > 0
                                            ? number_format((float) $request['property_price'], 0, ',', '.') . ' VND'
                                            : 'Liên hệ' ?>
                                    </span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Lịch đề xuất</span>
                                    <span class="uw-info-value"><?= $appointmentRequestedAt ?></span>
                                </div>
                                <div class="uw-info-row">
                                    <span class="uw-info-label">Lịch đã xác nhận</span>
                                    <span class="uw-info-value"><?= $appointmentConfirmedAt ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <?php if (!empty($peerRows)): ?>
                    <div class="uw-card">
                        <div class="uw-card-header">
                            <p class="uw-card-label">Các khách hàng khác</p>
                            <span style="font-size:18px;">👥</span>
                        </div>
                        <div class="uw-card-body">
                            <div class="uw-peer-table">
                                <div class="uw-peer-scroll">
                                    <div class="uw-peer-grid">
                                        <div class="uw-peer-header">
                                            <span>Thời gian</span>
                                            <span>Khách hàng</span>
                                            <span>Trạng thái hồ sơ</span>
                                            <span>Lịch hẹn</span>
                                            <span>Kết quả</span>
                                        </div>
                                        <div class="uw-peer-body">
                                            <?php foreach ($peerRows as $row):
                                                $item = $row['data'];
                                                $createdLabel = !empty($item['created_at']) ? date('d/m/Y H:i', strtotime((string) $item['created_at'])) : '--';
                                                $customerLabel = trim((string) ($item['inquirer_name'] ?? 'Khách hàng'));
                                                $contactLabel = trim((string) ($item['phone'] ?? ($item['work_email'] ?? '')));
                                                $leadId = isset($item['id']) ? (int) $item['id'] : 0;
                                                $peerStatus = strtolower((string) ($item['status'] ?? 'pending'));
                                                $peerCase = strtolower((string) ($item['case_status'] ?? 'new'));
                                                $peerAppt = strtolower((string) ($item['appointment_status'] ?? 'none'));
                                                [$peerStatusLabel, $peerStatusCls] = $statusMap[$peerStatus] ?? ['--', 'uw-badge-muted'];
                                                [$peerCaseLabel] = $caseMap[$peerCase] ?? [''];
                                                [$peerApptLabel, $peerApptCls] = $apptMap[$peerAppt] ?? ['--', 'uw-badge-muted'];
                                                $apptRequested = !empty($item['appointment_requested_at']) ? date('d/m/Y H:i', strtotime((string) $item['appointment_requested_at'])) : 'Chưa đặt lịch';
                                                $apptConfirmed = !empty($item['appointment_confirmed_at']) ? date('d/m/Y H:i', strtotime((string) $item['appointment_confirmed_at'])) : 'Chưa xác nhận';
                                                $workflowUpdated = !empty($item['workflow_updated_at']) ? date('d/m/Y H:i', strtotime((string) $item['workflow_updated_at'])) : '--';
                                                ?>
                                                <div class="uw-peer-row <?= $row['is_primary'] ? 'is-primary' : '' ?>">
                                                    <span><?= htmlspecialchars($createdLabel) ?></span>
                                                    <div class="uw-peer-meta">
                                                        <span><?= htmlspecialchars($customerLabel) ?></span>
                                                        <span><?= $contactLabel !== '' ? htmlspecialchars($contactLabel) : 'Không có liên hệ' ?></span>
                                                    </div>
                                                    <div class="uw-peer-meta">
                                                        <span>ID lead #<?= $leadId ?></span>
                                                    </div>
                                                    <div class="uw-peer-meta">
                                                        <span><span class="uw-badge <?= $peerStatusCls ?>" style="font-size:11px;">● <?= $peerStatusLabel ?></span></span>
                                                    </div>
                                                    <div class="uw-peer-meta">
                                                        <span><span class="uw-badge <?= $peerApptCls ?>" style="font-size:11px;"><?= $peerApptLabel ?></span></span>
                                                        <small>Đề xuất: <?= htmlspecialchars($apptRequested) ?></small>
                                                        <small>Xác nhận: <?= htmlspecialchars($apptConfirmed) ?></small>
                                                    </div>
                                                    <div class="uw-peer-meta">
                                                        <?php if (trim($peerCaseLabel) !== ''): ?>
                                                            <span><?= htmlspecialchars($peerCaseLabel) ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($workflowUpdated !== '--'): ?>
                                                            <small>Cập nhật: <?= htmlspecialchars($workflowUpdated) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <aside class="uw-sidebar">
                <div class="uw-card">
                    <div class="uw-card-header">
                        <p class="uw-card-label">Tóm tắt trạng thái</p>
                    </div>
                    <div class="uw-card-body">
                        <div class="uw-summary-list">
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Tiếp nhận</span>
                                <span class="uw-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </div>
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Tiến trình</span>
                                <span class="uw-badge <?= $caseClass ?>"><?= $caseLabel ?></span>
                            </div>
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Lịch hẹn</span>
                                <span class="uw-badge <?= $apptClass ?>"><?= $apptLabel ?></span>
                            </div>
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Môi giới</span>
                                <span
                                    class="uw-summary-val"><?= htmlspecialchars((string) ($request['agent_name'] ?? 'Chưa phân công')) ?></span>
                            </div>
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Điện thoại</span>
                                <span
                                    class="uw-summary-val"><?= htmlspecialchars((string) ($request['agent_phone'] ?? '')) ?></span>
                            </div>
                            <div class="uw-summary-row">
                                <span class="uw-summary-key">Email</span>
                                <span
                                    class="uw-summary-val"><?= htmlspecialchars((string) ($request['agent_email'] ?? '')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uw-card">
                    <div class="uw-card-header">
                        <p class="uw-card-label">Đặt lịch xem nhà</p>
                    </div>
                    <div class="uw-card-body">
                        <?php if (!$canSubmitAppointment): ?>
                            <div
                                style="background:#fff1e8;border:1px solid #f5a623;border-radius:12px;padding:12px 14px;color:#b45309;font-size:13px;margin-bottom:16px;">
                                Bạn chỉ có thể gửi lịch mới khi yêu cầu đã được tiếp nhận và không có lịch đang hoạt động.
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($busySlots)): ?>
                            <div style="margin-bottom:16px;">
                                <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                                    Khung giờ đã được đặt
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    <?php foreach ($busySlots as $slot): ?>
                                        <span style="padding:6px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:12px;font-weight:600;">
                                            <?= date('d/m H:i', strtotime(str_replace('T', ' ', $slot))) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <form method="post"
                            action="<?= BASEURL ?>/userWorkspace/submitAppointment/<?= (int) ($request['id'] ?? 0) ?>">
                            <div class="uw-form-group">
                                <label class="uw-form-label">Ngày giờ đề xuất</label>
                                <input type="datetime-local" class="uw-form-control" name="appointment_at"
                                    <?= $canSubmitAppointment ? 'required' : 'disabled' ?>>
                            </div>
                            <div class="uw-form-group">
                                <label class="uw-form-label">Ghi chú</label>
                                <textarea class="uw-form-control" rows="3" name="appointment_note"
                                    <?= $canSubmitAppointment ? '' : 'disabled' ?>></textarea>
                            </div>
                            <button type="submit" class="uw-submit-btn" <?= $canSubmitAppointment ? '' : 'disabled' ?>>Gửi lịch hẹn</button>
                        </form>
                    </div>
                </div>

                <div class="uw-card">
                    <div class="uw-card-body" style="padding:20px;">
                        <a href="<?= BASEURL ?>/userWorkspace/index?section=requests" class="uw-back-btn">← Quay lại
                            danh sách yêu cầu</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var busySlots = <?php echo json_encode($busySlots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        var input = document.querySelector('input[name="appointment_at"]');
        var submitButton = document.querySelector('.uw-submit-btn');
        if (!input || !submitButton) {
            return;
        }

        var conflictHint = document.createElement('div');
        conflictHint.textContent = 'Khung giờ này đã có lịch khác. Vui lòng chọn thời gian khác.';
        conflictHint.style.marginTop = '8px';
        conflictHint.style.fontSize = '12px';
        conflictHint.style.fontWeight = '600';
        conflictHint.style.color = '#b91c1c';
        conflictHint.style.display = 'none';
        input.parentNode.appendChild(conflictHint);

        function checkConflict() {
            var value = input.value;
            var hasConflict = value && busySlots.indexOf(value) !== -1;
            conflictHint.style.display = hasConflict ? 'block' : 'none';
            if (hasConflict) {
                submitButton.setAttribute('disabled', 'disabled');
                submitButton.classList.add('is-disabled');
            } else if (<?php echo $canSubmitAppointment ? 'true' : 'false'; ?>) {
                submitButton.removeAttribute('disabled');
                submitButton.classList.remove('is-disabled');
            }
        }

        input.addEventListener('input', checkConflict);
        input.addEventListener('change', checkConflict);
        checkConflict();
    });
</script>
