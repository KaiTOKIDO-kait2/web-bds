<?php require_once '../app/views/layouts/header.php'; ?>

<!-- Submit property -->
<div class="full-row bg-gray">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-12">
                <h2 class="text-secondary double-down-line text-center">Công cụ tính trả góp</h2>
            </div>
        </div>
        <center>
            <table class="items-list col-lg-6 table-hover" style="border-collapse:inherit;">
                <thead>
                    <tr class="bg-secondary">
                        <th class="text-white font-weight-bolder">Khoản mục</th>
                        <th class="text-white font-weight-bolder">Giá trị</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($data['amount'])): ?>
                        <tr class="text-center font-18">
                            <td><b>Số tiền vay</b></td>
                            <td><b><?= number_format($data['amount'], 0, ',', '.') ?> VNĐ</b></td>
                        </tr>
                        <tr class="text-center">
                            <td><b>Tổng thời hạn</b></td>
                            <td><b><?= htmlspecialchars($data['mon']) ?> tháng</b></td>
                        </tr>
                        <tr class="text-center">
                            <td><b>Lãi suất</b></td>
                            <td><b><?= htmlspecialchars($data['int']) ?>%</b></td>
                        </tr>
                        <tr class="text-center">
                            <td><b>Tổng tiền lãi</b></td>
                            <td><b><?= number_format($data['interest'], 0, ',', '.') ?> VNĐ</b></td>
                        </tr>
                        <tr class="text-center">
                            <td><b>Tổng số tiền</b></td>
                            <td><b><?= number_format($data['pay'], 0, ',', '.') ?> VNĐ</b></td>
                        </tr>
                        <tr class="text-center">
                            <td><b>Số tiền trả mỗi tháng</b></td>
                            <td><b><?= number_format($data['month'], 0, ',', '.') ?> VNĐ</b></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">Vui lòng nhập thông tin để tính toán.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </center>
    </div>
</div>
<!-- Submit property -->

<?php require_once '../app/views/layouts/footer.php'; ?>