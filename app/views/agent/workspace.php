<?php require_once '../app/views/layouts/header.php'; ?>
<?php
if (!function_exists('formatWorkspacePrice')) {
    function formatWorkspacePrice($price, $stype)
    {
        if ((float) $price <= 0) {
            return 'Thỏa thuận';
        }
        $formatted = number_format((float) $price, 0, ',', '.') . ' triệu';
        return $stype === 'rent' ? $formatted . '/tháng' : $formatted;
    }
}

if (!function_exists('agentWorkspaceGetRowDate')) {
    function agentWorkspaceGetRowDate($row, $keys)
    {
        foreach ($keys as $key) {
            if (!empty($row[$key])) {
                $ts = strtotime((string) $row[$key]);
                if ($ts !== false) {
                    return $ts;
                }
            }
        }
        return null;
    }
}

if (!function_exists('agentWorkspaceDetectPostTab')) {
    function agentWorkspaceDetectPostTab($row)
    {
        $approval = strtolower((string) ($row['approval_status'] ?? 'approved'));
        $status = strtolower((string) ($row['status'] ?? ''));

        if ($approval === 'rejected') {
            return 'not_approved';
        }

        if ($approval === 'pending') {
            return 'pending_approval';
        }

        if (in_array($status, ['inactive', 'hidden', 'taken_down', 'down'], true)) {
            return 'taken_down';
        }

        if (in_array($status, ['pending_display', 'pending_show', 'waiting_display'], true)) {
            return 'pending_display';
        }

        return 'displaying';
    }
}

if (!function_exists('agentWorkspacePostBadge')) {
    function agentWorkspacePostBadge($tabKey)
    {
        $map = [
            'displaying' => ['Đang hiển thị', 'show'],
            'pending_display' => ['Chờ hiển thị', 'wait'],
            'pending_approval' => ['Chờ duyệt', 'wait'],
            'not_approved' => ['Không duyệt', 'danger'],
            'taken_down' => ['Đã hoàn thành', 'muted'],
        ];
        return $map[$tabKey] ?? ['Tin đăng', 'muted'];
    }
}

if (!function_exists('agentWorkspaceLeadBadge')) {
    function agentWorkspaceLeadBadge($value, $type = 'lead')
    {
        $value = strtolower(trim((string) $value));
        $map = [
            'lead' => [
                'new' => ['Mới', 'wait'],
                'contacted' => ['Đã tiếp nhận', 'show'],
                'scheduled' => ['Đã hẹn lịch', 'warn'],
                'viewed' => ['Đã xem nhà', 'show'],
                'completed' => ['Hoàn tất', 'show'],
                'cancelled' => ['Không thành công', 'danger'],
            ],
            'appointment' => [
                'pending' => ['Chờ xác nhận', 'warn'],
                'confirmed' => ['Đã xác nhận', 'show'],
                'completed' => ['Đã xem nhà', 'show'],
                'cancelled' => ['Đã hủy lịch', 'danger'],
                'none' => ['Chưa đặt lịch', 'muted'],
            ],
        ];

        return $map[$type][$value] ?? [($value !== '' ? ucfirst($value) : '--'), 'muted'];
    }
}

if (!function_exists('agentWorkspaceListingSummary')) {
    function agentWorkspaceListingSummary($row, $fallback = '')
    {
        $fields = ['pcontent', 'feature', 'description'];
        foreach ($fields as $field) {
            if (!isset($row[$field])) {
                continue;
            }
            $raw = html_entity_decode((string) $row[$field], ENT_QUOTES, 'UTF-8');
            $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
            if ($clean !== '') {
                return mb_strimwidth($clean, 0, 220, '...');
            }
        }

        $cleanFallback = trim(preg_replace('/\s+/u', ' ', (string) $fallback));
        return $cleanFallback !== '' ? $cleanFallback : 'Chưa có mô tả.';
    }
}

$section = isset($data['section']) ? (string) $data['section'] : 'overview';
$user = isset($data['user']) ? $data['user'] : [];
$stats = isset($data['stats']) ? $data['stats'] : [];
$properties = isset($data['properties']) && is_array($data['properties']) ? $data['properties'] : [];
$inquiries = isset($data['inquiries']) && is_array($data['inquiries']) ? $data['inquiries'] : [];
$canManageUsers = !empty($data['canManageUsers']);
$canManageLeads = !empty($data['canManageLeads']);
$msg = isset($data['msg']) ? (string) $data['msg'] : '';

$searchKeyword = trim((string) ($_GET['q'] ?? ''));
$activePostTab = strtolower(trim((string) ($_GET['tab'] ?? 'all')));
$allowedTabs = ['all', 'displaying', 'pending_display', 'pending_approval', 'not_approved', 'taken_down'];
if (!in_array($activePostTab, $allowedTabs, true)) {
    $activePostTab = 'all';
}

$customerCountMap = [];
foreach ($inquiries as $inquiry) {
    $pid = (int) ($inquiry['property_id'] ?? 0);
    if ($pid <= 0) {
        continue;
    }
    if (!isset($customerCountMap[$pid])) {
        $customerCountMap[$pid] = 0;
    }
    $customerCountMap[$pid]++;
}

$tabCounts = [
    'all' => 0,
    'displaying' => 0,
    'pending_display' => 0,
    'pending_approval' => 0,
    'not_approved' => 0,
    'taken_down' => 0,
];

$filteredPosts = [];
foreach ($properties as $row) {
    $tabKey = agentWorkspaceDetectPostTab($row);
    if (isset($tabCounts[$tabKey])) {
        $tabCounts[$tabKey]++;
    }
    $tabCounts['all']++;

    $title = trim((string) ($row['title'] ?? ''));
    $pid = (int) ($row['pid'] ?? 0);
    $keywordMatched = $searchKeyword === ''
        || stripos($title, $searchKeyword) !== false
        || ($pid > 0 && stripos((string) $pid, $searchKeyword) !== false);

    if (!$keywordMatched) {
        continue;
    }

    if ($activePostTab !== 'all' && $tabKey !== $activePostTab) {
        continue;
    }

    $row['_tab_key'] = $tabKey;
    $filteredPosts[] = $row;
}

$postsPerPage = 5;
$currentPostPage = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$totalFilteredPosts = count($filteredPosts);
$totalPostPages = max(1, (int) ceil($totalFilteredPosts / $postsPerPage));
if ($currentPostPage > $totalPostPages) {
    $currentPostPage = $totalPostPages;
}
$postOffset = ($currentPostPage - 1) * $postsPerPage;
$pagedPosts = array_slice($filteredPosts, $postOffset, $postsPerPage);

$postQueryBase = [
    'section' => 'posts',
    'tab' => $activePostTab,
];
if ($searchKeyword !== '') {
    $postQueryBase['q'] = $searchKeyword;
}

$topAvatar = trim((string) ($user['uimage'] ?? ''));
$avatarUrl = $topAvatar !== '' ? (BASEURL . '/admin/user/' . rawurlencode($topAvatar)) : '';
$userName = trim((string) ($user['uname'] ?? 'Người dùng'));
$userInitial = strtoupper(substr($userName, 0, 1));
$userType = ucfirst((string) ($user['utype'] ?? 'user'));
$memberSince = !empty($user['utime']) ? date('m/Y', strtotime((string) $user['utime'])) : '';

$menuItems = [
    ['key' => 'create', 'label' => 'Đăng tin', 'icon' => 'fa-plus-circle', 'url' => BASEURL . '/agentWorkspace/index?section=create', 'primary' => true],
    ['key' => 'overview', 'label' => 'Tổng quan', 'icon' => 'fa-th-large', 'url' => BASEURL . '/agentWorkspace/index?section=overview'],
    ['key' => 'posts', 'label' => 'Tin đăng', 'icon' => 'fa-list-alt', 'url' => BASEURL . '/agentWorkspace/index?section=posts', 'count' => (int) ($stats['total_posts'] ?? count($properties))],
    ['key' => 'customers', 'label' => 'Leads', 'icon' => 'fa-users', 'url' => $canManageLeads ? (BASEURL . '/agentWorkspace/index?section=leads') : '#', 'disabled' => !$canManageLeads, 'count' => $canManageLeads ? count($inquiries) : null],
    ['key' => 'appointments', 'label' => 'Lịch hẹn', 'icon' => 'fa-calendar', 'url' => $canManageLeads ? (BASEURL . '/agentWorkspace/index?section=appointments') : '#', 'disabled' => !$canManageLeads, 'count' => $canManageLeads ? (int) ($stats['scheduled_appointments'] ?? 0) : null],
];

$postTabs = [
    'all' => 'Tất cả',
    'displaying' => 'Đang hiển thị',
    'pending_approval' => 'Chờ duyệt',
    'pending_display' => 'Chờ hiển thị',
    'not_approved' => 'Không duyệt',
    'taken_down' => 'Đã hoàn thành',
];

$sectionMeta = [
    'appointments' => [
        'title' => 'Lịch hẹn xử lý',
    ],
    'users' => [
        'title' => 'Danh sách người dùng',
    ],
];

// $currentSectionMeta = $sectionMeta[$section] ?? $sectionMeta['overview'];
$heroStats = [
    ['label' => 'Tổng tin', 'value' => (int) ($stats['total_posts'] ?? count($properties))],
    ['label' => 'Đang hiển thị', 'value' => (int) ($tabCounts['displaying'] ?? 0)],
    ['label' => 'Sắp cần xử lý', 'value' => (int) ($tabCounts['pending_approval'] ?? 0)],
];

if ($canManageUsers) {
    $heroStats[] = ['label' => 'Tổng lead', 'value' => (int) ($stats['total_contacts'] ?? count($inquiries))];
}

$overviewCards = [
    ['label' => 'Tổng bài đăng', 'value' => (int) ($stats['total_posts'] ?? 0), 'hint' => 'Toàn bộ tin đã tạo'],
    ['label' => 'Đang hiển thị', 'value' => (int) ($tabCounts['displaying'] ?? 0), 'hint' => 'Tin đăng hoạt động trên hệ thống'],
    ['label' => 'Chờ duyệt', 'value' => (int) ($stats['pending_posts'] ?? 0), 'hint' => 'Cần theo dõi kết quả phê duyệt'],
];

if ($canManageUsers) {
    $overviewCards[] = ['label' => 'Lead mới', 'value' => (int) ($stats['new_leads'] ?? 0), 'hint' => 'Khách chưa được tiếp nhận'];
    $overviewCards[] = ['label' => 'Đã tiếp nhận', 'value' => (int) ($stats['contacted_leads'] ?? 0), 'hint' => 'Đã phản hồi khách'];
    $overviewCards[] = ['label' => 'Đã hẹn lịch', 'value' => (int) ($stats['scheduled_leads'] ?? 0), 'hint' => 'Đang chuẩn bị đi xem'];
    $overviewCards[] = ['label' => 'Đã xem nhà', 'value' => (int) ($stats['viewed_leads'] ?? 0), 'hint' => 'Chờ chốt kết quả'];
}

$postDiagramRows = [
    ['label' => 'Đang hiển thị', 'value' => (int) ($tabCounts['displaying'] ?? 0), 'tone' => 'show'],
    ['label' => 'Chờ duyệt', 'value' => (int) ($tabCounts['pending_approval'] ?? 0), 'tone' => 'wait'],
];

$postDiagramTotal = max(1, (int) ($tabCounts['all'] ?? 0));
foreach ($postDiagramRows as &$diagramRow) {
    $diagramRow['percent'] = (int) round(($diagramRow['value'] / $postDiagramTotal) * 100);
}
unset($diagramRow);
?>

<style>
    .agent-dashboard {
        --dash-bg: #f8f9fa;
        --dash-surface: #ffffff;
        --dash-card: #ffffff;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-line: #e2e8f0;
        --dash-accent: #2563eb;
        --dash-accent-strong: #1d4ed8;
        --dash-accent-soft: #dbeafe;
        --dash-warn: #b45309;
        --dash-warn-soft: #fef3c7;
        --dash-danger: #b42318;
        --dash-danger-soft: #fee2e2;
        --dash-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        background: var(--dash-bg);
        height: 100vh;
        min-height: 100vh;
        padding: 0 !important;
        overflow: hidden;
    }

    .agent-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        gap: 0;
        align-items: stretch;
        height: 100%;
    }

    .agent-sidebar {
        background: #fff;
        border-right: 1px solid #e5e7eb;
        border-radius: 0;
        box-shadow: 8px 0 18px rgba(15, 23, 42, 0.08);
        padding: 16px;
        position: relative;
        height: 100%;
        min-height: 100%;
        display: flex;
        flex-direction: column;
        backdrop-filter: none;
        z-index: 2;
    }

    .agent-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 6px 12px;
        border-bottom: 1px solid #f0f2f5;
    }

    .agent-brand-mark {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: inherit;
        box-shadow: none;
    }

    .agent-brand-mark img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .agent-brand-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }

    .agent-user-meta small {
        display: block;
        font-size: 11px;
        line-height: 1.2;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--dash-muted);
        margin-bottom: 6px;
    }

    .agent-menu {
        margin-top: 0;
        padding: 12px 0 0;
        display: grid;
        gap: 8px;
        align-content: start;
        flex: 1;
    }

    .agent-menu-link {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid transparent;
        background: transparent;
        color: #111827;
        text-decoration: none;
        transition: all .2s ease;
        min-height: 44px;
        position: relative;
    }

    .agent-menu-link:hover {
        text-decoration: none;
        color: var(--dash-text);
        background: #f3f4f6;
    }

    .agent-menu-link.primary {
        background: var(--dash-accent);
        color: #fff;
        border-color: var(--dash-accent);
        justify-content: center;
    }

    .agent-menu-link.primary:hover {
        color: #fff;
        background: var(--dash-accent-strong);
        border-color: var(--dash-accent-strong);
    }

    .agent-menu-link.active {
        background: #f3f4f6;
        color: #111827;
        border-color: #e5e7eb;
        z-index: 1;
    }

    .agent-menu-link.disabled {
        opacity: 0.45;
        pointer-events: none;
    }

    .agent-menu-copy {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        min-width: 0;
        width: 100%;
    }

    .agent-menu-copy i {
        width: 18px;
        text-align: center;
        font-size: 14px;
    }

    .agent-menu-copy span {
        font-size: 14px;
        font-weight: 700;
        text-align: left;
        line-height: 1.3;
    }

    .agent-menu-count {
        min-width: 24px;
        padding: 3px 7px;
        border-radius: 999px;
        background: #f3f4f6;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        position: absolute;
        top: 2px;
        right: 10px;
    }

    .agent-menu-link:not(.primary) .agent-menu-count,
    .agent-menu-link.primary .agent-menu-count {
        background: #f3f4f6;
        color: var(--dash-muted);
    }

    .agent-menu-link.primary .agent-menu-count {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .agent-sidebar-bottom {
        margin-top: auto;
        border-top: 1px solid #f0f2f5;
        padding: 14px 0 0;
    }

    .agent-sidebar-notice {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 56px;
        border-radius: 12px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        line-height: 1.4;
        padding: 10px 8px;
    }

    .agent-sidebar-notice i {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #fff;
        color: #6b7280;
        font-size: 13px;
    }

    .agent-main {
        min-width: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
    }

    .agent-main-scroll {
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        /* padding: 24px 24px 24px 12px; */
        width: 100%;
    }

    .agent-hero {
        padding: 20px 22px;
        /* border-radius: 14px; */
        /* background: #fff; */
        /* border: 1px solid var(--dash-line); */
        /* box-shadow: var(--dash-shadow); */
        display: block;
    }

    .agent-hero-title {
        margin: 0;
        color: var(--dash-text);
        font-size: clamp(22px, 2.6vw, 30px);
        line-height: 1.18;
        letter-spacing: -0.02em;
        font-weight: 800;
    }

    .agent-hero-copy p {
        margin: 12px 0 0;
        max-width: 720px;
        color: #526071;
        font-size: 16px;
        line-height: 1.7;
    }

    .agent-hero-actions {
        margin-top: 22px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .agent-btn,
    .agent-btn-solid {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 11px 18px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .agent-btn {
        border: 1px solid var(--dash-line);
        background: #fff;
        color: var(--dash-text);
    }

    .agent-btn:hover {
        text-decoration: none;
        color: var(--dash-text);
        border-color: #d7cdbf;
        transform: translateY(-1px);
    }

    .agent-btn-solid {
        border: 1px solid var(--dash-accent);
        background: var(--dash-accent);
        color: #fff;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.22);
    }

    .agent-btn-solid:hover {
        text-decoration: none;
        color: #fff;
        background: var(--dash-accent-strong);
        border-color: var(--dash-accent-strong);
    }

    .agent-alert {
        margin-top: 18px;
        padding: 14px 18px;
        border-radius: 18px;
        border: 1px solid #cfe8da;
        background: #f2fbf5;
        color: #185c43;
        line-height: 1.6;
    }

    .agent-section {
        margin-top: 18px;
        /* border-radius: 14px; */
        /* background: #fff; */
        /* border: 1px solid var(--dash-line); */
        /* box-shadow: var(--dash-shadow); */
        padding: 20px 22px 22px;
    }

    .agent-section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .agent-section-title {
        margin: 0;
        color: var(--dash-text);
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .agent-section-copy p {
        margin: 8px 0 0;
        color: var(--dash-muted);
        max-width: 720px;
        line-height: 1.6;
    }

    .agent-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
    }

    .agent-diagram-panel {
        border-radius: 16px;
        border: 1px solid var(--dash-line);
        background: #fff;
        padding: 18px;
    }

    .agent-diagram-total {
        margin: 0;
        color: var(--dash-text);
        font-size: 14px;
        font-weight: 700;
    }

    .agent-diagram-total strong {
        font-size: 22px;
        margin-left: 6px;
    }

    .agent-diagram-list {
        margin: 14px 0 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 12px;
    }

    .agent-diagram-item {
        display: grid;
        gap: 8px;
    }

    .agent-diagram-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: var(--dash-text);
        font-size: 14px;
        font-weight: 700;
    }

    .agent-diagram-meta {
        color: var(--dash-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .agent-diagram-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #eceff3;
        overflow: hidden;
    }

    .agent-diagram-fill {
        display: block;
        height: 100%;
        border-radius: 999px;
    }

    .agent-diagram-fill.show {
        background: #10b981;
    }

    .agent-diagram-fill.wait {
        background: #6366f1;
    }

    .agent-diagram-fill.warn {
        background: #f59e0b;
    }

    .agent-diagram-fill.muted {
        background: #71717a;
    }

    .agent-summary-card {
        border-radius: 22px;
        border: 1px solid var(--dash-line);
        background: linear-gradient(180deg, #ffffff, #fbf9f4);
        padding: 18px;
    }

    .agent-summary-card span {
        display: block;
        color: var(--dash-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-weight: 700;
    }

    .agent-summary-card strong {
        display: block;
        margin-top: 10px;
        color: var(--dash-text);
        font-size: 30px;
        line-height: 1;
        font-weight: 900;
    }

    .agent-summary-card p {
        margin: 12px 0 0;
        color: #596778;
        font-size: 14px;
        line-height: 1.6;
    }

    .agent-highlight-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.9fr);
        gap: 18px;
        margin-top: 18px;
    }

    .agent-stack {
        display: grid;
        gap: 14px;
    }

    .agent-mini-panel {
        border-radius: 16px;
        border: 1px solid var(--dash-line);
        background: #fff;
        padding: 18px;
        box-shadow: var(--dash-shadow);
    }

    .agent-mini-panel ul {
        list-style: none;
        margin: 14px 0 0;
        padding: 0;
        display: grid;
        gap: 12px;
    }

    .agent-inline-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .agent-inline-row strong {
        color: var(--dash-text);
    }

    .agent-inline-row small {
        color: var(--dash-muted);
    }

    .agent-search-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
    }

    .agent-search-box {
        position: relative;
    }

    .agent-search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a94a3;
    }

    .agent-search-box input {
        width: 100%;
        min-height: 50px;
        padding: 12px 16px 12px 46px;
        border-radius: 999px;
        border: 1px solid var(--dash-line);
        background: #fff;
        outline: none;
        transition: all .2s ease;
    }

    .agent-search-box input:focus {
        border-color: rgba(37, 99, 235, 0.45);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .agent-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .agent-tabs {
        margin-top: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .agent-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        border: 1px solid var(--dash-line);
        background: #fff;
        color: var(--dash-text);
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
    }

    .agent-tab:hover {
        text-decoration: none;
        color: var(--dash-text);
    }

    .agent-tab.active {
        background: var(--dash-accent-strong);
        border-color: var(--dash-accent-strong);
        color: #fff;
    }

    .agent-tab-count {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.15);
        font-size: 12px;
    }

    .agent-tab:not(.active) .agent-tab-count {
        background: #f6efe6;
        color: var(--dash-muted);
    }

    .listing-wrap {
        margin-top: 18px;
        display: grid;
        gap: 16px;
    }

    .agent-pagination {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }

    .agent-page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid var(--dash-line);
        background: #fff;
        color: var(--dash-text);
        font-weight: 700;
        text-decoration: none;
    }

    .agent-page-link:hover {
        text-decoration: none;
        color: var(--dash-text);
        background: #f6efe6;
    }

    .agent-page-link.active {
        background: var(--dash-accent-strong);
        border-color: var(--dash-accent-strong);
        color: #fff;
    }

    .listing-card {
        display: grid;
        grid-template-columns: 210px minmax(0, 1fr) 230px;
        gap: 16px;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid var(--dash-line);
        background: #fff;
    }

    .listing-empty {
        display: block;
        text-align: center;
        color: var(--dash-muted);
        padding: 34px 18px;
    }

    .listing-thumb {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 12px;
        background: #e2e8f0;
    }

    .listing-copy {
        min-width: 0;
    }

    .listing-title {
        margin: 8px 0 0;
        color: var(--dash-text);
        font-size: 21px;
        line-height: 1.28;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .listing-sub {
        margin-top: 12px;
        color: #5b6878;
        font-size: 14px;
        line-height: 1.7;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .listing-tags {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .listing-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .listing-actions {
        margin-top: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .listing-meta {
        border-left: 1px solid var(--dash-line);
        padding-left: 14px;
        display: grid;
        gap: 10px;
        align-content: start;
    }

    .listing-meta-card {
        border-radius: 12px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid var(--dash-line);
    }

    .listing-meta-card span {
        display: block;
        color: var(--dash-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-weight: 700;
    }

    .listing-meta-card strong {
        display: block;
        margin-top: 8px;
        color: var(--dash-text);
        font-size: 22px;
        line-height: 1.1;
        font-weight: 900;
    }

    .listing-status,
    .agent-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .listing-status.show,
    .agent-badge.show {
        background: #dcfce7;
        color: #166534;
    }

    .listing-status.wait,
    .agent-badge.wait {
        background: #eef2ff;
        color: #4338ca;
    }

    .listing-status.warn,
    .agent-badge.warn {
        background: var(--dash-warn-soft);
        color: var(--dash-warn);
    }

    .listing-status.danger,
    .agent-badge.danger {
        background: var(--dash-danger-soft);
        color: var(--dash-danger);
    }

    .listing-status.muted,
    .agent-badge.muted {
        background: #f4f4f5;
        color: #52525b;
    }

    .agent-table-wrap {
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid var(--dash-line);
        background: #fff;
    }

    .agent-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .agent-table thead th {
        background: #175577;
        color: #fff;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 800;
        padding: 16px 18px;
        border-bottom: 1px solid var(--dash-line);
    }

    .agent-table tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid #f1eadf;
        color: #334155;
    }

    .agent-table tbody tr:last-child td {
        border-bottom: none;
    }

    .agent-table tbody tr:hover {
        background: #fffcf7;
    }

    .agent-table-title {
        display: block;
        color: var(--dash-text);
        font-weight: 800;
        line-height: 1.4;
    }

    .agent-table-sub {
        display: block;
        margin-top: 4px;
        color: var(--dash-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .agent-table-empty {
        text-align: center;
        color: var(--dash-muted);
        padding: 36px 18px;
    }

    @media (max-width: 1199.98px) {
        .agent-shell {
            grid-template-columns: 1fr;
        }

        .agent-sidebar {
            position: static;
            height: auto;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }

        .agent-hero,
        .agent-highlight-grid,
        .listing-card {
            grid-template-columns: 1fr;
        }

        .listing-meta {
            border-left: none;
            border-top: 1px solid var(--dash-line);
            padding-left: 0;
            padding-top: 18px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .agent-dashboard {
            height: auto;
            min-height: 100vh;
            overflow: visible;
        }

        .agent-shell {
            gap: 14px;
            height: auto;
        }

        .agent-main-scroll {
            padding: 10px;
        }

        .agent-sidebar,
        .agent-hero,
        .agent-section {
            padding: 14px;
            border-radius: 12px;
        }

        .agent-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .agent-tab {
            flex: 0 0 auto;
        }

        .agent-search-row {
            grid-template-columns: 1fr;
        }

        .agent-actions,
        .agent-hero-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .agent-btn,
        .agent-btn-solid {
            width: 100%;
        }

        .listing-meta {
            grid-template-columns: 1fr;
        }

        .listing-card {
            padding: 12px;
        }

        .listing-thumb {
            height: 190px;
        }

        .agent-table-wrap {
            overflow-x: auto;
        }

        .agent-table {
            min-width: 720px;
        }

        .agent-main {
            height: auto;
            overflow: visible;
        }
    }

    .agent-dashboard footer.full-row {
        padding: 0 !important;
        margin: 0;
    }
</style>

<div class="full-row agent-dashboard">
    <div class="agent-shell">
        <aside class="agent-sidebar" aria-label="Dieu huong workspace">
            <a href="<?= BASEURL ?>/home/index">
                <div class="agent-brand">
                    <div class="agent-brand-mark">
                        <img src="<?= BASEURL ?>/admin/assets/img/logo.png" alt="Logo">
                    </div>
                    <h2 class="agent-brand-title">Broker</h2>
                </div>
            </a>
            <nav class="agent-menu" aria-label="Danh mục">
                <?php foreach ($menuItems as $menu): ?>
                    <?php
                    $isActive = false;
                    if ($menu['key'] === 'create' && $section === 'create') {
                        $isActive = true;
                    } elseif ($menu['key'] === 'posts' && $section === 'posts') {
                        $isActive = true;
                    } elseif ($menu['key'] === 'overview' && $section === 'overview') {
                        $isActive = true;
                    } elseif ($menu['key'] === 'customers' && in_array($section, ['users', 'leads'], true)) {
                        $isActive = true;
                    }
                    ?>
                    <a class="agent-menu-link <?= !empty($menu['primary']) ? 'primary' : '' ?> <?= $isActive ? 'active' : '' ?> <?= !empty($menu['disabled']) ? 'disabled' : '' ?>"
                        href="<?= htmlspecialchars((string) $menu['url']) ?>">
                        <span class="agent-menu-copy">
                            <i class="fa <?= htmlspecialchars((string) $menu['icon']) ?>"></i>
                            <span><?= htmlspecialchars((string) $menu['label']) ?></span>
                        </span>
                        <?php if (array_key_exists('count', $menu) && $menu['count'] !== null): ?>
                            <span class="agent-menu-count"><?= (int) $menu['count'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="agent-sidebar-bottom">
                <div class="agent-sidebar-notice">
                    <i class="fa fa-bell-o"></i>
                    <span>Thông báo</span>
                </div>
            </div>
        </aside>

        <main class="agent-main">
            <div class="agent-main-scroll">
                <?php if ($section === 'posts'): ?>
                    <?php require_once __DIR__ . '/sections/posts.php'; ?>
                <?php elseif ($section === 'overview'): ?>
                    <?php require_once __DIR__ . '/sections/overview.php'; ?>
                <?php elseif ($section === 'leads' && $canManageLeads): ?>
                    <?php require_once __DIR__ . '/sections/leads.php'; ?>
                <?php elseif ($section === 'users' && $canManageUsers): ?>
                    <?php require_once __DIR__ . '/sections/customers.php'; ?>
                <?php elseif ($section === 'appointments' && $canManageLeads): ?>
                    <?php require_once __DIR__ . '/sections/appointments.php'; ?>
                <?php endif; ?>

                <?php require '../app/views/layouts/footer_content.php'; ?>
            </div>
        </main>
    </div>
</div>

<?php $skipLayoutFooterContent = true; ?>
<?php require_once '../app/views/layouts/footer.php'; ?>