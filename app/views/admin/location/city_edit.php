<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Sửa Thành phố</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Sửa Thành phố</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật Thành phố</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    <?php if(isset($city) && !empty($city)): ?>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tên Thành phố</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="city" required value="<?= htmlspecialchars($city['cname']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu thay đổi" name="update">
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
