<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Phường / Xã</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Phường / Xã</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm Phường / Xã</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                        <?php if(isset($_GET['msg'])) echo urldecode($_GET['msg']); ?>
                    </div>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Thành phố</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="city_id" id="ward-city-id" required>
                                                <option value="">Chọn thành phố</option>
                                                <?php if(isset($cities)): foreach($cities as $city): ?>
                                                    <option value="<?= (int)($city['cid'] ?? 0) ?>"><?= htmlspecialchars($city['cname'] ?? '') ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Tên Phường/Xã</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="ward" required placeholder="Nhập tên phường/xã">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <input type="submit" class="btn btn-primary" value="Lưu" name="insert">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Danh sách Phường / Xã</h4>
                    </div>
                    <div class="card-body">
                        <table id="basic-datatable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên Phường/Xã</th>
                                    <th>Thành phố</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $cityMap = [];
                                if (isset($cities) && is_array($cities)) {
                                    foreach ($cities as $city) {
                                        $cityMap[(int)($city['cid'] ?? 0)] = (string)($city['cname'] ?? '');
                                    }
                                }
                            ?>
                            <?php $cnt=1; if(isset($wards) && is_array($wards)): foreach($wards as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['ward_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($cityMap[(int)($row['city_id'] ?? 0)] ?? '') ?></td>
                                    <td>
                                        <a href="<?= BASEURL ?>/adminLocation/wardEdit/<?= (int)($row['ward_id'] ?? 0) ?>"><button class="btn btn-info">Sửa</button></a>
                                        <a href="<?= BASEURL ?>/adminLocation/wardDelete/<?= (int)($row['ward_id'] ?? 0) ?>"><button class="btn btn-danger">Xóa</button></a>
                                    </td>
                                </tr>
                            <?php $cnt++; endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
