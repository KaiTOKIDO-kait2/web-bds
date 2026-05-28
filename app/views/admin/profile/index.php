<?php require_once '../app/views/admin/layouts/header.php'; ?>
<?php
$admin = isset($data['admin']) && is_array($data['admin']) ? $data['admin'] : [];
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Hồ sơ quản trị viên</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Hồ sơ</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sửa thông tin quản trị viên</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="auser">Tên đăng nhập</label>
                                <input id="auser" type="text" name="auser" class="form-control" value="<?= htmlspecialchars((string) ($admin['auser'] ?? '')) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="aemail">Email</label>
                                <input id="aemail" type="email" name="aemail" class="form-control" value="<?= htmlspecialchars((string) ($admin['aemail'] ?? '')) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="adob">Ngày sinh</label>
                                <input id="adob" type="date" name="adob" class="form-control" value="<?= htmlspecialchars((string) ($admin['adob'] ?? '')) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="aphone">Số điện thoại</label>
                                <input id="aphone" type="text" name="aphone" class="form-control" value="<?= htmlspecialchars((string) ($admin['aphone'] ?? '')) ?>" required>
                            </div>
                            <button type="submit" name="update_admin_profile" class="btn btn-primary">Lưu hồ sơ</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Đổi mật khẩu</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <input id="current_password" type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới</label>
                                <input id="new_password" type="password" name="new_password" class="form-control" minlength="6" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Nhập lại mật khẩu mới</label>
                                <input id="confirm_password" type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                            <button type="submit" name="change_admin_password" class="btn btn-warning">Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin hiện tại</h5>
                        <p class="mb-1"><strong>Tên đăng nhập:</strong> <?= htmlspecialchars((string) ($admin['auser'] ?? '')) ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars((string) ($admin['aemail'] ?? '')) ?></p>
                        <p class="mb-1"><strong>Ngày sinh:</strong> <?= htmlspecialchars((string) ($admin['adob'] ?? '')) ?></p>
                        <p class="mb-0"><strong>Liên hệ:</strong> <?= htmlspecialchars((string) ($admin['aphone'] ?? '')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($data['popup']) && !empty($data['popup']['message'])): ?>
<script>
window.APP_POPUP_AUTO = {
    type: <?= json_encode($data['popup']['type'] ?? 'info', JSON_UNESCAPED_UNICODE) ?>,
    message: <?= json_encode($data['popup']['message'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    confirmText: 'Đóng'
};
</script>
<?php endif; ?>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>