<?php require_once '../app/views/layouts/header.php'; ?>

<style>
    .custom-login-container {
        max-width: 450px;
        margin: 60px auto;
        padding: 50px 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        font-family: inherit;
    }

    .custom-login-container h1 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 40px;
        font-size: 32px;
        color: #007bff;
    }

    .custom-login-container p.account-subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
        margin-top: -30px;
    }

    .custom-form-group {
        margin-bottom: 30px;
    }

    .custom-form-group label {
        display: block;
        font-weight: 700;
        margin-bottom: 10px;
        color: #444;
        font-size: 15px;
    }

    .custom-form-control {
        width: 100%;
        border: none;
        border-bottom: 2px solid #eee;
        padding: 10px 0;
        font-size: 16px;
        background: transparent;
        transition: border-color 0.3s ease;
        color: #333;
    }

    .custom-form-control:focus {
        outline: none;
        border-bottom-color: #007bff;
    }

    .custom-form-control::placeholder {
        color: #aaa;
    }

    /* Fix background color for browser autofill */
    .custom-form-control:-webkit-autofill,
    .custom-form-control:-webkit-autofill:hover,
    .custom-form-control:-webkit-autofill:focus,
    .custom-form-control:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
        -webkit-text-fill-color: #333 !important;
        transition: background-color 5000s ease-in-out 0s;
    }

    .custom-login-btn {
        width: 100%;
        padding: 16px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 40px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.1s ease;
    }

    .custom-login-btn:hover {
        background: #0056b3;
    }

    .custom-login-btn:active {
        transform: scale(0.98);
    }

    .custom-register-link {
        text-align: center;
        margin-top: 30px;
        font-size: 15px;
        color: #666;
    }

    .custom-register-link a {
        font-weight: 700;
        color: #007bff;
        text-decoration: none;
        margin-left: 5px;
    }

    .custom-register-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="page-wrappers login-body full-row bg-gray">
    <div class="container">
        <div class="custom-login-container">
            <h1>Đặt lại mật khẩu</h1>
            <p class="account-subtitle">Tạo mật khẩu mới cho tài khoản của bạn</p>

            <?= isset($data['error']) ? $data['error'] : '' ?>
            <?= isset($data['msg']) ? $data['msg'] : '' ?>

            <?php if (!empty($data['is_valid'])): ?>
                <form method="post" action="<?= BASEURL ?>/auth/resetPassword?email=<?= urlencode($data['email']) ?>&token=<?= urlencode($data['token']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($data['token']) ?>">

                    <div class="custom-form-group">
                        <input type="password" name="password" class="custom-form-control" placeholder="Mật khẩu mới*" required>
                    </div>
                    <div class="custom-form-group">
                        <input type="password" name="confirm_password" class="custom-form-control" placeholder="Nhập lại mật khẩu mới*" required>
                    </div>
                    <button class="custom-login-btn" name="update_password" type="submit">Cập nhật mật khẩu</button>
                </form>
            <?php else: ?>
                <div class="custom-register-link text-center mb-4">
                    <a href="<?= BASEURL ?>/auth/forgot">Yêu cầu link mới</a>
                </div>
            <?php endif; ?>

            <div class="custom-register-link">
                Quay lại <a href="<?= BASEURL ?>/auth/login">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>