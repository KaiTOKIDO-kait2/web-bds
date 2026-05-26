<?php require_once '../app/views/layouts/header.php'; ?>
<?php
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

$favoritePropertyIds = isset($data['favoritePropertyIds']) && is_array($data['favoritePropertyIds']) ? array_map('intval', $data['favoritePropertyIds']) : [];
$feedbacks = isset($data['feedbacks']) && is_array($data['feedbacks']) ? $data['feedbacks'] : [];
$agents = isset($data['agents']) && is_array($data['agents']) ? array_values($data['agents']) : [];
$topAgents = array_slice($agents, 0, 4);

$searchKeyword = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$searchPriceRange = isset($_GET['price_range']) ? (string) $_GET['price_range'] : '';
$searchType = isset($_GET['type']) ? (string) $_GET['type'] : '';

$priceRangeOptions = [
    '' => 'Tất cả khoảng giá',
    'lt_1m' => 'Dưới 1 triệu',
    '1_3m' => '1 - 3 triệu',
    '3_5m' => '3 - 5 triệu',
    '5_10m' => '5 - 10 triệu',
    '10_40m' => '10 - 40 triệu',
    '40_70m' => '40 - 70 triệu',
    '70_100m' => '70 - 100 triệu',
    'gt_100m' => 'Trên 100 triệu',
];

$propertyTypeOptions = [
    '' => 'Tất cả loại hình',
    'Căn hộ chung cư' => 'Căn hộ chung cư',
    'Chung cư mini' => 'Chung cư mini',
    'Nhà' => 'Nhà',
    'Biệt thự' => 'Biệt thự',
    'Nhà mặt phố' => 'Nhà mặt phố',
    'Nhà trọ' => 'Nhà trọ',
    'Văn phòng' => 'Văn phòng',
];

$currentPriceLabel = $priceRangeOptions[$searchPriceRange] ?? $priceRangeOptions[''];
$currentTypeLabel = $propertyTypeOptions[$searchType] ?? $propertyTypeOptions[''];
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    .lx-home {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        color: #191c1d;
        font-family: 'Inter', sans-serif;
        background: linear-gradient(180deg, #f8f9fa 0%, #f2f4f8 35%, #f8f9fa 100%);
    }

    .lx-wrap {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .lx-block {
        padding: 80px 0;
    }

    .lx-heading-kicker {
        color: #0056d2;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 700;
        margin-bottom: 8px;
        display: inline-block;
    }

    .lx-heading {
        margin: 0;
        color: #001847;
        font-size: 32px;
        line-height: 1.3;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .lx-subheading {
        margin-top: 14px;
        max-width: 720px;
        color: #4a4f5f;
        font-size: 16px;
    }

    .lx-hero {
        min-height: 640px;
        padding: 90px 0 68px;
        position: relative;
        background:
            linear-gradient(115deg, rgba(0, 24, 71, 0.82), rgba(0, 64, 161, 0.64)),
            url('<?= BASEURL ?>/images/banner/rshmpg.jpg') center/cover no-repeat;
        color: #fff;
    }

    .lx-hero .lx-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .lx-hero .lx-heading {
        color: #fff;
        font-size: 48px;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .lx-hero .lx-subheading {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 30px;
        margin-left: auto;
        margin-right: auto;
    }

    .lx-hero-grid {
        display: flex;
        align-items: center;
        gap: 2px;
        background: #fff;
        border-radius: 999px;
        padding: 8px;
        max-width: 980px;
        width: 100%;
        margin: 0 auto;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .lx-field {
        background: transparent;
        border: 0;
        border-right: 1px solid #e6e9f2;
        padding: 8px 16px;
        min-height: 58px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }

    .lx-field:last-of-type {
        border-right: 0;
    }

    .lx-field-content {
        min-width: 0;
        width: 100%;
        text-align: left;
    }

    .lx-field-icon {
        color: #0056d2;
        font-size: 18px;
    }

    .lx-field label {
        margin: 0 0 6px;
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #6b7280;
        font-weight: 700;
        line-height: 1;
    }

    .lx-field select,
    .lx-field input {
        border: 0;
        padding: 0;
        font-size: 14px;
        color: #1f2937;
        font-weight: 500;
        outline: none;
        background: transparent;
        width: 100%;
        line-height: 1.2;
    }

    .lx-select {
        position: relative;
        width: 100%;
    }

    .lx-select-toggle {
        width: 100%;
        border: 0;
        border-radius: 16px;
        padding: 12px 48px 12px 18px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(4px);
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: none;
        cursor: pointer;
        transition: background 0.25s ease, color 0.25s ease;
    }

    .lx-select.has-value .lx-select-toggle {
        color: #0b1f52;
    }

    .lx-select-toggle:hover {
        background: rgba(255, 255, 255, 0.98);
    }

    .lx-select-chevron {
        width: 14px;
        height: 8px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='8' viewBox='0 0 14 8'%3E%3Cpath d='M1.41 0.59 7 6.17l5.59-5.58L13 1l-6 6-6-6z' fill='%23233f93'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 14px 8px;
        transition: transform 0.25s ease;
    }

    .lx-select.is-open .lx-select-chevron {
        transform: rotate(180deg);
    }

    .lx-select-menu {
        position: absolute;
        top: calc(100% + 12px);
        left: 50%;
        width: min(360px, 100vw - 40px);
        max-height: 420px;
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(213, 220, 238, 0.95);
        box-shadow: 0 32px 70px rgba(22, 42, 90, 0.26);
        list-style: none;
        margin: 0;
        padding: 0;
        z-index: 30;
        opacity: 0;
        transform: translate(-50%, -8px);
        pointer-events: none;
        transition: opacity 0.24s ease, transform 0.24s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .lx-select.is-open .lx-select-menu {
        opacity: 1;
        transform: translate(-50%, 0);
        pointer-events: auto;
    }

    .lx-select-menu-header {
        padding: 18px 24px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid rgba(230, 236, 250, 0.9);
    }

    .lx-select-menu-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #0b1f52;
    }

    .lx-select-menu-close {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        padding: 6px;
        border-radius: 50%;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .lx-select-menu-close:hover {
        background: rgba(241, 245, 255, 0.9);
        color: #1e3a8a;
    }

    .lx-select-menu-body {
        padding: 8px 0;
        overflow-y: auto;
        flex: 1 1 auto;
    }

    .lx-select-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        cursor: pointer;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .lx-select-option:hover,
    .lx-select-option:focus {
        background: rgba(241, 245, 255, 0.9);
        color: #0f1f52;
    }

    .lx-select-option.is-active {
        color: #0f1f52;
    }

    .lx-select-radio {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid rgba(226, 232, 240, 0.9);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .lx-select-option.is-active .lx-select-radio {
        border-color: #ef4444;
        background: radial-gradient(circle, #ef4444 55%, transparent 56%);
    }

    .lx-select-menu-footer {
        padding: 14px 24px 20px;
        border-top: 1px solid rgba(230, 236, 250, 0.9);
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .lx-select-reset,
    .lx-select-apply {
        border-radius: 10px;
        font-weight: 600;
        padding: 7px 20px;
        font-size: 14px;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .lx-select-reset {
        border: 1px solid rgba(15, 23, 42, 0.15);
        background: #fff;
        color: #0f172a;
        margin-right: auto;
    }

    .lx-select-reset:hover {
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
        transform: translateY(-1px);
    }

    .lx-select-apply {
        border: 0;
        background: linear-gradient(125deg, #ef4444, #f97316);
        color: #fff;
        box-shadow: 0 12px 24px rgba(239, 68, 68, 0.28);
    }

    .lx-select-apply:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(249, 115, 22, 0.32);
    }

    @media (max-width: 575.98px) {
        .lx-select-toggle {
            padding: 12px 42px 12px 16px;
            font-size: 14px;
        }

        .lx-select-menu {
            width: 100%;
            max-height: 220px;
        }
    }

    .lx-search-btn {
        border: 0;
        border-radius: 999px;
        width: 56px;
        min-width: 56px;
        height: 56px;
        min-height: 56px;
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(120deg, #0056d2, #0040a1);
    }

    .lx-stats {
        margin-top: -52px;
        position: relative;
        z-index: 3;
    }

    .lx-stat-card {
        background: #fff;
        border: 1px solid #e0e4ef;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 14px 30px rgba(15, 33, 72, 0.08);
        height: 100%;
    }

    .lx-stat-value {
        font-size: 34px;
        color: #0040a1;
        line-height: 1;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .lx-stat-label {
        color: #4b5563;
        margin: 0;
    }

    .lx-property-grid {
        margin-top: 26px;
    }

    .lx-property-card {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e2e7f2;
        box-shadow: 0 14px 34px rgba(16, 28, 61, 0.08);
        transition: transform .25s ease, box-shadow .25s ease;
        height: 100%;
    }

    .lx-property-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 24px 45px rgba(16, 28, 61, 0.15);
    }

    .lx-property-media {
        height: 245px;
        position: relative;
    }

    .lx-property-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lx-chip {
        position: absolute;
        top: 14px;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }

    .lx-chip.left {
        left: 14px;
        background: #a93802;
    }

    .lx-chip.right {
        right: 14px;
        background: rgba(0, 24, 71, 0.85);
    }

    /* Price block moved inside card body (static, not overlay) */
    .lx-price {
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.98);
        color: red;
        font-size: 16px;
        /* padding: 10px 12px; */
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-top: 8px;
    }

    .lx-property-body {
        padding: 16px;
    }

    .lx-property-title {
        color: #0f172a;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
    }

    .lx-property-title a {
        color: inherit;
        text-decoration: none;
    }

    .lx-property-title a:hover {
        color: #0040a1;
    }

    .lx-property-location {
        margin-top: 8px;
        color: #6b7280;
        font-size: 14px;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lx-property-meta {
        margin-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .lx-fav-btn {
        border: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: #a93802;
        background: #f1f3f8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .lx-fav-btn.active {
        background: #a93802;
        color: #fff;
    }

    .lx-broker-card {
        background: #ffffff;
        border: 1px solid #e2e7f2;
        border-radius: 18px;
        text-align: center;
        padding: 26px 20px;
        height: 100%;
        box-shadow: 0 12px 30px rgba(16, 28, 61, 0.07);
    }

    .lx-broker-avatar {
        width: 82px;
        height: 82px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 12px;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(8, 30, 70, 0.15);
        display: block;
    }

    .lx-broker-fallback {
        width: 82px;
        height: 82px;
        border-radius: 50%;
        margin: 0 auto 12px;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(8, 30, 70, 0.15);
        background: linear-gradient(120deg, #0040a1, #0056d2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 28px;
        text-transform: uppercase;
    }

    .lx-broker-name {
        margin: 0 0 3px;
        font-size: 20px;
        color: #0f172a;
        font-weight: 700;
    }

    .lx-broker-role {
        margin: 0 0 10px;
        color: #64748b;
        font-size: 14px;
    }

    .lx-broker-contact {
        color: #334155;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .lx-broker-btn {
        display: inline-block;
        min-width: 140px;
        border-radius: 12px;
        padding: 9px 14px;
        text-decoration: none;
        font-weight: 700;
        border: 1px solid #c9d6f8;
        color: #0040a1;
        background: #f5f8ff;
    }

    .lx-brokers .lx-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .lx-brokers .lx-subheading {
        margin-left: auto;
        margin-right: auto;
    }

    .lx-cta {
        border-radius: 26px;
        overflow: hidden;
        padding: 52px 40px;
        min-height: 405px;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        color: #fff;
        background:
            linear-gradient(110deg, rgba(0, 64, 161, 0.9), rgba(0, 86, 210, 0.85)),
            url('<?= BASEURL ?>/images/breadcromb.jpg') center/cover no-repeat;
    }

    .lx-cta h3 {
        font-size: clamp(1.9rem, 3vw, 2.9rem);
        margin-bottom: 12px;
        font-weight: 400;
    }

    .lx-cta p {
        max-width: 760px;
        margin: 0 auto 20px;
        color: rgba(255, 255, 255, 0.92);
    }

    .lx-cta .btn {
        border-radius: 12px;
        font-weight: 700;
        padding: 10px 48px;
    }

    @media (max-width: 1199px) {
        .lx-wrap {
            max-width: 1280px;
        }

        .lx-hero .lx-heading {
            font-size: 42px;
        }
    }

    @media (max-width: 991px) {
        .lx-block {
            padding: 64px 0;
        }

        .lx-hero {
            min-height: 590px;
        }

        .lx-stats {
            margin-top: -36px;
        }

        .lx-hero-grid {
            border-radius: 20px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 10px;
        }

        .lx-field {
            border-right: 0;
            border: 1px solid #e4e7f0;
            border-radius: 12px;
            min-height: 62px;
        }

        .lx-search-btn {
            width: 100%;
            min-width: 0;
            border-radius: 12px;
        }
    }

    @media (max-width: 767px) {
        .lx-wrap {
            padding: 0 16px;
        }

        .lx-block {
            padding: 54px 0;
        }

        .lx-heading {
            font-size: 28px;
        }

        .lx-hero {
            padding: 78px 0 52px;
            min-height: auto;
        }

        .lx-hero .lx-heading {
            font-size: 34px;
        }

        .lx-hero-grid {
            grid-template-columns: 1fr;
        }

        .lx-search-btn,
        .lx-field {
            min-height: 56px;
        }

        .lx-property-media {
            height: 210px;
        }

        .lx-cta {
            padding: 34px 20px;
        }
    }
</style>

<div class="lx-home">
    <section class="lx-hero">
        <div class="lx-wrap">
            <span class="lx-heading-kicker" style="color:#b8d1ff;">LuxEstate Collection</span>
            <h1 class="lx-heading">Kiến tạo không gian sống thượng lưu</h1>
            <p class="lx-subheading">Khám phá hàng ngàn bất động sản cao cấp được tuyển chọn kỹ lưỡng, với trải nghiệm
                tìm kiếm trực quan như bản thiết kế mẫu.</p>

            <form method="get" action="<?= BASEURL ?>/property/index" class="lx-hero-grid">
                <div class="lx-field">
                    <span class="lx-field-icon"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="lx-field-content">
                        <label>Vị trí</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($searchKeyword) ?>">
                    </div>
                </div>
                <div class="lx-field">
                    <span class="lx-field-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="lx-field-content">
                        <label>Khoảng Giá</label>
                        <div class="lx-select <?= $searchPriceRange !== '' ? 'has-value' : '' ?>" data-target="#home-price-range">
                            <button type="button" class="lx-select-toggle" aria-haspopup="listbox"
                                aria-expanded="false" aria-controls="price-range-menu">
                                <span class="lx-select-text"><?= htmlspecialchars($currentPriceLabel) ?></span>
                                <span class="lx-select-chevron" aria-hidden="true"></span>
                            </button>
                            <div class="lx-select-menu" id="price-range-menu" aria-hidden="true" role="dialog"
                                aria-label="Chọn khoảng giá">
                                <div class="lx-select-menu-header">
                                    <h3 class="lx-select-menu-title">Khoảng giá</h3>
                                    <button type="button" class="lx-select-menu-close" aria-label="Đóng">
                                        &times;
                                    </button>
                                </div>
                                <div class="lx-select-menu-body" role="listbox">
                                    <?php foreach ($priceRangeOptions as $value => $label): ?>
                                        <div class="lx-select-option <?= $value === $searchPriceRange ? 'is-active' : '' ?>"
                                            data-value="<?= htmlspecialchars($value) ?>" role="option"
                                            aria-selected="<?= $value === $searchPriceRange ? 'true' : 'false' ?>">
                                            <span><?= htmlspecialchars($label) ?></span>
                                            <span class="lx-select-radio" aria-hidden="true"></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="lx-select-menu-footer">
                                    <button type="button" class="lx-select-reset">Đặt lại</button>
                                    <button type="button" class="lx-select-apply">Áp dụng</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="price_range" id="home-price-range"
                            value="<?= htmlspecialchars($searchPriceRange) ?>">
                    </div>
                </div>
                <div class="lx-field">
                    <span class="lx-field-icon"><i class="fas fa-building"></i></span>
                    <div class="lx-field-content">
                        <label>Loại hình</label>
                        <div class="lx-select <?= $searchType !== '' ? 'has-value' : '' ?>"
                            data-target="#home-property-type">
                            <button type="button" class="lx-select-toggle" aria-haspopup="listbox"
                                aria-expanded="false" aria-controls="property-type-menu">
                                <span class="lx-select-text"><?= htmlspecialchars($currentTypeLabel) ?></span>
                                <span class="lx-select-chevron" aria-hidden="true"></span>
                            </button>
                            <div class="lx-select-menu" id="property-type-menu" aria-hidden="true" role="dialog"
                                aria-label="Chọn loại hình">
                                <div class="lx-select-menu-header">
                                    <h3 class="lx-select-menu-title">Loại hình</h3>
                                    <button type="button" class="lx-select-menu-close" aria-label="Đóng">&times;</button>
                                </div>
                                <div class="lx-select-menu-body" role="listbox">
                                    <?php foreach ($propertyTypeOptions as $value => $label): ?>
                                        <div class="lx-select-option <?= $value === $searchType ? 'is-active' : '' ?>"
                                            data-value="<?= htmlspecialchars($value) ?>" role="option"
                                            aria-selected="<?= $value === $searchType ? 'true' : 'false' ?>">
                                            <span><?= htmlspecialchars($label) ?></span>
                                            <span class="lx-select-radio" aria-hidden="true"></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="lx-select-menu-footer">
                                    <button type="button" class="lx-select-reset">Đặt lại</button>
                                    <button type="button" class="lx-select-apply">Áp dụng</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="type" id="home-property-type"
                            value="<?= htmlspecialchars($searchType) ?>">
                    </div>
                </div>
                <button type="submit" class="lx-search-btn" aria-label="Tìm kiếm bất động sản">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </section>
    <section class="lx-block" style="padding-top:54px;">
        <div class="lx-wrap">
            <span class="lx-heading-kicker">Danh sách nổi bật</span>
            <h2 class="lx-heading">Bất động sản dành cho bạn</h2>
            <div class="row lx-property-grid">
                <?php if (!empty($data['recentProperties'])): ?>
                    <?php foreach ($data['recentProperties'] as $row): ?>
                        <?php $isFavorited = in_array((int) ($row['pid'] ?? 0), $favoritePropertyIds, true); ?>
                        <div class="col-sm-6 col-xl-3 mb-4">
                            <article class="lx-property-card">
                                <div class="lx-property-media">
                                    <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>"
                                        alt="<?= htmlspecialchars($row['title']) ?>">
                                    <span class="lx-chip left">Mới</span>
                                    <span
                                        class="lx-chip right text-capitalize"><?= ($row['stype'] ?? '') === 'sale' ? 'Mua bán' : 'Cho thuê' ?></span>
                                </div>
                                <div class="lx-property-body">
                                    <h3 class="lx-property-title"><a
                                            href="<?= BASEURL ?>/property/detail/<?= (int) ($row['pid'] ?? 0) ?>"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    </h3>
                                      <div class="lx-price">
                                        <span><?= formatPropertyPrice($row['price'] ?? 0, $row['stype'] ?? '') ?></span>
                                        <span><?= htmlspecialchars($row['size'] ?? 0) ?> m2</span>
                                    </div>
                                    <span class="lx-property-location"><i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($row['location'] ?? '') ?></span>
                                    <div class="lx-property-meta">
                                        <small><i class="far fa-calendar-alt"></i>
                                            <?= !empty($row['date']) ? date('d-m-Y', strtotime($row['date'])) : '' ?></small>
                                        <?php if (isset($_SESSION['uid'])): ?>
                                            <form method="post"
                                                action="<?= BASEURL ?>/property/toggleFavorite/<?= (int) ($row['pid'] ?? 0) ?>"
                                                class="m-0">
                                                <input type="hidden" name="redirect" value="<?= BASEURL ?>/home/index">
                                                <button type="submit" class="lx-fav-btn <?= $isFavorited ? 'active' : '' ?>"
                                                    aria-label="Yêu thích bài đăng">
                                                    <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?= BASEURL ?>/auth/login" class="lx-fav-btn"
                                                aria-label="Đăng nhập để lưu tin">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">Chưa có bất động sản nào.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="lx-block lx-brokers" style="background:#fff;">
        <div class="lx-wrap">
            <h2 class="lx-heading">Chuyên gia môi giới hàng đầu</h2>
            <p class="lx-subheading">Bổ sung đúng tinh thần phần môi giới của mẫu, ưu tiên thông tin thật từ hệ thống
                hiện tại.</p>
            <div class="row justify-content-center w-100" style="margin-top: 26px;">
                <?php if (!empty($topAgents)): ?>
                    <?php foreach ($topAgents as $agent): ?>
                        <?php
                        $agentName = trim((string) ($agent['uname'] ?? 'Môi giới'));
                        $firstLetter = $agentName !== '' ? mb_strtoupper(mb_substr($agentName, 0, 1)) : 'A';
                        $agentImage = !empty($agent['uimage']) ? (string) $agent['uimage'] : '';
                        $agentHasImage = $agentImage !== '' && file_exists(__DIR__ . '/../../../admin/user/' . $agentImage);
                        ?>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="lx-broker-card">
                                <?php if ($agentHasImage): ?>
                                    <img class="lx-broker-avatar"
                                        src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($agentImage) ?>"
                                        alt="<?= htmlspecialchars($agentName) ?>">
                                <?php else: ?>
                                    <div class="lx-broker-fallback"><?= htmlspecialchars($firstLetter) ?></div>
                                <?php endif; ?>
                                <h4 class="lx-broker-name"><?= htmlspecialchars($agentName) ?></h4>
                                <p class="lx-broker-role">Chuyên viên môi giới</p>
                                <div class="lx-broker-contact">
                                    <?php if (!empty($agent['uphone'])): ?>
                                        <div><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($agent['uphone']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($agent['uemail'])): ?>
                                        <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($agent['uemail']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <a class="lx-broker-btn" href="<?= BASEURL ?>/agent/index">Xem hồ sơ</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">Hiện chưa có dữ liệu môi giới để hiển thị.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($feedbacks)): ?>
        <section class="lx-block" style="padding-top:64px;">
            <div class="lx-wrap">
                <h2 class="lx-heading">Đánh giá khách hàng</h2>
                <div class="row" style="margin-top:22px;">
                    <?php foreach (array_slice($feedbacks, 0, 3) as $feedback): ?>
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div style="background:#fff;border:1px solid #e2e7f2;border-radius:14px;padding:18px;height:100%;">
                                <p class="mb-3">"<?= htmlspecialchars($feedback['fdescription'] ?? '') ?>"</p>
                                <strong
                                    class="d-block text-capitalize"><?= htmlspecialchars($feedback['uname'] ?? 'Khách hàng') ?></strong>
                                <small
                                    class="text-muted text-capitalize"><?= htmlspecialchars($feedback['utype'] ?? 'User') ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="lx-block">
        <div class="lx-wrap">
            <div class="lx-cta">
                <h3>Bạn đang muốn bán hoặc cho thuê nhà?</h3>
                <p>Tiếp cận với người dùng tiềm năng nhanh hơn và giữ nguyên tinh thần thẩm mỹ của giao diện mẫu
                    LuxEstate.</p>
                <a href="<?= BASEURL ?>/property/create" class="btn btn-light mr-2">Đăng tin ngay</a>
                <a href="<?= BASEURL ?>/property/index" class="btn btn-outline-light">Xem danh sách</a>
            </div>
        </div>
    </section>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
