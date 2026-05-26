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
                        <h4 class="card-title">Thêm thông tin bất động sản</h4>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin cơ bản</h5>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                        
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Tiêu đề</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="title" required placeholder="Nhập tiêu đề">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Nội dung</label>
                                        <div class="col-lg-9">
                                            <textarea class="tinymce form-control" name="content" rows="10" cols="30"></textarea>
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
                                                    <option value="<?= htmlspecialchars($typeName) ?>"><?= htmlspecialchars($typeName) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Hình thức</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" required name="stype">
                                                <option value="">Chọn hình thức</option>
                                                <option value="rent">Cho thuê</option>
                                                <option value="sale">Bán</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Phòng tắm</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="bath" required placeholder="Nhập số phòng tắm">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Bếp</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="kitc" required placeholder="Nhập số bếp">
                                        </div>
                                    </div>
                                </div>   
                                <div class="col-xl-6">
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-3 col-form-label">Hướng nhà</label>
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
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Phòng ngủ</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="bed" required placeholder="Nhập số phòng ngủ">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Ban công</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="balc" required placeholder="Nhập số ban công">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Phòng khách</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="hall" required placeholder="Nhập số phòng khách">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h4 class="card-title">Giá và vị trí</h4>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tầng hiện tại</label>
                                        <div class="col-lg-9">
                                            <input type="hidden" name="floor" id="admin-add-floor-value" value="">
                                            <input type="number" class="form-control" id="admin-add-floor-number" min="0" step="1" required placeholder="Nhập số tầng">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="admin-add-floor-all">
                                                <label class="form-check-label" for="admin-add-floor-all">Tất cả</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Giá</label>
                                        <div class="col-lg-9">
                                            <input type="hidden" name="price" id="admin-add-price-value" value="">
                                            <input type="number" class="form-control" id="admin-add-price-number" min="0" step="1" required placeholder="Nhập giá">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="admin-add-price-agree">
                                                <label class="form-check-label" for="admin-add-price-agree">Thỏa thuận</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Thành phố</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="city_id" id="admin-add-city-id" required>
                                                <option value="">Chọn thành phố</option>
                                                <?php if(isset($cities) && is_array($cities)): foreach($cities as $city): ?>
                                                    <option value="<?= (int)($city['cid'] ?? 0) ?>"><?= htmlspecialchars($city['cname'] ?? '') ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Phường/Xã</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="ward_id" id="admin-add-ward-id" required>
                                                <option value="">Chọn phường/xã</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tổng số tầng</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="totalfl" min="1" step="1" required placeholder="Nhập tổng số tầng">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Diện tích</label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control" name="asize" min="0" step="any" required placeholder="Nhập diện tích">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Địa chỉ</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="loc" required placeholder="Nhập địa chỉ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                                $amenityValues = [
                                    'property_age' => '10 năm',
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
                                    
                            <h4 class="card-title">Hình ảnh và trạng thái</h4>
                            <p class="text-muted small mb-2">Các ảnh đều tùy chọn — có thể để trống (lưu dưới dạng rỗng / không đổi ảnh cũ khi sửa).</p>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Image</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Image 2</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage2" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Image 4</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage4" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Status</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" required name="status">
                                                <option value="">Chọn trạng thái</option>
                                                <option value="available">Available</option>
                                                <option value="sold out">Sold Out</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Mặt bằng tầng hầm</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="fimage1" type="file">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Image 1</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage1" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Ảnh 3</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage3" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Người đại diện</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="uid" required>
                                                <option value="">Chọn chủ sở hữu</option>
                                                <?php if(isset($users)): foreach($users as $u): ?>
                                                    <option value="<?= $u['uid'] ?>"><?= $u['uname'] ?> (ID: <?= $u['uid'] ?>)</option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Ảnh sơ đồ</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="fimage" type="file">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Mặt bằng tầng trệt</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="fimage2" type="file">
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
                                                <option value="">Chọn...</option>
                                                <option value="0">Không</option>
                                                <option value="1">Có</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="submit" value="Lưu" class="btn btn-primary" name="add" style="margin-left:200px;">
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>			
</div>
<!-- /Main Wrapper -->

<?php require_once '../app/views/admin/layouts/footer.php'; ?>

<script>
(function() {
    var citySelect = document.getElementById('admin-add-city-id');
    var wardSelect = document.getElementById('admin-add-ward-id');
    if (!citySelect || !wardSelect) {
        return;
    }

    var allWards = <?= json_encode(array_map(function($item) {
        return [
            'id' => (int)($item['ward_id'] ?? $item['wid'] ?? 0),
            'name' => (string)($item['ward_name'] ?? $item['wname'] ?? ''),
            'city_id' => (int)($item['city_id'] ?? 0),
        ];
    }, isset($wards) && is_array($wards) ? $wards : []), JSON_UNESCAPED_UNICODE) ?>;

    function fillWards() {
        var selectedCityId = parseInt(citySelect.value || '0', 10);
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

        allWards.forEach(function(item) {
            if (selectedCityId > 0 && item.city_id === selectedCityId) {
                var opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = item.name;
                wardSelect.appendChild(opt);
            }
        });
    }

    citySelect.addEventListener('change', fillWards);
    fillWards();

    // Floor and Price Syncing Logic
    var floorValueInput = document.getElementById('admin-add-floor-value');
    var floorNumberInput = document.getElementById('admin-add-floor-number');
    var floorAllCheckbox = document.getElementById('admin-add-floor-all');
    var priceValueInput = document.getElementById('admin-add-price-value');
    var priceNumberInput = document.getElementById('admin-add-price-number');
    var priceAgreeCheckbox = document.getElementById('admin-add-price-agree');

    if (floorNumberInput && floorAllCheckbox) {
        var syncFloorState = function() {
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
        floorNumberInput.addEventListener('input', function() {
            if (!floorAllCheckbox.checked) {
                floorValueInput.value = floorNumberInput.value;
            }
        });
        syncFloorState();
    }

    if (priceNumberInput && priceAgreeCheckbox) {
        var syncPriceState = function() {
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
        priceNumberInput.addEventListener('input', function() {
            if (!priceAgreeCheckbox.checked) {
                priceValueInput.value = priceNumberInput.value;
            }
        });
        syncPriceState();
    }
})();
</script>
