<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Chi tiết người dùng</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/adminUser/users/<?= isset($type) ? urlencode($type) : 'user' ?>">Người dùng</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?= BASEURL ?>/adminUser/userEdit/<?= (int)($user['uid'] ?? 0) ?>/<?= isset($type) ? urlencode($type) : 'user' ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit"></i> Sửa tài khoản
                    </a>
                </div>
            </div>
        </div>

        <?= isset($msg) ? $msg : '' ?>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($user['uimage'] ?? '') ?>"
                             alt="avatar"
                             style="width:120px;height:120px;border-radius:50%;object-fit:cover;"
                             onerror="this.src='<?= BASEURL ?>/admin/assets/img/profiles/avatar-01.png'">
                        <h5 style="margin-top:1rem;"><?= htmlspecialchars($user['uname'] ?? '') ?></h5>
                        <p style="margin-bottom:.35rem;color:#6b7280;"><?= htmlspecialchars($user['uemail'] ?? '') ?></p>
                        <p style="margin-bottom:.35rem;"><b>SĐT:</b> <?= htmlspecialchars($user['uphone'] ?? '') ?></p>
                        <p style="margin-bottom:.35rem;"><b>Loại TK:</b> <?= htmlspecialchars($user['utype'] ?? '') ?></p>
                        <p style="margin-bottom:0;">
                            <b>Trạng thái:</b>
                            <?php if (!empty($user['blocked'])): ?>
                                <span class="badge badge-danger">Đang khóa</span>
                            <?php else: ?>
                                <span class="badge badge-success">Hoạt động</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="margin:0;">Danh sách bài đăng BĐS</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tiêu đề</th>
                                        <th>Loại</th>
                                        <th>Giao dịch</th>
                                        <th>Duyệt</th>
                                        <th>Ngày</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($properties)): ?>
                                        <?php $idx = 1; foreach ($properties as $prop): ?>
                                            <tr>
                                                <td><?= $idx++ ?></td>
                                                <td><?= htmlspecialchars($prop['title'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($prop['type'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($prop['stype'] ?? '') ?></td>
                                                <td>
                                                    <?php $approval = $prop['approval_status'] ?? ''; ?>
                                                    <?php if ($approval === 'approved'): ?>
                                                        <span class="badge badge-success">Approved</span>
                                                    <?php elseif ($approval === 'rejected'): ?>
                                                        <span class="badge badge-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning"><?= htmlspecialchars($approval) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($prop['date'] ?? '') ?></td>
                                                <td>
                                                    <a href="<?= BASEURL ?>/adminProperty/edit/<?= (int)($prop['pid'] ?? 0) ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fa fa-external-link"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Chưa có bài đăng nào.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="margin:0;">Lịch sử hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hoạt động</th>
                                        <th>Chi tiết</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($activities)): ?>
                                        <?php $logIdx = 1; foreach ($activities as $act): ?>
                                            <tr>
                                                <td><?= $logIdx++ ?></td>
                                                <td><?= htmlspecialchars($act['activity_label'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($act['activity_detail'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($act['activity_date'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Chưa có dữ liệu hoạt động.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
