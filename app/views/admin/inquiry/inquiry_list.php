<?php require_once '../app/views/admin/layouts/header.php'; ?>
<?php
$caseLabels = [
    'new' => 'Mới',
    'contacted' => 'Đã tiếp nhận',
    'scheduled' => 'Đã hẹn lịch',
    'viewed' => 'Đã xem nhà',
    'completed' => 'Hoàn tất',
    'cancelled' => 'Không thành công',
];
?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Quản lý Lead</h3></div>
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-4"><input type="text" name="search" class="form-control" value="<?= htmlspecialchars((string) ($filters['search'] ?? '')) ?>" placeholder="Tìm khách, email, số điện thoại"></div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Tất cả trạng thái</option>
                        <?php foreach (($workflowOptions['status'] ?? []) as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="case_status" class="form-control">
                        <option value="">Tất cả tiến trình</option>
                        <?php foreach (($workflowOptions['case_status'] ?? []) as $caseStatus): ?>
                            <option value="<?= htmlspecialchars($caseStatus) ?>" <?= (($filters['case_status'] ?? '') === $caseStatus) ? 'selected' : '' ?>><?= htmlspecialchars($caseLabels[$caseStatus] ?? $caseStatus) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Lọc</button></div>
            </div>
        </form>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>ID</th><th>Khách</th><th>BĐS</th><th>Môi giới</th><th>Trạng thái</th><th>Tiến trình</th><th></th></tr></thead>
                        <tbody>
                        <?php if (!empty($inquiries)): foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td>#<?= (int) $inquiry['id'] ?></td>
                                <td><?= htmlspecialchars((string) ($inquiry['inquirer_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($inquiry['property_title'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($inquiry['agent_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($inquiry['status'] ?? 'pending')) ?></td>
                                <td><?= htmlspecialchars((string) ($caseLabels[$inquiry['case_status'] ?? 'new'] ?? ($inquiry['case_status'] ?? 'new'))) ?></td>
                                <td><a href="<?= BASEURL ?>/adminInquiry/detail/<?= (int) $inquiry['id'] ?>" class="btn btn-sm btn-info">Xem</a></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center">Chưa có dữ liệu.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/admin/layouts/footer.php'; ?>
