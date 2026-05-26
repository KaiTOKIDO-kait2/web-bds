<div class="bg-white p-4 rounded shadow-sm mb-4">
    <h4 class="mb-3"><?= htmlspecialchars((string) ($detailInfoTitle ?? 'Thông tin')) ?></h4>
    <?php foreach (($detailInfoRows ?? []) as $item): ?>
        <div class="mb-2">
            <strong><?= htmlspecialchars((string) ($item['label'] ?? '')) ?>:</strong>
            <span><?= $item['value'] ?? '' ?></span>
        </div>
    <?php endforeach; ?>
</div>