<section class="agent-section">
    <div class="agent-section-head">
        <div class="agent-section-copy">
            <h3 class="agent-section-title">Theo dõi toàn bộ trạng thái tin đăng</h3>
        </div>
    </div>

    <form method="get" action="<?= BASEURL ?>/agentWorkspace/index" class="agent-search-row">
        <input type="hidden" name="section" value="posts">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($activePostTab) ?>">
        <div class="agent-search-box">
            <i class="fa fa-search"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($searchKeyword) ?>"
                placeholder="Nhập mã tin hoặc tiêu đề tin">
        </div>
        <div class="agent-actions">
            <button type="submit" class="agent-btn"><i class="fa fa-filter"></i> Áp dụng bộ lọc</button>
            <a class="agent-btn" href="<?= BASEURL ?>/agentWorkspace/index?section=posts&tab=all"><i
                    class="fa fa-refresh"></i> Đặt lại</a>
            <a class="agent-btn" href="<?= BASEURL ?>/agentWorkspace/index?section=create"><i
                    class="fa fa-plus-circle"></i> Đăng tin mới</a>
        </div>
    </form>

    <div class="agent-tabs" role="tablist" aria-label="Trạng thái tin đăng">
        <?php foreach ($postTabs as $tabKey => $tabLabel): ?>
            <a class="agent-tab <?= $activePostTab === $tabKey ? 'active' : '' ?>"
                href="<?= BASEURL ?>/agentWorkspace/index?section=posts&tab=<?= urlencode($tabKey) ?>&q=<?= urlencode($searchKeyword) ?>">
                <span><?= htmlspecialchars($tabLabel) ?></span>
                <span class="agent-tab-count"><?= (int) ($tabCounts[$tabKey] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="listing-wrap">
        <?php if (empty($pagedPosts)): ?>
            <div class="listing-card">
                <div class="listing-empty">Chưa có tin đăng phù hợp với điều kiện hiện tại.</div>
            </div>
        <?php else: ?>
            <?php foreach ($pagedPosts as $row): ?>
                <?php
                $pid = (int) ($row['pid'] ?? 0);
                $title = (string) ($row['title'] ?? ('Tin #' . $pid));
                $location = trim((string) ($row['location'] ?? 'Chưa cập nhật vị trí'));
                $price = formatWorkspacePrice((float) ($row['price'] ?? 0), (string) ($row['stype'] ?? 'sale'));
                $thumb = trim((string) ($row['pimage'] ?? ''));
                $thumbUrl = $thumb !== '' ? (BASEURL . '/admin/property/' . rawurlencode($thumb)) : (BASEURL . '/images/noimage.jpg');
                $tabInfo = agentWorkspacePostBadge((string) ($row['_tab_key'] ?? 'displaying'));
                $desc = agentWorkspaceListingSummary($row, $location . ' | ' . $price);
                $viewCount = (int) ($row['view_count'] ?? 0);
                $customerCount = (int) ($customerCountMap[$pid] ?? 0);
                $postDate = !empty($row['date']) ? date('d/m/Y', strtotime((string) $row['date'])) : '--/--/----';
                ?>
                <article class="listing-card">
                    <div>
                        <img class="listing-thumb" src="<?= htmlspecialchars($thumbUrl) ?>" alt="thumbnail">
                    </div>

                    <div class="listing-copy">
                        <span
                            class="listing-status <?= htmlspecialchars((string) $tabInfo[1]) ?>"><?= htmlspecialchars((string) $tabInfo[0]) ?></span>
                        <h4 class="listing-title"><?= htmlspecialchars($title) ?></h4>
                        <div class="listing-sub"><?= htmlspecialchars($desc) ?></div>

                        <div class="listing-tags">
                            <span class="listing-chip"><i class="fa fa-map-marker"></i>
                                <?= htmlspecialchars($location) ?></span>
                            <span class="listing-chip"><i class="fa fa-money"></i> <?= htmlspecialchars($price) ?></span>
                            <span class="listing-chip"><i class="fa fa-hashtag"></i> Mã tin #<?= $pid ?></span>
                        </div>

                        <div class="listing-actions">
                            <a class="agent-btn-solid" href="<?= BASEURL ?>/property/update/<?= $pid ?>"><i
                                    class="fa fa-pencil"></i> Chỉnh sửa</a>
                            <a class="agent-btn" href="<?= BASEURL ?>/property/detail/<?= $pid ?>"><i
                                    class="fa fa-external-link"></i> Xem chi tiết</a>
                            <a class="agent-btn" href="<?= BASEURL ?>/property/delete/<?= $pid ?>"
                                onclick="event.preventDefault(); var url=this.href; AppPopup.confirm('Bạn có chắc muốn xóa tin này không?', function() { window.location.href = url; });"><i class="fa fa-trash"></i> Xóa
                                tin</a>
                        </div>
                    </div>

                    <div class="listing-meta">
                        <div class="listing-meta-card">
                            <span>Lượt xem</span>
                            <strong><?= $viewCount ?></strong>
                        </div>
                        <div class="listing-meta-card">
                            <span>Khách quan tâm</span>
                            <strong><?= $customerCount ?></strong>
                        </div>
                        <div class="listing-meta-card">
                            <span>Ngày đăng</span>
                            <strong><?= htmlspecialchars($postDate) ?></strong>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPostPages > 1): ?>
        <?php
        $startPage = max(1, $currentPostPage - 2);
        $endPage = min($totalPostPages, $currentPostPage + 2);
        ?>
        <nav class="agent-pagination" aria-label="Phan trang danh sach tin dang">
            <?php if ($currentPostPage > 1): ?>
                <a class="agent-page-link"
                    href="<?= BASEURL ?>/agentWorkspace/index?<?= htmlspecialchars(http_build_query(array_merge($postQueryBase, ['page' => $currentPostPage - 1]))) ?>">Trước</a>
            <?php endif; ?>

            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                <a class="agent-page-link <?= $page === $currentPostPage ? 'active' : '' ?>"
                    href="<?= BASEURL ?>/agentWorkspace/index?<?= htmlspecialchars(http_build_query(array_merge($postQueryBase, ['page' => $page]))) ?>"><?= (int) $page ?></a>
            <?php endfor; ?>

            <?php if ($currentPostPage < $totalPostPages): ?>
                <a class="agent-page-link"
                    href="<?= BASEURL ?>/agentWorkspace/index?<?= htmlspecialchars(http_build_query(array_merge($postQueryBase, ['page' => $currentPostPage + 1]))) ?>">Sau</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>