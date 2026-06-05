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
        margin-bottom: 10px;
        font-size: 28px;
        color: #333;
    }

    .custom-login-subtitle {
        text-align: center;
        color: #666;
        font-size: 15px;
        margin-bottom: 30px;
        line-height: 1.5;
    }

    .custom-form-group {
        margin-bottom: 30px;
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
        margin-top: 10px;
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

    .custom-radio-group {
        margin-bottom: 25px;
    }

    .custom-radio-group>label {
        display: block;
        font-weight: 700;
        margin-bottom: 12px;
        color: #444;
        font-size: 15px;
    }

    .custom-radio-inline {
        display: inline-block;
        margin-right: 20px;
        color: #555;
        cursor: pointer;
        font-size: 15px;
    }

    .custom-radio-inline input[type="radio"] {
        margin-right: 6px;
        cursor: pointer;
    }

    .avatar-upload-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 30px;
    }

    .avatar-preview-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px dashed #bbb;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f2f5;
        cursor: pointer;
        margin-bottom: 15px;
        transition: border-color 0.3s;
    }

    .avatar-preview-wrapper:hover {
        border-color: #0056b3;
    }

    .avatar-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-preview-fallback {
        background: linear-gradient(135deg, #e9f5ff 0%, #d6e9ff 100%);
        color: #0d6efd;
        font-size: 34px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .avatar-edit-icon {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #0056b3;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
    }

    .avatar-upload-text {
        font-size: 15px;
        color: #555;
        font-weight: 600;
    }
</style>

<div class="page-wrappers login-body full-row bg-gray">
    <div class="container">
        <div class="custom-login-container">
            <h1>Đăng ký thành viên</h1>
            <p class="custom-login-subtitle">Vui lòng điền thông tin bên dưới để tạo tài<br>khoản mới.</p>
            <?= isset($data['error']) ? $data['error'] : '' ?>
            <?= isset($data['msg']) ? $data['msg'] : '' ?>

            <form method="post" action="<?= BASEURL ?>/auth/register" enctype="multipart/form-data">
                <div class="avatar-upload-container">
                    <div class="avatar-preview-wrapper" onclick="document.getElementById('uimage-input').click()">
                        <div class="avatar-preview avatar-preview-fallback" id="avatar-preview">?</div>
                        <div class="avatar-edit-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#fff"
                                viewBox="0 0 16 16">
                                <path
                                    d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1v6zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2z" />
                                <path
                                    d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5zm0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="avatar-upload-text">Ảnh đại diện không bắt buộc</div>
                    <input id="uimage-input" name="uimage" type="file" accept="image/*" style="display: none;">
                </div>
                <div class="custom-form-group">
                    <input id="register-name" type="text" name="name" class="custom-form-control" placeholder="Họ và tên" required>
                </div>
                <div class="custom-form-group">
                    <input type="email" name="email" class="custom-form-control" placeholder="Email" required>
                </div>
                <div class="custom-form-group">
                    <input type="text" name="phone" class="custom-form-control" placeholder="Số điện thoại"
                        maxlength="11" required>
                </div>
                <div class="custom-form-group">
                    <input type="password" name="pass" class="custom-form-control" placeholder="Mật khẩu" required>
                </div>

                <div class="custom-radio-group">
                    <label>Bạn muốn</label>
                    <label class="custom-radio-inline">
                        <input type="radio" class="js-account-intent" name="account_intent" value="user" checked> Thuê
                        bất động sản
                    </label>
                    <label class="custom-radio-inline">
                        <input type="radio" class="js-account-intent" name="account_intent" value="landlord"> Cho thuê
                        bất động sản
                    </label>
                </div>

                <div class="custom-radio-group" id="provider-role-group" style="display:none;">
                    <label>Vai trò cho thuê</label>
                    <label class="custom-radio-inline">
                        <input type="radio" class="js-provider-role" name="provider_role" value="owner" checked> Chính
                        chủ
                    </label>
                    <label class="custom-radio-inline">
                        <input type="radio" class="js-provider-role" name="provider_role" value="agent"> Môi giới
                    </label>
                </div>
                <input type="hidden" name="utype" id="utype" value="user">
                <button class="custom-login-btn" name="reg" value="Đăng ký" type="submit">Đăng ký</button>
            </form>

            <div class="custom-register-link">
                Đã có tài khoản? <a href="<?= BASEURL ?>/auth/login">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>

<script>
    (function () {
        var intentInputs = document.querySelectorAll('.js-account-intent');
        var providerInputs = document.querySelectorAll('.js-provider-role');
        var providerGroup = document.getElementById('provider-role-group');
        var utypeInput = document.getElementById('utype');

        if (!intentInputs.length || !providerGroup || !utypeInput) {
            return;
        }

        function getCheckedValue(nodeList, fallback) {
            for (var i = 0; i < nodeList.length; i++) {
                if (nodeList[i].checked) {
                    return nodeList[i].value;
                }
            }
            return fallback;
        }

        function syncAccountType() {
            var intent = getCheckedValue(intentInputs, 'user');

            if (intent === 'landlord') {
                providerGroup.style.display = 'block';
                utypeInput.value = getCheckedValue(providerInputs, 'owner');
                return;
            }

            providerGroup.style.display = 'none';
            utypeInput.value = 'user';
        }

        for (var i = 0; i < intentInputs.length; i++) {
            intentInputs[i].addEventListener('change', syncAccountType);
        }

        for (var j = 0; j < providerInputs.length; j++) {
            providerInputs[j].addEventListener('change', syncAccountType);
        }

        syncAccountType();

        // Image preview logic
        var imageInput = document.getElementById('uimage-input');
        var nameInput = document.getElementById('register-name');
        var avatarPreview = document.getElementById('avatar-preview');

        function renderInitialAvatar() {
            if (!avatarPreview) {
                return;
            }

            var fullName = nameInput ? (nameInput.value || '').trim() : '';
            var firstLetter = fullName ? fullName.charAt(0).toUpperCase() : '?';
            avatarPreview.className = 'avatar-preview avatar-preview-fallback';
            avatarPreview.textContent = firstLetter;
        }

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (!imageInput || !imageInput.files || !imageInput.files[0]) {
                    renderInitialAvatar();
                }
            });
        }

        if (imageInput) {
            imageInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if (!avatarPreview) {
                            return;
                        }
                        avatarPreview.className = 'avatar-preview';
                        avatarPreview.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">';
                    }
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    renderInitialAvatar();
                }
            });
        }

        renderInitialAvatar();
    })();
</script>