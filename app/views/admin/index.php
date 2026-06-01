<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Ventura Admin – Đăng nhập</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?= BASEURL ?>/admin/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/admin-modern.css">
    <style>
        body { background: linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 55%,#0ea5e9 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
    </style>
</head>
<body>
    <div style="width:100%;max-width:440px;margin:auto;padding:1rem;">
        <!-- Card -->
        <div style="background:#fff;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,.25);padding:2.5rem 2.25rem;animation:fadeUp .4s ease;">
            <!-- Logo -->
            <div style="text-align:center;margin-bottom:2rem;">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#2563eb,#0ea5e9);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;box-shadow:0 8px 20px rgba(37,99,235,.35);">
                    <i class="fa fa-home" style="font-size:22px;color:#fff;"></i>
                </div>
                <h2 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 .3rem;font-family:Inter,sans-serif;">
                    Ventura<span style="color:#2563eb;">Admin</span>
                </h2>
                <p style="font-size:13px;color:#64748b;margin:0;">Đăng nhập để tiếp tục quản lý</p>
            </div>

            <!-- Error -->
            <?php if(!empty($error)): ?>
            <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;">
                <i class="fa fa-exclamation-circle" style="color:#ef4444;font-size:15px;flex-shrink:0;"></i>
                <span style="font-size:13px;color:#991b1b;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" action="">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:.4rem;">Tên đăng nhập</label>
                    <div style="position:relative;">
                        <i class="fa fa-user" style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;"></i>
                        <input type="text" name="user" class="form-control"
                               placeholder="Nhập tên đăng nhập" required
                               style="padding-left:2.5rem;height:44px;border-radius:10px;border:1.5px solid #e2e8f0;font-size:14px;width:100%;">
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:.4rem;">Mật khẩu</label>
                    <div style="position:relative;">
                        <i class="fa fa-lock" style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;"></i>
                        <input type="password" name="pass" class="form-control"
                               placeholder="Nhập mật khẩu" required
                               style="padding-left:2.5rem;height:44px;border-radius:10px;border:1.5px solid #e2e8f0;font-size:14px;width:100%;">
                    </div>
                </div>

                <button type="submit" name="login" value="1"
                        style="width:100%;height:46px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:600;cursor:pointer;letter-spacing:.01em;transition:all .25s;box-shadow:0 4px 14px rgba(37,99,235,.35);"
                        onmouseover="this.style.boxShadow='0 6px 20px rgba(37,99,235,.5)'"
                        onmouseout="this.style.boxShadow='0 4px 14px rgba(37,99,235,.35)'">
                    <i class="fa fa-sign-in" style="margin-right:.5rem;"></i>Đăng nhập
                </button>
            </form>

            <!-- Footer hint -->
            <div style="margin-top:1.5rem;text-align:center;font-size:12px;color:#94a3b8;">
                <i class="fa fa-shield" style="margin-right:.35rem;"></i>Hệ thống quản trị Real Estate
            </div>
        </div>
    </div>

    <style>
    @keyframes fadeUp {
        from { opacity:0; transform:translateY(20px); }
        to   { opacity:1; transform:translateY(0); }
    }
    input:focus { border-color:#2563eb !important; outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.12) !important; }
    </style>

    <script src="<?= BASEURL ?>/admin/assets/js/jquery-3.2.1.min.js"></script>
    <script src="<?= BASEURL ?>/admin/assets/js/popper.min.js"></script>
    <script src="<?= BASEURL ?>/admin/assets/js/bootstrap.min.js"></script>
</body>
</html>
