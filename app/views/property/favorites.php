<?php require_once '../app/views/layouts/header.php'; ?>
<?php
if (!function_exists('formatPropertyPrice')) {
    function formatPropertyPrice($price, $stype) {
        $formatted = number_format((float)$price, 0, ',', '.') . ' triệu';
        return $stype === 'rent' ? $formatted . '/tháng' : $formatted;
    }
}

$totalProperties = isset($data['properties']) && is_array($data['properties']) ? count($data['properties']) : 0;
$favoritePropertyIds = isset($data['favoritePropertyIds']) && is_array($data['favoritePropertyIds']) ? array_map('intval', $data['favoritePropertyIds']) : [];
?>

<style>
    .favorite-listing-col {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .favorite-listing-card {
        width: 100%;
        max-width: 770px;
        height: 480px;
        margin: 0 auto 24px;
        display: flex;
        flex-direction: column;
    }

    .favorite-listing-media {
        height: 300px;
        overflow: hidden;
    }

    .favorite-listing-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .favorite-listing-body {
        height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .favorite-listing-title a {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .favorite-listing-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .favorite-page-top {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        color: #2c2c2c;
    }

    .favorite-page-top .breadcrumb {
        margin-bottom: 0;
    }

    .favorite-page-top .breadcrumb-item,
    .favorite-page-top .breadcrumb-item a,
    .favorite-page-top .breadcrumb-item.active,
    .favorite-page-top h1,
    .favorite-page-top p {
        color: #2c2c2c;
        margin: 0;
    }

    .favorite-listing-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .favorite-phone-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        background: #0f9aad;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
    }

    .favorite-phone-chip:hover {
        color: #fff;
    }

    .favorite-toggle-form {
        margin: 0;
    }

    .favorite-toggle-btn {
        width: 44px;
        height: 44px;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        background: #fff;
        color: #e74c3c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    @media (max-width: 991.98px) {
        .favorite-listing-card {
            max-width: 100%;
            height: auto;
        }

        .favorite-listing-media {
            height: 240px;
        }

        .favorite-listing-body {
            height: auto;
            min-height: 180px;
        }
    }
</style>

<div class="banner-full-row page-banner" style="background-image:url('<?= BASEURL ?>/images/breadcromb.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="page-name text-white text-uppercase mt-1 mb-0"><b><?= htmlspecialchars($data['pageTitle'] ?? 'Tin đăng yêu thích') ?></b></h2>
            </div>
        </div>
    </div>
</div>

<div class="full-row">
    <div class="container">
        <div class="row mb-3">
            <div class="col-12">
                <div class="favorite-page-top">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/home/index">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/property/index">Bất động sản</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($data['breadcrumbLabel'] ?? 'Yêu thích') ?></li>
                        </ol>
                    </nav>
                    <h1 class="h4"><?= htmlspecialchars($data['pageHeadline'] ?? 'Danh sách tin đăng yêu thích') ?></h1>
                    <p><?= htmlspecialchars($data['totalLabel'] ?? 'Hiện có') ?> <strong><?= (int) $totalProperties ?></strong> bài đăng yêu thích</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <?php if (!empty($data['properties'])): ?>
                        <?php foreach ($data['properties'] as $row): ?>
                            <div class="col-12 favorite-listing-col">
                                <div class="featured-thumb hover-zoomer favorite-listing-card">
                                    <div class="overlay-black position-relative favorite-listing-media">
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>" alt="pimage">
                                        <div class="sale bg-success text-white">Hình thức <?= $row['stype'] == 'sale' ? 'Bán' : 'Cho thuê' ?></div>
                                        <div class="price text-primary" style="font-size:16px;"><?= formatPropertyPrice($row['price'], $row['stype']) ?> <span class="text-white" style="font-size:16px;"><?= htmlspecialchars($row['size']) ?> m2</span></div>
                                    </div>
                                    <div class="featured-thumb-data shadow-one favorite-listing-body">
                                        <div class="p-4 pb-2">
                                            <h5 class="text-secondary hover-text-success mb-2 text-capitalize favorite-listing-title"><a href="<?= BASEURL ?>/property/detail/<?= $row['pid'] ?>"><?= htmlspecialchars($row['title']) ?></a></h5>
                                            <span class="location text-capitalize"><i class="fas fa-map-marker-alt text-success"></i> <?= htmlspecialchars($row['location']) ?></span>
                                        </div>
                                        <div class="px-4 pb-4 favorite-listing-meta">
                                            <div class="text-capitalize"><i class="fas fa-user text-success mr-1"></i>Đăng bởi: <?= htmlspecialchars($row['uname']) ?></div>
                                            <div class="favorite-listing-actions">
                                                <a class="favorite-phone-chip" href="tel:<?= htmlspecialchars($row['uphone'] ?? '') ?>">
                                                    <i class="fas fa-phone-alt"></i>
                                                    <?= htmlspecialchars($row['uphone'] ?: 'Liên hệ chủ tin') ?>
                                                </a>
                                                <?php $isFavorited = in_array((int) $row['pid'], $favoritePropertyIds, true); ?>
                                                <form method="post" action="<?= BASEURL ?>/property/toggleFavorite/<?= $row['pid'] ?>" class="favorite-toggle-form">
                                                    <input type="hidden" name="redirect" value="<?= BASEURL ?>/property/favorites">
                                                    <button type="submit" class="favorite-toggle-btn" aria-label="Bỏ lưu bài đăng">
                                                        <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12"><h3 class="mb-5"><center><?= htmlspecialchars($data['emptyMessage'] ?? 'Chưa có dữ liệu') ?></center></h3></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-widget">
                    <h4 class="double-down-line-left text-secondary position-relative pb-4 my-4">Công cụ tính trả góp</h4>
                    <form class="d-inline-block w-100" action="<?= BASEURL ?>/page/calc" method="post">
                        <label class="sr-only">Giá trị bất động sản</label>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">$</div>
                            </div>
                            <input type="text" class="form-control" name="amount" placeholder="Giá bất động sản">
                        </div>
                        <label class="sr-only">Thời hạn (tháng)</label>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
                            </div>
                            <input type="text" class="form-control" name="month" placeholder="Số tháng vay">
                        </div>
                        <label class="sr-only">Lãi suất</label>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">%</div>
                            </div>
                            <input type="text" class="form-control" name="interest" placeholder="Lãi suất">
                        </div>
                        <button type="submit" value="submit" name="calc" class="btn btn-danger mt-4">Tính trả góp</button>
                    </form>
                </div>

                <div class="sidebar-widget mt-5">
                    <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">Bất động sản mới đăng</h4>
                    <ul class="property_list_widget">
                        <?php if(isset($data['recentProperties'])): foreach($data['recentProperties'] as $row): ?>
                        <li> <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>" alt="pimage">
                            <h6 class="text-secondary hover-text-success text-capitalize"><a href="<?= BASEURL ?>/property/detail/<?= $row['pid'] ?>"><?= htmlspecialchars($row['title']) ?></a></h6>
                            <span class="font-14"><i class="fas fa-map-marker-alt icon-success icon-small"></i> <?= htmlspecialchars($row['location']) ?></span>
                        </li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>