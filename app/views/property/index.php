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


if (!function_exists('formatPropertyFloor')) {
    function formatPropertyFloor($floor)
    {
        $floor = trim((string) $floor);
        return $floor !== '' ? $floor : 'Chưa cập nhật';
    }
}

$totalProperties = isset($data['properties']) && is_array($data['properties']) ? count($data['properties']) : 0;
$headlinePeriod = 'T' . date('n') . '/' . date('Y');
$favoritePropertyIds = isset($data['favoritePropertyIds']) && is_array($data['favoritePropertyIds']) ? array_map('intval', $data['favoritePropertyIds']) : [];
$cities = isset($data['cities']) && is_array($data['cities']) ? $data['cities'] : [];
$filterKeyword = isset($data['filter_q']) ? (string) $data['filter_q'] : '';
$filterPriceRange = isset($data['filter_price_range']) ? (string) $data['filter_price_range'] : '';
$filterAreaRange = isset($data['filter_area_range']) ? (string) $data['filter_area_range'] : '';
$filterRooms = isset($data['filter_rooms']) ? (string) $data['filter_rooms'] : '';
$selectedTypes = isset($data['selected_types']) && is_array($data['selected_types']) ? $data['selected_types'] : [];
$viewMode = (($data['view_mode'] ?? 'grid') === 'list') ? 'list' : 'grid';

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

$areaRangeOptions = [
    '' => 'Tất cả diện tích',
    'lt_30' => 'Dưới 30 m²',
    '30_50' => '30 - 50 m²',
    '50_80' => '50 - 80 m²',
    '80_100' => '80 - 100 m²',
    '100_150' => '100 - 150 m²',
    '150_200' => '150 - 200 m²',
    '200_250' => '200 - 250 m²',
    '250_300' => '250 - 300 m²',
    '300_500' => '300 - 500 m²',
    'gt_500' => 'Trên 500 m²',
];

$roomsOptions = [
    '' => 'Tất cả số phòng',
    '1' => '1+',
    '2' => '2+',
    '3' => '3+',
    '4' => '4+',
];

$currentPriceLabel = $priceRangeOptions[$filterPriceRange] ?? $priceRangeOptions[''];
$currentAreaLabel = $areaRangeOptions[$filterAreaRange] ?? $areaRangeOptions[''];
$currentRoomsLabel = $roomsOptions[$filterRooms] ?? $roomsOptions[''];

$perPage = 30;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$allProperties = isset($data['properties']) && is_array($data['properties']) ? $data['properties'] : [];
$totalItems = count($allProperties);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$pagedProperties = array_slice($allProperties, ($currentPage - 1) * $perPage, $perPage);

if (!function_exists('ppPageUrl')) {
    function ppPageUrl($page, $params)
    {
        $params['page'] = $page;
        return BASEURL . '/property/index?' . http_build_query($params, '', '&');
    }
}

$viewParams = [
    'type' => $data['filter_type'] ?? '',
    'stype' => $data['filter_stype'] ?? '',
    'city' => $data['filter_city'] ?? '',
    'city_id' => (int) ($data['filter_city_id'] ?? 0),
    'q' => $filterKeyword,
    'price_range' => $filterPriceRange,
    'area_range' => $filterAreaRange,
    'rooms' => $filterRooms,
    'sort' => $data['sort'] ?? '',
];
if (!empty($selectedTypes)) {
    $viewParams['types'] = $selectedTypes;
}
$gridViewParams = $viewParams;
$gridViewParams['view'] = 'grid';
unset($gridViewParams['page']);
$listViewParams = $viewParams;
$listViewParams['view'] = 'list';
unset($listViewParams['page']);
$paginationParams = $viewParams;
$paginationParams['view'] = $viewMode;
$gridViewUrl = BASEURL . '/property/index?' . http_build_query($gridViewParams);
$listViewUrl = BASEURL . '/property/index?' . http_build_query($listViewParams);

$fallbackTypes = ['Căn hộ chung cư', 'Chung cư mini', 'Nhà', 'Biệt thự', 'Nhà mặt phố', 'Nhà trọ', 'Văn phòng'];
$typeRows = isset($data['propertyTypes']) && is_array($data['propertyTypes']) && !empty($data['propertyTypes'])
    ? $data['propertyTypes']
    : array_map(function ($name) {
        return ['name' => $name];
    }, $fallbackTypes);
?>

<style>
    .pp-page {
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        background: linear-gradient(180deg, #f8f9fa 0%, #f1f4fa 38%, #f8f9fa 100%);
        color: #191c1d;
    }

    .pp-wrap {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .pp-hero {
        position: relative;
        z-index: 20;
        padding: 64px 0 48px;
        background:
            linear-gradient(115deg, rgba(0, 24, 71, 0.84), rgba(0, 64, 161, 0.62)),
            url('<?= BASEURL ?>/images/breadcromb.jpg') center/cover no-repeat;
        color: #fff;
    }

    .pp-kicker {
        display: inline-block;
        font-size: 12px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 700;
        color: #b8d1ff;
        margin-bottom: 8px;
    }

    .pp-title {
        font-size: clamp(1.8rem, 3.2vw, 2.9rem);
        font-weight: 800;
        line-height: 1.2;
        margin: 0;
    }

    .pp-sub {
        margin-top: 10px;
        color: rgba(255, 255, 255, 0.9);
        max-width: 780px;
        margin-bottom: 0;
    }

    .pp-search-shell {
        margin-top: 24px;
        background: rgba(255, 255, 255, 0.96);
        border-radius: 22px;
        padding: 10px;
        box-shadow: 0 18px 40px rgba(0, 10, 30, 0.22);
    }

    .pp-search-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 0.9fr 0.5fr;
        gap: 8px;
        align-items: center;
    }

    .pp-field {
        border: 1px solid #e2e7f2;
        border-radius: 14px;
        padding: 10px 12px;
        background: #fff;
        min-height: 58px;
    }

    .pp-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        font-weight: 700;
        color: #64748b;
        line-height: 1;
    }

    .pp-field select {
        border: 0;
        background: transparent;
        width: 100%;
        padding: 0;
        font-size: 14px;
        color: #1f2937;
        outline: none;
    }

    .pp-field input {
        border: 0;
        background: transparent;
        width: 100%;
        padding: 0;
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
        outline: none;
    }

    .pp-field-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pp-field-icon {
        color: #0040a1;
        font-size: 14px;
        width: 16px;
        text-align: center;
        flex-shrink: 0;
    }

    .pp-search-btn {
        border: 0;
        width: 100%;
        min-height: 58px;
        border-radius: 14px;
        background: rgb(0, 64, 161);
        color: #fff;
        font-weight: 700;
        font-size: 14px;
    }

    .pp-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin-bottom: 24px;
    }

    .pp-topbar h2 {
        color: #001847;
        font-size: clamp(1.4rem, 2.2vw, 2rem);
        margin: 0 0 6px;
        font-weight: 800;
        line-height: 1.2;
    }

    .pp-topbar p,
    .pp-topbar .breadcrumb,
    .pp-topbar .breadcrumb-item,
    .pp-topbar .breadcrumb-item a,
    .pp-topbar .breadcrumb-item.active {
        color: #4a4f5f;
        margin-bottom: 0;
    }

    .pp-view-switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pp-view-btn {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        border: 1px solid #d9e1f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #4b5563;
        background: #fff;
        text-decoration: none;
        font-size: 16px;
    }

    .pp-view-btn.active {
        color: #0040a1;
        border-color: #0040a1;
        background: #eaf1ff;
    }

    .pp-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 24px;
    }

    .pp-main {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }

    .pp-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .pp-side-filter {
        display: grid;
        gap: 10px;
    }

    .pp-side-filter .pp-field {
        min-height: 54px;
    }

    .pp-side-filter .pp-search-btn {
        min-height: 46px;
        border-radius: 10px;
        font-size: 13px;
    }

    .pp-check-grid {
        border-radius: 12px;
        padding: 10px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        max-height: 200px;
        overflow: auto;
    }

    .pp-check-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #334155;
        margin: 0;
    }

    .pp-check-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }

    .pp-direction-grid .pp-check-item {
        margin: 0;
    }

    .pp-direction-grid .pp-check-item input[type="checkbox"] {
        display: none;
    }

    .pp-direction-label {
        padding: 5px 47px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dfe8f6;
        border-radius: 12px;
        color: #0f172a;
        background: #fff;
        cursor: pointer;
        user-select: none;
    }

    .pp-direction-grid .pp-check-item input[type="checkbox"]:checked+.pp-direction-label {
        border-color: #0040a1;
        color: #0040a1;
        background: #edf3ff;
    }

    .pp-card {
        background: #fff;
        border: 1px solid #e2e7f2;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(16, 28, 61, 0.08);
        transition: transform .25s ease, box-shadow .25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .pp-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 42px rgba(16, 28, 61, 0.14);
    }

    .pp-media {
        position: relative;
        height: 220px;
    }

    .pp-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pp-chip {
        position: absolute;
        top: 12px;
        right: 12px;
        border-radius: 999px;
        background: rgba(0, 24, 71, 0.85);
        color: #fff;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Price block used inside the card body (static, not overlaying the image) */
    .pp-price {
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.98);
        padding: 8px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #001847;
        font-weight: 700;
        font-size: 13px;
        margin-top: 4px;
    }

    .pp-price-1{
        padding: 0 0 !important;
        font-size: 16px !important;
        color: red !important;
    }
    .pp-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .pp-title-link {
        color: #0f172a;
        font-weight: 700;
        font-size: 17px;
        line-height: 1.35;
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 46px;
    }

    .pp-title-link:hover {
        color: #0040a1;
    }

    .pp-location {
        color: #64748b;
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pp-excerpt {
        margin: 0;
        color: #495057;
        font-size: 14px;
        line-height: 1.4;
        max-height: 3.2em; /* approx 2 lines */
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .pp-meta {
        color: #475569;
        font-size: 13px;
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pp-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pp-phone {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 10px;
        padding: 8px 10px;
        background: #0056d2;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .pp-phone:hover {
        color: #fff;
        text-decoration: none;
    }

    .pp-favorite-form {
        margin: 0;
    }

    .pp-favorite {
        width: 36px;
        height: 36px;
        border: 1px solid #d9d9d9;
        border-radius: 10px;
        background: #fff;
        color: #2c2c2c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all .2s ease;
        text-decoration: none;
    }

    .pp-favorite.active,
    .pp-favorite:hover {
        border-color: #e74c3c;
        color: #e74c3c;
        text-decoration: none;
    }

    /* ── Pagination ── */
    .pp-pagination-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 28px;
        align-self: center;
    }

    .pp-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .pp-page-btn {
        min-width: 38px;
        height: 38px;
        padding: 0 10px;
        border-radius: 10px;
        border: 1.5px solid #d9e1f0;
        background: #fff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .pp-page-btn:hover {
        border-color: #0040a1;
        color: #0040a1;
        background: #eaf1ff;
    }

    .pp-page-btn.active {
        background: #0040a1;
        border-color: #0040a1;
        color: #fff;
        pointer-events: none;
    }

    .pp-page-btn.disabled {
        opacity: 0.35;
        pointer-events: none;
    }

    .pp-page-ellipsis {
        color: #94a3b8;
        font-size: 14px;
        line-height: 38px;
        padding: 0 2px;
    }

    .pp-page-info {
        font-size: 12px;
        color: #64748b;
        margin-top: 10px;
        text-align: center;
    }

    .pp-empty {
        background: #fff;
        border: 1px dashed #c6d0e1;
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        color: #475569;
    }

    .pp-sidebar-card {
        background: #fff;
        border: 1px solid #e2e7f2;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 12px 28px rgba(16, 28, 61, 0.06);
    }

    .pp-sidebar-card h4 {
        color: #001847;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .pp-sidebar-card .form-control,
    .pp-sidebar-card .input-group-text {
        border-radius: 10px;
    }

    .pp-sidebar-card .btn {
        border-radius: 10px;
        font-weight: 700;
    }

    .pp-recent-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .pp-recent-item {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #eef1f6;
    }

    .pp-recent-item:last-child {
        border-bottom: 0;
    }

    .pp-recent-item img {
        width: 82px;
        height: 62px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .pp-recent-title {
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .pp-recent-title:hover {
        color: #0040a1;
    }

    .pp-recent-location {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .pp-grid.pp-grid-list {
        grid-template-columns: 1fr;
    }

    .pp-grid.pp-grid-list .pp-card {
        flex-direction: row;
        min-height: 330px;
        height: 330px;
    }

    .pp-grid.pp-grid-list .pp-media {
        width: 320px;
        min-width: 320px;
        height: 100%;
        overflow: hidden;
        flex: 0 0 320px;
    }

    .pp-grid.pp-grid-list .pp-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pp-grid.pp-grid-list .pp-body {
        padding: 18px;
    }

    @media (max-width: 1199.98px) {
        .pp-layout {
            grid-template-columns: 1fr;
        }

        .pp-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .pp-hero {
            padding: 48px 0 36px;
        }

        .pp-search-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .pp-grid.pp-grid-list .pp-card {
            flex-direction: column;
            min-height: 0;
        }

        .pp-grid.pp-grid-list .pp-media {
            width: 100%;
            min-width: 0;
            height: 220px;
        }
    }

    @media (max-width: 767.98px) {
        .pp-wrap {
            padding: 0 16px;
        }

        .pp-search-grid,
        .pp-grid {
            grid-template-columns: 1fr;
        }

        .pp-media {
            height: 210px;
        }
    }

    /* ── lx-select Styles ── */
    .lx-select {
        position: relative;
        width: 100%;
    }

    .lx-select-toggle {
        width: 100%;
        border: 0;
        border-radius: 12px;
        padding: 0;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        transition: color 0.25s ease;
        text-align: left;
    }

    .lx-select.has-value .lx-select-toggle {
        color: #0b1f52;
    }

    .lx-select-chevron {
        width: 12px;
        height: 7px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='8' viewBox='0 0 14 8'%3E%3Cpath d='M1.41 0.59 7 6.17l5.59-5.58L13 1l-6 6-6-6z' fill='%23233f93'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 12px 7px;
        transition: transform 0.25s ease;
        flex-shrink: 0;
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
        .lx-select-menu {
            width: 100%;
            max-height: 220px;
        }
    }
</style>

<div class="pp-page">
    <section class="pp-hero">
        <div class="pp-wrap">
            <span class="pp-kicker">Danh mục LuxEstate</span>
            <h1 class="pp-title">Danh sách bất động sản theo nhu cầu của bạn</h1>
            <div class="pp-search-shell">
                <form method="get" action="<?= BASEURL ?>/property/index" class="pp-search-grid">
                    <div class="pp-field">
                        <label>Vị trí</label>
                        <div class="pp-field-row">
                            <i class="fas fa-map-marker-alt pp-field-icon"></i>
                            <input type="text" name="q" value="<?= htmlspecialchars($filterKeyword) ?>"
                                placeholder="Tìm thành phố, quận huyện...">
                        </div>
                    </div>
                    <div class="pp-field">
                        <label>Khoảng giá</label>
                        <div class="pp-field-row">
                            <i class="fas fa-wallet pp-field-icon"></i>
                            <div class="lx-select <?= $filterPriceRange !== '' ? 'has-value' : '' ?>"
                                data-target="#filter-price-range">
                                <button type="button" class="lx-select-toggle" aria-haspopup="listbox"
                                    aria-expanded="false" aria-controls="price-range-menu">
                                    <span class="lx-select-text"><?= htmlspecialchars($currentPriceLabel) ?></span>
                                    <span class="lx-select-chevron" aria-hidden="true"></span>
                                </button>
                                <div class="lx-select-menu" id="price-range-menu" aria-hidden="true" role="dialog"
                                    aria-label="Chọn khoảng giá">
                                    <div class="lx-select-menu-header">
                                        <h3 class="lx-select-menu-title">Khoảng giá</h3>
                                        <button type="button" class="lx-select-menu-close"
                                            aria-label="Đóng">&times;</button>
                                    </div>
                                    <div class="lx-select-menu-body" role="listbox">
                                        <?php foreach ($priceRangeOptions as $value => $label): ?>
                                            <div class="lx-select-option <?= $value === $filterPriceRange ? 'is-active' : '' ?>"
                                                data-value="<?= htmlspecialchars($value) ?>" role="option"
                                                aria-selected="<?= $value === $filterPriceRange ? 'true' : 'false' ?>">
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
                            <input type="hidden" name="price_range" id="filter-price-range"
                                value="<?= htmlspecialchars($filterPriceRange) ?>">
                        </div>
                    </div>
                    <div class="pp-field">
                        <label>Diện tích</label>
                        <div class="pp-field-row">
                            <i class="fas fa-vector-square pp-field-icon"></i>
                            <div class="lx-select <?= $filterAreaRange !== '' ? 'has-value' : '' ?>"
                                data-target="#filter-area-range">
                                <button type="button" class="lx-select-toggle" aria-haspopup="listbox"
                                    aria-expanded="false" aria-controls="area-range-menu">
                                    <span class="lx-select-text"><?= htmlspecialchars($currentAreaLabel) ?></span>
                                    <span class="lx-select-chevron" aria-hidden="true"></span>
                                </button>
                                <div class="lx-select-menu" id="area-range-menu" aria-hidden="true" role="dialog"
                                    aria-label="Chọn diện tích">
                                    <div class="lx-select-menu-header">
                                        <h3 class="lx-select-menu-title">Diện tích</h3>
                                        <button type="button" class="lx-select-menu-close"
                                            aria-label="Đóng">&times;</button>
                                    </div>
                                    <div class="lx-select-menu-body" role="listbox">
                                        <?php foreach ($areaRangeOptions as $value => $label): ?>
                                            <div class="lx-select-option <?= $value === $filterAreaRange ? 'is-active' : '' ?>"
                                                data-value="<?= htmlspecialchars($value) ?>" role="option"
                                                aria-selected="<?= $value === $filterAreaRange ? 'true' : 'false' ?>">
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
                            <input type="hidden" name="area_range" id="filter-area-range"
                                value="<?= htmlspecialchars($filterAreaRange) ?>">
                        </div>
                    </div>
                    <div class="pp-field">
                        <label>Số phòng</label>
                        <div class="pp-field-row">
                            <i class="fas fa-bed pp-field-icon"></i>
                            <div class="lx-select <?= $filterRooms !== '' ? 'has-value' : '' ?>"
                                data-target="#filter-rooms">
                                <button type="button" class="lx-select-toggle" aria-haspopup="listbox"
                                    aria-expanded="false" aria-controls="rooms-menu">
                                    <span class="lx-select-text"><?= htmlspecialchars($currentRoomsLabel) ?></span>
                                    <span class="lx-select-chevron" aria-hidden="true"></span>
                                </button>
                                <div class="lx-select-menu" id="rooms-menu" aria-hidden="true" role="dialog"
                                    aria-label="Chọn số phòng">
                                    <div class="lx-select-menu-header">
                                        <h3 class="lx-select-menu-title">Số phòng</h3>
                                        <button type="button" class="lx-select-menu-close"
                                            aria-label="Đóng">&times;</button>
                                    </div>
                                    <div class="lx-select-menu-body" role="listbox">
                                        <?php foreach ($roomsOptions as $value => $label): ?>
                                            <div class="lx-select-option <?= $value === $filterRooms ? 'is-active' : '' ?>"
                                                data-value="<?= htmlspecialchars($value) ?>" role="option"
                                                aria-selected="<?= $value === $filterRooms ? 'true' : 'false' ?>">
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
                            <input type="hidden" name="rooms" id="filter-rooms"
                                value="<?= htmlspecialchars($filterRooms) ?>">
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="city_id" value="<?= (int) ($data['filter_city_id'] ?? 0) ?>">
                        <input type="hidden" name="city" value="<?= htmlspecialchars($data['filter_city'] ?? '') ?>">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($data['filter_type'] ?? '') ?>">
                        <input type="hidden" name="stype" value="<?= htmlspecialchars($data['filter_stype'] ?? '') ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($data['sort'] ?? '') ?>">
                        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                        <?php foreach ($selectedTypes as $selectedType): ?>
                            <input type="hidden" name="types[]" value="<?= htmlspecialchars($selectedType) ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="pp-search-btn"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="pp-main">
        <div class="pp-wrap">
            <div class="pp-topbar">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-2">
                            <li class="breadcrumb-item"><a href="<?= BASEURL ?>/home/index">Trang chủ</a></li>
                            <li class="breadcrumb-item active">Bất động sản</li>
                        </ol>
                    </nav>
                    <h2>Cho Thuê Nhà Đất Toàn Việt Nam - Giá Rẻ, Chính Chủ <?= $headlinePeriod ?></h2>
                    <p>Hiện có <strong><?= (int) $totalProperties ?></strong> bất động sản</p>
                </div>
                <div class="pp-view-switch">
                    <a class="pp-view-btn <?= $viewMode === 'grid' ? 'active' : '' ?>"
                        href="<?= htmlspecialchars($gridViewUrl) ?>" aria-label="Chế độ lưới">
                        <i class="fas fa-th"></i>
                    </a>
                    <a class="pp-view-btn <?= $viewMode === 'list' ? 'active' : '' ?>"
                        href="<?= htmlspecialchars($listViewUrl) ?>" aria-label="Chế độ danh sách ngang">
                        <i class="fas fa-list"></i>
                    </a>
                </div>
            </div>

            <div class="pp-layout">
                <aside>
                    <div class="pp-sidebar-card mb-4">
                        <h4>Bộ lọc tìm kiếm</h4>
                        <form method="get" action="<?= BASEURL ?>/property/index" class="pp-side-filter">

                            <div>
                                <label class="d-block mb-2" style="">Loại hình nhà đất</label>
                                <div class="pp-check-grid">
                                    <?php foreach ($typeRows as $typeRow): ?>
                                        <?php $typeName = trim((string) ($typeRow['name'] ?? ''));
                                        if ($typeName === '')
                                            continue; ?>
                                        <label class="pp-check-item">
                                            <input type="checkbox" name="types[]" value="<?= htmlspecialchars($typeName) ?>"
                                                <?= in_array($typeName, $selectedTypes, true) ? 'checked' : '' ?>>
                                            <span><?= htmlspecialchars($typeName) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div>
                                <label class="d-block mb-2" style="">Hướng nhà</label>
                                <div class="pp-check-grid pp-direction-grid">
                                    <?php
                                    $directionOptions = ['Đông', 'Tây', 'Nam', 'Bắc'];
                                    $selectedDirections = isset($data['selected_directions']) && is_array($data['selected_directions']) ? $data['selected_directions'] : [];
                                    ?>
                                    <?php foreach ($directionOptions as $direction): ?>
                                        <label class="pp-check-item">
                                            <input type="checkbox" name="directions[]"
                                                value="<?= htmlspecialchars($direction) ?>" <?= in_array($direction, $selectedDirections, true) ? 'checked' : '' ?>>
                                            <span class="pp-direction-label"><?= htmlspecialchars($direction) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <label>Sắp xếp</label>
                            <div class="pp-field">
                                <select name="sort">
                                    <option value="" <?= empty($data['sort']) ? 'selected' : '' ?>>Sắp xếp mặc định
                                    </option>
                                    <option value="verified_first" <?= ($data['sort'] ?? '') === 'verified_first' ? 'selected' : '' ?>>Tin xác thực xếp trước</option>
                                    <option value="price_asc" <?= ($data['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                                    <option value="price_desc" <?= ($data['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                                    <option value="area_asc" <?= ($data['sort'] ?? '') === 'area_asc' ? 'selected' : '' ?>>
                                        Diện tích nhỏ đến lớn</option>
                                    <option value="area_desc" <?= ($data['sort'] ?? '') === 'area_desc' ? 'selected' : '' ?>>Diện tích lớn đến nhỏ</option>
                                </select>
                            </div>
                            <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                            <button type="submit" name="filter" class="pp-search-btn">Áp dụng bộ lọc</button>
                        </form>
                    </div>

                    <div class="pp-sidebar-card mb-4">
                        <h4>Công cụ tính trả góp</h4>
                        <form class="d-inline-block w-100" action="<?= BASEURL ?>/page/calc" method="post">
                            <label class="sr-only">Giá trị bất động sản</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">vnd</div>
                                </div>
                                <input type="number" min="0" step="any" class="form-control" name="amount"
                                    placeholder="Giá bất động sản" required>
                            </div>
                            <label class="sr-only">Thời hạn (tháng)</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
                                </div>
                                <input type="number" min="1" class="form-control" name="month"
                                    placeholder="Số tháng vay" required>
                            </div>
                            <label class="sr-only">Lãi suất</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">%</div>
                                </div>
                                <input type="number" min="0" step="any" class="form-control" name="interest"
                                    placeholder="Lãi suất" required>
                            </div>
                            <button type="submit" value="submit" name="calc" class="btn btn-danger mt-3 w-100">Tính trả
                                góp</button>
                        </form>
                    </div>

                    <div class="pp-sidebar-card">
                        <h4>Bất động sản mới đăng</h4>
                        <ul class="pp-recent-list">
                            <?php if (isset($data['recentProperties'])):
                                foreach ($data['recentProperties'] as $row): ?>
                                    <li class="pp-recent-item">
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>"
                                            alt="pimage">
                                        <div>
                                            <a class="pp-recent-title"
                                                href="<?= BASEURL ?>/property/detail/<?= (int) ($row['pid'] ?? 0) ?>"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                            <div class="pp-recent-location"><i class="fas fa-map-marker-alt text-primary"></i>
                                                <?= htmlspecialchars($row['location'] ?? '') ?></div>
                                        </div>
                                    </li>
                                <?php endforeach; endif; ?>
                        </ul>
                    </div>
                </aside>

                <div class="pp-main">
                    <div class="pp-grid <?= $viewMode === 'list' ? 'pp-grid-list' : '' ?>">
                        <?php if (!empty($pagedProperties)):
                            foreach ($pagedProperties as $row): ?>
                                <?php $isFavorited = in_array((int) ($row['pid'] ?? 0), $favoritePropertyIds, true); ?>
                                <article class="pp-card">
                                    <div class="pp-media">
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>"
                                            alt="pimage">
                                        <span
                                            class="pp-chip"><?= ($row['stype'] ?? '') === 'sale' ? 'Mua bán' : 'Cho thuê' ?></span>
                                    </div>
                                    <div class="pp-body">
                                        <a class="pp-title-link"
                                            href="<?= BASEURL ?>/property/detail/<?= (int) ($row['pid'] ?? 0) ?>"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                        <?php if ($viewMode === 'list'): ?>
                                            <p class="pp-excerpt"><?= htmlspecialchars(mb_strimwidth(trim(strip_tags(html_entity_decode($row['pcontent'] ?? '', ENT_QUOTES, 'UTF-8'))), 0, 160, '...', 'UTF-8')) ?></p>
                                        <?php endif; ?>
                                        <div class="pp-price pp-price-1">
                                            <span><?= formatPropertyPrice($row['price'] ?? 0, $row['stype'] ?? '') ?></span>
                                            <span><?= htmlspecialchars($row['size'] ?? 0) ?> m2</span>
                                        </div>
                                        <span class="pp-location"><i class="fas fa-map-marker-alt text-primary"></i>
                                            <?= htmlspecialchars($row['location'] ?? '') ?></span>
                                        <div class="pp-meta">
                                            <span><i
                                                    class="fas fa-user text-primary mr-1"></i><?= htmlspecialchars($row['uname'] ?? 'Khách') ?></span>
                                            <div class="pp-actions">
                                                <a class="pp-phone" href="tel:<?= htmlspecialchars($row['uphone'] ?? '') ?>">
                                                    <i class="fas fa-phone-alt"></i>
                                                </a>
                                                <?php if (isset($_SESSION['uid'])): ?>
                                                    <form method="post"
                                                        action="<?= BASEURL ?>/property/toggleFavorite/<?= (int) ($row['pid'] ?? 0) ?>"
                                                        class="pp-favorite-form">
                                                        <input type="hidden" name="redirect" value="<?= BASEURL ?>/property/index">
                                                        <button type="submit"
                                                            class="pp-favorite <?= $isFavorited ? 'active' : '' ?>"
                                                            aria-label="Yêu thích bài đăng">
                                                            <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" class="pp-favorite"
                                                        aria-label="Đăng nhập để lưu tin"
                                                        onclick="AppPopup.warning('Vui lòng đăng nhập để lưu tin yêu thích.');">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; else: ?>
                            <div class="pp-empty">
                                Không có bất động sản phù hợp với bộ lọc hiện tại.
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pp-pagination-wrap">
                            <div class="pp-pagination">
                                <a href="<?= ppPageUrl($currentPage - 1, $paginationParams) ?>"
                                    class="pp-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">&#8249;</a>
                                <?php
                                $window = 2;
                                $start = max(1, $currentPage - $window);
                                $end = min($totalPages, $currentPage + $window);
                                if ($start > 1): ?>
                                    <a href="<?= ppPageUrl(1, $paginationParams) ?>" class="pp-page-btn">1</a>
                                    <?php if ($start > 2): ?><span class="pp-page-ellipsis">&hellip;</span><?php endif; ?>
                                <?php endif; ?>
                                <?php for ($page = $start; $page <= $end; $page++): ?>
                                    <a href="<?= ppPageUrl($page, $paginationParams) ?>"
                                        class="pp-page-btn <?= $page === $currentPage ? 'active' : '' ?>"><?= $page ?></a>
                                <?php endfor; ?>
                                <?php if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?><span
                                            class="pp-page-ellipsis">&hellip;</span><?php endif; ?>
                                    <a href="<?= ppPageUrl($totalPages, $paginationParams) ?>"
                                        class="pp-page-btn"><?= $totalPages ?></a>
                                <?php endif; ?>
                                <a href="<?= ppPageUrl($currentPage + 1, $paginationParams) ?>"
                                    class="pp-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">&#8250;</a>
                            </div>
                            <p class="pp-page-info">Trang <?= $currentPage ?> / <?= $totalPages ?> &nbsp;&bull;&nbsp; Hiển
                                thị <?= count($pagedProperties) ?> / <?= $totalItems ?> bất động sản</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </section>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>

<script src="<?= BASEURL ?>/js/lx-select.js"></script>
<script>
    (function () {
        function bindCitySync(selectId, inputId) {
            var citySelect = document.getElementById(selectId);
            var cityNameInput = document.getElementById(inputId);
            if (!citySelect || !cityNameInput) {
                return;
            }

            function syncCityName() {
                var selected = citySelect.options[citySelect.selectedIndex];
                cityNameInput.value = citySelect.value ? (selected ? selected.text : '') : '';
            }

            citySelect.addEventListener('change', syncCityName);
            syncCityName();
        }

        bindCitySync('filter-city-id', 'filter-city-name');
        bindCitySync('filter-city-id-sidebar', 'filter-city-name-sidebar');
    })();
</script>