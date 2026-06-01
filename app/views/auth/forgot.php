<?php require_once '../app/views/layouts/header.php'; ?>

<div class="page-wrappers login-body full-row bg-gray">
    <div class="login-wrapper">
        <div class="container">
            <div class="loginbox">
                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Quên mật khẩu</h1>
                        <p class="account-subtitle">Nhập email để nhận link đặt lại mật khẩu</p>

                        <?= isset($data['error']) ? $data['error'] : '' ?>
                        <?= isset($data['msg']) ? $data['msg'] : '' ?>

                        <?php if (!empty($data['dev_link']) && !empty($data['is_local'])): ?>
                            <p class="alert alert-info mb-3">
                                Môi trường localhost hoặc chưa cấu hình gửi mail. Dùng link này để đặt lại mật khẩu:<br>
                                <a
                                    href="<?= htmlspecialchars($data['dev_link']) ?>"><?= htmlspecialchars($data['dev_link']) ?></a>
                            </p>
                            <a href="<?= htmlspecialchars($data['dev_link']) ?>"
                                class="btn btn-outline-success btn-sm mb-3">Mở trang đặt lại mật khẩu</a>
                        <?php endif; ?>

                        <form method="post" action="<?= BASEURL ?>/auth/forgot">
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email của bạn*"
                                    required>
                            </div>
                            <button class="btn btn-success" name="request_reset" type="submit">Gửi link đặt lại</button>
                        </form>

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