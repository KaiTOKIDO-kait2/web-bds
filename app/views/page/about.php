<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$aboutRows = isset($data['aboutData']) && is_array($data['aboutData']) ? array_values($data['aboutData']) : [];
$leaders = isset($data['leadership']) && is_array($data['leadership']) ? array_values($data['leadership']) : [];
$pageTitle = 'Về LuxEstate';
if (!empty($aboutRows)) {
    $firstTitle = trim((string)($aboutRows[0]['title'] ?? $aboutRows[0][1] ?? ''));
    if ($firstTitle !== '') {
        $pageTitle = $firstTitle;
    }
}

$overviewHtml = '';
$overviewImage = '';
if (!empty($aboutRows)) {
    $overviewHtml = (string)($aboutRows[0]['content'] ?? $aboutRows[0][2] ?? '');
    $overviewImage = trim((string)($aboutRows[0]['image'] ?? $aboutRows[0][3] ?? ''));
}
$storyRows = count($aboutRows) > 1 ? array_slice($aboutRows, 1) : [];

$coreValues = [
    [
        'title' => 'Uy Tin',
        'desc' => 'Thong tin duoc xac thuc va quy trinh dang tin ro rang, giup ban an tam khi giao dich.',
        'icon' => 'fas fa-shield-alt'
    ],
    [
        'title' => 'Chuyen Nghiep',
        'desc' => 'Doi ngu van hanh va moi gioi dong hanh theo quy trinh chuan hoa va de theo doi.',
        'icon' => 'fas fa-user-tie'
    ],
    [
        'title' => 'Minh Bach',
        'desc' => 'Thong tin gia, vi tri va trang thai tin duoc trinh bay nhat quan, de kiem chung.',
        'icon' => 'fas fa-balance-scale'
    ],
    [
        'title' => 'Hieu Qua',
        'desc' => 'Toi uu hanh trinh tim kiem va ket noi de nguoi dung ra quyet dinh nhanh hon.',
        'icon' => 'fas fa-bolt'
    ]
];
?>

<style>
    .about-page {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        color: #191c1d;
        background: linear-gradient(180deg, #f8f9fa 0%, #f2f4f8 38%, #f8f9fa 100%);
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }
    .about-shell {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }
    .about-hero {
        padding: 110px 0 92px;
        text-align: center;
        position: relative;
        overflow: hidden;
        background:
            linear-gradient(115deg, rgba(0, 24, 71, 0.78), rgba(0, 64, 161, 0.64)),
            url('<?= BASEURL ?>/images/banner/rshmpg.jpg') center/cover no-repeat;
    }
    .about-kicker {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #0056d2;
    }
    .about-title {
        margin: 0;
        color: #ffffff;
        font-size: 44px;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .about-lead {
        margin: 14px auto 0;
        max-width: 70ch;
        color: rgba(255, 255, 255, 0.92);
        font-size: 17px;
        line-height: 1.65;
    }
    .about-overview {
        padding: 64px 0 40px;
    }
    .about-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 40px;
        align-items: center;
    }
    .about-overview-content {
        flex: 1;
    }
    .about-overview-image {
        position: relative;
        min-height: 380px;
        background: #edeeef;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .about-overview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .about-overview-image-fallback {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #424654;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.02em;
        padding: 20px;
        background: #f3f4f5;
    }
    .about-section-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #0056d2;
    }
    .about-section-title {
        margin: 0;
        color: #001847;
        font-size: 34px;
        line-height: 1.25;
        font-weight: 700;
        letter-spacing: -0.01em;
    }
    .about-overview-copy {
        margin-top: 16px;
        color: #424654;
        font-size: 16px;
        line-height: 1.72;
        max-width: 72ch;
    }
    .about-values {
        padding: 10px 0 70px;
    }
    .about-values-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }
    .about-value-card {
        border-radius: 18px;
        border: 1px solid #e1e3e4;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        padding: 22px 18px;
    }
    .about-value-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e8f0ff;
        color: #0040a1;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .about-value-card h3 {
        margin: 0 0 8px;
        color: #191c1d;
        font-size: 21px;
        font-weight: 700;
    }
    .about-value-card p {
        margin: 0;
        color: #424654;
        line-height: 1.65;
    }
    .about-vision {
        padding: 6px 0 74px;
    }
    .about-vision-grid {
        margin-top: 20px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .about-vision-card {
        border-radius: 20px;
        border: 1px solid #e1e3e4;
        background: #ffffff;
        padding: 26px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .about-vision-card h3 {
        margin: 0 0 10px;
        color: #001847;
        font-size: 24px;
        font-weight: 700;
    }
    .about-vision-card p {
        margin: 0;
        color: #424654;
        line-height: 1.72;
    }
    .about-vision-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 24px;
        line-height: 1;
    }
    .about-vision-card:nth-child(1) .about-vision-icon {
        background: #e8f0ff;
        color: #0040a1;
    }
    .about-vision-card:nth-child(2) .about-vision-icon {
        background: #ffe8d8;
        color: #822800;
    }
    .about-grid {
        padding: 0 0 84px;
    }
    .about-section-block {
        margin-bottom: 48px;
        border: 1px solid #e1e3e4;
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        opacity: 0;
        transform: translateY(18px);
        transition: opacity .2s cubic-bezier(0.25, 1, 0.5, 1), transform .2s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .about-section-block.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    .about-section-row {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr);
        min-height: 340px;
    }
    .about-section-row.is-reverse {
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
    }
    .about-section-content {
        padding: 36px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }
    .about-section-heading {
        margin: 0 0 16px;
        color: #191c1d;
        font-size: 30px;
        line-height: 1.3;
        font-weight: 600;
    }
    .about-section-text {
        color: #424654;
        font-size: 16px;
        line-height: 1.72;
        max-width: 70ch;
        word-break: break-word;
    }
    .about-section-text > *:last-child {
        margin-bottom: 0;
    }
    .about-section-media {
        position: relative;
        min-height: 320px;
        background: #edeeef;
    }
    .about-section-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .about-media-fallback {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #424654;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.02em;
        padding: 20px;
        background: #f3f4f5;
    }
    .about-empty {
        margin: 0 auto 84px;
        max-width: 860px;
        border: 1px solid #e1e3e4;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        padding: 42px 28px;
        text-align: center;
    }
    .about-empty h2 {
        margin: 0 0 10px;
        color: #191c1d;
        font-size: 30px;
        line-height: 1.3;
        font-weight: 600;
    }
    .about-empty p {
        margin: 0 auto;
        max-width: 65ch;
        color: #424654;
        line-height: 1.7;
    }
    .about-leadership {
        padding: 0 0 74px;
    }
    .about-leaders-grid {
        margin-top: 20px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .about-leader-card {
        border-radius: 18px;
        border: 1px solid #e1e3e4;
        background: #ffffff;
        padding: 24px;
        text-align: center;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .about-leader-avatar {
        width: 88px;
        height: 88px;
        margin: 0 auto 14px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 8px 22px rgba(8, 30, 70, 0.12);
        display: block;
    }
    .about-leader-fallback {
        width: 88px;
        height: 88px;
        margin: 0 auto 14px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(120deg, #0040a1, #0056d2);
        color: #ffffff;
        font-size: 30px;
        font-weight: 800;
        border: 4px solid #ffffff;
        box-shadow: 0 8px 22px rgba(8, 30, 70, 0.12);
    }
    .about-leader-name {
        margin: 0 0 4px;
        color: #191c1d;
        font-size: 22px;
        line-height: 1.3;
        font-weight: 700;
    }
    .about-leader-role {
        margin: 0;
        color: #0056d2;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .about-leader-meta {
        margin-top: 10px;
        color: #424654;
        font-size: 14px;
        line-height: 1.55;
    }
    .about-cta {
        padding: 0 0 82px;
    }
    .about-cta-shell {
        border-radius: 24px;
        overflow: hidden;
        padding: 50px 30px;
        text-align: center;
        color: #ffffff;
        background:
            linear-gradient(115deg, rgba(0, 24, 71, 0.84), rgba(0, 64, 161, 0.8)),
            url('<?= BASEURL ?>/images/breadcromb.jpg') center/cover no-repeat;
    }
    .about-cta-shell h2 {
        margin: 0 0 10px;
        font-size: 34px;
        line-height: 1.25;
        font-weight: 700;
    }
    .about-cta-shell p {
        margin: 0 auto 20px;
        max-width: 65ch;
        color: rgba(255, 255, 255, 0.92);
        line-height: 1.68;
    }
    .about-cta-actions {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .about-btn {
        border: 0;
        border-radius: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 170px;
        padding: 11px 20px;
        font-weight: 700;
    }
    .about-btn.primary {
        background: #ffffff;
        color: #0040a1;
    }
    .about-btn.secondary {
        background: transparent;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    .about-reveal {
        opacity: 0;
        transform: translateY(18px);
        transition: opacity .2s cubic-bezier(0.25, 1, 0.5, 1), transform .2s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .about-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    @media (max-width: 991px) {
        .about-hero {
            padding: 86px 0 66px;
        }
        .about-title {
            font-size: 36px;
        }
        .about-values-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .about-overview-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        .about-overview-image {
            min-height: 300px;
        }
        .about-vision-grid,
        .about-leaders-grid {
            grid-template-columns: 1fr;
        }
        .about-section-row,
        .about-section-row.is-reverse {
            grid-template-columns: 1fr;
        }
        .about-section-media {
            min-height: 260px;
            order: 1;
        }
        .about-section-content {
            order: 2;
            padding: 30px 24px;
        }
    }
    @media (max-width: 767px) {
        .about-shell {
            padding: 0 16px;
        }
        .about-title {
            font-size: 30px;
        }
        .about-section-title {
            font-size: 28px;
        }
        .about-lead {
            font-size: 16px;
        }
        .about-values-grid {
            grid-template-columns: 1fr;
        }
        .about-grid {
            padding-bottom: 62px;
        }
        .about-section-block {
            margin-bottom: 28px;
        }
        .about-section-heading {
            font-size: 25px;
        }
        .about-cta-shell h2 {
            font-size: 28px;
        }
    }
</style>

<div class="about-page">
    <section class="about-hero">
        <div class="about-shell">
            <span class="about-kicker" style="color:#d4e3ff;">LuxEstate Story</span>
            <h1 class="about-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="about-lead">Nen tang bat dong san hien dai ket hop cong nghe, du lieu da xac thuc va doi ngu chuyen nghiep, giup ban tim dung tai san voi trai nghiem ro rang va nhat quan.</p>
        </div>
    </section>

    <section class="about-overview">
        <div class="about-shell">
            <div class="about-reveal">
                <span class="about-section-label">Tong quan nen tang</span>
                <h2 class="about-section-title">Gia tri ben vung duoc xay dung tu su tin cay</h2>
            </div>
            <div class="about-overview-grid" style="margin-top: 32px;">
                <div class="about-overview-content about-reveal">
                    <div class="about-overview-copy">
                        <?= $overviewHtml !== '' ? htmlspecialchars_decode($overviewHtml) : '<p>LuxEstate duoc xay dung de ket noi nguoi mua, nguoi ban va moi gioi trong cung mot he sinh thai minh bach. Chung toi uu tien chat luong du lieu, tinh ro rang trong thong tin va kha nang ra quyet dinh nhanh cho nguoi dung.</p>' ?>
                    </div>
                </div>
                <div class="about-overview-image about-reveal">
                    <?php if ($overviewImage !== ''): ?>
                        <img src="<?= BASEURL ?>/admin/upload/<?= htmlspecialchars($overviewImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <?php else: ?>
                        <img src="<?= BASEURL ?>/images/banner/rshmpg.jpg" alt="Khong gian song thang chim LuxEstate" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <?php endif; ?>
                    <div class="about-overview-image-fallback">Hinh anh gioi thieu</div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-values">
        <div class="about-shell">
            <div class="about-values-grid">
                <?php foreach ($coreValues as $value): ?>
                    <article class="about-value-card about-reveal">
                        <span class="about-value-icon"><i class="<?= htmlspecialchars($value['icon']) ?>"></i></span>
                        <h3><?= htmlspecialchars($value['title']) ?></h3>
                        <p><?= htmlspecialchars($value['desc']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="about-vision">
        <div class="about-shell">
            <span class="about-section-label">Tam nhin va su menh</span>
            <h2 class="about-section-title">Dinh huong dai han cua LuxEstate</h2>
            <div class="about-vision-grid">
                <article class="about-vision-card about-reveal">
                    <i class="fas fa-eye about-vision-icon"></i>
                    <h3>Tam nhin</h3>
                    <p>Tro thanh nen tang bat dong san duoc uu tien tai Viet Nam, noi nguoi dung co the tim kiem, doi chieu va quyet dinh giao dich tren mot he thong minh bach, de tin va de su dung.</p>
                </article>
                <article class="about-vision-card about-reveal">
                    <i class="fas fa-rocket about-vision-icon"></i>
                    <h3>Su menh</h3>
                    <p>Toi gian hoa hanh trinh bat dong san bang du lieu da xac thuc, bo loc thong minh va doi ngu ho tro chuyen nghiep, de moi nguoi dung dat duoc muc tieu giao dich nhanh va hieu qua.</p>
                </article>
            </div>
        </div>
    </section>

    <?php if (!empty($storyRows)): ?>
        <section class="about-grid">
            <div class="about-shell">
                <?php foreach ($storyRows as $idx => $row): ?>
                    <?php
                        $rowTitle = trim((string)($row['title'] ?? $row[1] ?? ''));
                        $rowContent = (string)($row['content'] ?? $row[2] ?? '');
                        $rowImage = trim((string)($row['image'] ?? $row[3] ?? ''));
                        $heading = $rowTitle !== '' ? $rowTitle : 'Gioi thieu thong tin';
                        $hasImage = $rowImage !== '';
                        $isReverse = $idx % 2 !== 0;
                    ?>
                    <article class="about-section-block about-reveal">
                        <div class="about-section-row <?= $isReverse ? 'is-reverse' : '' ?>">
                            <?php if ($isReverse): ?>
                                <div class="about-section-media">
                                    <?php if ($hasImage): ?>
                                        <img src="<?= BASEURL ?>/admin/upload/<?= htmlspecialchars($rowImage) ?>" alt="<?= htmlspecialchars($heading) ?>" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    <div class="about-media-fallback" style="<?= $hasImage ? '' : 'display:flex;' ?>">Hinh anh dang duoc cap nhat</div>
                                </div>
                            <?php endif; ?>

                            <div class="about-section-content">
                                <h2 class="about-section-heading"><?= htmlspecialchars($heading) ?></h2>
                                <div class="about-section-text">
                                    <?= $rowContent !== '' ? htmlspecialchars_decode($rowContent) : '<p>Noi dung dang duoc cap nhat.</p>' ?>
                                </div>
                            </div>

                            <?php if (!$isReverse): ?>
                                <div class="about-section-media">
                                    <?php if ($hasImage): ?>
                                        <img src="<?= BASEURL ?>/admin/upload/<?= htmlspecialchars($rowImage) ?>" alt="<?= htmlspecialchars($heading) ?>" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    <div class="about-media-fallback" style="<?= $hasImage ? '' : 'display:flex;' ?>">Hinh anh dang duoc cap nhat</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="about-grid">
            <!-- <div class="about-shell">
                <div class="about-empty">
                    <h2>Thong tin gioi thieu dang duoc cap nhat</h2>
                    <p>Chung toi dang bo sung noi dung de ban hieu ro hon ve dinh huong, doi ngu va gia tri cua LuxEstate. Vui long quay lai sau de xem cac cap nhat moi nhat.</p>
                </div>
            </div> -->
        </section>
    <?php endif; ?>

    <section class="about-leadership">
        <div class="about-shell">
            <span class="about-section-label">Doi ngu lanh dao</span>
            <h2 class="about-section-title">Nhung nguoi dan dat chat luong dich vu</h2>
            <div class="about-leaders-grid">
                <?php if (!empty($leaders)): ?>
                    <?php foreach ($leaders as $leader): ?>
                        <?php
                            $leaderName = trim((string)($leader['uname'] ?? 'Thanh vien LuxEstate'));
                            $leaderRoleRaw = strtolower((string)($leader['utype'] ?? 'agent'));
                            $leaderRole = $leaderRoleRaw === 'agent' ? 'Chuyen gia tu van' : ucfirst($leaderRoleRaw);
                            $leaderImage = trim((string)($leader['uimage'] ?? ''));
                            $leaderHasImage = $leaderImage !== '' && file_exists(__DIR__ . '/../../../admin/user/' . $leaderImage);
                            $leaderInitial = $leaderName !== '' ? mb_strtoupper(mb_substr($leaderName, 0, 1)) : 'L';
                        ?>
                        <article class="about-leader-card about-reveal">
                            <?php if ($leaderHasImage): ?>
                                <img class="about-leader-avatar" src="<?= BASEURL ?>/admin/user/<?= htmlspecialchars($leaderImage) ?>" alt="<?= htmlspecialchars($leaderName) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="about-leader-fallback"><?= htmlspecialchars($leaderInitial) ?></div>
                            <?php endif; ?>
                            <h3 class="about-leader-name"><?= htmlspecialchars($leaderName) ?></h3>
                            <p class="about-leader-role"><?= htmlspecialchars($leaderRole) ?></p>
                            <div class="about-leader-meta">
                                <?= !empty($leader['uemail']) ? htmlspecialchars($leader['uemail']) : 'Thong tin lien he dang cap nhat' ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($k = 1; $k <= 3; $k += 1): ?>
                        <article class="about-leader-card about-reveal">
                            <div class="about-leader-fallback">L</div>
                            <h3 class="about-leader-name">Lanh dao <?= $k ?></h3>
                            <p class="about-leader-role">Chuyen gia tu van</p>
                            <div class="about-leader-meta">Thong tin dang duoc cap nhat</div>
                        </article>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="about-cta">
        <div class="about-shell">
            <div class="about-cta-shell about-reveal">
                <h2>San sang tim bat dong san phu hop voi ban?</h2>
                <p>Kham pha danh sach tai san da duoc cap nhat lien tuc, ket noi voi doi ngu LuxEstate va bat dau hanh trinh giao dich voi nen tang minh bach, chuyen nghiep.</p>
                <div class="about-cta-actions">
                    <a class="about-btn primary" href="<?= BASEURL ?>/property/index">Kham pha bat dong san</a>
                    <a class="about-btn secondary" href="<?= BASEURL ?>/page/contact">Lien he tu van</a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    var blocks = document.querySelectorAll('.about-reveal');
    if (!blocks.length || !('IntersectionObserver' in window)) {
        for (var i = 0; i < blocks.length; i += 1) {
            blocks[i].classList.add('is-visible');
        }
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i += 1) {
            if (entries[i].isIntersecting) {
                entries[i].target.classList.add('is-visible');
                observer.unobserve(entries[i].target);
            }
        }
    }, {
        threshold: 0.14,
        rootMargin: '0px 0px -24px 0px'
    });

    for (var j = 0; j < blocks.length; j += 1) {
        observer.observe(blocks[j]);
    }
})();
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
