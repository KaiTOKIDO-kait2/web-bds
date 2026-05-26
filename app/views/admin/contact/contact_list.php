<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Liên hệ</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Liên hệ</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-envelope" style="color:#2563eb;margin-right:.5rem;"></i>Danh sách liên hệ</h5>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive"><table id="basic-datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Chủ đề</th>
                                    <th>Tin nhắn</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $cnt=1; if(isset($contacts)): foreach($contacts as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['subject']) ?></td>
                                    <td><?= htmlspecialchars($row['message']) ?></td>
                                    <td>
                                        <a href="<?= BASEURL ?>/adminContact/delete/<?= $row['cid'] ?>"><button class="btn btn-danger">Xóa</button></a>
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
<!-- /Main Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
