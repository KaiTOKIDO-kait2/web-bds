<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Phản hồi</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASEURL ?>/admin/dashboard">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item active">Phản hồi</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-star" style="color:#2563eb;margin-right:.5rem;"></i>Danh sách phản hồi</h5>
                        <p style="font-size:12.5px;color:#64748b;margin:0">Duyệt hoặc ẩn phản hồi để kiểm soát đánh giá hiển thị ngoài trang chủ.</p>
                        <?= isset($msg) && !empty($msg) ? $msg : '' ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive"><table id="basic-datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Nội dung phản hồi</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $cnt = 1; if (isset($feedbacks)): foreach ($feedbacks as $row): ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td><?= htmlspecialchars($row['uname'] ?? 'Người dùng') ?></td>
                                    <td><?= htmlspecialchars($row['uemail'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['fdescription']) ?></td>
                                    <td>
                                        <?= (int) $row['status'] === 1
                                            ? '<span class="badge badge-success">Đã duyệt</span>'
                                            : '<span class="badge badge-secondary">Đang ẩn</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ((int) $row['status'] === 0): ?>
                                        <form method="post" action="<?= BASEURL ?>/adminFeedback/updateStatus/<?= $row['fid'] ?>" style="display:inline-block;">
                                            <input type="hidden" name="status" value="1">
                                            <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
                                        </form>
                                        <?php else: ?>
                                        <form method="post" action="<?= BASEURL ?>/adminFeedback/updateStatus/<?= $row['fid'] ?>" style="display:inline-block;">
                                            <input type="hidden" name="status" value="0">
                                            <button type="submit" class="btn btn-secondary btn-sm">Ẩn</button>
                                        </form>
                                        <?php endif; ?>
                                        <a href="<?= BASEURL ?>/adminFeedback/delete/<?= $row['fid'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa phản hồi này không?');">
                                            <button class="btn btn-danger btn-sm">Xóa</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php $cnt++; endforeach; endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
