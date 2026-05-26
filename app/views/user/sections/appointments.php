<?php /** @var array $requests */
$appointmentRows = [];
$appointmentStats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0,
];
$nextAppointmentTs = null;

foreach (($requests ?? []) as $request) {
    $statusRaw = strtolower((string) ($request['appointment_status'] ?? 'none'));
    if ($statusRaw === 'none') {
        continue;
    }

    $appointmentStats['total']++;
    if (isset($appointmentStats[$statusRaw])) {
        $appointmentStats[$statusRaw]++;
    }

    $requestedTs = !empty($request['appointment_requested_at']) ? strtotime((string) $request['appointment_requested_at']) : null;
    if ($requestedTs !== null && ($nextAppointmentTs === null || $requestedTs < $nextAppointmentTs)) {
        $nextAppointmentTs = $requestedTs;
    }

    $request['_normalized_status'] = $statusRaw;
    $request['_requested_iso'] = $requestedTs !== null ? date('c', $requestedTs) : '';
    $propertyTitle = trim((string) ($request['property_title'] ?? ''));
    if ($propertyTitle !== '') {
        $propertyTitleLower = function_exists('mb_strtolower')
            ? mb_strtolower($propertyTitle, 'UTF-8')
            : strtolower($propertyTitle);
    } else {
        $propertyTitleLower = '';
    }
    $request['_property_search'] = $propertyTitleLower;

    $appointmentRows[] = $request;
}

$nextAppointmentLabel = $nextAppointmentTs !== null ? date('d/m/Y H:i', $nextAppointmentTs) : 'Chưa có';
$appointmentDefaultPageSize = 10;
$appointmentTotalCount = count($appointmentRows);
$appointmentInitialEnd = min($appointmentDefaultPageSize, $appointmentTotalCount);
$appointmentInitialRange = $appointmentTotalCount > 0 ? ('1-' . $appointmentInitialEnd) : '0-0';
$appointmentInitialTotalPages = $appointmentTotalCount > 0
    ? (int) ceil($appointmentTotalCount / $appointmentDefaultPageSize)
    : 0;
?>

<section class="agent-section agent-appointments">
    <div class="agent-section-head">
        <div class="agent-section-copy">
            <h3 class="agent-section-title">Lịch hẹn xem nhà</h3>
            <p class="agent-section-copy"><small>Theo dõi và quản lý các cuộc hẹn xem bất động sản của bạn</small></p>
        </div>
    </div>

    <div class="agent-metric-grid">
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #0040a1, #0056d2);">
                <i class="fa fa-calendar-check"></i>
            </div>
            <div>
                <p class="agent-metric-label">Tổng lịch hẹn</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['total'] ?></p>
                <span class="agent-metric-hint">Tất cả cuộc hẹn của bạn</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div>
                <p class="agent-metric-label">Đang chờ xác nhận</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['pending'] ?></p>
                <span class="agent-metric-hint">Chờ môi giới phản hồi</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <p class="agent-metric-label">Đã xác nhận</p>
                <p class="agent-metric-value"><?= (int) $appointmentStats['confirmed'] ?></p>
                <span class="agent-metric-hint">Sẵn sàng đi xem nhà</span>
            </div>
        </div>
        <div class="agent-metric-card">
            <div class="agent-metric-icon" style="background: linear-gradient(135deg, #6366f1, #4338ca);">
                <i class="fa fa-clock"></i>
            </div>
            <div>
                <p class="agent-metric-label">Lịch sắp tới</p>
                <p class="agent-metric-value"><?= htmlspecialchars($nextAppointmentLabel) ?></p>
                <span class="agent-metric-hint">Thời gian hẹn gần nhất</span>
            </div>
        </div>
    </div>

    <div class="agent-filter-panel">
        <div class="agent-filter-group">
            <div class="agent-filter-control">
                <label for="appointment-filter-status">Trạng thái</label>
                <select id="appointment-filter-status" class="form-control">
                    <option value="all">Tất cả</option>
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Hoàn tất</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div class="agent-filter-control">
                <label for="appointment-filter-date-from">Từ ngày</label>
                <input type="date" id="appointment-filter-date-from" class="form-control">
            </div>
            <div class="agent-filter-control">
                <label for="appointment-filter-date-to">Đến ngày</label>
                <input type="date" id="appointment-filter-date-to" class="form-control">
            </div>
            <div class="agent-filter-control agent-filter-control--search">
                <label for="appointment-filter-search">Tên BĐS</label>
                <div class="agent-filter-search">
                    <input type="text" id="appointment-filter-search">
                </div>
            </div>
        </div>
        <div class=" agent-filter-actions">
            <button type="button" class="agent-action-btn agent-action-btn--ghost" id="appointment-filter-reset">
                <i class="fa fa-undo"></i>
                Đặt lại
            </button>
        </div>
    </div>

    <div class="agent-table-wrap agent-table-wrap--elevated">

        <table class="agent-table agent-table--modern">
            <thead>
                <tr>
                    <th>Môi giới hỗ trợ</th>
                    <th>BĐS quan tâm</th>
                    <th>Đề xuất</th>
                    <th>Xác nhận</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Hành động</th>
                </tr>
            </thead>
            <tbody data-appointment-table-body>
                <?php if (!empty($appointmentRows)): ?>
                    <?php foreach ($appointmentRows as $request): ?>
                        <?php
                        $appointmentBadge = userAppointmentBadge($request['appointment_status'] ?? 'none');
                        $statusKey = $request['_normalized_status'];
                        $requestedLabel = !empty($request['appointment_requested_at'])
                            ? date('d/m/Y H:i', strtotime((string) $request['appointment_requested_at']))
                            : 'Chưa có';
                        $confirmedLabel = !empty($request['appointment_confirmed_at'])
                            ? date('d/m/Y H:i', strtotime((string) $request['appointment_confirmed_at']))
                            : 'Chưa có';
                        $agentName = trim((string) ($request['agent_name'] ?? 'Chưa rõ môi giới'));
                        if (function_exists('mb_substr')) {
                            $initials = mb_strtoupper(mb_substr($agentName, 0, 2, 'UTF-8'), 'UTF-8');
                        } else {
                            $initials = strtoupper(substr($agentName, 0, 2));
                        }
                        ?>
                        <tr data-appointment-row data-status="<?= htmlspecialchars($statusKey) ?>"
                            data-requested="<?= htmlspecialchars($request['_requested_iso']) ?>"
                            data-property="<?= htmlspecialchars($request['_property_search']) ?>">
                            <td>
                                <div class="agent-table-client">
                                    <div class="agent-table-avatar">
                                        <span><?= htmlspecialchars($initials) ?></span>
                                    </div>
                                    <div>
                                        <span class="agent-table-title"><?= htmlspecialchars($agentName) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="agent-table-title"><?= htmlspecialchars((string) ($request['property_title'] ?? 'Không rõ bất động sản')) ?></span>
                                <span class="agent-table-sub">ID Yêu cầu #<?= (int) ($request['id'] ?? 0) ?></span>
                            </td>
                            <td><?= htmlspecialchars($requestedLabel) ?></td>
                            <td><?= htmlspecialchars($confirmedLabel) ?></td>
                            <td><?= $appointmentBadge ?></td>
                            <td class="text-right">
                                <a class="agent-btn agent-btn--compact"
                                    href="<?= BASEURL ?>/userWorkspace/requestDetail/<?= (int) ($request['id'] ?? 0) ?>">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="appointment-empty-state" style="display: none;">
                        <td colspan="6" class="agent-table-empty">Không tìm thấy lịch hẹn nào khớp với bộ lọc.</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="agent-table-empty">Bạn chưa có lịch hẹn nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="agent-table-footer">
            <div class="agent-table-footer-info">
                <span class="agent-table-footer-note">Hiển thị
                    <strong id="appointment-visible-count"><?= (int) $appointmentInitialEnd ?></strong> yêu cầu</span>
            </div>
            <div class="agent-table-footer-controls">
                <label class="agent-table-page-size">
                    <select class="form-control form-control-1" id="appointment-page-size">
                        <option value="10" <?= $appointmentDefaultPageSize === 10 ? 'selected' : '' ?>>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </label>
                <div class="agent-table-pagination" aria-label="Phân trang danh sách lịch hẹn">
                    <button type="button" class="agent-table-page-btn" id="appointment-page-prev"
                        aria-label="Trang trước">&lsaquo;</button>
                    <span class="agent-table-page-indicator" id="appointment-page-indicator">Trang
                        <?= $appointmentTotalCount > 0 ? 1 : 0 ?> / <?= (int) $appointmentInitialTotalPages ?></span>
                    <button type="button" class="agent-table-page-btn" id="appointment-page-next"
                        aria-label="Trang sau">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .agent-appointments .agent-section-head {
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .agent-appointments .agent-section-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .agent-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        background: #f3f4f6;
        transition: all .2s ease;
    }

    .agent-action-btn i {
        font-size: 14px;
    }

    .agent-action-btn--outline {
        background: #fff;
        border-color: #d1d5db;
    }

    .agent-action-btn--primary {
        color: #fff;
        background: linear-gradient(135deg, #0040a1, #0056d2);
        box-shadow: 0 10px 20px rgba(0, 86, 210, 0.22);
    }

    .agent-action-btn--ghost {
        background: rgba(15, 23, 42, 0.05);
        color: #1f2937;
    }

    .agent-action-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .agent-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        /* margin-bottom: 24px; */
    }

    .agent-metric-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        border-radius: 20px;
        background: linear-gradient(160deg, #ffffff, #f5f7ff);
        border: 1px solid rgba(99, 102, 241, 0.08);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .agent-metric-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 16px;
        color: #fff;
        font-size: 22px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .agent-metric-label {
        margin: 0;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 700;
    }

    .agent-metric-value {
        font-size: 20px;
        font-weight: 600;
        /* border: 1px solid var(--dash-line); */
        /* background: #fff; */
        color: var(--dash-text);
    }

    .agent-metric-hint {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
    }

    .agent-filter-panel {
        margin-top: 80px;
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        justify-content: space-between;
        align-items: flex-end;
        padding: 18px 20px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 16px 32px rgba(148, 163, 184, 0.15);
        margin-bottom: 24px;
    }

    .agent-filter-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        flex: 1;
    }

    .agent-filter-control label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .agent-filter-control .form-control,
    .agent-filter-search input {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 14px;
        padding: 10px 12px;
        min-height: 40px;
    }

    .agent-filter-control .form-control:focus,
    .agent-filter-search input:focus {
        outline: none;
        border-color: #0040a1;
        box-shadow: 0 0 0 4px rgba(0, 86, 210, 0.12);
        background: #fff;
    }

    .agent-filter-search {
        display: flex;
        align-items: center;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 0;
    }

    .agent-btn--compact {
        font-size: 12px !important;
        padding: 6px 12px !important;
    }

    .agent-filter-search input {
        border: none;
        background: transparent;
        padding: 10px 12px;
        width: 100%;
    }

    .agent-table-wrap--elevated {
        border-radius: 20px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 24px 45px rgba(100, 116, 139, 0.18);
    }

    .agent-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px 0;
    }

    .agent-table-toolbar .agent-table-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #1f2937;
    }

    .agent-table--modern thead {
        background: linear-gradient(120deg, #f8fafc, #eef2ff);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 12px;
        color: #64748b;
    }

    .agent-table--modern th,
    .agent-table--modern td {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .agent-table--modern tbody tr:hover {
        background: rgba(226, 232, 240, 0.35);
    }

    .agent-table-client {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agent-table-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(0, 64, 161, 0.2), rgba(0, 86, 210, 0.35));
        color: #0040a1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
    }

    .agent-table-sub {
        display: block;
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .agent-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    .agent-table-footer strong {
        color: #0f172a;
    }

    .agent-table-footer-info,
    .agent-table-footer-controls,
    .agent-table-page-size,
    .agent-table-pagination {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .agent-table-footer-note {
        color: #64748b;
    }


    .agent-table-page-size .form-control {
        margin-top: 10px;
        min-width: 92px;
        min-height: 30px;
        padding: 6px 10px;
        font-size: 12px;
        border-radius: 10px;
    }

    .form-control-1 {
        height: 10px !important;
    }

    .agent-table-page-btn {
        width: 34px;
        height: 32px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-weight: 700;
        line-height: 1;
        transition: all .2s ease;
    }

    .agent-table-page-btn:hover:not(:disabled) {
        background: #eff6ff;
        border-color: #0056d2;
        color: #0056d2;
    }

    .agent-table-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .agent-table-page-indicator {
        min-width: 88px;
        text-align: center;
        color: #475569;
    }

    @media (max-width: 991.98px) {
        .agent-section-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .agent-filter-panel {
            padding: 18px;
        }

        .agent-table-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .agent-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .agent-metric-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .agent-table--modern th,
        .agent-table--modern td {
            padding: 14px 16px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('.agent-appointments table');
        if (!table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('[data-appointment-row]'));
        const statusSelect = document.getElementById('appointment-filter-status');
        const dateFrom = document.getElementById('appointment-filter-date-from');
        const dateTo = document.getElementById('appointment-filter-date-to');
        const searchInput = document.getElementById('appointment-filter-search');
        const resetButton = document.getElementById('appointment-filter-reset');
        const emptyRow = document.getElementById('appointment-empty-state');
        const tableBody = table.querySelector('[data-appointment-table-body]');
        const pageSizeSelect = document.getElementById('appointment-page-size');
        const prevPageBtn = document.getElementById('appointment-page-prev');
        const nextPageBtn = document.getElementById('appointment-page-next');
        const pageIndicator = document.getElementById('appointment-page-indicator');
        const visibleRange = document.getElementById('appointment-visible-range');
        const filteredTotal = document.getElementById('appointment-filtered-total');
        const visibleCount = document.getElementById('appointment-visible-count');
        let currentPage = 1;

        if (!rows.length) {
            return;
        }

        const parseDate = (value) => {
            return value ? new Date(value) : null;
        };

        const getPageSize = () => {
            const pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '10', 10);
            return pageSize > 0 ? pageSize : 10;
        };

        const updatePagination = (totalRows, pageRows, totalPages, startIndex, endIndex) => {
            const hasRows = totalRows > 0;

            if (visibleRange) {
                visibleRange.textContent = hasRows ? `${startIndex + 1}-${endIndex}` : '0-0';
            }

            if (filteredTotal) {
                filteredTotal.textContent = totalRows;
            }

            if (visibleCount) {
                visibleCount.textContent = pageRows.length;
            }

            if (pageIndicator) {
                pageIndicator.textContent = hasRows ? `Trang ${currentPage} / ${totalPages}` : 'Trang 0 / 0';
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = !hasRows || currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = !hasRows || currentPage >= totalPages;
            }
        };

        const applyFilters = () => {
            const statusValue = statusSelect.value;
            const keyword = searchInput.value.trim().toLowerCase();
            const fromDate = dateFrom.value ? new Date(dateFrom.value + 'T00:00:00') : null;
            const toDate = dateTo.value ? new Date(dateTo.value + 'T23:59:59') : null;
            const pageSize = getPageSize();
            const filteredRows = [];

            rows.forEach((row) => {
                const rowStatus = row.dataset.status || '';
                const rowKeyword = row.dataset.property || '';
                const requestedRaw = row.dataset.requested || '';
                const requestedDate = requestedRaw ? parseDate(requestedRaw) : null;

                let isVisible = true;

                if (statusValue !== 'all' && rowStatus !== statusValue) {
                    isVisible = false;
                }

                if (isVisible && keyword && !rowKeyword.includes(keyword)) {
                    isVisible = false;
                }

                if (isVisible && fromDate) {
                    if (!requestedDate || requestedDate < fromDate) {
                        isVisible = false;
                    }
                }

                if (isVisible && toDate) {
                    if (!requestedDate || requestedDate > toDate) {
                        isVisible = false;
                    }
                }

                row.style.display = 'none';
                if (isVisible) {
                    filteredRows.push(row);
                }
            });

            const totalPages = filteredRows.length > 0 ? Math.ceil(filteredRows.length / pageSize) : 0;
            if (totalPages > 0 && currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            const startIndex = filteredRows.length > 0 ? (currentPage - 1) * pageSize : 0;
            const endIndex = filteredRows.length > 0 ? Math.min(startIndex + pageSize, filteredRows.length) : 0;
            const pageRows = filteredRows.slice(startIndex, endIndex);

            pageRows.forEach((row) => {
                row.style.display = '';
                if (tableBody) {
                    tableBody.appendChild(row);
                }
            });

            if (emptyRow) {
                if (tableBody) {
                    tableBody.appendChild(emptyRow);
                }
                emptyRow.style.display = filteredRows.length === 0 ? '' : 'none';
            }

            updatePagination(filteredRows.length, pageRows, totalPages, startIndex, endIndex);
        };

        statusSelect.addEventListener('change', () => {
            currentPage = 1;
            applyFilters();
        });
        dateFrom.addEventListener('change', () => {
            currentPage = 1;
            applyFilters();
        });
        dateTo.addEventListener('change', () => {
            currentPage = 1;
            applyFilters();
        });
        searchInput.addEventListener('input', () => {
            currentPage = 1;
            applyFilters();
        });
        resetButton.addEventListener('click', () => {
            statusSelect.value = 'all';
            dateFrom.value = '';
            dateTo.value = '';
            searchInput.value = '';
            currentPage = 1;
            applyFilters();
        });

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', () => {
                currentPage = 1;
                applyFilters();
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilters();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', () => {
                currentPage++;
                applyFilters();
            });
        }

        applyFilters();
    });
</script>