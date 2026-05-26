<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Sửa loại bất động sản</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/adminPropertyType/index">Loại bất động sản</a></li>
                        <li class="breadcrumb-item active">Sửa</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật loại bất động sản</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>

                    <?php if(isset($type) && !empty($type)): ?>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <label>Tên loại</label>
                                        <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($type['name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="form-group">
                                        <label>Slug</label>
                                        <input type="text" class="form-control" name="slug" value="<?= htmlspecialchars($type['slug'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <label>Thứ tự</label>
                                        <input type="number" class="form-control" name="sort_order" value="<?= (int)($type['sort_order'] ?? 0) ?>" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" <?= (int)($type['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="edit_is_active">Đang bật (bỏ chọn để ẩn)</label>
                                </div>
                            </div>

                            <div class="text-left mt-3">
                                <input type="submit" class="btn btn-primary" value="Cập nhật" name="update">
                                <a href="<?= BASEURL ?>/adminPropertyType/index" class="btn btn-secondary">Quay lại</a>
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
