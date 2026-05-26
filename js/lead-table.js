(function (global) {
    'use strict';

    function normalize(text) {
        return (text || '')
            .toString()
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function toArray(nodes) {
        return Array.prototype.slice.call(nodes || []);
    }

    function getRoot(rootOrSelector) {
        if (!rootOrSelector) {
            return document.querySelector('[data-leads-page]');
        }

        if (typeof rootOrSelector === 'string') {
            return document.querySelector(rootOrSelector);
        }

        return rootOrSelector;
    }

    function initLeadTable(rootOrSelector) {
        var root = getRoot(rootOrSelector);
        if (!root || root.getAttribute('data-lead-table-ready') === 'true') {
            return root;
        }

        var searchInput = root.querySelector('[data-lead-search]');
        var statusSelect = root.querySelector('[data-lead-status-select]');
        var sortSelect = root.querySelector('[data-lead-sort-select]');
        var pageSizeSelect = root.querySelector('[data-lead-page-size]');
        var prevPageBtn = root.querySelector('[data-lead-page-prev]');
        var nextPageBtn = root.querySelector('[data-lead-page-next]');
        var pageIndicator = root.querySelector('[data-lead-page-indicator]');
        var rangeNode = root.querySelector('[data-lead-visible-range]');
        var filteredTotalNode = root.querySelector('[data-lead-filtered-total]');
        var cards = toArray(root.querySelectorAll('[data-status-card]'));
        var rows = toArray(root.querySelectorAll('[data-lead-row]'));
        var emptyRow = root.querySelector('[data-leads-empty]');
        var visibleCountNodes = toArray(root.querySelectorAll('[data-lead-visible-count]'))
            .concat(toArray(root.querySelectorAll('[data-lead-visible-count-footer]')));

        var activeStatus = 'all';
        var currentPage = 1;
        var defaultPageSize = parseInt((pageSizeSelect && pageSizeSelect.value) || '10', 10);

        function setCardActive(status) {
            cards.forEach(function (card) {
                var isActive = card.getAttribute('data-status-card') === status;
                card.classList.toggle('is-active', isActive);
                card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function compareRows(a, b) {
            var sortKey = sortSelect ? sortSelect.value : 'newest';
            var aName = normalize(a.getAttribute('data-lead-name'));
            var bName = normalize(b.getAttribute('data-lead-name'));
            var aTs = parseInt(a.getAttribute('data-lead-sort-ts') || '0', 10);
            var bTs = parseInt(b.getAttribute('data-lead-sort-ts') || '0', 10);

            if (sortKey === 'oldest') {
                return aTs - bTs;
            }

            if (sortKey === 'name_asc') {
                return aName.localeCompare(bName, 'vi');
            }

            if (sortKey === 'name_desc') {
                return bName.localeCompare(aName, 'vi');
            }

            return bTs - aTs;
        }

        function updateCounts(pageRows) {
            visibleCountNodes.forEach(function (node) {
                node.textContent = pageRows.length;
            });
        }

        function getPageSize() {
            var selectedSize = parseInt(pageSizeSelect ? pageSizeSelect.value : String(defaultPageSize), 10);
            return selectedSize > 0 ? selectedSize : defaultPageSize;
        }

        function updatePagination(totalRows, pageRows, totalPages, startIndex, endIndex) {
            var hasRows = totalRows > 0;

            if (rangeNode) {
                rangeNode.textContent = hasRows ? ((startIndex + 1) + '-' + endIndex) : '0-0';
            }

            if (filteredTotalNode) {
                filteredTotalNode.textContent = totalRows;
            }

            if (pageIndicator) {
                pageIndicator.textContent = hasRows ? ('Trang ' + currentPage + ' / ' + totalPages) : 'Trang 0 / 0';
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = !hasRows || currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = !hasRows || currentPage >= totalPages;
            }

            updateCounts(pageRows);
        }

        function render() {
            var query = normalize(searchInput ? searchInput.value : '');
            var selectedStatus = statusSelect ? statusSelect.value : 'all';
            var pageSize = getPageSize();

            var visibleRows = rows.filter(function (row) {
                var leadName = normalize(row.getAttribute('data-lead-name'));
                var leadStatus = normalize(row.getAttribute('data-lead-status'));
                var matchesQuery = query === '' || leadName.indexOf(query) !== -1;
                var matchesCard = activeStatus === 'all' || leadStatus === activeStatus;
                var matchesStatus = selectedStatus === 'all' || leadStatus === selectedStatus;

                return matchesQuery && matchesCard && matchesStatus;
            });

            visibleRows.sort(compareRows);

            var totalPages = visibleRows.length > 0 ? Math.ceil(visibleRows.length / pageSize) : 0;
            if (totalPages > 0 && currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            var startIndex = visibleRows.length > 0 ? (currentPage - 1) * pageSize : 0;
            var endIndex = visibleRows.length > 0 ? Math.min(startIndex + pageSize, visibleRows.length) : 0;
            var pageRows = visibleRows.slice(startIndex, endIndex);

            rows.forEach(function (row) {
                row.style.display = 'none';
            });

            pageRows.forEach(function (row) {
                row.style.display = '';
                row.parentNode.appendChild(row);
            });

            if (emptyRow) {
                emptyRow.parentNode.appendChild(emptyRow);
                emptyRow.hidden = visibleRows.length !== 0;
            }

            updatePagination(visibleRows.length, pageRows, totalPages, startIndex, endIndex);
        }

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                activeStatus = card.getAttribute('data-status-card') || 'all';
                if (statusSelect) {
                    statusSelect.value = activeStatus;
                }
                currentPage = 1;
                setCardActive(activeStatus);
                render();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentPage = 1;
                render();
            });
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                activeStatus = statusSelect.value || 'all';
                currentPage = 1;
                setCardActive(activeStatus);
                render();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                currentPage++;
                render();
            });
        }

        root.setAttribute('data-lead-table-ready', 'true');
        setCardActive(activeStatus);
        render();

        return root;
    }

    function handleLeadAction(inquiryId, actionKey, rootOrSelector) {
        var root = initLeadTable(rootOrSelector);
        var actionBase = root ? root.getAttribute('data-lead-action-base') : '';

        if (!actionBase) {
            if (global.AppPopup && typeof global.AppPopup.error === 'function') {
                global.AppPopup.error('Thiếu cấu hình cập nhật lead');
            }
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', actionBase + '/' + inquiryId, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        global.AppPopup.success(response.message || 'Cập nhật thành công', function () {
                            global.location.reload();
                        });
                    } else {
                        global.AppPopup.error(response.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    global.AppPopup.success('Cập nhật thành công', function () {
                        global.location.reload();
                    });
                }
            } else {
                global.AppPopup.error('Lỗi: ' + xhr.status);
            }
        };

        xhr.onerror = function () {
            global.AppPopup.error('Lỗi kết nối server');
        };

        xhr.send('action_key=' + encodeURIComponent(actionKey));
    }

    function autoInit() {
        toArray(document.querySelectorAll('[data-leads-page]')).forEach(function (root) {
            initLeadTable(root);
        });
    }

    function initAppointmentTable(rootOrSelector) {
        var root = rootOrSelector;
        if (!root) {
            root = document.querySelector('[data-appointments-page]');
        } else if (typeof rootOrSelector === 'string') {
            root = document.querySelector(rootOrSelector);
        }

        if (!root || root.getAttribute('data-appointment-table-ready') === 'true') {
            return root;
        }

        var tableBody = root.querySelector('[data-appointment-table-body]');
        var rows = toArray(root.querySelectorAll('[data-appointment-row]'));
        var statusSelect = root.querySelector('#appointment-filter-status');
        var dateFrom = root.querySelector('#appointment-filter-date-from');
        var dateTo = root.querySelector('#appointment-filter-date-to');
        var searchInput = root.querySelector('#appointment-filter-search');
        var resetButton = root.querySelector('#appointment-filter-reset');
        var emptyRow = root.querySelector('[data-appointment-empty]');
        var pageSizeSelect = root.querySelector('[data-appointment-page-size]');
        var prevPageBtn = root.querySelector('[data-appointment-page-prev]');
        var nextPageBtn = root.querySelector('[data-appointment-page-next]');
        var pageIndicator = root.querySelector('[data-appointment-page-indicator]');
        var rangeNode = root.querySelector('[data-appointment-visible-range]');
        var filteredTotalNode = root.querySelector('[data-appointment-filtered-total]');
        var visibleCountNodes = toArray(root.querySelectorAll('[data-appointment-visible-count]'));
        var currentPage = 1;
        var defaultPageSize = parseInt((pageSizeSelect && pageSizeSelect.value) || '10', 10);

        function parseDate(value) {
            return value ? new Date(value) : null;
        }

        function getPageSize() {
            var selectedSize = parseInt(pageSizeSelect ? pageSizeSelect.value : String(defaultPageSize), 10);
            return selectedSize > 0 ? selectedSize : defaultPageSize;
        }

        function updateCounts(pageRows) {
            visibleCountNodes.forEach(function (node) {
                node.textContent = pageRows.length;
            });
        }

        function updatePagination(totalRows, pageRows, totalPages, startIndex, endIndex) {
            var hasRows = totalRows > 0;

            if (rangeNode) {
                rangeNode.textContent = hasRows ? ((startIndex + 1) + '-' + endIndex) : '0-0';
            }

            if (filteredTotalNode) {
                filteredTotalNode.textContent = totalRows;
            }

            if (pageIndicator) {
                pageIndicator.textContent = hasRows ? ('Trang ' + currentPage + ' / ' + totalPages) : 'Trang 0 / 0';
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = !hasRows || currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = !hasRows || currentPage >= totalPages;
            }

            updateCounts(pageRows);
        }

        function getFilteredRows() {
            var statusValue = statusSelect ? statusSelect.value : 'all';
            var keyword = normalize(searchInput ? searchInput.value : '');
            var fromValue = dateFrom && dateFrom.value ? new Date(dateFrom.value + 'T00:00:00') : null;
            var toValue = dateTo && dateTo.value ? new Date(dateTo.value + 'T23:59:59') : null;

            return rows.filter(function (row) {
                var rowStatus = row.getAttribute('data-status') || '';
                var rowKeyword = normalize(row.getAttribute('data-property'));
                var requestedRaw = row.getAttribute('data-requested') || '';
                var requestedDate = requestedRaw ? parseDate(requestedRaw) : null;

                if (statusValue !== 'all' && rowStatus !== statusValue) {
                    return false;
                }

                if (keyword && rowKeyword.indexOf(keyword) === -1) {
                    return false;
                }

                if (fromValue && (!requestedDate || requestedDate < fromValue)) {
                    return false;
                }

                if (toValue && (!requestedDate || requestedDate > toValue)) {
                    return false;
                }

                return true;
            });
        }

        function render() {
            var filteredRows = getFilteredRows();
            var pageSize = getPageSize();
            var totalPages = filteredRows.length > 0 ? Math.ceil(filteredRows.length / pageSize) : 0;

            if (totalPages > 0 && currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            var startIndex = filteredRows.length > 0 ? (currentPage - 1) * pageSize : 0;
            var endIndex = filteredRows.length > 0 ? Math.min(startIndex + pageSize, filteredRows.length) : 0;
            var pageRows = filteredRows.slice(startIndex, endIndex);

            rows.forEach(function (row) {
                row.style.display = 'none';
            });

            pageRows.forEach(function (row) {
                row.style.display = '';
                if (tableBody) {
                    tableBody.appendChild(row);
                }
            });

            if (emptyRow && tableBody) {
                tableBody.appendChild(emptyRow);
                emptyRow.hidden = filteredRows.length !== 0;
                emptyRow.style.display = filteredRows.length === 0 ? '' : 'none';
            }

            updatePagination(filteredRows.length, pageRows, totalPages, startIndex, endIndex);
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (dateFrom) {
            dateFrom.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (dateTo) {
            dateTo.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentPage = 1;
                render();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (statusSelect) {
                    statusSelect.value = 'all';
                }
                if (dateFrom) {
                    dateFrom.value = '';
                }
                if (dateTo) {
                    dateTo.value = '';
                }
                if (searchInput) {
                    searchInput.value = '';
                }
                currentPage = 1;
                render();
            });
        }

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                currentPage++;
                render();
            });
        }

        root.setAttribute('data-appointment-table-ready', 'true');
        render();

        return root;
    }

    function autoInitAppointments() {
        toArray(document.querySelectorAll('[data-appointments-page]')).forEach(function (root) {
            initAppointmentTable(root);
        });
    }

    function initRequestTable(rootOrSelector) {
        var root = rootOrSelector;
        if (!root) {
            root = document.querySelector('[data-requests-page]');
        } else if (typeof rootOrSelector === 'string') {
            root = document.querySelector(rootOrSelector);
        }

        if (!root || root.getAttribute('data-request-table-ready') === 'true') {
            return root;
        }

        var searchInput = root.querySelector('[data-request-search]');
        var statusSelect = root.querySelector('[data-request-status-select]');
        var sortSelect = root.querySelector('[data-request-sort-select]');
        var pageSizeSelect = root.querySelector('[data-request-page-size]');
        var prevPageBtn = root.querySelector('[data-request-page-prev]');
        var nextPageBtn = root.querySelector('[data-request-page-next]');
        var pageIndicator = root.querySelector('[data-request-page-indicator]');
        var rangeNode = root.querySelector('[data-request-visible-range]');
        var filteredTotalNode = root.querySelector('[data-request-filtered-total]');
        var cards = toArray(root.querySelectorAll('[data-status-card]'));
        var rows = toArray(root.querySelectorAll('[data-request-row]'));
        var emptyRow = root.querySelector('[data-requests-empty]');
        var visibleCountNodes = toArray(root.querySelectorAll('[data-request-visible-count]'))
            .concat(toArray(root.querySelectorAll('[data-request-visible-count-footer]')));
        var activeStatus = 'all';
        var currentPage = 1;
        var defaultPageSize = parseInt((pageSizeSelect && pageSizeSelect.value) || '10', 10);

        function setCardActive(status) {
            cards.forEach(function (card) {
                var isActive = card.getAttribute('data-status-card') === status;
                card.classList.toggle('is-active', isActive);
                card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function compareRows(a, b) {
            var sortKey = sortSelect ? sortSelect.value : 'newest';
            var aName = normalize(a.getAttribute('data-request-name'));
            var bName = normalize(b.getAttribute('data-request-name'));
            var aTs = parseInt(a.getAttribute('data-request-sort-ts') || '0', 10);
            var bTs = parseInt(b.getAttribute('data-request-sort-ts') || '0', 10);

            if (sortKey === 'oldest') {
                return aTs - bTs;
            }

            if (sortKey === 'name_asc') {
                return aName.localeCompare(bName, 'vi');
            }

            if (sortKey === 'name_desc') {
                return bName.localeCompare(aName, 'vi');
            }

            return bTs - aTs;
        }

        function updateCounts(pageRows) {
            visibleCountNodes.forEach(function (node) {
                node.textContent = pageRows.length;
            });
        }

        function getPageSize() {
            var selectedSize = parseInt(pageSizeSelect ? pageSizeSelect.value : String(defaultPageSize), 10);
            return selectedSize > 0 ? selectedSize : defaultPageSize;
        }

        function updatePagination(totalRows, pageRows, totalPages, startIndex, endIndex) {
            var hasRows = totalRows > 0;

            if (rangeNode) {
                rangeNode.textContent = hasRows ? ((startIndex + 1) + '-' + endIndex) : '0-0';
            }

            if (filteredTotalNode) {
                filteredTotalNode.textContent = totalRows;
            }

            if (pageIndicator) {
                pageIndicator.textContent = hasRows ? ('Trang ' + currentPage + ' / ' + totalPages) : 'Trang 0 / 0';
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = !hasRows || currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = !hasRows || currentPage >= totalPages;
            }

            updateCounts(pageRows);
        }

        function render() {
            var query = normalize(searchInput ? searchInput.value : '');
            var selectedStatus = statusSelect ? statusSelect.value : 'all';
            var pageSize = getPageSize();

            var visibleRows = rows.filter(function (row) {
                var requestName = normalize(row.getAttribute('data-request-name'));
                var requestStatus = normalize(row.getAttribute('data-request-status'));
                var matchesQuery = query === '' || requestName.indexOf(query) !== -1;
                var matchesCard = activeStatus === 'all' || requestStatus === activeStatus;
                var matchesStatus = selectedStatus === 'all' || requestStatus === selectedStatus;

                return matchesQuery && matchesCard && matchesStatus;
            });

            visibleRows.sort(compareRows);

            var totalPages = visibleRows.length > 0 ? Math.ceil(visibleRows.length / pageSize) : 0;
            if (totalPages > 0 && currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            var startIndex = visibleRows.length > 0 ? (currentPage - 1) * pageSize : 0;
            var endIndex = visibleRows.length > 0 ? Math.min(startIndex + pageSize, visibleRows.length) : 0;
            var pageRows = visibleRows.slice(startIndex, endIndex);

            rows.forEach(function (row) {
                row.style.display = 'none';
            });

            pageRows.forEach(function (row) {
                row.style.display = '';
                row.parentNode.appendChild(row);
            });

            if (emptyRow) {
                emptyRow.parentNode.appendChild(emptyRow);
                emptyRow.hidden = visibleRows.length !== 0;
            }

            updatePagination(visibleRows.length, pageRows, totalPages, startIndex, endIndex);
        }

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                activeStatus = card.getAttribute('data-status-card') || 'all';
                if (statusSelect) {
                    statusSelect.value = activeStatus;
                }
                currentPage = 1;
                setCardActive(activeStatus);
                render();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentPage = 1;
                render();
            });
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                activeStatus = statusSelect.value || 'all';
                currentPage = 1;
                setCardActive(activeStatus);
                render();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                currentPage = 1;
                render();
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                currentPage++;
                render();
            });
        }

        root.setAttribute('data-request-table-ready', 'true');
        setCardActive(activeStatus);
        render();

        return root;
    }

    function autoInitRequests() {
        toArray(document.querySelectorAll('[data-requests-page]')).forEach(function (root) {
            initRequestTable(root);
        });
    }

    global.LeadTable = {
        init: initLeadTable,
        handleAction: handleLeadAction,
        initAppointments: initAppointmentTable,
        initRequests: initRequestTable
    };
    global.handleLeadAction = handleLeadAction;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            autoInit();
            autoInitAppointments();
            autoInitRequests();
        }, { once: true });
    } else {
        autoInit();
        autoInitAppointments();
        autoInitRequests();
    }
}(window));
