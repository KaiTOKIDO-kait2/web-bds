<?php require_once '../app/views/admin/layouts/header.php'; ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Quy trình thuê</h3></div>
        <?php $caseLabels = ['new'=>'Mới','contacted'=>'Đã tiếp nhận','scheduled'=>'Đã hẹn lịch','viewed'=>'Đã xem nhà','completed'=>'Hoàn tất','cancelled'=>'Không thành công']; ?>
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-4"><input type="text" name="search" class="form-control" value="<?= htmlspecialchars((string) ($filters['search'] ?? '')) ?>" placeholder="Tìm khách, môi giới, BĐS"></div>
                <div class="col-md-3"><select name="status" class="form-control"><option value="">Tất cả</option><?php foreach (($workflowOptions['status'] ?? []) as $status): ?><option value="<?= htmlspecialchars($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><select name="case_status" class="form-control"><option value="">Tiến trình</option><?php foreach (($workflowOptions['case_status'] ?? []) as $caseStatus): ?><option value="<?= htmlspecialchars($caseStatus) ?>" <?= (($filters['case_status'] ?? '') === $caseStatus) ? 'selected' : '' ?>><?= htmlspecialchars($caseLabels[$caseStatus] ?? $caseStatus) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Lọc</button></div>
            </div>
        </form>
        <div class="card"><div class="card-body"><div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>#</th><th>Khách</th><th>Môi giới</th><th>BĐS</th><th>Trạng thái</th><th>Tiến trình</th><th></th></tr></thead>
                <tbody>
                <?php if (!empty($transactions)): foreach ($transactions as $item): ?>
                    <tr>
                        <td>#<?= (int) $item['id'] ?></td>
                        <td><?= htmlspecialchars((string) ($item['inquirer_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['agent_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['property_title'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['status'] ?? 'pending')) ?></td>
                        <td><?= htmlspecialchars((string) ($caseLabels[$item['case_status'] ?? 'new'] ?? ($item['case_status'] ?? 'new'))) ?></td>
                        <td><a href="<?= BASEURL ?>/adminTransaction/detail/<?= (int) $item['id'] ?>" class="btn btn-sm btn-info">Xem</a></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div></div>
    </div>
</div>
<?php require_once '../app/views/admin/layouts/footer.php'; ?>
