<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Bất động sản</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Bất động sản</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật thông tin bất động sản</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    
                    <form method="post" enctype="multipart/form-data" action="" id="admin-property-edit-form" onsubmit="return syncFormDataBeforeSubmit()">
                    <?php if(isset($property) && !empty($property)): $row = $property; ?>
                    <div class="card-body">
                        <h5 class="card-title">Thông tin bất động sản</h5>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label">Tiêu đề</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="title" required value="<?= htmlspecialchars($row['title']) ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label">Nội dung</label>
                                    <div class="col-lg-9">
                                        <textarea class="tinymce form-control" name="content" rows="10" cols="30"><?= htmlspecialchars_decode($row['pcontent']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Loại bất động sản</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="ptype">
                                            <option value="">Chọn loại</option>
                                            <?php
                                                $fallbackTypes = ['Căn hộ chung cư', 'Chung cư mini', 'Nhà', 'Biệt thự', 'Nhà mặt phố', 'Nhà trọ', 'Văn phòng'];
                                                $typeRows = isset($propertyTypes) && is_array($propertyTypes) && !empty($propertyTypes)
                                                    ? $propertyTypes
                                                    : array_map(function ($name) {
                                                        return ['name' => $name];
                                                    }, $fallbackTypes);
                                            ?>
                                            <?php foreach ($typeRows as $typeRow): ?>
                                                <?php $typeName = trim((string)($typeRow['name'] ?? '')); if ($typeName === '') continue; ?>
                                                <option value="<?= htmlspecialchars($typeName) ?>" <?= ($row['type'] ?? '') == $typeName ? 'selected' : '' ?>><?= htmlspecialchars($typeName) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Hình thức</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="stype">
                                            <option value="">Chọn hình thức</option>
                                            <option value="rent" <?= $row['stype']=='rent'?'selected':'' ?>>Cho thuê</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Phòng tắm</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="bath" required value="<?= htmlspecialchars($row['bathroom']) ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Bếp</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="kitc" required value="<?= htmlspecialchars($row['kitchen']) ?>">
                                    </div>
                                </div>
                            </div>   
                            <div class="col-xl-6">
                                <div class="form-group row mb-3">
                                    <label class="col-lg-3 col-form-label">Hướng nhà</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="direction">
                                            <option value="">Chọn hướng</option>
                                            <option value="Đông" <?= ($row['direction'] ?? '') == 'Đông' ? 'selected' : '' ?>>Đông</option>
                                            <option value="Tây" <?= ($row['direction'] ?? '') == 'Tây' ? 'selected' : '' ?>>Tây</option>
                                            <option value="Nam" <?= ($row['direction'] ?? '') == 'Nam' ? 'selected' : '' ?>>Nam</option>
                                            <option value="Bắc" <?= ($row['direction'] ?? '') == 'Bắc' ? 'selected' : '' ?>>Bắc</option>
                                            <option value="Đông Bắc" <?= ($row['direction'] ?? '') == 'Đông Bắc' ? 'selected' : '' ?>>Đông Bắc</option>
                                            <option value="Đông Nam" <?= ($row['direction'] ?? '') == 'Đông Nam' ? 'selected' : '' ?>>Đông Nam</option>
                                            <option value="Tây Bắc" <?= ($row['direction'] ?? '') == 'Tây Bắc' ? 'selected' : '' ?>>Tây Bắc</option>
                                            <option value="Tây Nam" <?= ($row['direction'] ?? '') == 'Tây Nam' ? 'selected' : '' ?>>Tây Nam</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Phòng ngủ</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="bed" required value="<?= htmlspecialchars($row['bedroom']) ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Ban công</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="balc" required value="<?= htmlspecialchars($row['balcony']) ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Phòng khách</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="hall" required value="<?= htmlspecialchars($row['hall']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h4 class="card-title">Giá và vị trí</h4>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Tầng</label>
                                    <div class="col-lg-9">
                                        <?php
                                            $floorValue = trim((string)($row['floor'] ?? ''));
                                            $floorNumberValue = preg_match('/\d+/', $floorValue, $floorMatch) ? (int)$floorMatch[0] : '';
                                            $floorIsAll = $floorValue === 'Tất cả';
                                        ?>
                                        <input type="hidden" name="floor" id="admin-edit-floor-value" value="<?= htmlspecialchars($floorValue) ?>">
                                        <input type="number" class="form-control" id="admin-edit-floor-number" min="0" step="1" value="<?= htmlspecialchars((string)$floorNumberValue) ?>" required placeholder="Nhập số tầng">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="admin-edit-floor-all" <?= $floorIsAll ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="admin-edit-floor-all">Tất cả</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Giá</label>
                                    <div class="col-lg-9">
                                        <?php $priceIsNegotiable = (float)($row['price'] ?? 0) <= 0; ?>
                                        <input type="hidden" name="price" id="admin-edit-price-value" value="<?= htmlspecialchars((string)($row['price'] ?? '')) ?>">
                                        <input type="number" class="form-control" id="admin-edit-price-number" min="0" step="1" value="<?= htmlspecialchars((string)$row['price']) ?>" required placeholder="Nhập giá">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="admin-edit-price-agree" <?= $priceIsNegotiable ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="admin-edit-price-agree">Thỏa thuận</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Thành phố</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="city_id" id="admin-edit-city-id" required>
                                            <option value="">Chọn thành phố</option>
                                            <?php if(isset($cities) && is_array($cities)): foreach($cities as $city): ?>
                                                <?php $cid = (int)($city['cid'] ?? 0); ?>
                                                <option value="<?= $cid ?>" <?= (int)($row['city_id'] ?? 0) === $cid ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($city['cname'] ?? '') ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Phường/Xã</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="ward_id" id="admin-edit-ward-id" required>
                                            <option value="">Chọn phường/xã</option>
                                            <?php if(isset($wards) && is_array($wards)): foreach($wards as $ward): ?>
                                                <?php $wid = (int)($ward['ward_id'] ?? ($ward['wid'] ?? 0)); ?>
                                                <option value="<?= $wid ?>" data-city-id="<?= (int)($ward['city_id'] ?? 0) ?>" <?= (int)($row['ward_id'] ?? 0) === $wid ? 'selected' : '' ?> style="display:none;">
                                                    <?= htmlspecialchars($ward['ward_name'] ?? ($ward['wname'] ?? '')) ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                               <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tổng số tầng</label>
                                        <div class="col-lg-9">
                                            <?php
                                                $totalFloorValue = trim((string)($row['totalfloor'] ?? ''));
                                                $totalFloorNumberValue = preg_match('/\d+/', $totalFloorValue, $totalFloorMatch) ? (int)$totalFloorMatch[0] : '';
                                            ?>
                                            <input type="number" class="form-control" name="totalfl" min="1" step="1" required value="<?= htmlspecialchars((string)$totalFloorNumberValue) ?>" placeholder="Nhập tổng số tầng">
                                        </div>
                                    </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Diện tích (sqft)</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="asize" required value="<?= htmlspecialchars($row['size']) ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Địa chỉ</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="loc" required value="<?= htmlspecialchars($row['location']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php
                            $amenityValues = [
                                'property_age' => $row['property_age'] ?? '',
                                'swimming_pool' => $row['swimming_pool'] ?? 0,
                                'parking' => $row['parking'] ?? 0,
                                'gym' => $row['gym'] ?? 0,
                                'near_school' => $row['near_school'] ?? 0,
                                'security' => $row['security'] ?? 0,
                                'near_hospital' => $row['near_hospital'] ?? 0,
                                'near_market' => $row['near_market'] ?? 0,
                                'wifi' => $row['wifi'] ?? 0,
                                'elevator' => $row['elevator'] ?? 0,
                                'cctv' => $row['cctv'] ?? 0,
                                'water_source' => $row['water_source'] ?? '',
                                'frontage_m' => $row['frontage_m'] ?? '',
                                'access_road_m' => $row['access_road_m'] ?? '',
                                'interior_level' => $row['interior_level'] ?? '',
                            ];
                            require '../app/views/property/partials/amenities_fields.php';
                        ?>
                                
                        <h4 class="card-title">Hình ảnh và trạng thái</h4>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Image</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="aimage" type="file">
                                        <?php if (!empty(trim((string)($row['pimage'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage']) ?>" alt="pimage" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Image 2</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="aimage2" type="file">
                                        <?php if (!empty(trim((string)($row['pimage2'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage2']) ?>" alt="pimage2" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Image 4</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="aimage4" type="file">
                                        <?php if (!empty(trim((string)($row['pimage4'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage4']) ?>" alt="pimage4" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Status</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="status">
                                            <option value="available" <?= $row['status']=='available'?'selected':'' ?>>Còn trống</option>
                                            <option value="rented" <?= in_array($row['status'],['rented','sold out'])?'selected':'' ?>>Đã cho thuê</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Mặt bằng tầng hầm</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="fimage1" type="file">
                                        <?php if (!empty(trim((string)($row['topmapimage'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['topmapimage']) ?>" alt="topmapimage" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Image 1</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="aimage1" type="file">
                                        <?php if (!empty(trim((string)($row['pimage1'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage1']) ?>" alt="pimage1" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Ảnh 3</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="aimage3" type="file">
                                        <?php if (!empty(trim((string)($row['pimage3'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['pimage3']) ?>" alt="pimage3" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Người đại diện</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="uid" required>
                                            <option value="">Chọn chủ sở hữu</option>
                                            <?php if (isset($users)): foreach ($users as $u): ?>
                                            <option value="<?= (int)$u['uid'] ?>" <?= (int)($row['uid'] ?? 0) === (int)$u['uid'] ? 'selected' : '' ?>><?= htmlspecialchars((string)($u['uname'] ?? '')) ?> (ID: <?= (int)$u['uid'] ?>)</option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Ảnh sơ đồ</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="fimage" type="file">
                                        <?php if (!empty(trim((string)($row['mapimage'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['mapimage']) ?>" alt="mapimage" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label">Mặt bằng tầng trệt</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" name="fimage2" type="file">
                                        <?php if (!empty(trim((string)($row['groundmapimage'] ?? '')))): ?>
                                        <img src="<?= BASEURL ?>/admin/property/<?= htmlspecialchars($row['groundmapimage']) ?>" alt="groundmapimage" height="150" width="180" class="mt-2">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label"><b>Có nổi bật không?</b></label>
                                    <div class="col-lg-9">
                                        <select class="form-control" required name="isFeatured">
                                            <option value="0" <?= $row['isFeatured']=='0'?'selected':'' ?>>Không</option>
                                            <option value="1" <?= $row['isFeatured']=='1'?'selected':'' ?>>Có</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="submit" value="Lưu thay đổi" class="btn btn-primary" name="update" style="margin-left:200px;">
                    </div>
                    <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>			
</div>
<!-- /Main Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>

<script>
window.syncFormDataBeforeSubmit = function() {
    var floorValueInput = document.getElementById('admin-edit-floor-value');
    var floorNumberInput = document.getElementById('admin-edit-floor-number');
    var floorAllCheckbox = document.getElementById('admin-edit-floor-all');
    var priceValueInput = document.getElementById('admin-edit-price-value');
    var priceNumberInput = document.getElementById('admin-edit-price-number');
    var priceAgreeCheckbox = document.getElementById('admin-edit-price-agree');

    // Sync floor: use number input or 'Tất cả', fallback to current hidden value
    if (floorValueInput && floorNumberInput && floorAllCheckbox) {
        if (floorAllCheckbox.checked) {
            floorValueInput.value = 'Tất cả';
        } else {
            floorValueInput.value = (floorNumberInput.value || floorValueInput.value || '');
        }
    }

    // Sync price: use number input or 'Thỏa thuận', fallback to current hidden value
    if (priceValueInput && priceNumberInput && priceAgreeCheckbox) {
        if (priceAgreeCheckbox.checked) {
            priceValueInput.value = 'Thỏa thuận';
        } else {
            priceValueInput.value = (priceNumberInput.value || priceValueInput.value || '');
        }
    }

    return true;
};

document.addEventListener('DOMContentLoaded', function () {
    var editForm = document.getElementById('admin-property-edit-form');
    var citySelect = document.getElementById('admin-edit-city-id');
    var wardSelect = document.getElementById('admin-edit-ward-id');
    var floorValueInput = document.getElementById('admin-edit-floor-value');
    var floorNumberInput = document.getElementById('admin-edit-floor-number');
    var floorAllCheckbox = document.getElementById('admin-edit-floor-all');
    var priceValueInput = document.getElementById('admin-edit-price-value');
    var priceNumberInput = document.getElementById('admin-edit-price-number');
    var priceAgreeCheckbox = document.getElementById('admin-edit-price-agree');
    if (!citySelect || !wardSelect) {
        return;
    }

    function filterWards(cityId) {
        for (var i = 0; i < wardSelect.options.length; i++) {
            var option = wardSelect.options[i];
            if (!option.value) {
                option.style.display = '';
                continue;
            }
            var optionCityId = String(option.getAttribute('data-city-id') || '').trim();
            var selectedCityId = String(cityId || '').trim();
            if (!optionCityId || !selectedCityId) {
                option.style.display = '';
            } else {
                option.style.display = (optionCityId === selectedCityId) ? '' : 'none';
            }
        }

        if (wardSelect.value) {
            var selectedWard = wardSelect.options[wardSelect.selectedIndex];
            if (selectedWard && selectedWard.value) {
                var selectedOptionCityId = String(selectedWard.getAttribute('data-city-id') || '').trim();
                var currentCityId = String(cityId || '').trim();
                if (selectedOptionCityId && currentCityId && selectedOptionCityId !== currentCityId) {
                    wardSelect.value = '';
                }
            }
        }
    }

    citySelect.addEventListener('change', function () {
        filterWards(citySelect.value);
    });

    filterWards(citySelect.value);

    var clampNumberInput = function(input) {
        if (!input) {
            return;
        }

        input.addEventListener('input', function() {
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
    };

    clampNumberInput(floorNumberInput);
    clampNumberInput(priceNumberInput);

    if (floorNumberInput && floorAllCheckbox && floorValueInput) {
        var syncFloorState = function() {
            if (floorAllCheckbox.checked) {
                floorValueInput.value = 'Tất cả';
                floorNumberInput.value = '';
                floorNumberInput.disabled = true;
                floorNumberInput.required = false;
            } else {
                floorNumberInput.disabled = false;
                floorNumberInput.required = true;
                floorValueInput.value = floorNumberInput.value;
            }
        };

        floorAllCheckbox.addEventListener('change', syncFloorState);
        floorNumberInput.addEventListener('input', function() {
            if (!floorAllCheckbox.checked) {
                floorValueInput.value = floorNumberInput.value;
            }
        });
        syncFloorState();
    }

    if (priceNumberInput && priceAgreeCheckbox && priceValueInput) {
        var syncPriceState = function() {
            if (priceAgreeCheckbox.checked) {
                priceValueInput.value = 'Thỏa thuận';
                priceNumberInput.value = '';
                priceNumberInput.disabled = true;
                priceNumberInput.required = false;
            } else {
                priceNumberInput.disabled = false;
                priceNumberInput.required = true;
                priceValueInput.value = priceNumberInput.value;
            }
        };

        priceAgreeCheckbox.addEventListener('change', syncPriceState);
        priceNumberInput.addEventListener('input', function() {
            if (!priceAgreeCheckbox.checked) {
                priceValueInput.value = priceNumberInput.value;
            }
        });
        syncPriceState();
    }

    if (editForm) {
        editForm.addEventListener('reset', function() {
            setTimeout(function() {
                syncFloorState();
                syncPriceState();
            }, 100);
        });
    }
});
</script>
