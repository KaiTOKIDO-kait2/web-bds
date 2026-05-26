<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        <?php
                            $typeLabels = [
                                'admin'   => 'Quản trị viên',
                                'user'    => 'Người dùng',
                                'agent'   => 'Môi giới',
                            ];
                            echo $typeLabels[isset($type) ? $type : 'user'] ?? ucfirst(isset($type) ? $type : 'user');
                        ?>
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Danh sách người dùng</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?= BASEURL ?>/adminUser/userAdd/<?= isset($type) ? urlencode($type) : 'user' ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Thêm tài khoản
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fa fa-users" style="color:#2563eb;margin-right:.5rem;"></i>
                            Danh sách <?= $typeLabels[isset($type) ? $type : 'user'] ?? ucfirst(isset($type) ? $type : 'user') ?>
                        </h5>
                        <?= isset($msg) && !empty($msg)
                            ? '<div class="alert alert-info" style="margin-top:.5rem;margin-bottom:0;">'.urldecode($msg).'</div>'
                            : '' ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên</th>
                                        <th>Email</th>
                                        <th>Liên hệ</th>
                                        <th>Loại TK</th>
                                        <th>Trạng thái</th>
                                        <th>Ảnh</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $cnt = 1; if(isset($users)): foreach($users as $row): ?>
                                    <tr>
                                        <td style="color:#94a3b8;font-weight:500;"><?= $cnt ?></td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.65rem;">
                                                <img src="<?= BASEURL ?>/admin/user/<?= $row['uimage'] ?>"
                                                     class="table-avatar"
                                                     onerror="this.src='<?= BASEURL ?>/admin/assets/img/profiles/avatar-01.png'">
                                                <div>
                                                    <div style="font-weight:500;font-size:13.5px;"><?= htmlspecialchars($row['uname']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="color:#64748b;"><?= htmlspecialchars($row['uemail']) ?></td>
                                        <td><?= htmlspecialchars($row['uphone']) ?></td>
                                        <td>
                                            <?php
                                                $typeBadge = ['admin'=>'badge-danger','user'=>'badge-primary','agent'=>'badge-info'];
                                                $cls = $typeBadge[$row['utype']] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $cls ?>"><?= htmlspecialchars($row['utype']) ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['blocked'])): ?>
                                                <span class="badge badge-danger">Đang khóa</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Hoạt động</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <img src="<?= BASEURL ?>/admin/user/<?= $row['uimage'] ?>"
                                                 class="table-avatar"
                                                 onerror="this.src='<?= BASEURL ?>/admin/assets/img/profiles/avatar-01.png'">
                                        </td>
                                        <td>
                                            <a href="<?= BASEURL ?>/adminUser/userDetail/<?= $row['uid'] ?>/<?= isset($type) ? urlencode($type) : 'user' ?>"
                                               class="btn btn-info btn-sm"
                                               title="Chi tiết">
                                                <i class="fa fa-eye"></i> Chi tiết
                                            </a>
                                            <a href="<?= BASEURL ?>/adminUser/userEdit/<?= $row['uid'] ?>/<?= isset($type) ? urlencode($type) : 'user' ?>"
                                               class="btn btn-primary btn-sm"
                                               title="Sửa">
                                                <i class="fa fa-edit"></i> Sửa
                                            </a>

                                            <?php if (!empty($row['blocked'])): ?>
                                            <a href="<?= BASEURL ?>/adminUser/userUnblock/<?= $row['uid'] ?>/<?= isset($type) ? urlencode($type) : 'user' ?>"
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Bạn chắc muốn mở khóa tài khoản này?')"
                                               title="Mở khóa">
                                                <i class="fa fa-unlock"></i> Mở khóa
                                            </a>
                                            <?php else: ?>
                                            <a href="<?= BASEURL ?>/adminUser/userBlock/<?= $row['uid'] ?>/<?= isset($type) ? urlencode($type) : 'user' ?>"
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('Bạn chắc muốn khóa tài khoản này?')"
                                               title="Khóa">
                                                <i class="fa fa-lock"></i> Khóa
                                            </a>
                                            <?php endif; ?>

                                            <a href="<?= BASEURL ?>/adminUser/userDelete/<?= $row['uid'] ?>/<?= isset($type) ? urlencode($type) : 'user' ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Bạn chắc muốn xóa người dùng này?')"
                                               title="Xóa">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php $cnt++; endforeach; endif; ?>
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
