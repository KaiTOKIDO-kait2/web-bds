<style>
    .site-footer {
        background: #f8f9fa;
        color: #191c1d;
        border-top: 1px solid #e7e8e9;
    }
    .site-footer a {
        color: inherit;
    }
    .site-footer a:hover {
        color: #0056d2;
        text-decoration: none;
    }
    .site-footer .footer-title {
        font-size: 16px;
        font-weight: 800;
        color: #191c1d;
        margin-bottom: 16px;
    }
    .site-footer .footer-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .site-footer .footer-list li + li {
        margin-top: 10px;
    }
    .site-footer .footer-brand {
        max-width: 320px;
    }
    .site-footer .footer-brand p {
        color: #4a4f5f;
        margin-bottom: 18px;
    }
    .site-footer .footer-social a {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e1e3e4;
        margin-right: 10px;
        transition: all .2s ease;
        color: #4a4f5f;
    }
    .site-footer .footer-social a:hover {
        background: #0056d2;
        color: #fff;
        border-color: #0056d2;
    }
    .site-footer .footer-meta {
        border-top: 1px solid #e1e3e4;
        padding-top: 18px;
        margin-top: 28px;
        color: #4a4f5f;
        font-size: 14px;
    }
    .site-footer .footer-meta a {
        color: #191c1d;
    }
    .site-footer .site-logo-img {
        height: 52px;
        width: auto;
        object-fit: contain;
        display: block;
    }
</style>

<footer class="site-footer full-row p-0">
    <div class="container py-5 py-lg-5">
        <div class="row">
            <div class="col-md-12 col-lg-4 mb-4 mb-lg-0">
                <div class="footer-brand">
                    <div class="mb-4">
                        <a href="<?= BASEURL ?>/home/index"><img class="site-logo-img" src="<?= BASEURL ?>/admin/assets/img/logo-1.png" alt="Logo"></a>
                    </div>
                    <p>Nền tảng bất động sản giúp bạn tìm, đăng và quản lý tin nhanh chóng, dễ dàng và hiệu quả.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="RSS"><i class="fas fa-rss"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-4 mb-lg-0">
                <h4 class="footer-title">Hỗ trợ</h4>
                <ul class="footer-list">
                    <li><a href="#">Diễn đàn</a></li>
                    <li><a href="#">Điều khoản và điều kiện</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                    <li><a href="<?= BASEURL ?>/page/contact">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3 mb-4 mb-lg-0">
                <h4 class="footer-title">Liên kết nhanh</h4>
                <ul class="footer-list">
                    <li><a href="<?= BASEURL ?>/page/about">Giới thiệu</a></li>
                    <li><a href="<?= BASEURL ?>/property/index">Bất động sản</a></li>
                    <li><a href="<?= BASEURL ?>/property/create">Đăng tin bất động sản</a></li>
                    <li><a href="<?= BASEURL ?>/agent/index">Môi giới của chúng tôi</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3">
                <h4 class="footer-title">Liên hệ</h4>
                <ul class="footer-list">
                    <li><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Hà Nội, Việt Nam</li>
                    <li><i class="fas fa-phone-alt mr-2 text-primary"></i>+92 302 34 34 418</li>
                    <li><i class="fas fa-envelope mr-2 text-primary"></i>scriptandtools@webpenter.com</li>
                </ul>
            </div>
        </div>

        <div class="footer-meta d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>© <?= date('Y'); ?> Website bất động sản - Phát triển bởi Webpenter.com</div>
            <div class="mt-2 mt-md-0">
                <a href="#">Chính sách bảo mật</a>
                <span class="mx-2">|</span>
                <a href="#">Sơ đồ trang</a>
            </div>
        </div>
    </div>
</footer>
