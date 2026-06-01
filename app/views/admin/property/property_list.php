<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Bất động sản</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Danh sách BĐS</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="<?= BASEURL ?>/adminProperty/add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Thêm BĐS mới
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fa fa-list" style="color:#2563eb;margin-right:.5rem;"></i>
                            Danh sách bất động sản
                        </h5>
                    </div>
                    <div class="card-body">
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                        <div class="table-responsive">
                            <table id="datatable-buttons" class="table table-hover dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Loại</th>
                                        <th>Hướng nhà</th>
                                        <th>Hình thức</th>
                                        <th>Diện tích</th>
                                        <th>Giá</th>
                                        <th>Vị trí</th>
                                        <th>Chủ tin</th>
                                        <th>Duyệt tin</th>
                                        <th>Ngày đăng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(isset($properties)): foreach($properties as $row): ?>
                                    <tr>
                                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                            title="<?= htmlspecialchars($row['title']) ?>">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary"><?= htmlspecialchars($row['type']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['direction'] ?? '') ?></td>
                                        <td>
                                            <?php if($row['stype'] === 'sale'): ?>
                                                <span class="badge badge-success">Bán</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Thuê</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['size']) ?></td>
                                        <td style="font-weight:600;color:#2563eb;"><?= htmlspecialchars($row['price']) ?></td>
                                        <td><?= htmlspecialchars($row['location']) ?></td>
                                        <td><?= htmlspecialchars($row['uname'] ?? 'Không rõ') ?></td>
                                        <td>
                                            <?php if (($row['approval_status'] ?? 'approved') === 'approved'): ?>
                                                <span class="badge badge-success">Đã duyệt</span>
                                            <?php elseif (($row['approval_status'] ?? 'approved') === 'rejected'): ?>
                                                <span class="badge badge-danger">Từ chối</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Chờ duyệt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space:nowrap;"><?= htmlspecialchars($row['date']) ?></td>
                                        <td>
                                            <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                                                <?php if (($row['approval_status'] ?? 'approved') !== 'approved'): ?>
                                                    <form action="<?= BASEURL ?>/adminProperty/approve/<?= $row['pid'] ?>" method="post" style="display:inline;">
                                                        <button type="submit" class="btn btn-success btn-sm" title="Duyệt">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($row['approval_status'] ?? 'approved') !== 'rejected'): ?>
                                                    <form action="<?= BASEURL ?>/adminProperty/reject/<?= $row['pid'] ?>" method="post" style="display:inline;">
                                                        <button type="submit" class="btn btn-warning btn-sm" title="Từ chối">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <a href="<?= BASEURL ?>/adminProperty/edit/<?= $row['pid'] ?>" class="btn btn-info btn-sm" title="Chỉnh sửa">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?= BASEURL ?>/adminProperty/delete/<?= $row['pid'] ?>"
                                                   class="btn btn-danger btn-sm" title="Xóa"
                                                   onclick="return confirm('Bạn chắc muốn xóa BĐS này?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
