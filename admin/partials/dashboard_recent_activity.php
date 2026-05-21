<?php
declare(strict_types=1);

/** @var array<string, mixed> $dashMetrics */
/** @var bool $auditRiwayatVisible */

if (!$auditRiwayatVisible) {
    return;
}

$activityItems = $dashMetrics['recent_activity'] ?? [];
$activityCount = count($activityItems);
?>
<?php if ($activityCount > 0): ?>
<section class="adm-panel dash-section adm-activity-panel" id="panel-aktivitas-terbaru" aria-labelledby="adm-activity-title">
    <div class="adm-panel__head">
        <div>
            <h2 id="adm-activity-title" class="adm-panel__title">Aktivitas terbaru</h2>
            <p class="adm-panel__desc">Riwayat audit — entri terbaru di bagian bawah</p>
        </div>
        <a href="#panel-audit" class="adm-btn-ghost btn btn-sm">Lihat semua</a>
    </div>
    <ul class="adm-activity-list" data-adm-activity-feed>
        <?php foreach ($activityItems as $actIndex => $act): ?>
        <li class="adm-activity-item<?php echo ($actIndex === $activityCount - 1) ? ' adm-activity-item--latest' : ''; ?>">
            <span class="adm-activity-item__dot" aria-hidden="true"></span>
            <div>
                <p class="adm-activity-item__text">
                    <strong><?php echo htmlspecialchars((string) $act['admin'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    — <?php echo htmlspecialchars((string) $act['aksi'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="adm-activity-item__meta"><?php echo htmlspecialchars((string) $act['waktu_rel'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php else: ?>
<section class="adm-panel dash-section adm-activity-panel" id="panel-aktivitas-terbaru">
    <h2 class="adm-panel__title">Aktivitas terbaru</h2>
    <p class="adm-panel__desc mb-0">Belum ada entri audit. Perubahan konten akan muncul di sini.</p>
</section>
<?php endif; ?>
