<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$inquiry = isset($data['inquiry']) && is_array($data['inquiry']) ? $data['inquiry'] : [];
$logs = isset($data['logs']) && is_array($data['logs']) ? $data['logs'] : [];
$peerContacts = isset($data['peerContacts']) && is_array($data['peerContacts']) ? $data['peerContacts'] : [];

$status = (string) ($inquiry['status'] ?? 'pending');
$caseStatus = (string) ($inquiry['case_status'] ?? 'new');
$appointmentStatus = (string) ($inquiry['appointment_status'] ?? 'none');

$propertyRented = !empty($data['propertyRented']);

$canReviewLead = !$propertyRented && $status === 'pending';
$canConfirmAppointment = !$propertyRented && $status === 'accepted' && in_array($appointmentStatus, ['pending', 'confirmed'], true);
$canMarkViewed = !$propertyRented && $status === 'accepted' && in_array($appointmentStatus, ['confirmed', 'completed'], true) && $caseStatus !== 'completed';
$canComplete = !$propertyRented && $status === 'accepted' && in_array($caseStatus, ['viewed', 'scheduled'], true);

$statusMap = [
    'pending' => ['Chờ xác nhận', 'ld-badge-wait'],
    'accepted' => ['Đã tiếp nhận', 'ld-badge-ok'],
    'rejected' => ['Đã từ chối', 'ld-badge-danger'],
];
$caseMap = [
    'new' => ['Mới tạo', 'ld-badge-muted'],
    'scheduled' => ['Đã lên lịch', 'ld-badge-info'],
    'viewed' => ['Đã xem nhà', 'ld-badge-info'],
    'completed' => ['Thành công', 'ld-badge-ok'],
    'cancelled' => ['Đã hủy', 'ld-badge-danger'],
];
$apptMap = [
    'none' => ['Chưa có lịch', 'ld-badge-muted'],
    'pending' => ['Chờ xác nhận', 'ld-badge-wait'],
    'confirmed' => ['Đã xác nhận', 'ld-badge-info'],
    'completed' => ['Đã hoàn thành', 'ld-badge-ok'],
    'cancelled' => ['Đã hủy', 'ld-badge-danger'],
];
[$statusLabel, $statusCls] = $statusMap[$status] ?? ['--', 'ld-badge-muted'];
[$caseLabel, $caseCls] = $caseMap[$caseStatus] ?? ['--', 'ld-badge-muted'];
[$apptLabel, $apptCls] = $apptMap[$appointmentStatus] ?? ['--', 'ld-badge-muted'];

$isCancelled = ($status === 'rejected' || $caseStatus === 'cancelled');

$appointmentRequestedLabel = !empty($inquiry['appointment_requested_at'])
    ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_requested_at']))
    : 'Chưa có';
$appointmentConfirmedLabel = !empty($inquiry['appointment_confirmed_at'])
    ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_confirmed_at']))
    : 'Chưa có';

$customerName = trim((string) ($inquiry['inquirer_name'] ?? 'Khách hàng'));
$customerPhone = trim((string) ($inquiry['phone'] ?? ''));
$customerEmail = trim((string) ($inquiry['work_email'] ?? ''));
$customerContactParts = array_filter([$customerPhone, $customerEmail], static function ($value) {
    return trim((string) $value) !== '';
});

$t1Done = !empty($inquiry['contacted_at']); // was actually accepted
$t2Done = in_array($appointmentStatus, ['confirmed', 'completed']);
$t3Done = !empty($inquiry['viewed_at']) || $caseStatus === 'viewed';
$t4Done = $caseStatus === 'completed';

$cancelStep = 0;
if ($isCancelled) {
    if (!$t1Done)
        $cancelStep = 1;
    elseif (!$t2Done)
        $cancelStep = 2;
    elseif (!$t3Done)
        $cancelStep = 3;
    else
        $cancelStep = 4;
}
?>
<style>
    .ld-wrap {
        max-width: 1280px;
        margin: 0 auto;
        padding: 32px 24px 64px;
        display: flex;
        flex-direction: column;
        gap: 28px;
    }

    /* ── Breadcrumb + header ── */
    .ld-page-header {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ld-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #737785;
        font-weight: 600;
    }

    .ld-breadcrumb a {
        color: #737785;
        text-decoration: none;
    }

    .ld-breadcrumb a:hover {
        color: #0040a1;
    }

    .ld-breadcrumb-sep {
        color: #c3c6d6;
    }

    .ld-headline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ld-headline h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #191c1d;
        letter-spacing: -0.01em;
        line-height: 1.3;
    }

    .ld-headline h1 span {
        color: #0040a1;
    }

    /* ── Badges ── */
    .ld-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1;
    }

    .ld-badge-ok {
        background: #eaf9ef;
        color: #238a4b;
    }

    .ld-badge-wait {
        background: #fff1e8;
        color: #c65a00;
    }

    .ld-badge-danger {
        background: #feeced;
        color: #c33442;
    }

    .ld-badge-muted {
        background: #e7e8e9;
        color: #424654;
    }

    .ld-badge-info {
        background: #e8f1ff;
        color: #0040a1;
    }

    /* ── Cards ── */
    .ld-card {
        background: #ffffff;
        border: 1px solid #c3c6d6;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .ld-card-body-1 {
        padding: 0px !important;
    }

    .ld-card-body {
        padding: 24px;
    }

    .ld-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid #e1e3e4;
        background: #fafbff;
    }

    .ld-section-label {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: black;
    }

    /* ── Layout ── */
    .ld-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }

    .ld-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .ld-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ── Timeline ── */
    .ld-timeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 24px 32px;
    }

    .ld-timeline-track {
        position: absolute;
        top: 44px;
        left: calc(32px + 20px);
        right: calc(32px + 20px);
        height: 3px;
        background: #e1e3e4;
        border-radius: 9999px;
    }

    .ld-timeline-fill {
        height: 100%;
        background: #0040a1;
        border-radius: 9999px;
        transition: width 0.4s ease;
    }

    .ld-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .ld-step-dot {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        border: 3px solid #e1e3e4;
        background: #fff;
        color: #c3c6d6;
        transition: all 0.25s ease;
    }

    .ld-step.done .ld-step-dot {
        background: #0040a1;
        border-color: #0040a1;
        color: #fff;
    }

    .ld-step.active .ld-step-dot {
        background: #fff;
        border-color: #0040a1;
        color: #0040a1;
        box-shadow: 0 0 0 4px rgba(0, 64, 161, 0.12);
    }

    .ld-step-label {
        font-size: 12px;
        font-weight: 700;
        color: #737785;
        text-align: center;
        white-space: nowrap;
    }

    .ld-step.done .ld-step-label,
    .ld-step.active .ld-step-label {
        color: #0040a1;
    }

    .ld-step-sub {
        font-size: 10px;
        color: #c3c6d6;
        font-weight: 600;
        text-align: center;
    }

    .ld-step.done .ld-step-sub {
        color: #0040a1;
    }

    .ld-step.cancelled .ld-step-dot {
        background: #feeced;
        border-color: #f31260;
        color: #f31260;
    }

    .ld-step.cancelled .ld-step-label,
    .ld-step.cancelled .ld-step-sub {
        color: #f31260;
    }

    .ld-timeline-fill.is-cancelled {
        background: #f31260;
    }

    /* ── Info grid ── */
    .ld-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .ld-info-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .ld-info-list-1 {
        padding: 16px;
    }
    .ld-peer-table {
        border: 1px solid #e1e3e4;
        border-radius: 12px;
        overflow: hidden;
    }

    .ld-peer-scroll {
        overflow-x: auto;
    }

    .ld-peer-grid {
        min-width: 600px;
    }

    .ld-peer-header,
    .ld-peer-row {
        display: grid;
        grid-template-columns: 170px 200px 160px 200px 150px;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
    }

    .ld-peer-header {
        background: #f8fafc;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .ld-peer-body {
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
    }

    .ld-peer-row {
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        border-top: 1px solid #eef1f5;
        background: #fff;
    }

    .ld-peer-row:nth-child(odd) {
        background: #fbfcfe;
    }

    .ld-peer-row.is-primary {
        box-shadow: inset 2px 0 0 #0040a1;
    }

    .ld-peer-meta {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-weight: 500;
    }

    .ld-peer-meta span:last-child {
        font-size: 12px;
        /* color: #6b7280; */
        font-weight: 500;
    }

    .ld-peer-title {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .ld-peer-title small,
    .ld-peer-meta small {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
    }

    .ld-info-row {
        display: flex;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #f0f1f2;
        align-items: baseline;
    }

    .ld-info-row:last-child {
        border-bottom: none;
    }

    .ld-info-label {
        font-size: 11px;
        font-weight: 700;
        color: #737785;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        min-width: 80px;
        flex-shrink: 0;
    }

    .ld-info-value {
        font-size: 14px;
        color: #191c1d;
        font-weight: 500;
    }

    .ld-property-image {
        width: 100%;
        height: 280px;
        overflow: hidden;
        background: #f3f4f5;
        margin-bottom: 16px;
    }

    .ld-property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* ── Requirement block ── */
    .ld-req-block {
        background: #f3f4f5;
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 4px;
        font-size: 13px;
        color: #424654;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    /* ── Workflow actions ── */
    .ld-action-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ld-action-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        align-items: stretch;
    }

    .ld-action-pair>form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .ld-action-pair>form>.ld-action-btn {
        margin-top: auto;
    }

    .ld-action-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 13px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid #c3c6d6;
        background: #fff;
        color: #191c1d;
        transition: all 0.18s ease;
        text-align: left;
        gap: 10px;
    }

    .ld-action-btn:hover {
        background: #f3f4f5;
        border-color: #a0a5b9;
    }

    .ld-action-btn.primary {
        background: #0040a1;
        border-color: #0040a1;
        color: #fff;
        box-shadow: 0 3px 10px rgba(0, 64, 161, 0.25);
    }

    .ld-action-btn.primary:hover {
        background: #003285;
        border-color: #003285;
        box-shadow: 0 5px 16px rgba(0, 64, 161, 0.35);
    }

    .ld-action-btn.success-btn {
        background: linear-gradient(135deg, #17c964 0%, #12a053 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 3px 10px rgba(23, 201, 100, 0.28);
    }

    .ld-action-btn.success-btn:hover {
        box-shadow: 0 5px 16px rgba(23, 201, 100, 0.4);
        transform: translateY(-1px);
    }

    .ld-action-btn.danger-btn {
        background: linear-gradient(135deg, #f31260 0%, #c20e4d 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 3px 10px rgba(243, 18, 96, 0.26);
    }

    .ld-action-btn.danger-btn:hover {
        box-shadow: 0 5px 16px rgba(243, 18, 96, 0.38);
        transform: translateY(-1px);
    }

    .ld-action-btn.warn-btn {
        background: linear-gradient(135deg, #f5a623 0%, #d4880a 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 3px 10px rgba(245, 166, 35, 0.28);
    }

    .ld-action-btn.warn-btn:hover {
        transform: translateY(-1px);
    }

    .ld-action-btn.info-btn {
        background: linear-gradient(135deg, #0056d2 0%, #0040a1 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 3px 10px rgba(0, 86, 210, 0.26);
    }

    .ld-action-btn.info-btn:hover {
        transform: translateY(-1px);
    }

    .ld-action-icon {
        font-size: 18px;
        opacity: 0.85;
        flex-shrink: 0;
    }

    .ld-divider {
        border: none;
        border-top: 1px solid #e1e3e4;
        margin: 4px 0;
    }

    /* ── Sidebar Actions ── */
    .ld-sidebar-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s ease;
        text-align: left;
        margin-bottom: 8px;
    }

    .ld-sidebar-btn:last-child {
        margin-bottom: 0;
    }

    .ld-sidebar-btn.primary {
        background: #0040a1;
        color: #fff;
        border: 1px solid #0040a1;
    }

    .ld-sidebar-btn.primary:hover {
        background: #003285;
    }

    .ld-sidebar-btn.success {
        background: #16a34a;
        color: #fff;
        border: 1px solid #16a34a;
    }

    .ld-sidebar-btn.success:hover {
        background: #15803d;
    }

    .ld-sidebar-btn.danger-outline {
        background: transparent;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }

    .ld-sidebar-btn.danger-outline:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .ld-sidebar-btn.primary-outline {
        background: transparent;
        color: #0040a1;
        border: 1px solid #0040a1;
    }

    .ld-sidebar-btn.primary-outline:hover {
        background: #eff6ff;
    }

    .ld-sidebar-form-card {
        background: #fff;
        border: 1px solid #e1e3e4;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
    }

    .ld-sidebar-form-card.danger {
        border-color: #fca5a5;
        background: #fef2f2;
    }

    /* ── Appointment form fields ── */
    .ld-form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
    }

    .ld-form-label {
        font-size: 12px;
        font-weight: 700;
        color: #424654;
    }

    .ld-form-control {
        width: 100%;
        padding: 10px 13px;
        border-radius: 10px;
        border: 1.5px solid #c3c6d6;
        background: #fafbff;
        font-size: 14px;
        color: #191c1d;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .ld-form-control:focus {
        border-color: #0040a1;
        box-shadow: 0 0 0 3px rgba(0, 64, 161, 0.1);
    }

    /* ── Status summary ── */
    .ld-summary-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .ld-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .ld-summary-key {
        font-size: 12px;
        font-weight: 700;
        color: #737785;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ld-time-row {
        display: flex;
        flex-direction: column;
        padding: 10px 0;
        border-bottom: 1px solid #f0f1f2;
        gap: 3px;
    }

    .ld-time-row:last-child {
        border-bottom: none;
    }

    .ld-time-key {
        font-size: 11px;
        font-weight: 700;
        color: #737785;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .ld-time-val {
        font-size: 13px;
        font-weight: 600;
        color: #191c1d;
    }

    .ld-time-val.muted {
        color: #a0a5b9;
        font-weight: 500;
    }

    /* ── Notes form ── */
    .ld-notes-textarea {
        width: 100%;
        min-height: 90px;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1.5px solid #c3c6d6;
        background: #fafbff;
        font-size: 14px;
        color: #191c1d;
        resize: vertical;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
        font-family: inherit;
    }

    .ld-notes-textarea:focus {
        border-color: #0040a1;
        box-shadow: 0 0 0 3px rgba(0, 64, 161, 0.1);
    }

    .ld-note-submit {
        width: 100%;
        padding: 11px;
        border-radius: 10px;
        background: #0040a1;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.18s ease, box-shadow 0.18s ease;
    }

    .ld-note-submit:hover {
        background: #003285;
        box-shadow: 0 4px 12px rgba(0, 64, 161, 0.25);
    }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
        .ld-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .ld-info-grid {
            grid-template-columns: 1fr;
        }

        .ld-action-pair {
            grid-template-columns: 1fr;
        }

        .ld-timeline {
            padding: 20px 16px;
        }

        .ld-wrap {
            padding: 20px 16px 48px;
        }
    }
</style>

<div class="full-row" style="background:f3f4f514; min-height: calc(100vh - 80px);">
    <div class="ld-wrap">


        <?php if ($propertyRented && $caseStatus !== 'completed'): ?>
            <div
                style="background:#fff1e8;border:1.5px solid #f5a623;border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <span style="font-size:20px;">🔒</span>
                <div>
                    <strong style="color:#b45309;font-size:14px;">BĐS này đã được cho thuê</strong>
                    <p style="margin:2px 0 0;font-size:13px;color:#92400e;">Một lead khác đã hoàn tất cho BĐS này. Các thao
                        tác tiếp nhận và xử lý bị vô hiệu hóa.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page header -->
        <div class="ld-page-header">
            <div class="ld-breadcrumb">
                <a href="<?= BASEURL ?>/agentWorkspace">Agent Workspace</a>
                <span class="ld-breadcrumb-sep">›</span>
                <a href="<?= BASEURL ?>/agentWorkspace?section=leads">Danh sách lead</a>
                <span class="ld-breadcrumb-sep">›</span>
                <span style="color:#0040a1;">Lead #<?= (int) ($inquiry['id'] ?? 0) ?></span>
            </div>
            <div class="ld-headline">
                <h1>Chi tiết Lead <span>#<?= (int) ($inquiry['id'] ?? 0) ?></span></h1>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span class="ld-badge <?= $statusCls ?>">● <?= $statusLabel ?></span>
                    <span class="ld-badge <?= $caseCls ?>"><?= $caseLabel ?></span>
                    <?php if (!empty($inquiry['created_at'])): ?>
                        <span style="font-size:12px;color:#737785;">
                            <?= date('d/m/Y H:i', strtotime((string) $inquiry['created_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="ld-layout">
            <!-- ══ LEFT MAIN ══ -->
            <div class="ld-main">

                <!-- Timeline -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <p class="ld-section-label">Tiến trình xử lý</p>
                    </div>
                    <div class="ld-timeline">
                        <div class="ld-timeline-track">
                            <div class="ld-timeline-fill <?= $isCancelled ? 'is-cancelled' : '' ?>"
                                style="width:<?= $t4Done ? '100' : ($t3Done ? '75' : ($t2Done ? '50' : ($t1Done ? '25' : '0'))) ?>%;">
                            </div>
                        </div>
                        <div
                            class="ld-step <?= $t1Done ? 'done' : ($cancelStep === 1 ? 'cancelled' : ($status === 'pending' ? 'active' : '')) ?>">
                            <div class="ld-step-dot"><?= $t1Done ? '✓' : ($cancelStep === 1 ? '✕' : '1') ?></div>
                            <span class="ld-step-label">Tiếp nhận</span>
                            <span
                                class="ld-step-sub"><?= $t1Done ? 'Hoàn thành' : ($cancelStep === 1 ? 'Đã từ chối' : 'Đang chờ') ?></span>
                        </div>
                        <div
                            class="ld-step <?= $t2Done ? 'done' : ($cancelStep === 2 ? 'cancelled' : ($t1Done && !$isCancelled ? 'active' : '')) ?>">
                            <div class="ld-step-dot"><?= $t2Done ? '✓' : ($cancelStep === 2 ? '✕' : '2') ?></div>
                            <span class="ld-step-label">Lịch hẹn</span>
                            <span
                                class="ld-step-sub"><?= $t2Done ? $apptLabel : ($cancelStep === 2 ? 'Đã hủy' : $apptLabel) ?></span>
                        </div>
                        <div
                            class="ld-step <?= $t3Done ? 'done' : ($cancelStep === 3 ? 'cancelled' : ($t2Done && !$isCancelled ? 'active' : '')) ?>">
                            <div class="ld-step-dot"><?= $t3Done ? '✓' : ($cancelStep === 3 ? '✕' : '3') ?></div>
                            <span class="ld-step-label">Xem nhà</span>
                            <span
                                class="ld-step-sub"><?= $t3Done ? 'Hoàn thành' : ($cancelStep === 3 ? 'Đã hủy' : 'Chờ xử lý') ?></span>
                        </div>
                        <div
                            class="ld-step <?= $t4Done ? 'done' : ($cancelStep === 4 ? 'cancelled' : ($t3Done && !$isCancelled ? 'active' : '')) ?>">
                            <div class="ld-step-dot">
                                <?= $t4Done ? '✓' : ($cancelStep >= 1 && !$t4Done ? ($cancelStep === 4 ? '✕' : '') : '4') ?>
                            </div>
                            <span class="ld-step-label">Kết quả</span>
                            <span
                                class="ld-step-sub"><?= $t4Done ? $caseLabel : ($cancelStep === 4 ? 'Đã hủy' : ($cancelStep > 0 ? 'Bỏ qua' : 'Chờ kết quả')) ?></span>
                        </div>
                    </div>
                </div>

                <div class="ld-info-grid">
                    <!-- Customer card -->
                    <div class="ld-card">
                        <div class="ld-card-header">
                            <p class="ld-section-label">Khách hàng</p>
                            <span style="font-size:18px;">👤</span>
                        </div>
                        <div class="ld-card-body">
                            <div style="margin-bottom:16px;">
                                <div style="font-size:20px;font-weight:700;color:#191c1d;line-height:1.2;">
                                    <?= htmlspecialchars((string) ($inquiry['inquirer_name'] ?? 'Khách hàng')) ?>
                                </div>
                                <div style="font-size:13px;color:#737785;margin-top:4px;">
                                    <?= htmlspecialchars((string) ($inquiry['phone'] ?? '')) ?>
                                </div>
                                <?php if (!empty($inquiry['work_email'])): ?>
                                    <div style="font-size:13px;color:#737785;">
                                        <?= htmlspecialchars((string) $inquiry['work_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ld-info-list">
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Ngân sách</span>
                                    <span class="ld-info-value">
                                        <?= htmlspecialchars((string) ($inquiry['desired_budget'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Khu vực</span>
                                    <span class="ld-info-value">
                                        <?= htmlspecialchars((string) ($inquiry['desired_area'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Dọn vào</span>
                                    <span class="ld-info-value">
                                        <?= htmlspecialchars((string) ($inquiry['desired_move_in_time'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($inquiry['requirement'])): ?>
                                <div style="margin-top:12px;">
                                    <div
                                        style="font-size:10px;font-weight:700;color:#737785;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                        Nhu cầu
                                    </div>
                                    <div class="ld-req-block"><?= htmlspecialchars((string) $inquiry['requirement']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Property card -->
                    <?php
                    $propertyImage = trim((string) ($inquiry['property_image'] ?? ''));
                    $propertyImagePath = $propertyImage !== '' ? __DIR__ . '/../../../admin/property/' . $propertyImage : '';
                    $hasPropertyImage = $propertyImage !== '' && file_exists($propertyImagePath);
                    ?>
                    <div class="ld-card">
                        <!-- <div class="ld-card-header">
                            <p class="ld-section-label">Bất động sản</p>
                            <i class="fas fa-home"></i>
                        </div> -->
                        <div class="ld-card-body ld-card-body-1">
                            <?php if ($hasPropertyImage): ?>
                                <div class="ld-property-image">
                                    <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($propertyImage) ?>" alt="Ảnh bất động sản">
                                </div>
                            <?php endif; ?>

                            <div class="ld-info-list ld-info-list-1">
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Tên BĐS</span>
                                    <span class="ld-info-value" style="font-weight:700;">
                                        <?= htmlspecialchars((string) ($inquiry['property_title'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Địa chỉ</span>
                                    <span class="ld-info-value">
                                        <?= htmlspecialchars((string) ($inquiry['property_location'] ?? 'Chưa có')) ?>
                                    </span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Lịch đề xuất</span>
                                    <span class="ld-info-value">
                                        <?= !empty($inquiry['appointment_requested_at']) ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_requested_at'])) : '<span style="color:#a0a5b9;">Chưa có</span>' ?>
                                    </span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label">Lịch hẹn</span>
                                    <span class="ld-badge <?= $apptCls ?>" style="font-size:11px;">
                                        <?= $apptLabel ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info grid -->
                <?php
                $peerRows = [];
                if (!empty($inquiry)) {
                    $peerRows[] = ['data' => $inquiry, 'is_primary' => true];
                }
                $seenIds = [];
                if (!empty($inquiry['id'])) {
                    $seenIds[(int) $inquiry['id']] = true;
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
                <?php if (!empty($peerRows)): ?>
                    <div class="ld-card">
                        <div class="ld-card-header">
                            <p class="ld-section-label">Tổng quan liên hệ</p>
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ld-card-body">
                            <div class="ld-peer-table">
                                <div class="ld-peer-scroll">
                                    <div class="ld-peer-grid">
                                        <div class="ld-peer-header">
                                            <span>Thời gian</span>
                                            <span>Khách hàng</span>
                                            <span>Trạng thái hồ sơ</span>
                                            <span>Lịch hẹn</span>
                                            <span>Kết quả</span>
                                        </div>
                                        <div class="ld-peer-body">
                                            <?php foreach ($peerRows as $row):
                                                $item = $row['data'];
                                                $createdLabel = !empty($item['created_at']) ? date('d/m/Y H:i', strtotime((string) $item['created_at'])) : '--';
                                                $customerLabel = trim((string) ($item['inquirer_name'] ?? 'Khách hàng'));
                                                $contactLabel = trim((string) ($item['phone'] ?? ($item['work_email'] ?? '')));
                                                $leadId = isset($item['id']) ? (int) $item['id'] : 0;
                                                $peerStatus = strtolower((string) ($item['status'] ?? 'pending'));
                                                $peerCase = strtolower((string) ($item['case_status'] ?? 'new'));
                                                $peerAppt = strtolower((string) ($item['appointment_status'] ?? 'none'));
                                                [$peerStatusLabel, $peerStatusCls] = $statusMap[$peerStatus] ?? ['--', 'ld-badge-muted'];
                                                [$peerCaseLabel] = $caseMap[$peerCase] ?? [''];
                                                [$peerApptLabel, $peerApptCls] = $apptMap[$peerAppt] ?? ['--', 'ld-badge-muted'];
                                                $apptRequested = !empty($item['appointment_requested_at']) ? date('d/m/Y H:i', strtotime((string) $item['appointment_requested_at'])) : 'Chưa đặt lịch';
                                                $apptConfirmed = !empty($item['appointment_confirmed_at']) ? date('d/m/Y H:i', strtotime((string) $item['appointment_confirmed_at'])) : 'Chưa xác nhận';
                                                $workflowUpdated = !empty($item['workflow_updated_at']) ? date('d/m/Y H:i', strtotime((string) $item['workflow_updated_at'])) : '--';
                                                ?>
                                                <div class="ld-peer-row <?= $row['is_primary'] ? 'is-primary' : '' ?>">
                                                    <span><?= htmlspecialchars($createdLabel) ?></span>
                                                    <div class="ld-peer-meta">
                                                        <span><?= htmlspecialchars($customerLabel) ?></span>
                                                        <span><?= $contactLabel !== '' ? htmlspecialchars($contactLabel) : 'Không có liên hệ' ?></span>
                                                    </div>
                                                    <div class="ld-peer-meta">
                                                        <span>ID lead #<?= $leadId ?></span>
                                                    </div>
                                                    <div class="ld-peer-meta">
                                                        <span><span class="ld-badge <?= $peerStatusCls ?>"
                                                                style="font-size:11px;">&nbsp;<?= $peerStatusLabel ?></span></span>
                                                    </div>
                                                    <div class="ld-peer-meta">
                                                        <span><span class="ld-badge <?= $peerApptCls ?>"
                                                                style="font-size:11px;">&nbsp;<?= $peerApptLabel ?></span></span>
                                                        <small>Đề xuất: <?= htmlspecialchars($apptRequested) ?></small>
                                                        <small>Xác nhận: <?= htmlspecialchars($apptConfirmed) ?></small>
                                                    </div>
                                                    <div class="ld-peer-meta">
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

            </div><!-- end ld-main -->

            <!-- ══ RIGHT SIDEBAR ══ -->
            <div class="ld-sidebar">

                <!-- Workflow actions -->
                <?php if ($canReviewLead || $canConfirmAppointment || $canMarkViewed || $canComplete): ?>
                    <div class="ld-card" style="border-color: #0040a1; box-shadow: 0 4px 12px rgba(0,64,161,0.08);">
                        <div class="ld-card-header">
                            <p class="ld-section-label" style="color:#0040a1;">Thao tác nhanh</p>
                            <span style="font-size:18px;">⚡</span>
                        </div>
                        <div class="ld-card-body" style="display: flex; flex-direction: column; gap: 8px;">

                            <?php if ($canReviewLead): ?>
                                <form method="post"
                                    action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    <input type="hidden" name="action_key" value="accept_lead">
                                    <button type="button" class="ld-sidebar-btn success"
                                        onclick="(function(b){AppPopup.show({type:'success',message:'Bạn có chắc chắn muốn chấp nhận lead này không?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                        <span>Chấp nhận lead</span>
                                        <i class="fa fa-check-circle"></i>
                                    </button>
                                </form>
                                <form method="post"
                                    action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    <input type="hidden" name="action_key" value="reject_lead">
                                    <button type="button" class="ld-sidebar-btn danger-outline"
                                        onclick="(function(b){AppPopup.show({type:'error',message:'Bạn có chắc chắn muốn từ chối lead này không?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                        <span>Từ chối lead</span>
                                        <i class="fa fa-times-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($canConfirmAppointment): ?>
                                <?php if ($canReviewLead): ?>
                                    <hr class="ld-divider" style="margin: 4px 0;"><?php endif; ?>

                                <!-- Form Xác nhận -->
                                <div class="ld-sidebar-form-card">
                                    <div
                                        style="font-size: 13px; font-weight: 700; color: #0284c7; margin-bottom: 8px; display: flex; align-items: center;">
                                        <i class="fa fa-calendar-check-o" style="margin-right: 6px;"></i> Xác nhận lịch
                                    </div>
                                    <form method="post"
                                        action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                        <input type="hidden" name="action_key" value="confirm_appointment">
                                        <?php
                                        $appointmentDate = !empty($inquiry['appointment_requested_at'])
                                            ? str_replace(' ', 'T', substr((string) $inquiry['appointment_requested_at'], 0, 16))
                                            : '';
                                        ?>
                                        <input type="datetime-local" name="appointment_date" class="ld-form-control"
                                            style="padding: 6px 10px; font-size: 12px; border-radius: 6px; margin-bottom: 6px;"
                                            value="<?= htmlspecialchars($appointmentDate) ?>" required>
                                        <textarea name="broker_note" class="ld-form-control" rows="2"
                                            style="padding: 6px 10px; font-size: 12px; border-radius: 6px; resize: vertical; margin-bottom: 8px; min-height: 50px;"
                                            placeholder="Ghi chú thêm..."><?= htmlspecialchars((string) ($inquiry['appointment_note'] ?? '')) ?></textarea>
                                        <button type="button" class="ld-sidebar-btn primary" style="margin-bottom: 0;"
                                            onclick="(function(b){AppPopup.show({type:'info',message:'Xác nhận lịch hẹn này?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                            <span>Gửi xác nhận</span>
                                            <i class="fa fa-paper-plane-o"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Form Hủy -->
                                <div class="ld-sidebar-form-card danger">
                                    <div
                                        style="font-size: 13px; font-weight: 700; color: #9f1239; margin-bottom: 8px; display: flex; align-items: center;">
                                        <i class="fa fa-calendar-times-o" style="margin-right: 6px;"></i> Hủy lịch
                                    </div>
                                    <form method="post"
                                        action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                        <input type="hidden" name="action_key" value="cancel_appointment">
                                        <input type="text" name="broker_note" class="ld-form-control"
                                            style="padding: 6px 10px; font-size: 12px; border-radius: 6px; background: #fff; border-color: #fca5a5; margin-bottom: 8px;"
                                            placeholder="Lý do hủy..." required>
                                        <button type="button" class="ld-sidebar-btn danger-outline" style="margin-bottom: 0;"
                                            onclick="(function(b){AppPopup.show({type:'warning',message:'Hủy lịch hẹn này?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                            <span>Hủy lịch</span>
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <?php if ($canMarkViewed): ?>
                                <?php if ($canReviewLead || $canConfirmAppointment): ?>
                                    <hr class="ld-divider" style="margin: 4px 0;"><?php endif; ?>
                                <form method="post"
                                    action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    <input type="hidden" name="action_key" value="mark_viewed">
                                    <button type="button" class="ld-sidebar-btn primary-outline"
                                        onclick="(function(b){AppPopup.show({type:'info',message:'Đánh dấu khách đã xem nhà?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                        <span>Đã xem nhà</span>
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($canComplete): ?>
                                <?php if ($canReviewLead || $canConfirmAppointment || $canMarkViewed): ?>
                                    <hr class="ld-divider" style="margin: 4px 0;"><?php endif; ?>
                                <form method="post"
                                    action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    <input type="hidden" name="action_key" value="mark_completed">
                                    <button type="button" class="ld-sidebar-btn success"
                                        onclick="(function(b){AppPopup.show({type:'success',message:'Hoàn tất thành công lead này?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                        <span>Thành công</span>
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>

                                <form method="post"
                                    action="<?= BASEURL ?>/agentWorkspace/updateLeadWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                                    <input type="hidden" name="action_key" value="mark_cancelled">
                                    <button type="button" class="ld-sidebar-btn danger-outline"
                                        onclick="(function(b){AppPopup.show({type:'error',message:'Đánh dấu thất bại / đóng lead này?',confirmText:'Chấp nhận',cancelText:'Từ chối',onConfirm:function(){b.closest('form').submit();}});})(this)">
                                        <span>Thất bại</span>
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status summary -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <p class="ld-section-label">Tóm tắt trạng thái</p>
                    </div>
                    <div class="ld-card-body">
                        <div class="ld-summary-list">
                            <div class="ld-summary-row">
                                <span class="ld-summary-key">Tiếp nhận</span>
                                <span class="ld-badge <?= $statusCls ?>"><?= $statusLabel ?></span>
                            </div>
                            <div class="ld-summary-row">
                                <span class="ld-summary-key">Tiến trình</span>
                                <span class="ld-badge <?= $caseCls ?>"><?= $caseLabel ?></span>
                            </div>
                            <div class="ld-summary-row">
                                <span class="ld-summary-key">Lịch hẹn</span>
                                <span class="ld-badge <?= $apptCls ?>"><?= $apptLabel ?></span>
                            </div>
                        </div>
                        <hr class="ld-divider" style="margin:16px 0;">
                        <div>
                            <div class="ld-time-row">
                                <span class="ld-time-key">Đề xuất lịch</span>
                                <span
                                    class="ld-time-val <?= empty($inquiry['appointment_requested_at']) ? 'muted' : '' ?>">
                                    <?= !empty($inquiry['appointment_requested_at']) ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_requested_at'])) : 'Chưa có' ?>
                                </span>
                            </div>
                            <div class="ld-time-row">
                                <span class="ld-time-key">Xác nhận lúc</span>
                                <span
                                    class="ld-time-val <?= empty($inquiry['appointment_confirmed_at']) ? 'muted' : '' ?>">
                                    <?= !empty($inquiry['appointment_confirmed_at']) ? date('d/m/Y H:i', strtotime((string) $inquiry['appointment_confirmed_at'])) : 'Chưa có' ?>
                                </span>
                            </div>
                            <div class="ld-time-row">
                                <span class="ld-time-key">Đã xem lúc</span>
                                <span class="ld-time-val <?= empty($inquiry['viewed_at']) ? 'muted' : '' ?>">
                                    <?= !empty($inquiry['viewed_at']) ? date('d/m/Y H:i', strtotime((string) $inquiry['viewed_at'])) : 'Chưa có' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internal notes -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <p class="ld-section-label">Ghi chú nội bộ</p>
                        <span style="font-size:18px;">📝</span>
                    </div>
                    <div class="ld-card-body">
                        <form method="post"
                            action="<?= BASEURL ?>/agentWorkspace/addLeadNote/<?= (int) ($inquiry['id'] ?? 0) ?>">
                            <textarea class="ld-notes-textarea" name="notes"
                                placeholder="Thêm ghi chú nội bộ về lead này..."></textarea>
                            <button type="submit" class="ld-note-submit">Lưu ghi chú</button>
                        </form>
                    </div>
                </div>

            </div><!-- end ld-sidebar -->
        </div><!-- end ld-layout -->
    </div><!-- end ld-wrap -->
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
