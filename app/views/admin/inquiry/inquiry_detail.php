<?php require_once '../app/views/admin/layouts/header.php'; ?>
<?php $inquiry = isset($data['inquiry']) ? $data['inquiry'] : []; $logs = isset($data['logs']) ? $data['logs'] : []; ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Chi tiết Lead</h3></div>
        <?php if (!empty($data['msg'])): ?><div class="alert alert-info"><?= $data['msg'] ?></div><?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card"><div class="card-body">
                    <h5>Thông tin</h5>
                    <div><strong>Khách:</strong> <?= htmlspecialchars((string) ($inquiry['inquirer_name'] ?? '')) ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars((string) ($inquiry['work_email'] ?? '')) ?></div>
                    <div><strong>SĐT:</strong> <?= htmlspecialchars((string) ($inquiry['phone'] ?? '')) ?></div>
                    <div><strong>BĐS:</strong> <?= htmlspecialchars((string) ($inquiry['property_title'] ?? '')) ?></div>
                    <!-- Lịch đề xuất/ xác nhận đã dời vào sidebar form -->
                    <div><strong>Đã xem nhà:</strong> <?= !empty($inquiry['viewed_at']) ? date('d-m-Y H:i', strtotime((string) $inquiry['viewed_at'])) : 'N/A' ?></div>
                    <div><strong>Nhu cầu:</strong></div>
                    <div class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars((string) ($inquiry['requirement'] ?? ''))) ?></div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body">
                    <?php
                        $requestedVal = !empty($inquiry['appointment_requested_at']) ? date('Y-m-d\TH:i', strtotime((string) $inquiry['appointment_requested_at'])) : '';
                        $confirmedVal = !empty($inquiry['appointment_confirmed_at']) ? date('Y-m-d\TH:i', strtotime((string) $inquiry['appointment_confirmed_at'])) : '';
                    ?>
                    <form method="post" action="<?= BASEURL ?>/adminInquiry/updateWorkflow/<?= (int) ($inquiry['id'] ?? 0) ?>">
                        <div class="form-group">
                            <label>Trạng thái tiếp nhận</label>
                            <select name="status" class="form-control">
                                <?php foreach (($workflowOptions['status'] ?? []) as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= (($inquiry['status'] ?? 'pending') === $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tiến trình</label>
                            <select name="case_status" class="form-control">
                                <?php foreach (($workflowOptions['case_status'] ?? []) as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= (($inquiry['case_status'] ?? 'new') === $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái lịch hẹn</label>
                            <select name="appointment_status" class="form-control">
                                <?php foreach (($workflowOptions['appointment_status'] ?? ['none','pending','confirmed','completed','cancelled']) as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt) ?>" <?= (($inquiry['appointment_status'] ?? 'none') === $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Thời gian đề xuất</label><input type="datetime-local" name="appointment_requested_at" class="form-control" value="<?= htmlspecialchars($requestedVal) ?>"></div>
                        <div class="form-group"><label>Thời gian xác nhận</label><input type="datetime-local" name="appointment_confirmed_at" class="form-control" value="<?= htmlspecialchars($confirmedVal) ?>"></div>
                        <div class="form-group"><label>Ngân sách</label><input type="text" name="desired_budget" class="form-control" value="<?= htmlspecialchars((string) ($inquiry['desired_budget'] ?? '')) ?>"></div>
                        <div class="form-group"><label>Khu vực</label><input type="text" name="desired_area" class="form-control" value="<?= htmlspecialchars((string) ($inquiry['desired_area'] ?? '')) ?>"></div>
                        <div class="form-group"><label>Thời gian dọn vào</label><input type="text" name="desired_move_in_time" class="form-control" value="<?= htmlspecialchars((string) ($inquiry['desired_move_in_time'] ?? '')) ?>"></div>
                        <button type="submit" class="btn btn-primary btn-block">Lưu</button>
                    </form>
                </div></div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/admin/layouts/footer.php'; ?>
