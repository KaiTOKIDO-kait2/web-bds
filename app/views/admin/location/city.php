<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Thành phố</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Thành phố</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm Thành phố</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                        <?php if(isset($_GET['msg'])) echo urldecode($_GET['msg']); ?>
                    </div>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tên Thành phố</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="city" required placeholder="Nhập tên thành phố">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu" name="insert">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-globe" style="color:#2563eb;margin-right:.5rem;"></i>Danh sách Thành phố</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive"><table id="basic-datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên Thành phố</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $cnt=1; if(isset($cities)): foreach($cities as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['cname']) ?></td>
                                    <td>
                                        <a href="<?= BASEURL ?>/adminLocation/cityEdit/<?= $row['cid'] ?>"><button class="btn btn-info">Sửa</button></a>
                                        <a href="<?= BASEURL ?>/adminLocation/cityDelete/<?= $row['cid'] ?>"><button class="btn btn-danger">Xóa</button></a>
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

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
