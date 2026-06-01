<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Cài đặt About</h3>
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
                        <h2 class="card-title">Thêm nội dung Giới thiệu</h2>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <?= isset($error) && !empty($error) ? $error : '' ?>
                                    <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Tiêu đề</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="title" required="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Hình ảnh</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file" required="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Nội dung</label>
                                        <div class="col-lg-9">
                                            <textarea class="tinymce form-control" name="content" rows="10" cols="30"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu" name="addabout" style="margin-left:200px;">
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
                        <h4 class="card-title">Danh sách giới thiệu</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-stripped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tiêu đề</th>
                                        <th>Nội dung</th>
                                        <th>Hình ảnh</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $cnt = 1; if(isset($about_list)): foreach($about_list as $row): ?>
                                    <tr>
                                        <td><?= $cnt ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <?php
                                        $contentPreview = html_entity_decode(strip_tags((string) ($row['content'] ?? '')), ENT_QUOTES, 'UTF-8');
                                        $contentPreview = preg_replace('/\s+/u', ' ', trim($contentPreview));
                                        if (mb_strlen($contentPreview, 'UTF-8') > 50) {
                                            $contentPreview = mb_substr($contentPreview, 0, 50, 'UTF-8') . '...';
                                        }
                                        $aboutImage = trim((string) ($row['image'] ?? ''));
                                        ?>
                                        <td><?= htmlspecialchars($contentPreview, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if ($aboutImage !== ''): ?>
                                                <img src="<?= BASEURL ?>/admin/upload/<?= htmlspecialchars($aboutImage, ENT_QUOTES, 'UTF-8') ?>" alt="" style="display:block;width:100px;height:72px;object-fit:cover;object-position:center;border-radius:8px;">
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASEURL ?>/adminAbout/edit/<?= $row['id'] ?>"><button class="btn btn-info">Sửa</button></a>
                                            <a href="<?= BASEURL ?>/adminAbout/delete/<?= $row['id'] ?>"><button class="btn btn-danger">Xóa</button></a>
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
<!-- /Page Wrapper -->
<!-- /Main Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
