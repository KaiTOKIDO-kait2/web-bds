<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?= (isset($mode) && $mode === 'edit') ? 'Cập nhật tài khoản' : 'Thêm tài khoản' ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/adminUser/users/<?= isset($type) ? urlencode($type) : 'user' ?>">Người dùng</a></li>
                        <li class="breadcrumb-item active"><?= (isset($mode) && $mode === 'edit') ? 'Cập nhật' : 'Thêm mới' ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?= (isset($mode) && $mode === 'edit') ? 'Thông tin tài khoản' : 'Nhập thông tin tài khoản mới' ?></h5>
                    </div>
                    <div class="card-body">
                        <?= isset($error) ? $error : '' ?>
                        <?= isset($msg) ? $msg : '' ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Họ tên <span style="color:#dc2626;">*</span></label>
                                        <input type="text" class="form-control" name="uname" required value="<?= htmlspecialchars($user['uname'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span style="color:#dc2626;">*</span></label>
                                        <input type="email" class="form-control" name="uemail" required value="<?= htmlspecialchars($user['uemail'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Số điện thoại <span style="color:#dc2626;">*</span></label>
                                        <input type="text" class="form-control" name="uphone" required value="<?= htmlspecialchars($user['uphone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Loại tài khoản</label>
                                        <select class="form-control" name="utype" required>
                                            <?php $currentType = $user['utype'] ?? (isset($type) ? $type : 'user'); ?>
                                            <option value="user" <?= $currentType === 'user' ? 'selected' : '' ?>>Người dùng</option>
                                            <option value="agent" <?= $currentType === 'agent' ? 'selected' : '' ?>>Môi giới</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            Mật khẩu <?= (isset($mode) && $mode === 'edit') ? '(để trống nếu không đổi)' : '<span style="color:#dc2626;">*</span>' ?>
                                        </label>
                                        <input type="password" class="form-control" name="upass" <?= (isset($mode) && $mode === 'edit') ? '' : 'required' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ảnh đại diện <?= (isset($mode) && $mode === 'edit') ? '(tùy chọn)' : '' ?></label>
                                        <input type="file" class="form-control" name="uimage">
                                        <?php if (!empty($user['uimage'])): ?>
                                            <div style="margin-top:.5rem;">
                                                <img src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($user['uimage']) ?>" alt="avatar" style="width:72px;height:72px;border-radius:8px;object-fit:cover;" onerror="this.src='<?= BASEURL ?>/admin/assets/img/profiles/avatar-01.png'">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="blocked" name="blocked" value="1" <?= !empty($user['blocked']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="blocked">Khóa tài khoản</label>
                            </div>

                            <button type="submit" name="save" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?= (isset($mode) && $mode === 'edit') ? 'Lưu thay đổi' : 'Tạo tài khoản' ?>
                            </button>
                            <a href="<?= BASEURL ?>/adminUser/users/<?= isset($type) ? urlencode($type) : 'user' ?>" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
