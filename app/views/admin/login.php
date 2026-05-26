<?php require_once '../app/views/layouts/header.php'; ?>
<div class="full-row">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-secondary double-down-line text-center">Cập nhật bất động sản</h2>
            </div>
        </div>
        <div class="row p-5 bg-white">
            <form method="post" enctype="multipart/form-data" action="">
                <?php if(isset($property)): $row = $property; ?>
                <div class="description">
                    <h5 class="text-secondary">Thông tin cơ bản</h5><hr>
                    <?= isset($error) && !empty($error) ? htmlspecialchars_decode($error) : '' ?>
                    <?= isset($msg) && !empty($msg) ? htmlspecialchars_decode($msg) : '' ?>
                    
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
                                        <option value="Căn hộ chung cư" <?= in_array($row['type'], ['Căn hộ chung cư', 'Căn hộ', 'Chung cư'], true) ? 'selected' : '' ?>>Căn hộ chung cư</option>
                                        <option value="Chung cư mini" <?= $row['type']=='Chung cư mini'?'selected':'' ?>>Chung cư mini</option>
                                        <option value="Nhà" <?= $row['type']=='Nhà'?'selected':'' ?>>Nhà</option>
                                        <option value="Biệt thự" <?= $row['type']=='Biệt thự'?'selected':'' ?>>Biệt thự</option>
                                        <option value="Nhà mặt phố" <?= $row['type']=='Nhà mặt phố'?'selected':'' ?>>Nhà mặt phố</option>
                                        <option value="Nhà trọ" <?= in_array($row['type'], ['Nhà trọ', 'Tòa nhà'], true) ? 'selected' : '' ?>>Nhà trọ</option>
                                        <option value="Văn phòng" <?= $row['type']=='Văn phòng'?'selected':'' ?>>Văn phòng</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình thức</label>
                                <div class="col-lg-9">
                                    <select class="form-control" required name="stype">
                                        <option value="">Chọn hình thức</option>
                                        <option value="rent" <?= $row['stype']=='rent'?'selected':'' ?>>Cho thuê</option>
                                        <option value="sale" <?= $row['stype']=='sale'?'selected':'' ?>>Bán</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Phòng tắm</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="bath" required value="<?= $row['bathroom'] ?>"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Bếp</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="kitc" required value="<?= $row['kitchen'] ?>"></div>
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
                                <div class="col-lg-9"><input type="text" class="form-control" name="bed" required value="<?= $row['bedroom'] ?>"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Ban công</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="balc" required value="<?= $row['balcony'] ?>"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Phòng khách</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="hall" required value="<?= $row['hall'] ?>"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="text-secondary">Giá và vị trí</h5><hr>
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
                                        <input type="hidden" name="floor" id="admin-login-floor-value" value="<?= htmlspecialchars($floorValue) ?>">
                                        <input type="number" class="form-control" id="admin-login-floor-number" min="0" step="1" value="<?= htmlspecialchars((string)$floorNumberValue) ?>" required placeholder="Nhập số tầng">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="admin-login-floor-all" <?= $floorIsAll ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="admin-login-floor-all">Tất cả</label>
                                        </div>
                                    </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Giá</label>
                                    <div class="col-lg-9">
                                        <?php $priceIsNegotiable = (float)($row['price'] ?? 0) <= 0; ?>
                                        <input type="hidden" name="price" id="admin-login-price-value" value="<?= htmlspecialchars((string)($row['price'] ?? '')) ?>">
                                        <input type="number" class="form-control" id="admin-login-price-number" min="0" step="1" value="<?= htmlspecialchars((string)$row['price']) ?>" required placeholder="Nhập giá">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="admin-login-price-agree" <?= $priceIsNegotiable ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="admin-login-price-agree">Thỏa thuận</label>
                                        </div>
                                    </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Thành phố</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="city" required value="<?= htmlspecialchars($row['city']) ?>"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Phường/Xã</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="ward" required value="<?= htmlspecialchars($row['ward'] ?? '') ?>"></div>
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
                                <label class="col-lg-3 col-form-label">Diện tích</label>
                                    <div class="col-lg-9">
                                        <input type="number" class="form-control" name="asize" min="0" step="any" required value="<?= htmlspecialchars($row['size']) ?>" placeholder="Nhập diện tích">
                                    </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Địa chỉ</label>
                                <div class="col-lg-9"><input type="text" class="form-control" name="loc" required value="<?= htmlspecialchars($row['location']) ?>"></div>
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
                            
                    <h5 class="text-secondary">Hình ảnh và trạng thái</h5><hr>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình ảnh chính</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="aimage" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['pimage'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình ảnh 2</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="aimage2" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['pimage2'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình ảnh 4</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="aimage4" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['pimage4'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Trạng thái</label>
                                <div class="col-lg-9">
                                    <select class="form-control" required name="status">
                                        <option value="">Chọn trạng thái</option>
                                        <option value="available" <?= $row['status']=='available'?'selected':'' ?>>Còn sẵn</option>
                                        <option value="sold out" <?= $row['status']=='sold out'?'selected':'' ?>>Đã bán</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Sơ đồ tầng hầm</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="fimage1" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['topmapimage'] ?>" height="150" width="180">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình ảnh 1</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="aimage1" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['pimage1'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Hình ảnh 3</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="aimage3" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['pimage3'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Sơ đồ tổng thể</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="fimage" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['mapimage'] ?>" height="150" width="180">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Sơ đồ tầng trệt</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="fimage2" type="file">
                                    <img src="<?= BASEURL ?>/admin/property/<?= $row['groundmapimage'] ?>" height="150" width="180">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="submit" value="Lưu thay đổi" class="btn btn-success" name="add" style="margin-left:200px;">
                </div>
                <?php endif; ?>
            </form>
        </div>            
    </div>
</div>

<script src="<?= BASEURL ?>/js/tinymce/tinymce.min.js"></script>
<script src="<?= BASEURL ?>/js/tinymce/init-tinymce.min.js"></script>
<script>
(function() {
    var floorValueInput = document.getElementById('admin-login-floor-value');
    var floorNumberInput = document.getElementById('admin-login-floor-number');
    var floorAllCheckbox = document.getElementById('admin-login-floor-all');
    var priceValueInput = document.getElementById('admin-login-price-value');
    var priceNumberInput = document.getElementById('admin-login-price-number');
    var priceAgreeCheckbox = document.getElementById('admin-login-price-agree');

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
})();
</script>
<?php require_once '../app/views/layouts/header.php'; ?>
