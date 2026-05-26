<?php require_once '../app/views/layouts/header.php'; ?>

<?php
$formData = isset($data['formData']) ? $data['formData'] : [];
$msg = isset($data['msg']) ? $data['msg'] : '';
$error = isset($data['error']) ? $data['error'] : '';
?>

<style>
    .contact-page {
        width: 100%;
        background: linear-gradient(180deg, #f8f9fa 0%, #f2f4f8 38%, #f8f9fa 100%);
        font-family: 'Inter', 'Segoe UI', sans-serif;
        color: #191c1d;
    }
    .contact-shell {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }
    
    /* Hero Section */
    .contact-hero {
        padding: 110px 0 92px;
        text-align: center;
        position: relative;
        overflow: hidden;
        background:
            linear-gradient(115deg, rgba(0, 24, 71, 0.78), rgba(0, 64, 161, 0.64)),
            url('<?= BASEURL ?>/images/banner/rshmpg.jpg') center/cover no-repeat;
    }
    .contact-kicker {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #d4e3ff;
    }
    .contact-title {
        margin: 0;
        color: #ffffff;
        font-size: 44px;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .contact-lead {
        margin: 14px auto 0;
        max-width: 70ch;
        color: rgba(255, 255, 255, 0.92);
        font-size: 17px;
        line-height: 1.65;
    }

    /* Main Content Grid */
    .contact-container {
        padding: 80px 0;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 40px;
        align-items: flex-start;
    }

    /* Left Panel: Info + Map */
    .contact-info-panel {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }

    /* Contact Info Block */
    .contact-info-block {
        background: #ffffff;
        border: 1px solid #e1e3e4;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .contact-info-block h3 {
        margin: 0 0 24px;
        color: #001847;
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .contact-info-item {
        margin-bottom: 24px;
        display: flex;
        gap: 16px;
    }
    .contact-info-item:last-child {
        margin-bottom: 0;
    }
    .contact-info-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #e8f0ff;
        color: #0040a1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .contact-info-content {
        flex: 1;
        min-width: 0;
    }
    .contact-info-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #0056d2;
        margin-bottom: 4px;
    }
    .contact-info-value {
        font-size: 16px;
        font-weight: 500;
        color: #191c1d;
        word-break: break-word;
    }
    .contact-info-value a {
        color: #0040a1;
        text-decoration: none;
    }
    .contact-info-value a:hover {
        text-decoration: underline;
    }

    /* Response Badge */
    .contact-response-badge {
        background: #ffdbcf;
        border: 1px solid #ffd4c2;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
    }
    .contact-response-badge i {
        color: #a93802;
        font-size: 18px;
    }
    .contact-response-badge span {
        color: #a93802;
        font-size: 14px;
        font-weight: 600;
    }

    /* Social Links */
    .contact-socials {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }
    .contact-social-link {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #e8f0ff;
        color: #0040a1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 20px;
        transition: all 200ms cubic-bezier(0.25, 1, 0.5, 1);
    }
    .contact-social-link:hover {
        background: #0040a1;
        color: #ffffff;
    }

    /* Map Container */
    .contact-map-container {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: #edeeef;
        border: 1px solid #e1e3e4;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        height: 400px;
    }
    .contact-map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    .contact-map-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f5;
        color: #424654;
        font-size: 14px;
        text-align: center;
    }

    /* Right Panel: Form */
    .contact-form-container {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        border: 1px solid #e1e3e4;
    }
    .contact-form-container h3 {
        margin: 0 0 28px;
        color: #001847;
        font-size: 24px;
        font-weight: 700;
    }

    /* Form Group */
    .contact-form-group {
        margin-bottom: 24px;
    }
    .contact-form-group:last-of-type {
        margin-bottom: 32px;
    }
    .contact-form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #191c1d;
        margin-bottom: 8px;
    }
    .contact-form-group input,
    .contact-form-group textarea,
    .contact-form-group select {
        width: 100%;
        padding: 12px 16px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 16px;
        border: 1px solid #e1e3e4;
        border-radius: 12px;
        color: #191c1d;
        background: #ffffff;
        transition: all 200ms cubic-bezier(0.25, 1, 0.5, 1);
    }
    .contact-form-group input:focus,
    .contact-form-group textarea:focus,
    .contact-form-group select:focus {
        outline: none;
        border-color: #0040a1;
        box-shadow: 0 0 0 3px rgba(0, 64, 161, 0.1);
    }
    .contact-form-group input::placeholder {
        color: #424654;
    }
    .contact-form-group textarea {
        resize: vertical;
        min-height: 120px;
    }
    .contact-form-group.error input,
    .contact-form-group.error textarea,
    .contact-form-group.error select {
        border-color: #ba1a1a;
        background-color: rgba(186, 26, 26, 0.04);
    }
    .contact-form-error {
        font-size: 13px;
        color: #ba1a1a;
        margin-top: 6px;
        display: none;
    }
    .contact-form-group.error .contact-form-error {
        display: block;
    }

    /* Submit Button */
    .contact-submit {
        width: 100%;
        padding: 12px 24px;
        background: #0040a1;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 200ms cubic-bezier(0.25, 1, 0.5, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 48px;
    }
    .contact-submit:hover:not(:disabled) {
        background: #0056d2;
    }
    .contact-submit:active:not(:disabled) {
        transform: scale(0.98);
    }
    .contact-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .contact-submit-spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    .contact-submit.loading .contact-submit-spinner {
        display: block;
    }
    .contact-submit.loading .contact-submit-text {
        display: none;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Notification Messages */
    .contact-notification {
        margin-bottom: 24px;
        padding: 16px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.6;
        display: none;
        animation: slideIn 300ms cubic-bezier(0.25, 1, 0.5, 1);
    }
    .contact-notification.show {
        display: block;
    }
    .contact-notification.success {
        background: #e8f5e9;
        border: 1px solid #c8e6c9;
        color: #1b5e20;
    }
    .contact-notification.error {
        background: #ffebee;
        border: 1px solid #ffcdd2;
        color: #b71c1c;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 991px) {
        .contact-hero {
            padding: 86px 0 66px;
        }
        .contact-title {
            font-size: 36px;
        }
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 32px;
        }
        .contact-map-container {
            height: 320px;
        }
        .contact-form-container {
            padding: 32px;
        }
    }

    @media (max-width: 767px) {
        .contact-shell {
            padding: 0 16px;
        }
        .contact-hero {
            padding: 60px 0 48px;
        }
        .contact-title {
            font-size: 32px;
        }
        .contact-lead {
            font-size: 16px;
        }
        .contact-container {
            padding: 48px 0;
        }
        .contact-grid {
            gap: 24px;
        }
        .contact-info-block {
            padding: 24px;
        }
        .contact-info-block h3 {
            font-size: 20px;
        }
        .contact-form-container {
            padding: 24px;
        }
        .contact-form-container h3 {
            font-size: 20px;
        }
        .contact-map-container {
            height: 280px;
        }
    }
</style>

<div class="contact-page">
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="contact-shell">
            <span class="contact-kicker">SẴN SÀNG LIÊN HỆ?</span>
            <h1 class="contact-title">Hãy để chúng tôi giúp bạn</h1>
            <p class="contact-lead">Chuyên gia tư vấn LuxEstate sẵn sàng hỗ trợ bạn 24/7. Phản hồi trong 30 phút hoặc ít hơn.</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="contact-container">
        <div class="contact-shell">
            <div class="contact-grid">
                <!-- Left Panel: Contact Info + Map -->
                <div class="contact-info-panel">
                    <!-- Contact Information Block -->
                    <div class="contact-info-block">
                        <h3><i class="fas fa-info-circle"></i> Thông tin liên hệ</h3>
                        
                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-info-content">
                                <div class="contact-info-label">Gọi cho chúng tôi</div>
                                <div class="contact-info-value">
                                    <a href="tel:+84208322224">+84 (0)208 3222 224</a>
                                </div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info-content">
                                <div class="contact-info-label">Gửi email</div>
                                <div class="contact-info-value">
                                    <a href="mailto:contact@luxestate.com">contact@luxestate.com</a>
                                </div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info-content">
                                <div class="contact-info-label">Địa chỉ</div>
                                <div class="contact-info-value">123 Đinh Công Tráng, TP. Thái Nguyên, Thái Nguyên 280000, Việt Nam</div>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-info-content">
                                <div class="contact-info-label">Giờ làm việc</div>
                                <div class="contact-info-value">
                                    Thứ Hai - Thứ Sáu: 8:00 - 18:00<br>
                                    Thứ Bảy: 9:00 - 17:00<br>
                                    Chủ Nhật: Đóng cửa
                                </div>
                            </div>
                        </div>

                        <div class="contact-response-badge">
                            <i class="fas fa-bolt"></i>
                            <span>Phản hồi trong &lt; 30 phút</span>
                        </div>

                        <div class="contact-socials">
                            <a href="https://facebook.com/luxestate" class="contact-social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://zalo.me/luxestate" class="contact-social-link" title="Zalo">
                                <i class="fab fa-zalo" style="font-family: serif;">Z</i>
                            </a>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="contact-map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3583.5968314881803!2d105.84088!3d21.593!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab9bd9999999%3A0x1234567890abcdef!2sLuxEstate%20Office!5e0!3m2!1svi!2svn!4v1234567890" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <!-- Right Panel: Form -->
                <div class="contact-form-container">
                    <h3>Gửi yêu cầu của bạn</h3>

                    <?php if($msg): ?>
                        <div class="contact-notification success show">
                            <strong>✓ Thành công!</strong> <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="contact-notification error show">
                            <strong>⚠ Lỗi:</strong> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form id="contactForm" class="contact-form" action="<?= BASEURL ?>/page/contact" method="POST" novalidate>
                        <div class="contact-form-group">
                            <label for="contactName">Tên của bạn *</label>
                            <input 
                                type="text" 
                                id="contactName" 
                                name="name" 
                                placeholder="Nhập tên đầy đủ"
                                value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                required
                            >
                            <div class="contact-form-error">Vui lòng nhập tên</div>
                        </div>

                        <div class="contact-form-group">
                            <label for="contactEmail">Email *</label>
                            <input 
                                type="email" 
                                id="contactEmail" 
                                name="email" 
                                placeholder="Nhập email của bạn"
                                value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                                required
                            >
                            <div class="contact-form-error">Email không hợp lệ</div>
                        </div>

                        <div class="contact-form-group">
                            <label for="contactPhone">Số điện thoại *</label>
                            <input 
                                type="tel" 
                                id="contactPhone" 
                                name="phone" 
                                placeholder="Nhập số điện thoại"
                                value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                                required
                            >
                            <div class="contact-form-error">Vui lòng nhập số điện thoại</div>
                        </div>

                        <div class="contact-form-group">
                            <label for="contactUserType">Bạn là... *</label>
                            <select 
                                id="contactUserType" 
                                name="user_type"
                                required
                            >
                                <option value="buyer" <?= ($formData['user_type'] ?? '') === 'buyer' ? 'selected' : '' ?>>Người mua</option>
                                <option value="renter" <?= ($formData['user_type'] ?? '') === 'renter' ? 'selected' : '' ?>>Người thuê</option>
                                <option value="owner" <?= ($formData['user_type'] ?? '') === 'owner' ? 'selected' : '' ?>>Chủ nhà</option>
                                <option value="broker" <?= ($formData['user_type'] ?? '') === 'broker' ? 'selected' : '' ?>>Môi giới</option>
                            </select>
                            <div class="contact-form-error">Vui lòng chọn loại hình</div>
                        </div>

                        <div class="contact-form-group">
                            <label for="contactSubject">Chủ đề</label>
                            <input 
                                type="text" 
                                id="contactSubject" 
                                name="subject" 
                                placeholder="Chủ đề của bạn"
                                value="<?= htmlspecialchars($formData['subject'] ?? '') ?>"
                            >
                        </div>

                        <div class="contact-form-group">
                            <label for="contactMessage">Tin nhắn *</label>
                            <textarea 
                                id="contactMessage" 
                                name="message" 
                                placeholder="Mô tả chi tiết vấn đề hoặc yêu cầu của bạn..."
                                required
                            ><?= htmlspecialchars($formData['message'] ?? '') ?></textarea>
                            <div class="contact-form-error">Tin nhắn phải có ít nhất 10 ký tự</div>
                        </div>

                        <button type="submit" name="send" value="1" class="contact-submit">
                            <span class="contact-submit-spinner"></span>
                            <span class="contact-submit-text">Gửi yêu cầu</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function() {
    const form = document.getElementById('contactForm');
    const submitBtn = form.querySelector('.contact-submit');

    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const fields = form.querySelectorAll('[required]');

        // Clear previous errors
        form.querySelectorAll('.contact-form-group').forEach(group => {
            group.classList.remove('error');
        });

        // Validate each field
        fields.forEach(field => {
            const group = field.closest('.contact-form-group');
            let fieldValid = true;
            let errorMsg = '';

            if(field.name === 'name' && !field.value.trim()) {
                fieldValid = false;
                errorMsg = 'Vui lòng nhập tên';
            } else if(field.name === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!field.value.trim() || !emailRegex.test(field.value.trim())) {
                    fieldValid = false;
                    errorMsg = 'Email không hợp lệ';
                }
            } else if(field.name === 'phone' && !field.value.trim()) {
                fieldValid = false;
                errorMsg = 'Vui lòng nhập số điện thoại';
            } else if(field.name === 'message') {
                if(!field.value.trim() || field.value.trim().length < 10) {
                    fieldValid = false;
                    errorMsg = 'Tin nhắn phải có ít nhất 10 ký tự';
                }
            }

            if(!fieldValid) {
                group.classList.add('error');
                group.querySelector('.contact-form-error').textContent = errorMsg;
                isValid = false;
            }
        });

        if(!isValid) {
            e.preventDefault();
        } else {
            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });

    // Clear error on field change
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('change', function() {
            const group = this.closest('.contact-form-group');
            if(group) group.classList.remove('error');
        });
    });
})();
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
