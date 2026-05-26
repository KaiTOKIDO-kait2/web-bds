<?php
$amenityValues = isset($amenityValues) && is_array($amenityValues) ? $amenityValues : [];
$propertyAge = isset($amenityValues['property_age']) && $amenityValues['property_age'] !== null
    ? (string) ((int) $amenityValues['property_age'])
    : '';
$waterSource = (string) ($amenityValues['water_source'] ?? '');
$frontageM = isset($amenityValues['frontage_m']) && $amenityValues['frontage_m'] !== null && $amenityValues['frontage_m'] !== ''
    ? (string) $amenityValues['frontage_m']
    : '';
$accessRoadM = isset($amenityValues['access_road_m']) && $amenityValues['access_road_m'] !== null && $amenityValues['access_road_m'] !== ''
    ? (string) $amenityValues['access_road_m']
    : '';
$interiorLevel = (string) ($amenityValues['interior_level'] ?? '');

$yesNoFields = [
    'swimming_pool' => 'Hồ bơi',
    'parking' => 'Bãi đỗ xe',
    'gym' => 'Phòng gym',
    'near_school' => 'Gần trường học',
    'security' => 'Bảo vệ',
    'near_hospital' => 'Gần bệnh viện',
    'near_market' => 'Gần chợ',
    'wifi' => 'Wifi',
    'elevator' => 'Thang máy',
    'cctv' => 'CCTV Camera',
];
?>
<div class="form-group row">
    <label class="col-lg-2 col-form-label">Tiện ích</label>
    <div class="col-lg-9">
        <div class="row">
            <div class="col-xl-6">
                <div class="form-group row">
                    <label class="col-lg-4 col-form-label">Tuổi bất động sản</label>
                    <div class="col-lg-8">
                        <input type="number" min="0" step="1" class="form-control" name="property_age" value="<?= htmlspecialchars($propertyAge) ?>" placeholder="Ví dụ: 10">
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="form-group row">
                    <label class="col-lg-4 col-form-label">Nguồn nước</label>
                    <div class="col-lg-8">
                        <select class="form-control" name="water_source">
                            <option value="">Chọn nguồn nước</option>
                            <option value="nuoc_ngam" <?= $waterSource === 'nuoc_ngam' ? 'selected' : '' ?>>Nước ngầm</option>
                            <option value="bon_chua" <?= $waterSource === 'bon_chua' ? 'selected' : '' ?>>Bồn chứa</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="form-group row">
                    <label class="col-lg-4 col-form-label">Mặt tiền (m)</label>
                    <div class="col-lg-8">
                        <input type="number" min="0" step="0.01" class="form-control" name="frontage_m" value="<?= htmlspecialchars($frontageM) ?>" placeholder="Ví dụ: 5">
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="form-group row">
                    <label class="col-lg-4 col-form-label">Đường vào (m)</label>
                    <div class="col-lg-8">
                        <input type="number" min="0" step="0.01" class="form-control" name="access_road_m" value="<?= htmlspecialchars($accessRoadM) ?>" placeholder="Ví dụ: 4">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="form-group row">
                    <label class="col-lg-4 col-form-label">Nội thất</label>
                    <div class="col-lg-8">
                        <select class="form-control" name="interior_level">
                            <option value="">Chọn mức nội thất</option>
                            <option value="co_ban" <?= $interiorLevel === 'co_ban' ? 'selected' : '' ?>>Cơ bản</option>
                            <option value="day_du" <?= $interiorLevel === 'day_du' ? 'selected' : '' ?>>Đầy đủ</option>
                            <option value="khong" <?= $interiorLevel === 'khong' ? 'selected' : '' ?>>Không nội thất</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($yesNoFields as $fieldName => $label): ?>
                <?php $fieldValue = (string) ($amenityValues[$fieldName] ?? '0'); ?>
                <div class="col-xl-6">
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label"><?= htmlspecialchars($label) ?></label>
                        <div class="col-lg-8">
                            <select class="form-control" name="<?= htmlspecialchars($fieldName) ?>">
                                <option value="1" <?= $fieldValue === '1' ? 'selected' : '' ?>>Có</option>
                                <option value="0" <?= $fieldValue !== '1' ? 'selected' : '' ?>>Không</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
