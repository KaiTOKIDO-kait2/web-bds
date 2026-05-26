<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$section = isset($data['section']) ? (string) $data['section'] : 'overview';
$user = isset($data['user']) ? $data['user'] : [];
$stats = isset($data['stats']) ? $data['stats'] : [];
$requests = isset($data['requests']) && is_array($data['requests']) ? $data['requests'] : [];
$msg = isset($data['msg']) ? (string) $data['msg'] : '';

$menuItems = [
    ['key' => 'overview', 'label' => 'Tổng quan', 'icon' => 'fa-th-large', 'url' => BASEURL . '/userWorkspace/index?section=overview'],
    ['key' => 'requests', 'label' => 'Yêu cầu của tôi', 'icon' => ' fa-paper-plane', 'url' => BASEURL . '/userWorkspace/index?section=requests', 'count' => (int) ($stats['total_requests'] ?? 0)],
    ['key' => 'appointments', 'label' => 'Lịch hẹn', 'icon' => 'fa-calendar', 'url' => BASEURL . '/userWorkspace/index?section=appointments', 'count' => (int) ($stats['scheduled_appointments'] ?? 0)],
];

function userStatusBadge($value)
{
    $value = strtolower((string) $value);
    $map = [
        'accepted' => ['Đã tiếp nhận', 'success'],
        'rejected' => ['Đã từ chối', 'danger'],
        'pending' => ['Đang chờ', 'warning text-dark'],
        'consulting' => ['Đang tư vấn', 'info'],
        'closed' => ['Đã kết thúc', 'secondary'],
        'cancelled' => ['Đã hủy', 'danger'],
    ];

    $item = $map[$value] ?? ['Đang chờ', 'warning text-dark'];

    return '<span class="badge badge-' . $item[1] . ' px-2 py-1">' . htmlspecialchars($item[0]) . '</span>';
}

function userCaseBadge($value)
{
    $value = strtolower((string) $value);
    $map = [
        'new' => ['Mới', 'secondary'],
        'contacted' => ['Đã tiếp nhận', 'info'],
        'scheduled' => ['Đã hẹn lịch', 'primary'],
        'viewed' => ['Đã xem nhà', 'info'],
        'completed' => ['Hoàn tất', 'success'],
        'cancelled' => ['Không thành công', 'danger'],
    ];
    $item = $map[$value] ?? ['Không rõ', 'secondary'];
    return '<span class="badge badge-' . $item[1] . ' px-2 py-1">' . htmlspecialchars($item[0]) . '</span>';
}

function userAppointmentBadge($value)
{
    $value = strtolower((string) $value);
    $map = [
        'none' => ['Chưa đặt', 'secondary'],
        'pending' => ['Chờ xác nhận', 'warning text-dark'],
        'confirmed' => ['Đã xác nhận', 'primary'],
        'completed' => ['Đã xem nhà', 'success'],
        'cancelled' => ['Đã hủy', 'danger'],
    ];
    $item = $map[$value] ?? ['Không rõ', 'secondary'];
    return '<span class="badge badge-' . $item[1] . ' px-2 py-1">' . htmlspecialchars($item[0]) . '</span>';
}

function userResultBadge($value)
{
    $value = strtolower((string) $value);
    if ($value === 'completed')
        return '<span class="badge badge-success px-2 py-1">Thành công</span>';
    if ($value === 'cancelled')
        return '<span class="badge badge-danger px-2 py-1">Thất bại</span>';
    return '<span class="text-muted">--</span>';
}
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
        box-shadow: 8px 0 18px rgba(15, 23, 42, 0.08);
        padding: 16px;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        z-index: 2;
    }

    .agent-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 6px 12px;
        border-bottom: 1px solid #f0f2f5;
    }

    .agent-brand-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
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

    .agent-menu-link.active {
        background: #f3f4f6;
        color: #111827;
        border-color: #e5e7eb;
        z-index: 1;
    }

    .agent-menu-copy {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
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
    }

    .agent-menu-count {
        min-width: 24px;
        padding: 3px 7px;
        border-radius: 999px;
        background: #f3f4f6;
        color: var(--dash-muted);
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        position: absolute;
        top: 2px;
        right: 10px;
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
        width: 100%;
    }

    .agent-section {
        margin-top: 18px;
        padding: 20px 22px 22px;
    }

    .agent-hero-title {
        margin: 0;
        color: var(--dash-text);
        font-size: clamp(22px, 2.6vw, 30px);
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .agent-hero {
        padding: 20px 22px;
        display: block;
    }

    .agent-hero-copy p {
        margin: 12px 0 0;
        color: #526071;
        font-size: 16px;
        line-height: 1.7;
    }

    .agent-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
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
        font-weight: 700;
    }

    .agent-summary-card strong {
        display: block;
        margin-top: 10px;
        color: var(--dash-text);
        font-size: 30px;
        font-weight: 900;
    }

    .agent-table-panel {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--dash-line);
        box-shadow: var(--dash-shadow);
        margin-top: 18px;
    }

    .agent-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .agent-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .agent-table th {
        background: #f8fafc;
        color: var(--dash-muted);
        font-size: 12px;
        text-transform: uppercase;
        padding: 14px 18px;
        border-bottom: 1px solid var(--dash-line);
    }

    .agent-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 14px;
        color: var(--dash-text);
    }

    .agent-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        border: 1px solid var(--dash-line);
        background: #fff;
        color: var(--dash-text);
    }

    .agent-btn:hover {
        background: #f8fafc;
        color: var(--dash-accent);
        text-decoration: none;
    }

    .agent-alert {
        margin: 18px 22px 0;
        padding: 14px 18px;
        border-radius: 12px;
        border: 1px solid #cfe8da;
        background: #f2fbf5;
        color: #185c43;
    }

    @media (max-width: 991.98px) {
        .agent-shell {
            grid-template-columns: 1fr;
        }

        .agent-sidebar {
            height: auto;
            position: static;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .agent-menu {
            display: flex;
            overflow-x: auto;
            flex-direction: row;
        }

        .agent-menu-link {
            flex: 0 0 auto;
            white-space: nowrap;
        }
    }

    @media (max-width: 767.98px) {
        .agent-dashboard {
            height: auto;
        }

        .agent-main-scroll {
            padding-bottom: 24px;
        }
    }

    .agent-dashboard footer.full-row {
        padding: 0 !important;
        margin: 0;
    }
</style>

<div class="full-row agent-dashboard">
    <div class="agent-shell">
        <aside class="agent-sidebar">
            <a href="<?= BASEURL ?>/home/index" class="agent-brand" style="text-decoration: none;">
                <div class="agent-brand-mark"
                    style="width:36px;height:36px;background:transparent;display:flex;align-items:center;justify-content:center;">
                    <img src="<?= BASEURL ?>/admin/assets/img/logo.png" alt="Logo"
                        style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <h2 class="agent-brand-title" style="color: #111827; font-size: 18px;">Khách hàng</h2>
            </a>
            <nav class="agent-menu">
                <?php foreach ($menuItems as $menu): ?>
                    <a class="agent-menu-link <?= $section === $menu['key'] ? 'active' : '' ?>"
                        href="<?= htmlspecialchars((string) $menu['url']) ?>">
                        <span class="agent-menu-copy">
                            <i class="fa <?= htmlspecialchars((string) $menu['icon']) ?>"></i>
                            <span><?= htmlspecialchars((string) $menu['label']) ?></span>
                        </span>
                        <?php if (isset($menu['count']) && $menu['count'] > 0): ?>
                            <span class="agent-menu-count"><?= (int) $menu['count'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="agent-main">
            <div class="agent-main-scroll">
                <?php if ($msg !== ''): ?>
                    <div class="agent-alert"><?= $msg ?></div>
                <?php endif; ?>

                <?php if ($section === 'overview'): ?>
                    <?php require_once __DIR__ . '/sections/overview.php'; ?>
                <?php elseif ($section === 'requests'): ?>
                    <?php require_once __DIR__ . '/sections/requests.php'; ?>
                <?php elseif ($section === 'appointments'): ?>
                    <?php require_once __DIR__ . '/sections/appointments.php'; ?>
                <?php endif; ?>

                <?php require '../app/views/layouts/footer_content.php'; ?>
            </div>
        </main>
    </div>
</div>

<?php $skipLayoutFooterContent = true; ?>
<?php require_once '../app/views/layouts/footer.php'; ?>