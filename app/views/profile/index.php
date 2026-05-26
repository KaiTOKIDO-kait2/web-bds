<?php require_once '../app/views/layouts/header.php'; ?>

<!-- Banner -->
<div class="banner-full-row page-banner" style="background-image:url('<?= BASEURL ?>/images/breadcromb.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- <h2 class="page-name text-white text-uppercase mt-1 mb-0"><b>Hồ sơ</b></h2> -->
            </div>
        </div>
    </div>
</div>
<!-- Banner -->

<div class="full-row">
    <div class="container">
        <div class="row mb-3">
            <div class="col-12">
                <div class="profile-page-top">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/home/index">Trang chủ</a></li>
                            <li class="breadcrumb-item active">Hồ sơ</li>
                        </ol>
                    </nav>
                    <h1 class="h4">Hồ sơ cá nhân</h1>
                </div>
            </div>
        </div>

        <style>
            .profile-page-top {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                color: #2c2c2c;
            }

            .profile-page-top .breadcrumb {
                margin-bottom: 0;
            }

            .profile-page-top .breadcrumb-item,
            .profile-page-top .breadcrumb-item a,
            .profile-page-top .breadcrumb-item.active,
            .profile-page-top h1,
            .profile-page-top p {
                color: #2c2c2c;
                margin: 0;
            }

            .profile-theme-card {
                border: 1px solid #e5eee8;
                border-radius: 16px;
                box-shadow: 0 12px 30px rgba(25, 135, 84, 0.08);
                background: linear-gradient(180deg, #ffffff 0%, #f7fcf9 100%);
            }

            .profile-theme-title {
                color: #166534;
                font-weight: 700;
            }

            .profile-avatar {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid #d1fae5;
                box-shadow: 0 8px 20px rgba(22, 101, 52, 0.2);
            }

            .profile-avatar-fallback {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                border: 4px solid #d1fae5;
                box-shadow: 0 8px 20px rgba(22, 101, 52, 0.2);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 44px;
                font-weight: 700;
                color: #ffffff;
                background: #198754;
                text-transform: uppercase;
            }

            .profile-meta-item {
                border-bottom: 1px dashed #d9e8de;
                padding-bottom: 10px;
                margin-bottom: 10px;
            }

            .profile-meta-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .password-field {
                position: relative;
            }

            .password-field .form-control {
                padding-right: 46px;
            }

            .password-toggle {
                position: absolute;
                top: 50%;
                right: 12px;
                transform: translateY(-50%);
                border: 0;
                background: transparent;
                color: #6c757d;
                cursor: pointer;
                padding: 4px;
                line-height: 1;
            }

            .password-toggle:focus {
                outline: none;
                color: #198754;
            }
        </style>

        <div class="row mt-4">
            <div class="col-lg-8 mb-4">
                <div class="profile-theme-card p-4 p-md-5 h-100">
                    <h5 class="profile-theme-title mb-4">Sửa thông tin cá nhân</h5>

                    <form action="<?= BASEURL ?>/profile/index" method="post" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="uname">Họ và tên</label>
                                <input id="uname" type="text" name="uname" class="form-control"
                                    placeholder="Nhập họ và tên"
                                    value="<?= htmlspecialchars($data['user']['uname']) ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="uphone">Số điện thoại</label>
                                <input id="uphone" type="text" name="uphone" class="form-control"
                                    placeholder="Nhập số điện thoại" maxlength="11" pattern="[0-9]{9,11}"
                                    title="Số điện thoại gồm 9-11 chữ số"
                                    value="<?= htmlspecialchars($data['user']['uphone']) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="uemail">Email</label>
                            <input id="uemail" type="email" class="form-control"
                                value="<?= htmlspecialchars($data['user']['uemail']) ?>" disabled>
                            <small class="text-muted">Email đăng nhập không thể thay đổi.</small>
                        </div>

                        <div class="form-group">
                            <label for="uimage">Ảnh đại diện mới</label>
                            <input id="uimage" type="file" name="uimage" class="form-control"
                                accept=".jpg,.jpeg,.png,.webp">
                            <small class="text-muted">Định dạng hỗ trợ: JPG, JPEG, PNG, WEBP.</small>
                        </div>

                        <button type="submit" class="btn btn-success px-4" name="update_profile">Lưu thông tin</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <?php if (isset($data['user'])):
                    $row = $data['user']; ?>
                    <div class="profile-theme-card p-4 h-100">
                        <div class="text-center mb-4">
                            <?php
                            $hasAvatar = !empty($row['uimage']) && file_exists(__DIR__ . '/../../../admin/user/' . $row['uimage']);
                            $firstLetter = mb_strtoupper(mb_substr($row['uname'], 0, 1));
                            ?>
                            <?php if ($hasAvatar): ?>
                                <img src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($row['uimage']) ?>" alt="userimage"
                                    class="profile-avatar mb-3">
                            <?php else: ?>
                                <div class="profile-avatar-fallback mb-3"><?= htmlspecialchars($firstLetter) ?></div>
                            <?php endif; ?>
                            <h5 class="mb-1 text-capitalize"><?= htmlspecialchars($row['uname']) ?></h5>
                            <span
                                class="badge badge-success text-uppercase px-3 py-2"><?= htmlspecialchars($row['utype']) ?></span>
                        </div>

                        <div class="font-15">
                            <div class="profile-meta-item"><b>Email:</b> <?= htmlspecialchars($row['uemail']) ?></div>
                            <div class="profile-meta-item"><b>Liên hệ:</b> <?= htmlspecialchars($row['uphone']) ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="profile-theme-card p-4 p-md-5">
                    <h5 class="profile-theme-title mb-4">Đổi mật khẩu</h5>

                    <form action="<?= BASEURL ?>/profile/index" method="post">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <div class="password-field">
                                    <input id="current_password" type="password" name="current_password"
                                        class="form-control" placeholder="Nhập mật khẩu hiện tại" required>
                                    <button type="button" class="password-toggle" data-toggle-password="current_password"
                                        aria-label="Hiện mật khẩu">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="new_password">Mật khẩu mới</label>
                                <div class="password-field">
                                    <input id="new_password" type="password" name="new_password" class="form-control"
                                        placeholder="Nhập mật khẩu mới" minlength="6" required>
                                    <button type="button" class="password-toggle" data-toggle-password="new_password"
                                        aria-label="Hiện mật khẩu">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="confirm_password">Nhập lại mật khẩu mới</label>
                                <div class="password-field">
                                    <input id="confirm_password" type="password" name="confirm_password"
                                        class="form-control" placeholder="Nhập lại mật khẩu mới" minlength="6" required>
                                    <button type="button" class="password-toggle" data-toggle-password="confirm_password"
                                        aria-label="Hiện mật khẩu">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success px-4" name="change_password">Cập nhật mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="profile-theme-card p-4 p-md-5">
                    <h5 class="profile-theme-title mb-4">Gửi phản hồi</h5>

                    <form action="<?= BASEURL ?>/profile/index" method="post">
                        <div class="form-group mb-0">
                            <label for="content">Nội dung phản hồi</label>
                            <textarea id="content" class="form-control" name="content" rows="6"
                                placeholder="Nhập nội dung phản hồi..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-info mt-3" name="insert_feedback">Gửi phản hồi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-toggle-password'));
            var icon = button.querySelector('i');

            if (!input) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                button.setAttribute('aria-label', 'Ẩn mật khẩu');
                if (icon) {
                    icon.className = 'fa fa-eye-slash';
                }
            } else {
                input.type = 'password';
                button.setAttribute('aria-label', 'Hiện mật khẩu');
                if (icon) {
                    icon.className = 'fa fa-eye';
                }
            }
        });
    });
</script>

<?php if (!empty($data['popup']) && !empty($data['popup']['message'])): ?>
    <script>
        window.APP_POPUP_AUTO = {
            type: <?= json_encode($data['popup']['type'] ?? 'info', JSON_UNESCAPED_UNICODE) ?>,
            message: <?= json_encode($data['popup']['message'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            confirmText: 'Đóng'
        };
    </script>
<?php endif; ?>

<?php require_once '../app/views/layouts/footer.php'; ?>
