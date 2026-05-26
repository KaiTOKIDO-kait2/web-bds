<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$cities = isset($data['cities']) && is_array($data['cities']) ? $data['cities'] : [];
$wards = isset($data['wards']) && is_array($data['wards']) ? $data['wards'] : [];
?>

<!-- Submit property -->
<style>
    .property-create-wrapper {
        background: #f8f9fa;
        padding: 50px 0;
        font-family: 'Inter', sans-serif;
    }

    .property-form-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 32px;
        border: 1px solid #e1e3e4;
        margin-bottom: 24px;
    }

    .property-form-title {
        font-size: 2rem;
        font-weight: 700;
        color: #191c1d;
        text-align: center;
        margin-bottom: 40px;
    }

    .form-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #191c1d;
        margin: 0 0 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e1e3e4;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-section-title i {
        color: #0056D2;
        font-size: 1.4rem;
    }

    .property-form-card .form-group.row {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        margin-left: 0;
        margin-right: 0;
    }

    .property-form-card .col-form-label {
        padding-bottom: 8px;
        padding-left: 0;
        padding-right: 0;
        font-weight: 600;
        color: #424654;
        font-size: 14px;
        max-width: 100%;
        flex: 0 0 100%;
        text-align: left;
    }

    .property-form-card .col-lg-9,
    .property-form-card .col-lg-10 {
        max-width: 100%;
        flex: 0 0 100%;
        padding-left: 0;
        padding-right: 0;
    }

    .property-form-card .form-control {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #ffffff;
        color: #191c1d;
        height: auto;
    }

    .property-form-card .form-control:focus {
        border-color: #0056D2;
        box-shadow: 0 0 0 4px rgba(0, 86, 210, 0.1);
        outline: none;
    }

    .property-form-card select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
        padding-right: 40px;
    }

    .file-input-wrapper {
        border: 2px dashed #c3c6d6;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.2s;
        cursor: pointer;
    }

    .file-input-wrapper:hover {
        background: #f3f4f5;
        border-color: #0056D2;
    }

    .property-form-card input[type="file"].form-control {
        padding: 8px;
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .property-form-card .form-check {
        display: flex;
        align-items: center;
        margin-top: 12px;
        padding-left: 0;
    }

    .property-form-card .form-check-input {
        position: static;
        margin-left: 3px;
        margin-top: 0;
        margin-right: 10px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #0056D2;
        border-radius: 4px;
    }

    .property-form-card .form-check-label {
        cursor: pointer;
        font-weight: 500;
        color: #424654;
    }

    .btn-submit-property {
        background: #0056D2;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        padding: 16px 24px;
        border-radius: 12px;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 86, 210, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        margin-top: 24px;
    }

    .btn-submit-property:hover {
        background: #0040a1;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 86, 210, 0.3);
        color: white;
    }
</style>

<div class="full-row property-create-wrapper">
    <div class="container" style="max-width: 1280px;">
        <h2 class="property-form-title">Đăng tin bất động sản</h2>

        <form method="post" action="<?= BASEURL ?>/property/create" enctype="multipart/form-data" class="w-100">
            <?= isset($data['error']) ? $data['error'] : '' ?>
            <?= isset($data['msg']) ? $data['msg'] : '' ?>

            <div class="row">
                <!-- LEFT COLUMN: Main info -->
                <div class="col-lg-8">
                    <!-- Basic Info -->
                    <div class="property-form-card">
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</div>
                        <div class="form-group row">
                            <label class="col-form-label">Tiêu đề tin đăng *</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="title" required
                                    placeholder="VD: Căn hộ Penhouse cao cấp...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label">Loại bất động sản *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="ptype">
                                            <option value="">Chọn loại</option>
                                            <?php
                                            $fallbackTypes = ['Căn hộ chung cư', 'Chung cư mini', 'Nhà', 'Biệt thự', 'Nhà mặt phố', 'Nhà trọ', 'Văn phòng'];
                                            $typeRows = isset($data['propertyTypes']) && is_array($data['propertyTypes']) && !empty($data['propertyTypes'])
                                                ? $data['propertyTypes']
                                                : array_map(function ($name) {
                                                    return ['name' => $name];
                                                }, $fallbackTypes);
                                            ?>
                                            <?php foreach ($typeRows as $typeRow): ?>
                                                <?php $typeName = trim((string) ($typeRow['name'] ?? ''));
                                                if ($typeName === '')
                                                    continue; ?>
                                                <option value="<?= htmlspecialchars($typeName) ?>">
                                                    <?= htmlspecialchars($typeName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label">Hình thức giao dịch *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="stype">
                                            <option value="">Chọn hình thức</option>
                                            <option value="rent">Cho thuê</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label">Mô tả chi tiết</label>
                            <div class="col-lg-9">
                                <textarea class="tinymce form-control" name="content" rows="6" cols="30"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="property-form-card">
                        <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Vị trí & Khu vực</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label">Tỉnh / Thành phố *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="city_id" id="create-city-id" required>
                                            <option value="">Chọn thành phố</option>
                                            <?php foreach ($cities as $city): ?>


                                                <option value="<?= (int) ($city['cid'] ?? 0) ?>">
                                                    <?= htmlspecialchars($city['cname'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="city" id="create-city-name" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-form-label">Quận / Huyện / Phường / Xã *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="ward_id" id="create-ward-id" required>
                                            <option value="">Chọn phường/xã</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label">Địa chỉ chi tiết *</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="loc" required
                                    placeholder="Số nhà, ngõ, tên đường...">
                            </div>
                        </div>
                    </div>

                    <!-- Property specs -->
                    <div class="property-form-card">
                        <div class="form-section-title"><i class="fas fa-ruler-combined"></i> Đặc điểm bất động sản
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Diện tích (m²) *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="asize" min="0" step="any"
                                            required placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Phòng ngủ *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="bed" min="0" step="1" required
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Phòng tắm *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="bath" min="0" step="1" required
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Hướng nhà *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="direction">
                                            <option value="">Chọn hướng</option>
                                            <option value="Đông">Đông</option>
                                            <option value="Tây">Tây</option>
                                            <option value="Nam">Nam</option>
                                            <option value="Bắc">Bắc</option>
                                            <option value="Đông Bắc">Đông Bắc</option>
                                            <option value="Đông Nam">Đông Nam</option>
                                            <option value="Tây Bắc">Tây Bắc</option>
                                            <option value="Tây Nam">Tây Nam</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Phòng khách *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="hall" min="0" step="1" required
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Bếp *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="kitc" min="0" step="1" required
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Ban công *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="balc" min="0" step="1" required
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Tổng số tầng *</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="totalfl" min="1" step="1"
                                            required placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label">Tầng hiện tại *</label>
                                    <div class="col-lg-9">
                                        <input type="hidden" name="floor" id="create-floor-value" value="">
                                        <input type="number" class="form-control" id="create-floor-number" min="0"
                                            step="1" required placeholder="0">
                                        <div class="form-check mt-2 d-flex align-items-center">
                                            <input class="form-check-input mt-0" type="checkbox" value="1"
                                                id="create-floor-all" style="margin-right: 8px;">
                                            <label class="form-check-label mb-0" for="create-floor-all"
                                                style="padding-top: 2px;">Tất cả</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="property-form-card">
                        <div class="form-section-title"><i class="fas fa-check-circle"></i> Tiện ích bổ sung</div>
                        <?php
                        $amenityValues = [
                            'property_age' => '',
                            'swimming_pool' => 1,
                            'parking' => 1,
                            'gym' => 1,
                            'near_school' => 1,
                            'security' => 1,
                            'near_hospital' => 1,
                            'near_market' => 0,
                            'wifi' => 0,
                            'elevator' => 1,
                            'cctv' => 1,
                            'water_source' => 'nuoc_ngam',
                            'frontage_m' => '',
                            'access_road_m' => '',
                            'interior_level' => '',
                        ];
                        require '../app/views/property/partials/amenities_fields.php';
                        ?>
                    </div>

                    <!-- Images -->
                    <div class="property-form-card">
                        <div class="form-section-title"><i class="fas fa-camera"></i> Hình ảnh & Sơ đồ</div>

                        <!-- Main Image Section -->
                        <div class="mb-4">
                            <!-- Main Image (aimage) -->
                            <div class="position-relative mb-3">
                                <div class="file-input-wrapper d-flex flex-column align-items-center justify-content-center overflow-hidden"
                                    style="height: 240px; background-color: #f0f0fb; border: 2px dashed #c3c6d6; border-radius: 12px; cursor: pointer; padding: 20px;">
                                    <i class="fas fa-cloud-upload-alt text-secondary mb-3 icon-placeholder"
                                        style="font-size: 48px; color: #737785 !important;"></i>
                                    <h5 class="font-weight-bold text-dark mb-1 text-placeholder"
                                        style="font-size: 16px;">Kéo và thả ảnh tại đây</h5>
                                    <p class="text-muted small mb-0 text-placeholder" style="font-size: 13px;">Hoặc nhấn
                                        để chọn file từ máy tính</p>
                                    <img class="image-preview position-absolute w-100 h-100"
                                        style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                    <input class="form-control position-absolute w-100 h-100" name="aimage" type="file"
                                        required style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                        onchange="previewImage(this)">
                                </div>
                            </div>

                            <!-- 4 Additional Images in a row (aimage1 to aimage4) -->
                            <div class="row mx-0">
                                <div class="col-3 px-1">
                                    <div class="position-relative d-flex align-items-center justify-content-center"
                                        style="aspect-ratio: 1; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                        <i class="fas fa-camera text-secondary icon-placeholder"
                                            style="font-size: 20px; color: #737785 !important;"></i>
                                        <img class="image-preview position-absolute w-100 h-100"
                                            style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                        <input class="form-control position-absolute w-100 h-100" name="aimage1"
                                            type="file" required
                                            style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                            onchange="previewImage(this)">
                                    </div>
                                </div>
                                <div class="col-3 px-1">
                                    <div class="position-relative d-flex align-items-center justify-content-center"
                                        style="aspect-ratio: 1; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                        <i class="fas fa-camera text-secondary icon-placeholder"
                                            style="font-size: 20px; color: #737785 !important;"></i>
                                        <img class="image-preview position-absolute w-100 h-100"
                                            style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                        <input class="form-control position-absolute w-100 h-100" name="aimage2"
                                            type="file" required
                                            style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                            onchange="previewImage(this)">
                                    </div>
                                </div>
                                <div class="col-3 px-1">
                                    <div class="position-relative d-flex align-items-center justify-content-center"
                                        style="aspect-ratio: 1; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                        <i class="fas fa-camera text-secondary icon-placeholder"
                                            style="font-size: 20px; color: #737785 !important;"></i>
                                        <img class="image-preview position-absolute w-100 h-100"
                                            style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                        <input class="form-control position-absolute w-100 h-100" name="aimage3"
                                            type="file" required
                                            style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                            onchange="previewImage(this)">
                                    </div>
                                </div>
                                <div class="col-3 px-1">
                                    <div class="position-relative d-flex align-items-center justify-content-center"
                                        style="aspect-ratio: 1; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                        <i class="fas fa-camera text-secondary icon-placeholder"
                                            style="font-size: 20px; color: #737785 !important;"></i>
                                        <img class="image-preview position-absolute w-100 h-100"
                                            style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                        <input class="form-control position-absolute w-100 h-100" name="aimage4"
                                            type="file" required
                                            style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                            onchange="previewImage(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" style="border-color: #e1e3e4;">

                        <div class="row mx-0">
                            <div class="col-4 px-2">
                                <label class="col-form-label pb-2 text-center w-100"
                                    style="font-size: 13px; font-weight: 500;">Sơ đồ tổng thể</label>
                                <div class="position-relative d-flex align-items-center justify-content-center"
                                    style="aspect-ratio: 4/3; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                    <i class="fas fa-camera text-secondary icon-placeholder"
                                        style="font-size: 24px; color: #737785 !important;"></i>
                                    <img class="image-preview position-absolute w-100 h-100"
                                        style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                    <input class="form-control position-absolute w-100 h-100" name="fimage" type="file"
                                        style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                        onchange="previewImage(this)">
                                </div>
                            </div>
                            <div class="col-4 px-2">
                                <label class="col-form-label pb-2 text-center w-100"
                                    style="font-size: 13px; font-weight: 500;">MB tầng hầm</label>
                                <div class="position-relative d-flex align-items-center justify-content-center"
                                    style="aspect-ratio: 4/3; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                    <i class="fas fa-camera text-secondary icon-placeholder"
                                        style="font-size: 24px; color: #737785 !important;"></i>
                                    <img class="image-preview position-absolute w-100 h-100"
                                        style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                    <input class="form-control position-absolute w-100 h-100" name="fimage1" type="file"
                                        style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                        onchange="previewImage(this)">
                                </div>
                            </div>
                            <div class="col-4 px-2">
                                <label class="col-form-label pb-2 text-center w-100"
                                    style="font-size: 13px; font-weight: 500;">MB tầng trệt</label>
                                <div class="position-relative d-flex align-items-center justify-content-center"
                                    style="aspect-ratio: 4/3; background-color: #f0f0fb; border: 1px solid #e1e2ec; border-radius: 12px; overflow: hidden; cursor: pointer;">
                                    <i class="fas fa-camera text-secondary icon-placeholder"
                                        style="font-size: 24px; color: #737785 !important;"></i>
                                    <img class="image-preview position-absolute w-100 h-100"
                                        style="object-fit: cover; display: none; z-index: 5;" src="" alt="">
                                    <input class="form-control position-absolute w-100 h-100" name="fimage2" type="file"
                                        style="opacity: 0; top: 0; left: 0; cursor: pointer; z-index: 10;"
                                        onchange="previewImage(this)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Sidebar (Price & Status) -->
                <div class="col-lg-4">
                    <div class="property-form-card sticky-top" style="top: 100px; z-index: 10;">
                        <div class="form-section-title"><i class="fas fa-tags"></i> Giá & Trạng thái</div>

                        <div class="form-group row">
                            <label class="col-form-label" style="font-size: 16px;">Giá niêm yết (VNĐ) *</label>
                            <div class="col-lg-9 position-relative">
                                <input type="hidden" name="price" id="create-price-value" value="">
                                <input type="number" class="form-control" id="create-price-number" min="0" step="1"
                                    required placeholder="0" style="font-size: 1.25rem; font-weight: 700; color: red;">
                            </div>
                        </div>

                        <hr class="my-4" style="border-color: #e1e3e4;">

                        <!-- <div class="form-group row">
                            <label class="col-form-label">Trạng thái tin đăng *</label>
                            <div class="col-lg-9">
                                <select class="form-control" required name="status">
                                    <option value="available" selected>Còn sẵn</option>
                                    <option value="sold out">Đã bán</option>
                                </select>
                            </div>
                        </div> -->

                        <div class="form-group row mt-3">
                            <label class="col-form-label">Đánh dấu nổi bật?</label>
                            <div class="col-lg-9">
                                <div class="form-check d-flex align-items-center">
                                    <input type="hidden" name="isFeatured" value="0">
                                    <input class="form-check-input mt-0" type="checkbox" name="isFeatured" value="1"
                                        id="isFeaturedCheck" style="margin-right: 8px;">
                                    <label class="form-check-label mb-0" for="isFeaturedCheck"
                                        style="padding-top: 2px;">Đăng làm tin nổi bật</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit-property" name="add">
                            <i class="fas fa-paper-plane"></i> Đăng bài ngay
                        </button>

                        <p class="text-center text-muted mt-3" style="font-size: 12px;">Bằng cách đăng bài, bạn xác nhận
                            mọi thông tin là chính xác.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Submit property -->

<?php require_once '../app/views/layouts/footer.php'; ?>

<script>
    (function () {
        var citySelect = document.getElementById('create-city-id');
        var wardSelect = document.getElementById('create-ward-id');
        var cityNameInput = document.getElementById('create-city-name');
        var floorValueInput = document.getElementById('create-floor-value');
        var floorNumberInput = document.getElementById('create-floor-number');
        var floorAllCheckbox = document.getElementById('create-floor-all');
        var priceValueInput = document.getElementById('create-price-value');
        var priceNumberInput = document.getElementById('create-price-number');
        var priceAgreeCheckbox = document.getElementById('create-price-agree');
        if (!citySelect || !wardSelect) {
            return;
        }

        var allWards = <?= json_encode(array_map(function ($item) {
            return [
                'id' => (int) ($item['ward_id'] ?? $item['wid'] ?? 0),
                'name' => (string) ($item['ward_name'] ?? $item['wname'] ?? ''),
                'city_id' => (int) ($item['city_id'] ?? 0),
            ];
        }, $wards ?? []), JSON_UNESCAPED_UNICODE) ?>;

        function fillWards() {
            var selectedCityId = parseInt(citySelect.value || '0', 10);
            wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

            allWards.forEach(function (item) {
                if (selectedCityId > 0 && item.city_id === selectedCityId) {
                    var opt = document.createElement('option');
                    opt.value = String(item.id);
                    opt.textContent = item.name;
                    wardSelect.appendChild(opt);
                }
            });
        }

        function syncCityState() {
            var selectedCityId = parseInt(citySelect.value || '0', 10);
            var selectedCityText = citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '';
            cityNameInput.value = selectedCityId > 0 ? selectedCityText : '';
            fillWards();
        }

        citySelect.addEventListener('change', syncCityState);
        syncCityState();

        var normalizeNumberInputs = function () {
            document.querySelectorAll('input[type="number"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    if (input.value === '') {
                        return;
                    }

                    var currentValue = Number(input.value);
                    if (!Number.isFinite(currentValue)) {
                        return;
                    }

                    var minValue = input.min !== '' ? Number(input.min) : 0;
                    if (currentValue < minValue) {
                        input.value = String(minValue);
                    }
                });
            });
        };

        if (floorNumberInput && floorAllCheckbox) {
            var syncFloorState = function () {
                if (floorAllCheckbox.checked) {
                    floorValueInput.value = 'Tất cả';
                    floorNumberInput.disabled = true;
                    floorNumberInput.required = false;
                } else {
                    floorNumberInput.disabled = false;
                    floorNumberInput.required = true;
                    floorValueInput.value = floorNumberInput.value ? floorNumberInput.value : '';
                }
            };

            floorAllCheckbox.addEventListener('change', syncFloorState);
            floorNumberInput.addEventListener('input', function () {
                if (!floorAllCheckbox.checked) {
                    floorValueInput.value = floorNumberInput.value;
                }
            });
            syncFloorState();
        }

        if (priceNumberInput && priceAgreeCheckbox) {
            var syncPriceState = function () {
                if (priceAgreeCheckbox.checked) {
                    priceValueInput.value = 'Thỏa thuận';
                    priceNumberInput.disabled = true;
                    priceNumberInput.required = false;
                } else {
                    priceNumberInput.disabled = false;
                    priceNumberInput.required = true;
                    priceValueInput.value = priceNumberInput.value;
                }
            };

            priceAgreeCheckbox.addEventListener('change', syncPriceState);
            priceNumberInput.addEventListener('input', function () {
                if (!priceAgreeCheckbox.checked) {
                    priceValueInput.value = priceNumberInput.value;
                }
            });
            syncPriceState();
        }

        normalizeNumberInputs();
    })();

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var container = input.parentElement;
                var img = container.querySelector('.image-preview');
                var placeholders = container.querySelectorAll('.icon-placeholder, .text-placeholder');

                if (img) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                }

                if (placeholders) {
                    placeholders.forEach(function (el) {
                        el.style.display = 'none';
                    });
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>