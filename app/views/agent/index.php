<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$personalAgents = isset($data['agents']) && is_array($data['agents']) ? $data['agents'] : [];
$companyAgents = isset($data['companies']) && is_array($data['companies']) ? $data['companies'] : [];

$perPageAgents = 5;

$personalPage     = max(1, (int)($_GET['page_personal'] ?? 1));
$companyPage      = max(1, (int)($_GET['page_company'] ?? 1));
$totalPersonal    = count($personalAgents);
$totalCompany     = count($companyAgents);
$totalPersonalPg  = max(1, (int)ceil($totalPersonal / $perPageAgents));
$totalCompanyPg   = max(1, (int)ceil($totalCompany / $perPageAgents));
$personalPage     = min($personalPage, $totalPersonalPg);
$companyPage      = min($companyPage, $totalCompanyPg);

$pagedPersonalAgents = array_slice($personalAgents, ($personalPage - 1) * $perPageAgents, $perPageAgents);
$pagedCompanyAgents  = array_slice($companyAgents, ($companyPage - 1) * $perPageAgents, $perPageAgents);

if (!function_exists('agentPageUrl')) {
    function agentPageUrl($key, $value) {
        $query = $_GET;
        $query[$key] = $value;
        return BASEURL . '/agent/index?' . http_build_query($query, '', '&');
    }
}
?>

<style>
    .broker-directory-tabs .nav-link {
        color: #505050;
        font-weight: 700;
        border-radius: 8px 8px 0 0;
    }

    .broker-directory-tabs .nav-link.active {
        color: #1f2937;
        background: #fff;
        border-color: #dfe3e8 #dfe3e8 #fff;
    }

    .broker-directory-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px 22px;
        margin-bottom: 24px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
    }

    .broker-directory-photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .broker-directory-name {
        color: #1f2937;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 10px;
    }

    .broker-directory-name:hover {
        color: #00a884;
    }

    .broker-directory-contact {
        color: #374151;
        font-size: 1.05rem;
        line-height: 1.55;
        margin-bottom: 10px;
    }

    .broker-directory-contact i {
        color: #0f9aad;
        width: 22px;
    }

    .broker-directory-action {
        display: inline-block;
        padding: 10px 18px;
        border-radius: 8px;
        border: 1px solid #cfd6de;
        color: #1f2937;
        font-weight: 700;
        background: #fff;
    }

    .broker-directory-action:hover {
        border-color: #00a884;
        color: #00a884;
    }

    .broker-directory-panel-title {
        color: #1f2937;
        font-size: 17px;
        font-weight: 800;
        margin-bottom: 18px;
        text-transform: uppercase;
    }

    .broker-directory-bullets {
        margin: 0;
        padding-left: 0;
        color: #374151;
        list-style: none;
    }

    .broker-directory-bullets li {
        margin-bottom: 10px;
        position: relative;
        padding-left: 20px;
    }

    .broker-directory-bullets li::before {
        content: "";
        position: absolute;
        left: 0;
        top: 11px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        color: #ef4444;
        background: #ef4444;
    }

    .broker-empty-state {
        padding: 24px;
        border: 1px dashed #d1d5db;
        border-radius: 16px;
        text-align: center;
        color: #6b7280;
        background: #fff;
    }

    .broker-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 16px;
        flex-wrap: wrap;
    }
    .broker-page-btn {
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
    .broker-page-btn:hover {
        border-color: #00a884;
        color: #00a884;
        background: #eefbf7;
    }
    .broker-page-btn.active {
        background: #00a884;
        border-color: #00a884;
        color: #fff;
        pointer-events: none;
    }
    .broker-page-btn.disabled {
        opacity: 0.4;
        pointer-events: none;
    }
    .broker-page-ellipsis {
        color: #94a3b8;
        font-size: 14px;
        line-height: 38px;
        padding: 0 4px;
    }
    .broker-page-info {
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        margin-top: 8px;
    }

    @media (max-width: 991.98px) {
        .broker-directory-name {
            font-size: 1.65rem;
        }

        .broker-directory-panel-title {
            margin-top: 20px;
        }
    }
</style>

<div class="full-row bg-gray" style="min-height: 75vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-secondary mb-3"><b>Danh bạ nhà môi giới</b></h2>
            </div>
        </div>

        <ul class="nav nav-tabs broker-directory-tabs mb-4" id="brokerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= empty($companyAgents) ? 'active' : '' ?>" id="personal-tab" data-toggle="tab" href="#personal-panel" role="tab" aria-controls="personal-panel" aria-selected="<?= empty($companyAgents) ? 'true' : 'false' ?>">Cá nhân môi giới</a>
            </li>
        </ul>

        <div class="tab-content" id="brokerTabsContent">
            <div class="tab-pane fade <?= empty($companyAgents) ? '' : 'show active' ?>" id="company-panel" role="tabpanel" aria-labelledby="company-tab">
                <?php if(!empty($pagedCompanyAgents)): foreach($pagedCompanyAgents as $item): ?>
                    <?php $broker = $item['broker']; ?>
                    <div class="broker-directory-card">
                        <div class="row align-items-start">
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3 mb-md-0">
                                <a href="<?= BASEURL ?>/agent/detail/<?= (int)$broker['uid'] ?>">
                                    <img class="broker-directory-photo" src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($broker['uimage'] ?? '') ?>" alt="<?= htmlspecialchars($broker['uname'] ?? '') ?>">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-sm-8">
                                <a class="broker-directory-name d-inline-block" href="<?= BASEURL ?>/agent/detail/<?= (int)$broker['uid'] ?>"><?= htmlspecialchars($broker['uname'] ?? '') ?></a>
                                <div class="broker-directory-contact"><i class="fas fa-mobile-alt"></i><?= htmlspecialchars($broker['uphone'] ?? '') ?></div>
                                <a class="broker-directory-action" href="mailto:<?= htmlspecialchars($broker['uemail'] ?? '') ?>">Gửi Email</a>
                            </div>
                            <div class="col-lg-6 col-md-12 mt-4 mt-lg-0">
                                <div class="broker-directory-panel-title"><?= htmlspecialchars($item['categoryLabel']) ?></div>
                                <ul class="broker-directory-bullets">
                                    <?php if(!empty($item['recentProperties'])): foreach($item['recentProperties'] as $property): ?>
                                        <li>
                                            <a href="<?= BASEURL ?>/property/detail/<?= (int)$property['pid'] ?>">
                                                <?= htmlspecialchars($property['title'] ?? '') ?> ở <?= htmlspecialchars(trim(($property['ward'] ?? '') . (($property['ward'] ?? '') !== '' && ($property['city'] ?? '') !== '' ? ', ' : '') . ($property['city'] ?? ''))) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; else: ?>
                                        <li>Chưa có tin đăng công khai.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($totalCompanyPg > 1): ?>
                    <div class="broker-pagination">
                        <a class="broker-page-btn <?= $companyPage <= 1 ? 'disabled' : '' ?>" href="<?= agentPageUrl('page_company', $companyPage - 1) ?>">&#8249;</a>
                        <?php
                        $window = 1;
                        $start = max(1, $companyPage - $window);
                        $end   = min($totalCompanyPg, $companyPage + $window);
                        if ($start > 1): ?>
                            <a class="broker-page-btn" href="<?= agentPageUrl('page_company', 1) ?>">1</a>
                            <?php if ($start > 2): ?><span class="broker-page-ellipsis">&hellip;</span><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($page = $start; $page <= $end; $page++): ?>
                            <a class="broker-page-btn <?= $page === $companyPage ? 'active' : '' ?>" href="<?= agentPageUrl('page_company', $page) ?>"><?= $page ?></a>
                        <?php endfor; ?>
                        <?php if ($end < $totalCompanyPg): ?>
                            <?php if ($end < $totalCompanyPg - 1): ?><span class="broker-page-ellipsis">&hellip;</span><?php endif; ?>
                            <a class="broker-page-btn" href="<?= agentPageUrl('page_company', $totalCompanyPg) ?>"><?= $totalCompanyPg ?></a>
                        <?php endif; ?>
                        <a class="broker-page-btn <?= $companyPage >= $totalCompanyPg ? 'disabled' : '' ?>" href="<?= agentPageUrl('page_company', $companyPage + 1) ?>">&#8250;</a>
                    </div>
                    <p class="broker-page-info">Trang <?= $companyPage ?> / <?= $totalCompanyPg ?> &nbsp;&bull;&nbsp; Hiển thị <?= count($pagedCompanyAgents) ?> / <?= $totalCompany ?> công ty</p>
                <?php endif; ?>
                <?php elseif ($totalCompany === 0): ?>
                    <div class="broker-empty-state">Hiện chưa có công ty môi giới nào hiển thị.</div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade <?= empty($companyAgents) ? 'show active' : '' ?>" id="personal-panel" role="tabpanel" aria-labelledby="personal-tab">
                <?php if(!empty($pagedPersonalAgents)): foreach($pagedPersonalAgents as $item): ?>
                    <?php $broker = $item['broker']; ?>
                    <div class="broker-directory-card">
                        <div class="row align-items-start">
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3 mb-md-0">
                                <a href="<?= BASEURL ?>/agent/detail/<?= (int)$broker['uid'] ?>">
                                    <img class="broker-directory-photo" src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($broker['uimage'] ?? '') ?>" alt="<?= htmlspecialchars($broker['uname'] ?? '') ?>">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-sm-8">
                                <a class="broker-directory-name d-inline-block" href="<?= BASEURL ?>/agent/detail/<?= (int)$broker['uid'] ?>"><?= htmlspecialchars($broker['uname'] ?? '') ?></a>
                                <div class="broker-directory-contact"><i class="fas fa-mobile-alt"></i><?= htmlspecialchars($broker['uphone'] ?? '') ?></div>
                                <a class="broker-directory-action" href="mailto:<?= htmlspecialchars($broker['uemail'] ?? '') ?>">Gửi Email</a>
                            </div>
                            <div class="col-lg-6 col-md-12 mt-4 mt-lg-0">
                                <div class="broker-directory-panel-title"><?= htmlspecialchars($item['categoryLabel']) ?></div>
                                <ul class="broker-directory-bullets">
                                    <?php if(!empty($item['recentProperties'])): foreach($item['recentProperties'] as $property): ?>
                                        <li>
                                            <a href="<?= BASEURL ?>/property/detail/<?= (int)$property['pid'] ?>">
                                                <?= htmlspecialchars($property['title'] ?? '') ?> ở <?= htmlspecialchars(trim(($property['ward'] ?? '') . (($property['ward'] ?? '') !== '' && ($property['city'] ?? '') !== '' ? ', ' : '') . ($property['city'] ?? ''))) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; else: ?>
                                        <li>Chưa có tin đăng công khai.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($totalPersonalPg > 1): ?>
                    <div class="broker-pagination">
                        <a class="broker-page-btn <?= $personalPage <= 1 ? 'disabled' : '' ?>" href="<?= agentPageUrl('page_personal', $personalPage - 1) ?>">&#8249;</a>
                        <?php
                        $window = 1;
                        $start = max(1, $personalPage - $window);
                        $end   = min($totalPersonalPg, $personalPage + $window);
                        if ($start > 1): ?>
                            <a class="broker-page-btn" href="<?= agentPageUrl('page_personal', 1) ?>">1</a>
                            <?php if ($start > 2): ?><span class="broker-page-ellipsis">&hellip;</span><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($page = $start; $page <= $end; $page++): ?>
                            <a class="broker-page-btn <?= $page === $personalPage ? 'active' : '' ?>" href="<?= agentPageUrl('page_personal', $page) ?>"><?= $page ?></a>
                        <?php endfor; ?>
                        <?php if ($end < $totalPersonalPg): ?>
                            <?php if ($end < $totalPersonalPg - 1): ?><span class="broker-page-ellipsis">&hellip;</span><?php endif; ?>
                            <a class="broker-page-btn" href="<?= agentPageUrl('page_personal', $totalPersonalPg) ?>"><?= $totalPersonalPg ?></a>
                        <?php endif; ?>
                        <a class="broker-page-btn <?= $personalPage >= $totalPersonalPg ? 'disabled' : '' ?>" href="<?= agentPageUrl('page_personal', $personalPage + 1) ?>">&#8250;</a>
                    </div>
                    <p class="broker-page-info">Trang <?= $personalPage ?> / <?= $totalPersonalPg ?> &nbsp;&bull;&nbsp; Hiển thị <?= count($pagedPersonalAgents) ?> / <?= $totalPersonal ?> môi giới</p>
                <?php endif; ?>
                <?php elseif ($totalPersonal === 0): ?>
                    <div class="broker-empty-state">Hiện chưa có cá nhân môi giới nào hiển thị.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
