<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Quản trị viên</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Quản trị viên</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-shield" style="color:#2563eb;margin-right:.5rem;"></i>Danh sách Quản trị viên</h5>
                        <?= isset($msg) && !empty($msg) ? '<div class="alert alert-info mt-2 mb-0">'.urldecode($msg).'</div>' : '' ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive"><table id="basic-datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Ngày sinh</th>
                                    <th>Liên hệ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $cnt=1; if(isset($admins)): foreach($admins as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['auser']) ?></td>
                                    <td><?= htmlspecialchars($row['aemail']) ?></td>
                                    <td><?= htmlspecialchars($row['adob']) ?></td>
                                    <td><?= htmlspecialchars($row['aphone']) ?></td>
                                    <td><a href="<?= BASEURL ?>/adminUser/adminDelete/<?= $row['aid'] ?>"><button class="btn btn-danger">Xóa</button></a></td>
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

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
