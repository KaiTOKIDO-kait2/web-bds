<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Loại bất động sản</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Loại bất động sản</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm loại bất động sản</h4>
                        <?= isset($error) && !empty($error) ? $error : '' ?>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    <form method="post" action="">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-5">
                                    <div class="form-group">
                                        <label>Tên loại</label>
                                        <input type="text" class="form-control" name="name" required placeholder="Ví dụ: Căn hộ dịch vụ">
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="form-group">
                                        <label>Slug (tùy chọn)</label>
                                        <input type="text" class="form-control" name="slug" placeholder="can-ho-dich-vu">
                                    </div>
                                </div>
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <label>Thứ tự</label>
                                        <input type="number" class="form-control" name="sort_order" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <label class="d-block">Hiển thị</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                            <label class="form-check-label" for="is_active">Đang bật</label>
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
                        <h5 class="card-title"><i class="fa fa-tags" style="color:#2563eb;margin-right:.5rem;"></i>Danh sách loại bất động sản</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive"><table id="basic-datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên loại</th>
                                    <th>Slug</th>
                                    <th>Thứ tự</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $cnt = 1; if(isset($types) && is_array($types)): foreach($types as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['slug'] ?? '') ?></td>
                                    <td><?= (int)($row['sort_order'] ?? 0) ?></td>
                                    <td>
                                        <?php if ((int)($row['is_active'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Đang bật</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Đang ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASEURL ?>/adminPropertyType/edit/<?= (int)$row['id'] ?>" class="btn btn-info btn-sm">Sửa</a>
                                        <a href="<?= BASEURL ?>/adminPropertyType/toggle/<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm">
                                            <?= (int)($row['is_active'] ?? 0) === 1 ? 'Ẩn' : 'Hiện' ?>
                                        </a>
                                        <a href="<?= BASEURL ?>/adminPropertyType/delete/<?= (int)$row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa loại này?');">Xóa</a>
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
