<?php require_once '../app/views/admin/layouts/header.php'; ?>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Quản lý lịch hẹn</h3></div>
        <?php $caseLabels = ['new'=>'Mới','contacted'=>'Đã tiếp nhận','scheduled'=>'Đã hẹn lịch','viewed'=>'Đã xem nhà','completed'=>'Hoàn thành','cancelled'=>'Không thành công']; ?>
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-3"><input type="text" name="search" class="form-control" value="<?= htmlspecialchars((string) ($filters['search'] ?? '')) ?>" placeholder="Tìm theo bài đăng" title="Tìm theo tiêu đề bài đăng"></div>
                <div class="col-md-3">
                    <select name="appointment_status" class="form-control" title="Lọc theo trạng thái lịch hẹn">
                        <option value="">Tất cả trạng thái lịch</option>
                        <?php foreach (($workflowOptions['appointment_status'] ?? []) as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= (($filters['appointment_status'] ?? '') === $s) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>" title="Từ ngày (yyyy-mm-dd)"></div>
                <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>" title="Đến ngày (yyyy-mm-dd)"></div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary me-2" title="Áp dụng bộ lọc">Lọc</button>
                    <button type="button" class="btn btn-secondary" title="Đặt lại bộ lọc" onclick="this.form.reset(); this.form.submit();">Đặt lại</button>
                </div>
            </div>
        </form>
        <div class="card"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Khách</th><th>Môi giới</th><th>Thời gian đề xuất</th><th>Thời gian xác nhận</th><th>Trạng thái</th><th>Tiến trình</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!empty($appointments)): foreach ($appointments as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($item['inquirer_name'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($item['agent_name'] ?? '')) ?></td>
                            <td><?= !empty($item['appointment_requested_at']) ? date('d-m-Y H:i', strtotime((string) $item['appointment_requested_at'])) : 'Chưa có' ?></td>
                            <td><?= !empty($item['appointment_confirmed_at']) ? date('d-m-Y H:i', strtotime((string) $item['appointment_confirmed_at'])) : 'Chưa có' ?></td>
                            <td><?= htmlspecialchars((string) ($item['status'] ?? 'pending')) ?></td>
                            <td><?= htmlspecialchars((string) ($caseLabels[$item['case_status'] ?? 'new'] ?? ($item['case_status'] ?? 'new'))) ?></td>
                            <td><a href="<?= BASEURL ?>/adminTransaction/detail/<?= (int) $item['id'] ?>" class="btn btn-sm btn-info">Xem</a></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center">Chưa có dữ liệu lịch hẹn.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
<?php require_once '../app/views/admin/layouts/footer.php'; ?>
