<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$agent = $data['agent'] ?? [];
$properties = isset($data['properties']) && is_array($data['properties']) ? $data['properties'] : [];
$areaLabels = isset($data['areaLabels']) && is_array($data['areaLabels']) ? $data['areaLabels'] : [];

if (!function_exists('formatPropertyPrice')) {
    function formatPropertyPrice($price, $stype)
    {
        if ((float) $price <= 0) {
            return 'Thỏa thuận';
        }
        $formatted = number_format((float) $price, 0, ',', '.') . ' triệu';
        return $stype === 'rent' ? $formatted . '/tháng' : $formatted;
    }
}
?>

<style>
    .agent-detail-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 28px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
        margin-bottom: 30px;
    }

    .agent-detail-photo {
        width: 220px;
        height: 260px;
        object-fit: cover;
        border-radius: 18px;
        display: block;
    }

    .agent-detail-name {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 12px;
    }

    .agent-detail-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .agent-detail-meta {
        color: #374151;
        margin-bottom: 12px;
        font-size: 1rem;
    }

    .agent-detail-meta i {
        width: 22px;
        color: #0f9aad;
    }

    .agent-detail-email {
        display: inline-block;
        margin-top: 8px;
        padding: 12px 18px;
        border-radius: 10px;
        background: #0f9aad;
        color: #fff;
        font-weight: 700;
    }

    .agent-detail-email:hover {
        color: #fff;
        background: #0b7b8b;
    }

    .agent-area-chip {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #fff7ed;
        color: #c2410c;
        font-weight: 700;
        margin: 0 8px 8px 0;
    }

    .agent-property-card {
        width: 100%;
        margin-bottom: 24px;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
    }

    .agent-property-media {
        height: 260px;
        overflow: hidden;
        position: relative;
    }

    .agent-property-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .agent-property-body {
        background: #fff;
        padding: 20px 22px 22px;
    }

    .agent-property-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .agent-property-title a {
        color: #1f2937;
    }

    .agent-property-title a:hover {
        color: #00a884;
    }

    .agent-property-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: #4b5563;
        margin-top: 14px;
    }

    .agent-property-price {
        color: #00a884;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .agent-property-empty {
        border: 1px dashed #d1d5db;
        border-radius: 18px;
        padding: 26px;
        text-align: center;
        color: #6b7280;
        background: #fff;
    }

    .agent-page-top {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        color: #2c2c2c;
    }

    .agent-page-top .breadcrumb {
        margin-bottom: 0;
    }

    .agent-page-top .breadcrumb-item,
    .agent-page-top .breadcrumb-item a,
    .agent-page-top .breadcrumb-item.active,
    .agent-page-top h1,
    .agent-page-top p {
        color: #2c2c2c;
        margin: 0;
    }
</style>

<div class="banner-full-row page-banner" style="background-image:url('<?= BASEURL ?>/images/breadcromb.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="page-name text-white text-uppercase mt-1 mb-0"><b>Chi tiết nhà môi giới</b></h2>
            </div>
        </div>
    </div>
</div>

<div class="full-row bg-gray">
    <div class="container">
        <div class="row mb-3">
            <div class="col-12">
                <div class="agent-page-top">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/home/index">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/agent/index">Môi giới</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($agent['uname'] ?? '') ?></li>
                        </ol>
                    </nav>
                    <h1 class="h4">Nhà môi giới: <?= htmlspecialchars($agent['uname'] ?? '') ?></h1>
                    <p>Hiện có <strong><?= (int) ($data['propertyCount'] ?? 0) ?></strong> bài đăng</p>
                </div>
            </div>
        </div>
        <div class="agent-detail-card">
            <div class="row align-items-start">
                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <img class="agent-detail-photo"
                        src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($agent['uimage'] ?? '') ?>"
                        alt="<?= htmlspecialchars($agent['uname'] ?? '') ?>">
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="agent-detail-name"><?= htmlspecialchars($agent['uname'] ?? '') ?></div>
                    <div class="agent-detail-badge"><?= htmlspecialchars($data['categoryLabel'] ?? 'Nhà môi giới') ?>
                    </div>
                    <div class="agent-detail-meta"><i
                            class="fas fa-phone-alt"></i><?= htmlspecialchars($agent['uphone'] ?? '') ?></div>
                    <div class="agent-detail-meta"><i
                            class="fas fa-envelope"></i><?= htmlspecialchars($agent['uemail'] ?? '') ?></div>
                    <div class="agent-detail-meta"><i class="fas fa-building"></i>Đã đăng
                        <?= (int) ($data['propertyCount'] ?? 0) ?> bất động sản công khai
                    </div>
                    <div class="agent-detail-meta"><i class="fas fa-map-marked-alt"></i>Khu vực môi giới</div>
                    <div class="mb-3">
                        <?php if (!empty($areaLabels)):
                            foreach ($areaLabels as $label): ?>
                                <span class="agent-area-chip"><?= htmlspecialchars($label) ?></span>
                            <?php endforeach; else: ?>
                            <span class="text-muted">Chưa có khu vực môi giới công khai.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-secondary mb-0"><b>Bài đã đăng</b></h3>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($properties)):
                foreach ($properties as $row): ?>
                    <div class="col-12">
                        <div class="agent-property-card">
                            <div class="row no-gutters">
                                <div class="col-lg-4">
                                    <div class="agent-property-media">
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage'] ?? '') ?>"
                                            alt="<?= htmlspecialchars($row['title'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="agent-property-body">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                            <div class="sale bg-success text-white px-3 py-2 rounded">Hình thức
                                                <?= ($row['stype'] ?? '') === 'sale' ? 'Bán' : 'Cho thuê' ?>
                                            </div>
                                            <div class="agent-property-price">
                                                <?= formatPropertyPrice($row['price'] ?? 0, $row['stype'] ?? '') ?>
                                            </div>
                                        </div>
                                        <div class="agent-property-title"><a
                                                href="<?= BASEURL ?>/property/detail/<?= (int) ($row['pid'] ?? 0) ?>"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                        </div>
                                        <div class="text-muted mb-2"><i
                                                class="fas fa-map-marker-alt text-success mr-2"></i><?= htmlspecialchars($row['location'] ?? '') ?><?= !empty($row['ward']) || !empty($row['city']) ? ', ' . htmlspecialchars(trim(($row['ward'] ?? '') . (($row['ward'] ?? '') !== '' && ($row['city'] ?? '') !== '' ? ', ' : '') . ($row['city'] ?? ''))) : '' ?>
                                        </div>
                                        <div class="text-muted">
                                            <?= htmlspecialchars(mb_strimwidth(trim(strip_tags(html_entity_decode($row['pcontent'] ?? '', ENT_QUOTES, 'UTF-8'))), 0, 220, '...', 'UTF-8')) ?>
                                        </div>
                                        <div class="agent-property-meta">
                                            <span><i
                                                    class="fas fa-vector-square text-success mr-1"></i><?= htmlspecialchars($row['size'] ?? '') ?>
                                                m2</span>
                                            <span><i
                                                    class="fas fa-bed text-success mr-1"></i><?= htmlspecialchars($row['bedroom'] ?? '') ?>
                                                phòng ngủ</span>
                                            <span><i
                                                    class="fas fa-bath text-success mr-1"></i><?= htmlspecialchars($row['bathroom'] ?? '') ?>
                                                phòng tắm</span>
                                            <span><i
                                                    class="fas fa-layer-group text-success mr-1"></i><?= htmlspecialchars($row['type'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                <div class="col-12">
                    <div class="agent-property-empty">Nhà môi giới này chưa có bài đăng công khai.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>