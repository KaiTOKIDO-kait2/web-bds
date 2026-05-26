<div class="bg-white p-4 rounded shadow-sm">
    <h4 class="mb-3"><?= htmlspecialchars((string) ($detailLogsTitle ?? 'Nhật ký')) ?></h4>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <?php foreach (($detailLogHeaders ?? ['Thời gian', 'Hành động', 'Từ', 'Sang', 'Người cập nhật']) as $header): ?>
                        <th><?= htmlspecialchars((string) $header) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($logs ?? []) as $log): ?>
                    <tr>
                        <td><?= !empty($log['created_at']) ? date('d-m-Y H:i:s', strtotime((string) $log['created_at'])) : '' ?></td>
                        <td><?= htmlspecialchars((string) ($log['action_label'] ?? $log['action_key'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['old_value'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['new_value'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['actor_name'] ?? $log['actor_type'] ?? 'system')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>