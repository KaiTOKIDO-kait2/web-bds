<?php require_once '../app/views/admin/layouts/header.php'; ?>
<?php
$inquiry = isset($data['inquiry']) && is_array($data['inquiry']) ? $data['inquiry'] : [];
$logs = isset($data['logs']) && is_array($data['logs']) ? $data['logs'] : [];

$status = (string) ($inquiry['status'] ?? 'pending');
$caseStatus = (string) ($inquiry['case_status'] ?? 'new');
$appointmentStatus = (string) ($inquiry['appointment_status'] ?? 'none');
$detailInfoRows = [
    ['label' => 'Khách', 'value' => htmlspecialchars((string) ($inquiry['inquirer_name'] ?? ''))],
    ['label' => 'Môi giới', 'value' => htmlspecialchars((string) ($inquiry['agent_name'] ?? ''))],
    ['label' => 'BĐS', 'value' => htmlspecialchars((string) ($inquiry['property_title'] ?? ''))],
    ['label' => 'Địa chỉ', 'value' => htmlspecialchars((string) ($inquiry['property_location'] ?? ''))],
    ['label' => 'SĐT', 'value' => htmlspecialchars((string) ($inquiry['phone'] ?? ''))],
    ['label' => 'Email', 'value' => htmlspecialchars((string) ($inquiry['work_email'] ?? ''))],
    ['label' => 'Tiếp nhận', 'value' => htmlspecialchars($status)],
    ['label' => 'Tiến trình', 'value' => htmlspecialchars($caseStatus)],
    ['label' => 'Lịch hẹn', 'value' => htmlspecialchars($appointmentStatus)],
    ['label' => 'Đề xuất lịch', 'value' => !empty($inquiry['appointment_requested_at']) ? date('d-m-Y H:i', strtotime((string) $inquiry['appointment_requested_at'])) : 'Chưa có'],
    ['label' => 'Xác nhận lúc', 'value' => !empty($inquiry['appointment_confirmed_at']) ? date('d-m-Y H:i', strtotime((string) $inquiry['appointment_confirmed_at'])) : 'Chưa có'],
    ['label' => 'Đã xem lúc', 'value' => !empty($inquiry['viewed_at']) ? date('d-m-Y H:i', strtotime((string) $inquiry['viewed_at'])) : 'Chưa có'],
    ['label' => 'Ghi chú', 'value' => '<div class="border rounded p-3 bg-light mt-2" style="white-space:pre-wrap;">' . htmlspecialchars((string) ($inquiry['requirement'] ?? '')) . '</div>'],
];
?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Chi tiết quy trình thuê</h3></div>
        <?php if (!empty($data['msg'])): ?><div class="alert alert-info"><?= $data['msg'] ?></div><?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <?php
                $detailInfoTitle = 'Thông tin giao dịch';
                require_once __DIR__ . '/../../shared/workflow_detail_info.php';
                ?>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body">
                    <form method="post" action="<?= BASEURL ?>/adminTransaction/updateStatus/<?= (int) ($inquiry['id'] ?? 0) ?>">
                        <div class="form-group"><label>Trạng thái tiếp nhận</label><select name="status" class="form-control"><?php foreach (($workflowOptions['status'] ?? []) as $step): ?><option value="<?= htmlspecialchars($step) ?>" <?= (($inquiry['status'] ?? 'pending') === $step) ? 'selected' : '' ?>><?= htmlspecialchars($step) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Tiến trình</label><select name="case_status" class="form-control"><?php foreach (($workflowOptions['case_status'] ?? []) as $step): ?><option value="<?= htmlspecialchars($step) ?>" <?= (($inquiry['case_status'] ?? 'new') === $step) ? 'selected' : '' ?>><?= htmlspecialchars($step) ?></option><?php endforeach; ?></select></div>
                        <button type="submit" class="btn btn-primary btn-block">Cập nhật</button>
                    </form>
                </div></div>
            </div>
        </div>
        <?php
        $detailLogsTitle = 'Nhật ký workflow';
        require_once __DIR__ . '/../../shared/workflow_detail_logs.php';
        ?>
    </div>
</div>
<?php require_once '../app/views/admin/layouts/footer.php'; ?>
