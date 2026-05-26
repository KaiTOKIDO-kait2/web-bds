<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Sửa About</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Giới thiệu</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Cập nhật nội dung Giới thiệu</h2>
                    </div>
                    <?php if(isset($about) && !empty($about)): ?>
                    <form method="post" enctype="multipart/form-data" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <?= isset($error) && !empty($error) ? $error : '' ?>
                                    <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Tiêu đề</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="utitle" required value="<?= htmlspecialchars($about['title']) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Hình ảnh</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file">
                                            <br>
                                            <img src="<?= BASEURL ?>/admin/upload/<?= $about['image'] ?>" height="150" width="150">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Nội dung</label>
                                        <div class="col-lg-9">
                                            <textarea class="tinymce form-control" name="ucontent" rows="10" cols="30"><?= htmlspecialchars_decode($about['content']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu thay đổi" name="update" style="margin-left:200px;">
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- /Page Wrapper -->
<!-- /Main Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
