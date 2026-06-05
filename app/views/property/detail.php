<?php require_once '../app/views/layouts/header.php'; ?>
<?php $row = $data['property']; ?>
<?php
if (!function_exists('formatPropertyPrice')) {
    function formatPropertyPrice($price, $stype) {
        if ((float) $price <= 0) {
            return 'Thỏa thuận';
        }
        $formatted = number_format((float)$price, 0, ',', '.') . ' triệu';
        return $stype === 'rent' ? $formatted . '/tháng' : $formatted;
    }
}

if (!function_exists('formatPropertyFloor')) {
    function formatPropertyFloor($floor) {
        $floor = trim((string) $floor);
        return $floor !== '' ? $floor : 'Chưa cập nhật';
    }
}

$agentPhoneRaw = trim((string)($row['uphone'] ?? ''));
$agentPhoneDigits = preg_replace('/\D+/', '', $agentPhoneRaw);
$agentProfileUrl = BASEURL . '/agent/detail/' . (int)($row['uid'] ?? 0);
$agentPostedCount = (int)($data['agentPropertyCount'] ?? 0);
$ownerType = strtolower((string)($row['utype'] ?? ''));
$agentFirstLetter = mb_strtoupper(mb_substr(trim((string)($row['uname'] ?? 'M')), 0, 1));
$isOwnerAccount = false;
$isBrokerAccount = in_array($ownerType, ['owner', 'agent'], true);

if ($isBrokerAccount) {
    $agentTypeLabel = 'Môi giới';
} else {
    $agentTypeLabel = 'Người đăng';
}
?>


<!-- Banner -->
<div class="banner-full-row page-banner" style="background-image:url('<?= BASEURL ?>/images/breadcromb.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Chi tiết bất động sản</b></h2>
            </div>
            <!-- <div class="col-md-6">
                <nav aria-label="breadcrumb" class="float-left float-md-right">
                    <ol class="breadcrumb bg-transparent m-0 p-0">
                        <li class="breadcrumb-item text-white"><a href="<?= BASEURL ?>/home/index">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Chi tiết bất động sản</li>
                    </ol>
                </nav>
            </div> -->
        </div>
    </div>
</div>
<!-- Banner -->

<div class="full-row">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-12">
                        <div id="single-property" style="width:100%; max-width:1200px; height:600px; margin:30px auto 100px;"> 
                            <!-- Slide 1-->
                            <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> <img style="object-fit: cover; width: 100%; height: 100%;" src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>" class="ls-bg" alt="" /> </div>
                            <!-- Slide 2-->
                            <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> <img style="object-fit: cover; width: 100%; height: 100%;" src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage1']) ?>" class="ls-bg" alt="" /> </div>
                            <!-- Slide 3-->
                            <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> <img style="object-fit: cover; width: 100%; height: 100%;" src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage2']) ?>" class="ls-bg" alt="" /> </div>
                            <!-- Slide 4-->
                            <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> <img style="object-fit: cover; width: 100%; height: 100%;" src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage3']) ?>" class="ls-bg" alt="" /> </div>
                            <!-- Slide 5-->
                            <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> <img style="object-fit: cover; width: 100%; height: 100%;" src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage4']) ?>" class="ls-bg" alt="" /> </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <!-- <div class="bg-success d-table px-3 py-2 rounded text-white text-capitalize">Hình thức <?= $row['stype'] == 'sale' ? 'Bán' : 'Cho thuê' ?></div> -->
                        <h5 class="mt-2 text-secondary text-capitalize detail-property-title"><?= htmlspecialchars($row['title']) ?></h5>
                        <?php
                            $locationParts = array_values(array_filter([
                                trim((string)($row['location'] ?? '')),
                                trim((string)($row['ward'] ?? '')),
                                trim((string)($row['city'] ?? '')),
                            ]));
                            $fullLocation = implode(', ', $locationParts);
                        ?>
                        <span class="mb-sm-20 d-block text-capitalize"><i class="fas fa-map-marker-alt text-success font-12"></i> &nbsp;<?= htmlspecialchars($fullLocation) ?></span>
                    </div>
                    <div class="col-12">
                        <div class="detail-top-metrics">
                            <div class="detail-top-metric">
                                <div class="detail-top-metric-label">Khoảng giá</div>
                                <div class="detail-top-metric-value"><?= formatPropertyPrice($row['price'], $row['stype']) ?></div>
                            </div>
                            <div class="detail-top-metric">
                                <div class="detail-top-metric-label">Diện tích</div>
                                <div class="detail-top-metric-value"><?= htmlspecialchars($row['size']) ?> m2</div>
                            </div>
                            <div class="detail-top-metric">
                                <div class="detail-top-metric-label">Phòng ngủ</div>
                                <div class="detail-top-metric-value"><?= (int)$row['bedroom'] ?> PN</div>
                            </div>
                            <div class="detail-top-actions">
                                <button type="button" class="detail-top-action-btn" aria-label="Chia sẻ"><i class="far fa-share-square"></i></button>
                                <button type="button" class="detail-top-action-btn" aria-label="Báo xấu"><i class="far fa-flag"></i></button>
                                <button type="button" class="detail-top-action-btn" aria-label="Yêu thích" onclick="<?php if (!isset($_SESSION['uid'])): ?>AppPopup.warning('Vui lòng đăng nhập để lưu tin yêu thích.');<?php endif; ?>"><i class="far fa-heart"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="property-details">
                    <div class="detail-spec-card">
                        <h4 class="text-secondary detail-spec-title detail-section-title">Đặc điểm bất động sản</h4>
                        <div class="detail-spec-grid">
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-money-bill-alt"></i></span>
                                <span class="detail-spec-label">Khoảng giá</span>
                                <span class="detail-spec-value"><?= formatPropertyPrice($row['price'], $row['stype']) ?></span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-dot-circle"></i></span>
                                <span class="detail-spec-label">Số phòng tắm, vệ sinh</span>
                                <span class="detail-spec-value"><?= (int)$row['bathroom'] ?> phòng</span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-square"></i></span>
                                <span class="detail-spec-label">Diện tích</span>
                                <span class="detail-spec-value"><?= htmlspecialchars($row['size']) ?> m2</span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-compass"></i></span>
                                <span class="detail-spec-label">Thành phố</span>
                                <span class="detail-spec-value text-capitalize"><?= htmlspecialchars($row['city']) ?></span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-square"></i></span>
                                <span class="detail-spec-label">Số phòng ngủ</span>
                                <span class="detail-spec-value"><?= (int)$row['bedroom'] ?> phòng</span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-map"></i></span>
                                <span class="detail-spec-label">Phường/Xã</span>
                                <span class="detail-spec-value text-capitalize"><?= htmlspecialchars($row['ward'] ?? '') ?></span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-compass"></i></span>
                                <span class="detail-spec-label">Hướng nhà</span>
                                <span class="detail-spec-value"><?= htmlspecialchars($row['direction'] ?? '') ?></span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-building"></i></span>
                                <span class="detail-spec-label">Loại bất động sản</span>
                                <span class="detail-spec-value text-capitalize"><?= htmlspecialchars($row['type']) ?></span>
                            </div>
                            <div class="detail-spec-item">
                                <span class="detail-spec-icon"><i class="far fa-clone"></i></span>
                                <span class="detail-spec-label">Tầng thuê / Tổng số tầng</span>
                                <span class="detail-spec-value text-capitalize"><?= htmlspecialchars(formatPropertyFloor($row['floor'] ?? '')) ?> / <?= htmlspecialchars($row['totalfloor']) ?></span>
                            </div>
                        </div>
                    </div>
                    <h4 class="text-secondary my-4 detail-section-title">Mô tả</h4>
                    <div class="detail-desc-card"><?= htmlspecialchars_decode($row['pcontent']) ?></div>

                    <h5 class="mt-5 mb-4 text-secondary detail-section-title">Tiện ích</h5>
                    <div class="row detail-feature-card">
                        <?= htmlspecialchars_decode($row['feature_html'] ?? '') ?>
                    </div>   
                    
                    <h5 class="mt-5 mb-4 text-secondary detail-section-title">Sơ đồ mặt bằng</h5>
                    <div class="accordion" id="accordionExample">
                        <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"> Sơ đồ tổng thể </button>
                        <div id="collapseOne" class="collapse show p-4" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['mapimage']) ?>" alt="Không có"> </div>
                        <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Tầng hầm</button>
                        <div id="collapseTwo" class="collapse p-4" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['topmapimage']) ?>" alt="Không có"> </div>
                        <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Tầng trệt</button>
                        <div id="collapseThree" class="collapse p-4" aria-labelledby="headingThree" data-parent="#accordionExample">
                            <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['groundmapimage']) ?>" alt="Không có"> </div>
                    </div>

                    <h5 class="mt-5 mb-4 text-secondary double-down-line-left position-relative detail-section-title">
                        Liên hệ môi giới
                    </h5>
                    <div class="agent-contact pt-60">
                        <div class="row">
                            <div class="col-sm-4 col-lg-3">
                                <?php $hasAgentAvatar = !empty($row['uimage']) && file_exists(__DIR__ . '/../../../admin/user/' . $row['uimage']); ?>
                                <?php if ($hasAgentAvatar): ?>
                                    <img src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($row['uimage'] ?? '') ?>" alt="" height="200" width="170">
                                <?php else: ?>
                                    <div style="width:170px;height:200px;border-radius:16px;background:linear-gradient(135deg,#e9f5ff 0%,#d6e9ff 100%);color:#0d6efd;display:flex;align-items:center;justify-content:center;font-size:56px;font-weight:800;">
                                        <?= htmlspecialchars($agentFirstLetter) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-8 col-lg-9">
                                <div class="agent-data text-ordinary mt-sm-20">
                                    <h6 class="text-success text-capitalize"><?= htmlspecialchars($row['uname'] ?? '') ?></h6>
                                    <ul class="mb-3">
                                        <li><strong><?= htmlspecialchars($agentTypeLabel) ?></strong></li>
                                        <li><?= htmlspecialchars($row['uphone'] ?? '') ?></li>
                                        <li><?= htmlspecialchars($row['uemail'] ?? '') ?></li>
                                    </ul>
                                    <div class="mt-3 text-secondary hover-text-success">
                                        <ul>
                                            <li class="float-left mr-3"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                            <li class="float-left mr-3"><a href="#"><i class="fab fa-twitter"></i></a></li>
                                            <li class="float-left mr-3"><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                            <li class="float-left mr-3"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                            <li class="float-left mr-3"><a href="#"><i class="fas fa-rss"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="ldp-agent-contact my-4">
                    <div class="ldp-agent-contact__header">Môi giới chuyên nghiệp</div>
                    <div class="ldp-agent-contact__body">
                        <div class="ldp-agent-contact__agent">
                            <?php if ($hasAgentAvatar): ?>
                                <img class="ldp-agent-contact__avatar" src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($row['uimage'] ?? '') ?>" alt="<?= htmlspecialchars($row['uname'] ?? '') ?>">
                            <?php else: ?>
                                <div class="ldp-agent-contact__avatar" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#e9f5ff 0%,#d6e9ff 100%);color:#0d6efd;font-weight:800;font-size:28px;">
                                    <?= htmlspecialchars($agentFirstLetter) ?>
                                </div>
                            <?php endif; ?>
                            <div class="ldp-agent-contact__name text-capitalize"><?= htmlspecialchars($row['uname'] ?? 'Môi giới') ?></div>
                        </div>
                        <div class="ldp-agent-contact__meta">
                            <div class="ldp-agent-contact__meta-item">
                                <span class="ldp-agent-contact__meta-label">Loại tài khoản</span>
                                <span class="ldp-agent-contact__meta-value text-capitalize"><?= htmlspecialchars($agentTypeLabel) ?></span>
                            </div>
                            <div class="ldp-agent-contact__meta-item">
                                <span class="ldp-agent-contact__meta-label">Tin đăng đang có</span>
                                <span class="ldp-agent-contact__meta-value"><?= $agentPostedCount ?></span>
                            </div>
                        </div>
                        <a class="ldp-agent-contact__profile" href="<?= $agentProfileUrl ?>">Xem profile môi giới <i class="fas fa-chevron-right ml-1"></i></a>
                        <?php $propertyStatus = strtolower((string) ($row['status'] ?? 'available')); ?>
                        <?php if (in_array($propertyStatus, ['rented', 'sold out'])): ?>
                            <button type="button" class="ldp-agent-contact__inquiry" disabled style="opacity: 0.6; cursor: not-allowed;">
                                Đã thuê
                            </button>
                        <?php elseif (!isset($_SESSION['uid'])): ?>
                            <button type="button" class="ldp-agent-contact__inquiry" onclick="AppPopup.warning('Vui lòng đăng nhập để gửi liên hệ.');">
                                Liên hệ
                            </button>
                        <?php else: ?>
                            <button type="button" class="ldp-agent-contact__inquiry" data-toggle="modal" data-target="#brokerInquiryModal">
                                Liên hệ
                            </button>
                        <?php endif; ?>
                        <a class="ldp-agent-contact__call" href="<?= 'tel:' . htmlspecialchars($agentPhoneRaw) ?>">
                            <i class="fas fa-phone-volume"></i>
                            <?= htmlspecialchars($agentPhoneRaw !== '' ? $agentPhoneRaw : 'Liên hệ môi giới') ?>
                        </a>
                    </div>
                </div>
                
                <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4 mt-5 detail-section-title">Bất động sản nổi bật</h4>
                <ul class="property_list_widget">
                    <?php if(isset($data['featuredProperties'])): foreach($data['featuredProperties'] as $frow): ?>
                    <li> <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($frow['pimage']) ?>" alt="pimage">
                        <h6 class="text-secondary hover-text-success text-capitalize tieu-de-moi"><a href="<?= BASEURL ?>/property/detail/<?= $frow['pid'] ?>"><?= htmlspecialchars($frow['title']) ?></a></h6>
                        <span class="font-14"><i class="fas fa-map-marker-alt icon-success icon-small"></i> <?= htmlspecialchars($frow['location']) ?></span>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>

                <div class="sidebar-widget mt-5">
                    <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4 detail-section-title">Bất động sản mới đăng</h4>
                    <ul class="property_list_widget">
                        <?php if(isset($data['recentProperties'])): foreach($data['recentProperties'] as $rrow): ?>
                        <li> <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($rrow['pimage']) ?>" alt="pimage">
                            <h6 class="text-secondary hover-text-success text-capitalize tieu-de-moi"><a href="<?= BASEURL ?>/property/detail/<?= $rrow['pid'] ?>"><?= htmlspecialchars($rrow['title']) ?></a></h6>
                            <span class="font-14"><i class="fas fa-map-marker-alt icon-success icon-small"></i> <?= htmlspecialchars($rrow['location']) ?></span>
                        </li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isBrokerAccount): ?>
<div class="modal fade ldp-inquiry-modal" id="brokerInquiryModal" tabindex="-1" role="dialog" aria-labelledby="brokerInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content ldp-inquiry-card">
            <div class="ldp-inquiry-card__header">
                <h5 class="ldp-inquiry-card__title" id="brokerInquiryModalLabel">Liên hệ môi giới</h5>
                <p class="ldp-inquiry-card__subtitle">Điền thông tin chi tiết để nhận phản hồi nhanh nhất</p>
            </div>
            <form id="brokerInquiryForm" class="ldp-inquiry-form-wrapper" action="<?= BASEURL ?>/property/submitInquiry/<?= (int)($row['pid'] ?? 0) ?>" method="post">
                <div class="ldp-inquiry-form">
                    <div class="ldp-inquiry-form__grid">
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-name">Họ và tên</label>
                            <input id="inquiry-name" type="text" name="name" class="ldp-inquiry-input" required autocomplete="name" placeholder="Nguyễn Văn A">
                        </div>
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-email">Email công việc</label>
                            <input id="inquiry-email" type="email" name="work_email" class="ldp-inquiry-input" required autocomplete="email" placeholder="ban@congty.com">
                        </div>
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-phone">Số điện thoại</label>
                            <input id="inquiry-phone" type="tel" name="phone" class="ldp-inquiry-input" required inputmode="tel" placeholder="0987 654 321">
                        </div>
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-budget">Ngân sách dự kiến (VNĐ/tháng)</label>
                            <input id="inquiry-budget" type="text" name="desired_budget" class="ldp-inquiry-input" inputmode="numeric" placeholder="12.000.000">
                        </div>
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-area">Khu vực quan tâm</label>
                            <input id="inquiry-area" type="text" name="desired_area" class="ldp-inquiry-input" placeholder="Quận 7, gần cầu Phú Mỹ">
                        </div>
                        <div class="ldp-inquiry-field">
                            <label for="inquiry-move-in">Thời gian có thể dọn vào</label>
                            <input id="inquiry-move-in" type="date" name="desired_move_in_time" class="ldp-inquiry-input" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="ldp-inquiry-field ldp-inquiry-field--full">
                        <label for="inquiry-requirement">Chi tiết nhu cầu thuê</label>
                        <textarea id="inquiry-requirement" name="requirement" class="ldp-inquiry-textarea" rows="4" required placeholder="Mô tả mong muốn về diện tích, nội thất, thời hạn thuê..."></textarea>
                    </div>
                </div>
                <div class="ldp-inquiry-footer">
                    <!-- <p class="ldp-inquiry-footer__note mb-0">Chúng tôi cam kết bảo mật thông tin của bạn.</p> -->
                    <div class="ldp-inquiry-actions">
                        <button type="button" class="btn ldp-inquiry-btn-cancel" data-dismiss="modal">Đóng</button>
                        <button type="submit" name="send_inquiry" value="1" class="btn ldp-inquiry-btn-submit">Gửi liên hệ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var form = document.getElementById('brokerInquiryForm');
    if (!form) {
        return;
    }

    var phoneInput = document.getElementById('inquiry-phone');
    var budgetInput = document.getElementById('inquiry-budget');
    var moveInInput = document.getElementById('inquiry-move-in');
    var areaInput = document.getElementById('inquiry-area');

    if (moveInInput) {
        var todayIso = new Date().toISOString().split('T')[0];
        if (!moveInInput.getAttribute('min') || moveInInput.getAttribute('min') < todayIso) {
            moveInInput.setAttribute('min', todayIso);
        }
    }

    function normaliseDigits(value) {
        return value ? value.replace(/[^0-9]/g, '') : '';
    }

    function formatCurrency(value) {
        var digits = normaliseDigits(value);
        if (!digits) {
            return '';
        }
        var number = parseInt(digits, 10);
        if (Number.isNaN(number)) {
            return '';
        }
        return new Intl.NumberFormat('vi-VN').format(number);
    }

    if (budgetInput) {
        budgetInput.addEventListener('blur', function () {
            budgetInput.value = formatCurrency(budgetInput.value);
        });

        budgetInput.addEventListener('input', function () {
            budgetInput.setCustomValidity('');
        });
    }

    if (areaInput) {
        areaInput.addEventListener('input', function () {
            areaInput.setCustomValidity('');
        });
    }

    if (moveInInput) {
        moveInInput.addEventListener('input', function () {
            moveInInput.setCustomValidity('');
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            phoneInput.setCustomValidity('');
        });
    }

    form.addEventListener('submit', function (event) {
        var isValid = true;

        if (phoneInput) {
            var phoneDigits = normaliseDigits(phoneInput.value);
            if (phoneDigits.length < 9 || phoneDigits.length > 15) {
                phoneInput.setCustomValidity('Vui lòng nhập số điện thoại từ 9 đến 15 chữ số.');
                phoneInput.reportValidity();
                isValid = false;
            }
        }

        if (budgetInput) {
            var budgetDigits = normaliseDigits(budgetInput.value);
            if (budgetDigits.length === 0) {
                budgetInput.setCustomValidity('Vui lòng nhập ngân sách dự kiến.');
                budgetInput.reportValidity();
                isValid = false;
            } else {
                budgetInput.value = budgetDigits;
            }
        }

        if (areaInput) {
            var areaValue = areaInput.value.trim();
            if (areaValue === '') {
                areaInput.setCustomValidity('Vui lòng nhập khu vực quan tâm.');
                areaInput.reportValidity();
                isValid = false;
            }
        }

        if (moveInInput && moveInInput.value) {
            var selectedDate = new Date(moveInInput.value + 'T00:00:00');
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                moveInInput.setCustomValidity('Ngày dọn vào phải từ hôm nay trở đi.');
                moveInInput.reportValidity();
                isValid = false;
            }
        } else if (moveInInput) {
            moveInInput.setCustomValidity('Vui lòng chọn thời gian có thể dọn vào.');
            moveInInput.reportValidity();
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
})();
</script>

<style>
    .detail-spec-card {
        border-radius: 12px;
        padding: 18px 0px;
        margin-bottom: 28px;
    }

    .detail-section-title,
    .detail-spec-title {
        font-size: 18px;
        line-height: 28px;
        font-weight: 500;
        margin-bottom: 16px;
    }

    .detail-property-title {
        font-size: 24px;
        line-height: 32px;
        font-weight: 700;
    }

    .property_list_widget h6 {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-weight: 700;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    .detail-spec-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 24px;
    }

    .detail-spec-item {
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #e3e7ea;
    }

    .detail-spec-icon {
        width: 30px;
        height: 30px;
        border: 1.5px solid #111;
        border-radius: 50%;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #0e1520;
        font-size: 14px;
        flex-shrink: 0;
    }

    .detail-top-metrics {
        border-top: 1px solid #e6eaee;
        border-bottom: 1px solid #e6eaee;
        padding: 12px 4px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .detail-top-metric {
        min-width: 110px;
    }

    .detail-top-metric-label {
        color: #999;
        font-size: 14px;
        font-weight: normal;
        line-height: 1.2;
    }

    .detail-top-metric-value {
        color: #131a21;
        font-size: 18px;
        font-weight: 500;
        line-height: 28px;
        margin-top: 3px;
    }

    .detail-top-actions {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }

    .detail-top-action-btn {
        width: 42px;
        height: 42px;
        border: 1.5px solid #111;
        border-radius: 50%;
        background: #fff;
        color: #111;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .detail-spec-label {
        font-weight: 500;
        color: #22303c;
        flex: 1;
    }

    .detail-spec-value {
        color: #0e1520;
        text-align: right;
        margin-left: auto;
    }

    .detail-desc-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px 20px;
        line-height: 1.9;
    }

    .detail-feature-card {
        background: #fff;
        border: 1px solid #e8ebee;
        border-radius: 12px;
        padding: 14px 16px;
    }

    .detail-feature-card ul,
    .detail-feature-card ol {
        margin-bottom: 0;
    }

    .ldp-inquiry-modal .modal-dialog {
        max-width: 640px;
        margin-top: 90px;
    }

    @media (max-width: 575.98px) {
        .ldp-inquiry-modal .modal-dialog {
            margin: 60px 12px 0;
        }
    }

    .ldp-inquiry-card {
        border-radius: 18px;
        overflow: hidden;
        border: none;
        box-shadow: 0 18px 45px rgba(9, 23, 36, 0.18);
        background: linear-gradient(155deg, #f9fbff 0%, #ffffff 45%, #f3f6ff 100%);
    }

    .ldp-inquiry-card__header {
        padding: 24px 28px 18px;
        background: #0d8f90;
        color: #fff;
    }

    .ldp-inquiry-card__title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
    }

    .ldp-inquiry-card__subtitle {
        margin-top: 6px;
        font-size: 13px;
        letter-spacing: 0.02em;
        opacity: 0.85;
    }

    .ldp-inquiry-form {
        padding: 26px 28px;
        display: grid;
        gap: 18px;
    }

    .ldp-inquiry-form__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    @media (max-width: 575.98px) {
        .ldp-inquiry-form {
            padding: 22px 20px;
        }

        .ldp-inquiry-form__grid {
            grid-template-columns: 1fr;
        }
    }

    .ldp-inquiry-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ldp-inquiry-field label {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #0f2a38;
        margin: 0;
    }

    .ldp-inquiry-input,
    .ldp-inquiry-textarea,
    .ldp-inquiry-select {
        border-radius: 14px;
        border: 1.5px solid rgba(16, 36, 52, 0.18);
        padding: 12px 14px;
        font-size: 15px;
        background-color: rgba(255, 255, 255, 0.92);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .ldp-inquiry-input:focus,
    .ldp-inquiry-textarea:focus,
    .ldp-inquiry-select:focus {
        border-color: #0d8f90;
        box-shadow: 0 0 0 3px rgba(13, 143, 144, 0.22);
        outline: none;
    }

    .ldp-inquiry-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .ldp-inquiry-field--full {
        grid-column: 1 / -1;
    }

    .ldp-inquiry-hint {
        font-size: 12px;
        color: rgba(16, 36, 52, 0.6);
    }

    .ldp-inquiry-footer {
        background: rgba(13, 143, 144, 0.06);
        padding: 18px 28px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ldp-inquiry-footer__note {
        font-size: 12px;
        color: rgba(16, 36, 52, 0.7);
    }

    .ldp-inquiry-actions {
        display: flex;
        margin: auto;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .ldp-inquiry-btn-submit,
    .ldp-inquiry-btn-cancel {
        min-width: 140px;
        border-radius: 999px;
        /* padding: 10px 20px; */
        font-weight: 600;
        letter-spacing: 0.03em;
    }

    .ldp-inquiry-btn-submit {
        background: linear-gradient(135deg, #0d8f90, #0aa1a4);
        border: none;
        color: #fff;
        box-shadow: 0 12px 24px rgba(13, 143, 144, 0.28);
    }

    .ldp-inquiry-btn-submit:focus,
    .ldp-inquiry-btn-submit:hover {
        background: linear-gradient(135deg, #0aa1a4, #0d8f90);
    }

    .ldp-inquiry-btn-cancel {
        background: #fff;
        color: #0d8f90;
        border: 1.5px solid rgba(13, 143, 144, 0.45);
    }

    .ldp-inquiry-btn-cancel:hover,
    .ldp-inquiry-btn-cancel:focus {
        background: rgba(13, 143, 144, 0.08);
        color: #0a6f70;
    }

    .ldp-inquiry-modal .modal-body {
        padding: 0;
    }

    .ldp-inquiry-modal .modal-header,
    .ldp-inquiry-modal .modal-footer {
        display: none;
    }

    .ldp-agent-contact {
        border: 4px solid #0d8f90;
        border-radius: 16px;
        overflow: hidden;
        background: #f4f5f6;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .ldp-agent-contact__header {
        background: #0d8f90;
        color: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        line-height: 1.25;
        padding: 14px 12px 68px;
    }

    .ldp-agent-contact__body {
        margin: -52px 12px 12px;
        background: #fff;
        border-radius: 14px;
        border: 1px solid #d8dcdf;
        padding: 0 10px 14px;
    }

    .ldp-agent-contact__agent {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 6px 12px;
    }

    .ldp-agent-contact__avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #dce2e6;
        background: #fff;
    }

    .ldp-agent-contact__name {
        font-size: 18px;
        font-weight: 700;
        color: #2b2f33;
        line-height: 1.25;
    }

    .ldp-agent-contact__meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        padding: 2px 4px 12px;
    }

    .ldp-agent-contact__meta-item {
        text-align: center;
        padding: 6px 8px;
    }

    .ldp-agent-contact__meta-item + .ldp-agent-contact__meta-item {
        border-left: 1px solid #d8dcdf;
    }

    .ldp-agent-contact__meta-label {
        display: block;
        color: #606870;
        font-size: 12px;
        line-height: 1.2;
    }

    .ldp-agent-contact__meta-value {
        display: block;
        color: #222a31;
        font-weight: 700;
        font-size: 18px;
        line-height: 1.2;
        margin-top: 6px;
    }

    .ldp-agent-contact__profile {
        display: block;
        text-align: center;
        color: #2d3338;
        font-size: 14px;
        line-height: 20px;
        font-weight: 600;
        padding: 14px 8px;
    }

    .ldp-agent-contact__call {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        border-radius: 10px;
        font-weight: 700;
        font-size: 16px;
        text-decoration: none;
    }


    .ldp-agent-contact__call {
        margin-top: 10px;
        background: #0d9da5;
        color: #fff;
        padding: 14px;
    }

    .ldp-agent-contact__inquiry {
        margin-top: 10px;
        width: 100%;
        border: 1px solid #0d9da5;
        border-radius: 10px;
        background: #0d9da5;
        color: #ffff;
        font-weight: 700;
        font-size: 16px;
        min-height: 50px;
        transition: all 0.2s ease;
    }

    .ldp-agent-contact__inquiry:hover {
        background: #0d9da5;
        color: #fff;
    }


    @media (max-width: 1400px) {
        .ldp-agent-contact__zalo-text {
            font-size: 20px;
        }
    }

    @media (max-width: 1200px) {
        .ldp-agent-contact__zalo-text {
            font-size: 18px;
        }
    }

    @media (max-width: 991.98px) {
        .ldp-agent-contact__zalo-text {
            font-size: 17px;
        }
    }

    @media (max-width: 991.98px) {
        .detail-top-actions {
            margin-left: 0;
        }
    }

    @media (max-width: 991.98px) {
        .detail-spec-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


<?php require_once '../app/views/layouts/footer.php'; ?>
</body>
</html>
