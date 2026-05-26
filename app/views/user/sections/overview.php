<?php /** @var array $user */ ?>
<?php /** @var array $stats */ ?>
<?php /** @var array $requests */ ?>

<?php
if (!function_exists('userWorkspaceFormatPrice')) {
    function userWorkspaceFormatPrice($price, $stype)
    {
        $amount = (float) $price;
        if ($amount <= 0) {
            return 'Thỏa thuận';
        }

        $label = number_format($amount, 0, ',', '.') . ' triệu';
        return strtolower((string) $stype) === 'rent' ? $label . '/tháng' : $label;
    }
}
?>

<div class="p-3 p-lg-4">

    <div class="agent-hero mb-4 px-0">
        <h1 class="agent-hero-title mb-2">Xin chào, <?= htmlspecialchars($user['uname'] ?? 'Người dùng') ?>!</h1>
        <div class="agent-hero-copy">
            <p>Chào mừng bạn quay trở lại. Hãy xem các cập nhật mới nhất về các yêu cầu tìm bất động sản và lịch hẹn của
                bạn ngày hôm nay.</p>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="agent-summary-grid mb-5">
        <div class="agent-summary-card d-flex align-items-center p-4 shadow-sm"
            style="background: #fff; border-radius: 16px;">
            <div class="mr-3 d-flex align-items-center justify-content-center text-white rounded-circle"
                style="background: #0056d2; width: 48px; height: 48px; font-size: 20px;">
                <i class="fas fa-home"></i>
            </div>
            <div>
                <span class="text-uppercase text-muted font-weight-bold"
                    style="font-size: 11px; letter-spacing: 1px;">Tổng yêu cầu</span>
                <strong class="d-block text-dark mt-1"
                    style="font-size: 24px; line-height: 1;"><?= (int) ($stats['total_requests'] ?? 0) ?></strong>
            </div>
        </div>

        <div class="agent-summary-card d-flex align-items-center p-4 shadow-sm"
            style="background: #fff; border-radius: 16px;">
            <div class="mr-3 d-flex align-items-center justify-content-center text-white rounded-circle"
                style="background: #f59e0b; width: 48px; height: 48px; font-size: 20px;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <span class="text-uppercase text-muted font-weight-bold"
                    style="font-size: 11px; letter-spacing: 1px;">Đang chờ duyệt</span>
                <strong class="d-block text-dark mt-1"
                    style="font-size: 24px; line-height: 1;"><?= (int) ($stats['pending_requests'] ?? 0) ?></strong>
            </div>
        </div>

        <div class="agent-summary-card d-flex align-items-center p-4 shadow-sm"
            style="background: #fff; border-radius: 16px;">
            <div class="mr-3 d-flex align-items-center justify-content-center text-white rounded-circle"
                style="background: #10b981; width: 48px; height: 48px; font-size: 20px;">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <span class="text-uppercase text-muted font-weight-bold"
                    style="font-size: 11px; letter-spacing: 1px;">Đã tiếp nhận</span>
                <strong class="d-block text-dark mt-1"
                    style="font-size: 24px; line-height: 1;"><?= (int) ($stats['accepted_requests'] ?? 0) ?></strong>
            </div>
        </div>

        <div class="agent-summary-card d-flex align-items-center p-4 shadow-sm"
            style="background: #fff; border-radius: 16px;">
            <div class="mr-3 d-flex align-items-center justify-content-center text-white rounded-circle"
                style="background: #6366f1; width: 48px; height: 48px; font-size: 20px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <span class="text-uppercase text-muted font-weight-bold"
                    style="font-size: 11px; letter-spacing: 1px;">Hoàn tất</span>
                <strong class="d-block text-dark mt-1"
                    style="font-size: 24px; line-height: 1;"><?= (int) ($stats['completed_cases'] ?? 0) ?></strong>
            </div>
        </div>
    </div>

    <!-- Giao dịch của tôi Section -->
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="agent-hero-title m-0" style="font-size: 22px;">Yêu cầu của tôi</h2>
            <a href="<?= BASEURL ?>/userWorkspace/index?section=requests"
                class="text-primary font-weight-bold d-flex align-items-center" style="font-size: 14px;">
                Xem tất cả <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>

        <div class="row">
            <?php
            $recentRequests = array_slice($requests, 0, 3);
            if (!empty($recentRequests)):
                foreach ($recentRequests as $req):
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100 property-card-hover"
                            style="border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                            <?php
                            $imageRaw = isset($req['property_image']) ? trim((string) $req['property_image']) : '';
                            $pimageUrl = $imageRaw !== ''
                                ? BASEURL . '/admin/property/' . rawurlencode($imageRaw)
                                : BASEURL . '/images/breadcromb.jpg';
                            $propertyId = (int) ($req['property_id'] ?? 0);
                            $propertyLink = $propertyId > 0 ? (BASEURL . '/property/detail/' . $propertyId) : '';
                            $propertyTitle = trim((string) ($req['property_title'] ?? 'Bất động sản'));
                            $priceLabel = userWorkspaceFormatPrice($req['property_price'] ?? null, $req['property_stype'] ?? '');
                            ?>
                            <div class="position-relative" style="height: 180px; width: 100%; overflow: hidden;">
                                <img src="<?= htmlspecialchars($pimageUrl) ?>" alt="property image"
                                    style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                    class="hover-zoom">
                                <div class="position-absolute"
                                    style="top: 12px; right: 12px; transform: scale(0.9); transform-origin: top right;font-size: 20px;">
                                    <?= userStatusBadge((string) ($req['status'] ?? 'pending')) ?>
                                </div>
                            </div>

                            <div class="card-body d-flex flex-column p-4">
                                <h3 class="font-weight-bold text-dark mb-2"
                                    style="font-size: 17px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="<?= htmlspecialchars($propertyTitle) ?>">
                                    <?php if ($propertyLink !== ''): ?>
                                        <a href="<?= htmlspecialchars($propertyLink) ?>" class="text-dark"
                                            style="text-decoration: none;">
                                            <?= htmlspecialchars($propertyTitle) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($propertyTitle) ?>
                                    <?php endif; ?>
                                </h3>

                                <div class="text-muted mb-4 d-flex align-items-center" style="font-size: 13px;">
                                    <i class="fas fa-user text-primary mr-2"></i> Môi giới: <span
                                        class="font-weight-bold text-dark ml-1"><?= htmlspecialchars((string) ($req['agent_name'] ?? 'Chưa phản hồi')) ?></span>
                                </div>

                                <div class="mt-auto d-flex justify-content-between align-items-center border-top pt-3">
                                    <span class="text-primary font-weight-bold"
                                        style="font-size: 23px; color: red !important; "><i
                                            class="fas fa-dollar-sign mr-2"></i><?= htmlspecialchars($priceLabel) ?>
                                    </span>
                                    <a href="<?= BASEURL ?>/userWorkspace/requestDetail/<?= (int) ($req['id'] ?? 0) ?>"
                                        class="btn btn-primary rounded-pill font-weight-bold px-4 py-2 shadow-sm"
                                        style="font-size: 13px; background: linear-gradient(135deg, #0040a1, #0056d2); border: none;">Chi
                                        tiết <i class="fas fa-arrow-right ml-1" style="font-size: 11px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
            else:
                ?>
                <div class="col-12">
                    <div class="bg-white p-4 rounded text-center text-muted"
                        style="border-radius: 16px !important; border: 1px dashed #cbd5e1;">
                        Bạn chưa có yêu cầu nào.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Agents Section -->
    <?php
    $agents = [];
    foreach ($requests as $r) {
        $agentName = trim((string) ($r['agent_name'] ?? ''));
        $agentUid = isset($r['agent_uid']) ? (int) $r['agent_uid'] : 0;
        if ($agentName === '') {
            continue;
        }

        $key = $agentUid > 0 ? $agentUid : $agentName;
        if (!isset($agents[$key])) {
            $imageRaw = isset($r['agent_image']) ? trim((string) $r['agent_image']) : '';
            $imageUrl = $imageRaw !== '' ? (BASEURL . '/admin/user/' . rawurlencode($imageRaw)) : '';
            $agents[$key] = [
                'name' => $agentName,
                'image' => $imageUrl,
            ];
        }
    }
    $agents = array_slice($agents, 0, 4);
    ?>
    <?php if (!empty($agents)): ?>
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="agent-hero-title m-0" style="font-size: 22px;">Môi giới của bạn</h2>
            </div>
            <div class="d-flex" style="gap: 16px; overflow-x: auto; padding-bottom: 10px;">
                <?php foreach ($agents as $ag): ?>
                    <div class="bg-white border-0 shadow-sm p-3 d-flex align-items-center flex-shrink-0"
                        style="border-radius: 16px; min-width: 250px;">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary mr-3"
                            style="width: 46px; height: 46px; font-size: 18px; overflow: hidden;">
                            <?php if (!empty($ag['image'])): ?>
                                <img src="<?= htmlspecialchars($ag['image']) ?>" alt="<?= htmlspecialchars($ag['name']) ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 15px;">
                                <?= htmlspecialchars($ag['name']) ?>
                            </div>
                            <div class="text-muted" style="font-size: 12px;">Chuyên viên hỗ trợ</div>
                        </div>
                        <a href="#"
                            class="btn btn-light text-primary rounded-circle ml-auto d-flex align-items-center justify-content-center"
                            style="width: 36px; height: 36px;" title="Nhắn tin">
                            <i class="fas fa-comments"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div> <!-- End of overall padding wrapper -->

<style>
    .property-card-hover:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-5px);
    }

    .property-card-hover:hover .hover-zoom {
        transform: scale(1.08) !important;
    }
</style>