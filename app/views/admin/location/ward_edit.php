<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Sửa Phường / Xã</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Sửa Phường / Xã</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật Phường / Xã</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    <?php if(isset($ward) && !empty($ward)): ?>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Thành phố</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="city_id" id="ward-edit-city-id" required>
                                                <option value="">Chọn thành phố</option>
                                                <?php if(isset($cities)): foreach($cities as $city): ?>
                                                    <?php $cityId = (int)($city['cid'] ?? 0); ?>
                                                    <option value="<?= $cityId ?>" <?= (int)($ward['city_id'] ?? 0) === $cityId ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($city['cname'] ?? '') ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tên Phường/Xã</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="ward" required value="<?= htmlspecialchars($ward['ward_name'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu thay đổi" name="update">
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
