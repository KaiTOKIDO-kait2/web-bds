<?php require_once '../app/views/layouts/header.php'; ?>

<div class="page-wrappers login-body full-row bg-gray">
    <div class="login-wrapper">
        <div class="container">
            <div class="loginbox">
                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Đặt lại mật khẩu</h1>
                        <p class="account-subtitle">Tạo mật khẩu mới cho tài khoản của bạn</p>

                        <?= isset($data['error']) ? $data['error'] : '' ?>
                        <?= isset($data['msg']) ? $data['msg'] : '' ?>

                        <?php if (!empty($data['is_valid'])): ?>
                            <form method="post"
                                action="<?= BASEURL ?>/auth/resetPassword?email=<?= urlencode($data['email']) ?>&token=<?= urlencode($data['token']) ?>">
                                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($data['token']) ?>">

                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới*"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="confirm_password" class="form-control"
                                        placeholder="Nhập lại mật khẩu mới*" required>
                                </div>
                                <button class="btn btn-success" name="update_password" type="submit">Cập nhật mật
                                    khẩu</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center dont-have">
                                <a href="<?= BASEURL ?>/auth/forgot">Yêu cầu link mới</a>
                            </div>
                        <?php endif; ?>

                        <div class="login-or">
                            <span class="or-line"></span>
                            <span class="span-or">hoặc</span>
                        </div>

                        <div class="text-center dont-have">Quay lại <a href="<?= BASEURL ?>/auth/login">Đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>