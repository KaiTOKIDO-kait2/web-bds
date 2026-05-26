?<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Ventura Admin – Bảng điều khiển</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= BASEURL ?>/admin/assets/img/favicon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/bootstrap.min.css">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/font-awesome.min.css">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/feathericon.min.css">
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/plugins/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/plugins/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/plugins/datatables/select.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/plugins/datatables/buttons.bootstrap4.min.css">
    <!-- Original CSS (keep for compatibility) -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/style.css">
    <!-- Modern Admin CSS (overrides) -->
    <link rel="stylesheet" href="<?= BASEURL ?>/admin/assets/css/admin-modern.css">

    <!--[if lt IE 9]>
        <script src="<?= BASEURL ?>/admin/assets/js/html5shiv.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <!-- Logo -->
    <div class="header-left">
        <a href="<?= BASEURL ?>/admin/dashboard" class="logo d-flex align-items-center" style="text-decoration:none;">
            <img src="<?= BASEURL ?>/admin/assets/img/logo.png" alt="Logo" style="height:40px;width:auto;max-width:190px;object-fit:contain;display:block;">
        </a>
        <a href="<?= BASEURL ?>/admin/dashboard" class="logo-small" style="text-decoration:none;display:none;">
            <img src="<?= BASEURL ?>/admin/assets/img/logo.png" alt="Logo" width="32" height="32" style="object-fit:contain;display:block;">
        </a>
    </div>

    <!-- Spacer -->
    <div style="flex:1;"></div>

    <!-- Header Right -->
    <ul class="nav user-menu">
        <!-- User dropdown -->
        <li class="nav-item dropdown">
            <a href="#" class="dropdown-toggle nav-link d-flex align-items-center" data-toggle="dropdown"
               style="gap:.6rem;padding:.25rem .5rem .25rem .25rem;">
                <div class="user-img">
                    <img class="rounded-circle"
                         src="<?= BASEURL ?>/admin/assets/img/profiles/avatar-01.png"
                         width="34" height="34" alt="Admin"
                         style="border:2px solid #dbeafe;">
                </div>
                <div class="d-none d-md-block" style="line-height:1.2;">
                    <div style="font-size:13px;font-weight:600;color:#0f172a;">
                        <?= isset($_SESSION['auser']) ? htmlspecialchars($_SESSION['auser']) : 'Admin' ?>
                    </div>
                    <div style="font-size:11px;color:#64748b;">Quản trị viên</div>
                </div>
                <i class="fa fa-angle-down d-none d-md-block" style="font-size:11px;color:#94a3b8;margin-left:.25rem;"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="<?= BASEURL ?>/adminProfile/index">
                    <i class="fa fa-user"></i> Hồ sơ
                </a>
                <a class="dropdown-item" href="<?= BASEURL ?>/admin/logout" style="color:#ef4444;">
                    <i class="fa fa-sign-out"></i> Đăng xuất
                </a>
            </div>
        </li>
    </ul>
</div>
<!-- /HEADER -->

<!-- ===== SIDEBAR ===== -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title"><span>Tổng quan</span></li>

                <li id="nav-dashboard">
                    <a href="<?= BASEURL ?>/admin/dashboard">
                        <i class="fa fa-tachometer"></i>
                        <span>Bảng điều khiển</span>
                    </a>
                </li>

                <li class="menu-title"><span>Người dùng</span></li>

                <li class="submenu" id="nav-users">
                    <a href="#">
                        <i class="fe fe-users"></i>
                        <span>Quản lý người dùng</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminUser/admin"><i class="fa fa-shield"></i> Quản trị viên</a></li>
                        <li><a href="<?= BASEURL ?>/adminUser/user"><i class="fa fa-user"></i> Người dùng</a></li>
                        <li><a href="<?= BASEURL ?>/adminUser/agent"><i class="fa fa-id-badge"></i> Môi giới</a></li>
                    </ul>
                </li>

                <li class="menu-title"><span>Địa điểm</span></li>

                <li class="submenu" id="nav-location">
                    <a href="#">
                        <i class="fa fa-map-marker"></i>
                        <span>Thành phố &amp; Phường/Xã</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminLocation/city"><i class="fa fa-globe"></i> Thành phố</a></li>
                        <li><a href="<?= BASEURL ?>/adminLocation/ward"><i class="fa fa-map-signs"></i> Phường/Xã</a></li>
                    </ul>
                </li>

                <li class="menu-title"><span>Bất động sản</span></li>

                <li class="submenu" id="nav-property">
                    <a href="#">
                        <i class="fa fa-home"></i>
                        <span>Bất động sản</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminProperty/add"><i class="fa fa-plus-circle"></i> Thêm mới</a></li>
                        <li><a href="<?= BASEURL ?>/adminProperty/index"><i class="fa fa-list"></i> Danh sách</a></li>
                        <li><a href="<?= BASEURL ?>/adminPropertyType/index"><i class="fa fa-tags"></i> Loại BĐS</a></li>
                    </ul>
                </li>

                <li class="menu-title"><span>Giám sát giao dịch</span></li>

                <li class="submenu" id="nav-supervision">
                    <a href="#">
                        <i class="fa fa-line-chart"></i>
                        <span>Lead &amp; Transaction</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminInquiry/index"><i class="fa fa-phone"></i> Quản lý lead</a></li>
                        <li><a href="<?= BASEURL ?>/adminTransaction/index"><i class="fa fa-exchange"></i> Quản lý transaction</a></li>
                        <li><a href="<?= BASEURL ?>/adminAppointment/index"><i class="fa fa-calendar"></i> Quản lý lịch hẹn</a></li>
                    </ul>
                </li>

                <li class="menu-title"><span>Phản hồi &amp; Nội dung</span></li>

                <li class="submenu" id="nav-feedback">
                    <a href="#">
                        <i class="fa fa-comments"></i>
                        <span>Liên hệ &amp; Phản hồi</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminContact/index"><i class="fa fa-envelope"></i> Liên hệ từ trang chủ</a></li>
                        <li><a href="<?= BASEURL ?>/adminFeedback/index"><i class="fa fa-star"></i> Phản hồi</a></li>
                        <li><a href="<?= BASEURL ?>/adminInquiry/index"><i class="fa fa-phone"></i> Liên hệ từ BĐS</a></li>
                    </ul>
                </li>

                <li class="submenu" id="nav-about">
                    <a href="#">
                        <i class="fa fa-info-circle"></i>
                        <span>Trang giới thiệu</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="display:none;">
                        <li><a href="<?= BASEURL ?>/adminAbout/index"><i class="fa fa-edit"></i> Nội dung giới thiệu</a></li>
                    </ul>
                </li>
            </ul>
            </div>
    </div>

</div>
<!-- /SIDEBAR -->

