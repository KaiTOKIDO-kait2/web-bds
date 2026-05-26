<?php
$propertyNotificationCount = 0;
$propertyNotifications = [];
$favoriteCount = 0;
$favoriteNotifications = [];
$topbarUser = null;

if (!function_exists('formatFavoriteTimeAgo')) {
    function formatFavoriteTimeAgo($dateTime)
    {
        $timestamp = strtotime((string) $dateTime);
        if (!$timestamp) {
            return 'Vừa lưu';
        }

        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Lưu vừa xong';
        }
        if ($diff < 3600) {
            return 'Lưu ' . floor($diff / 60) . ' phút trước';
        }
        if ($diff < 86400) {
            return 'Lưu ' . floor($diff / 3600) . ' giờ trước';
        }
        return 'Lưu ' . floor($diff / 86400) . ' ngày trước';
    }
}

if (!function_exists('formatNotificationTimeAgo')) {
    function formatNotificationTimeAgo($dateTime)
    {
        $timestamp = strtotime((string) $dateTime);
        if (!$timestamp) {
            return 'Vừa cập nhật';
        }

        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Vừa cập nhật';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' phút trước';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' giờ trước';
        }
        return floor($diff / 86400) . ' ngày trước';
    }
}

if (isset($_SESSION['uid'])) {
    require_once '../app/models/Property.php';
    require_once '../app/models/User.php';
    $propertyNotificationModel = new Property();
    $topbarUserModel = new User();
    $propertyNotificationCount = $propertyNotificationModel->countUnreadApprovalNotifications($_SESSION['uid']);
    $propertyNotifications = $propertyNotificationModel->getRecentApprovalNotifications($_SESSION['uid']);
    $favoriteCount = $propertyNotificationModel->countFavorites($_SESSION['uid']);
    $favoriteNotifications = $propertyNotificationModel->getRecentFavorites($_SESSION['uid'], 3);
    $topbarUser = $topbarUserModel->getUserById($_SESSION['uid']);
}

$canPostProperty = false;
if (!empty($topbarUser['utype'])) {
    $canPostProperty = in_array(strtolower((string) $topbarUser['utype']), ['owner', 'agent'], true);
}

$requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
$isWorkspacePage = strpos($requestUri, '/agentWorkspace') !== false || strpos($requestUri, '/userWorkspace') !== false;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="<?= BASEURL ?>/images/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/bootstrap-slider.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/layerslider.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/color.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/fonts/flaticon/flaticon.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?= BASEURL ?>/css/login.css">
    <?php if ($isWorkspacePage): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <style>
        .no-caret::after {
            display: none !important;
        }

        .site-logo-img {
            height: 50px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
            display: block;
        }

        .top-header,
        .main-nav {
            background: #fff !important;
        }

        .header-shell {
            background: #fff;
            border-bottom: 1px solid #eceef3;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            position: relative;
            z-index: 1000;
        }

        .topbar-icon-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            color: #e74c3c;
            font-size: 18px;
        }

        .topbar-icon-link:hover {
            color: #e74c3c;
        }

        .topbar-dropdown {
            position: relative;
        }

        .topbar-centered-menu {
            left: 50% !important;
            right: auto !important;
            top: calc(100% + 10px) !important;
            transform: translateX(-50%) !important;
            margin-top: 0 !important;
        }

        .topbar-count-badge {
            position: absolute;
            top: -7px;
            right: -7px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #e74c3c;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 20px;
            text-align: center;
        }

        .top-avatar-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            border: 1px solid #28a745;
            flex-shrink: 0;
        }

        .top-avatar-fallback {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #28a745;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            margin-right: 8px;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .favorite-dropdown-menu {
            min-width: 360px;
            padding: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(8, 18, 40, 0.14);
        }

        .favorite-dropdown-header {
            padding: 14px 16px;
            font-size: 16px;
            font-weight: 700;
            color: #2c2c2c;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            line-height: 1.2;
        }

        .dropdown-list-scroll {
            max-height: 280px;
            overflow-y: auto;
        }

        .favorite-dropdown-item {
            display: flex;
            align-items: stretch;
            padding: 10px 12px;
            border-bottom: 1px solid #f2f2f2;
            color: #2c2c2c;
            gap: 10px;
            min-height: 84px;
        }

        .favorite-dropdown-item:hover {
            background: #fafafa;
            color: #2c2c2c;
        }

        .favorite-dropdown-thumb {
            width: 84px;
            aspect-ratio: 4 / 3;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .favorite-dropdown-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .favorite-dropdown-content {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 64px;
        }

        .favorite-dropdown-title {
            font-size: 12px;
            line-height: 1.35;
            color: #2c2c2c;
            margin-bottom: 6px;
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: calc(1.35em * 2);
            word-break: break-word;
        }

        .favorite-dropdown-time {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            color: #666;
            line-height: 1.2;
        }

        .favorite-dropdown-footer {
            padding: 10px 14px;
            text-align: center;
            background: #fff;
        }

        .favorite-dropdown-footer a {
            color: #e74c3c;
            font-weight: 700;
        }

        .navbar-nav .nav-link {
            color: #2c2c2c !important;
            font-weight: 600;
            font-size: 14px;
            padding: 0.55rem 0.85rem !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0040a1 !important;
        }

        .nav-cta {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            background: rgb(0, 64, 161);
            border: 0;
            color: #fff !important;
            text-decoration: none;
        }

        .nav-cta:hover,
        .nav-cta:focus {
            background: rgb(0, 64, 161);
            color: #fff;
            text-decoration: none;
        }

        .nav-cta:hover {
            color: #fff;
            text-decoration: none;
        }
    </style>
    <title>LuxEstate | Bất động sản</title>
</head>

<body>

    <div id="page-wrapper">
        <div class="row">
            <header id="header" class="transparent-header-modern fixed-header-bg-white w-100">
                <?php if (!$isWorkspacePage): ?>
                    <div class="main-nav secondary-nav hover-success-nav py-2 header-shell">
                        <div class="container">
                            <nav class="navbar navbar-expand-lg navbar-light p-0 align-items-center">
                                <a class="navbar-brand position-relative" href="<?= BASEURL ?>/home/index"><img
                                        class="nav-logo site-logo-img" src="<?= BASEURL ?>/admin/assets/img/logo.png"
                                        alt="Logo"></a>
                                <button class="navbar-toggler" type="button" data-toggle="collapse"
                                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                    aria-expanded="false" aria-label="Toggle navigation"><span
                                        class="navbar-toggler-icon"></span></button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav mx-auto align-items-lg-center">
                                        <li class="nav-item"><a class="nav-link" href="<?= BASEURL ?>/home/index">Trang
                                                chủ</a></li>
                                        <li class="nav-item"><a class="nav-link" href="<?= BASEURL ?>/page/about">Giới
                                                thiệu</a></li>
                                        <li class="nav-item"><a class="nav-link" href="<?= BASEURL ?>/property/index">Bất
                                                động sản</a></li>
                                        <li class="nav-item"><a class="nav-link" href="<?= BASEURL ?>/agent/index">Môi
                                                giới</a></li>
                                        <li class="nav-item"><a class="nav-link" href="<?= BASEURL ?>/page/contact">Liên
                                                hệ</a></li>
                                    </ul>

                                    <div class="d-flex align-items-center ml-lg-3 mt-3 mt-lg-0">
                                        <ul class="list-text-white d-flex align-items-center mb-0">
                                            <?php if (isset($_SESSION['uemail'])): ?>
                                                <li class="dropdown mr-3 topbar-dropdown">
                                                    <a href="#" class="dropdown-toggle no-caret topbar-icon-link"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                        title="Tin đã lưu">
                                                        <i class="far fa-heart"></i>
                                                        <?php if ($favoriteCount > 0): ?>
                                                            <span class="topbar-count-badge"><?= $favoriteCount ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="dropdown-menu topbar-centered-menu favorite-dropdown-menu">
                                                        <div class="favorite-dropdown-header">Tin đăng đã lưu</div>
                                                        <?php if (!empty($favoriteNotifications)): ?>
                                                            <div class="dropdown-list-scroll">
                                                                <?php foreach ($favoriteNotifications as $favoriteItem): ?>
                                                                    <a class="favorite-dropdown-item"
                                                                        href="<?= BASEURL ?>/property/detail/<?= $favoriteItem['pid'] ?>">
                                                                        <span class="favorite-dropdown-thumb">
                                                                            <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($favoriteItem['pimage']) ?>"
                                                                                alt="favorite">
                                                                        </span>
                                                                        <span class="favorite-dropdown-content">
                                                                            <span
                                                                                class="favorite-dropdown-title"><?= htmlspecialchars($favoriteItem['title']) ?></span>
                                                                            <span
                                                                                class="favorite-dropdown-time"><?= htmlspecialchars(formatFavoriteTimeAgo($favoriteItem['favorite_created_at'])) ?></span>
                                                                        </span>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="p-3 text-muted">Bạn chưa lưu bài đăng nào.</div>
                                                        <?php endif; ?>
                                                        <div class="favorite-dropdown-footer">
                                                            <a href="<?= BASEURL ?>/property/favorites">Xem tất cả <i
                                                                    class="fas fa-arrow-right ml-1"></i></a>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="dropdown mr-3 topbar-dropdown">
                                                    <a href="#" class="dropdown-toggle no-caret topbar-icon-link"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                        title="Thông báo">
                                                        <i class="fas fa-bell"></i>
                                                        <?php if ($propertyNotificationCount > 0): ?>
                                                            <span
                                                                class="topbar-count-badge"><?= $propertyNotificationCount ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="dropdown-menu topbar-centered-menu favorite-dropdown-menu">
                                                        <div class="favorite-dropdown-header">Cập nhật bài đăng</div>
                                                        <?php if (!empty($propertyNotifications)): ?>
                                                            <div class="dropdown-list-scroll">
                                                                <?php foreach ($propertyNotifications as $notification): ?>
                                                                    <a class="favorite-dropdown-item"
                                                                        href="<?= BASEURL ?>/property/detail/<?= $notification['pid'] ?>">
                                                                        <span class="favorite-dropdown-thumb">
                                                                            <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($notification['pimage']) ?>"
                                                                                alt="notify">
                                                                        </span>
                                                                        <span class="favorite-dropdown-content">
                                                                            <span
                                                                                class="favorite-dropdown-title"><?= htmlspecialchars($notification['title']) ?></span>
                                                                            <span
                                                                                class="favorite-dropdown-time <?= $notification['approval_status'] === 'approved' ? 'text-success' : 'text-danger' ?>">
                                                                                <?= $notification['approval_status'] === 'approved' ? 'Đã duyệt' : 'Bị từ chối' ?>
                                                                                ·
                                                                                <?= htmlspecialchars(formatNotificationTimeAgo($notification['reviewed_at'])) ?>
                                                                            </span>
                                                                        </span>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="p-3 text-muted">Chưa có cập nhật duyệt bài nào.</div>
                                                        <?php endif; ?>
                                                        <div class="favorite-dropdown-footer">
                                                            <a href="<?= BASEURL ?>/agentWorkspace/index?section=posts">Xem bài
                                                                đăng của tôi <i class="fas fa-arrow-right ml-1"></i></a>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="dropdown">
                                                    <a href="#" class="dropdown-toggle no-caret d-flex align-items-center"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <?php
                                                        $topbarFirstLetter = mb_strtoupper(mb_substr($_SESSION['uname'], 0, 1));
                                                        $topbarHasAvatar = !empty($topbarUser['uimage']) && file_exists(__DIR__ . '/../../../admin/user/' . $topbarUser['uimage']);
                                                        ?>
                                                        <?php if ($topbarHasAvatar): ?>
                                                            <img src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($topbarUser['uimage']) ?>"
                                                                alt="avatar" class="top-avatar-img">
                                                        <?php else: ?>
                                                            <span
                                                                class="top-avatar-fallback"><?= htmlspecialchars($topbarFirstLetter) ?></span>
                                                        <?php endif; ?>
                                                        <span class="d-none d-lg-inline">Tài khoản của tôi</span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <?php if ($canPostProperty): ?>
                                                            <a class="dropdown-item"
                                                                href="<?= BASEURL ?>/agentWorkspace/index?section=overview"><i
                                                                    class="fas fa-chart-pie mr-2"></i>Tổng quan</a>
                                                            <a class="dropdown-item"
                                                                href="<?= BASEURL ?>/agentWorkspace/index?section=posts"><i
                                                                    class="fas fa-home mr-2"></i>Quản lý bài đăng</a>
                                                            <?php if (in_array(strtolower((string) ($_SESSION['utype'] ?? '')), ['owner', 'agent'], true)): ?>
                                                                <a class="dropdown-item"
                                                                    href="<?= BASEURL ?>/agentWorkspace/index?section=leads"><i
                                                                        class="fas fa-funnel-dollar mr-2"></i>Xử lý lead</a>
                                                                <a class="dropdown-item"
                                                                    href="<?= BASEURL ?>/agentWorkspace/index?section=appointments"><i
                                                                        class="fas fa-calendar-alt mr-2"></i>Quản lý lịch hẹn</a>
                                                            <?php endif; ?>
                                                            <div class="dropdown-divider"></div>
                                                        <?php endif; ?>
                                                        <?php if (!$canPostProperty): ?>
                                                            <a class="dropdown-item" href="<?= BASEURL ?>/userWorkspace/index"><i
                                                                    class="fas fa-tasks mr-2"></i>Yêu cầu của tôi</a>
                                                        <?php endif; ?>
                                                        <a class="dropdown-item" href="<?= BASEURL ?>/profile/index"><i
                                                                class="fas fa-user mr-2"></i>Hồ sơ</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                            href="<?= BASEURL ?>/auth/logout"><i
                                                                class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</a>
                                                    </div>
                                                </li>
                                                <?php if ($canPostProperty): ?>
                                                    <li class="ml-3 d-none d-lg-block">
                                                        <a class="nav-cta"
                                                            href="<?= BASEURL ?>/agentWorkspace/index?section=create">Đăng tin</a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <li class="mr-3"><i class="fas fa-user text-success mr-1"></i><a
                                                        href="<?= BASEURL ?>/auth/login">Đăng nhập</a></li>
                                                <li><i class="fas fa-user-plus text-success mr-1"></i><a
                                                        href="<?= BASEURL ?>/auth/register">Đăng ký</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            </header>
