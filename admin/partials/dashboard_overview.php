<?php

/** @var string $adminName */
/** @var string $adminRoleLabel */
/** @var array<string, mixed> $dashMetrics */
/** @var bool $isSubAdminPublikasiActor */
$showTamuChart = !$isSubAdminPublikasiActor;
$showDownloadChart = !$isSubAdminPublikasiActor && count($dashMetrics['download_labels'] ?? []) > 0;
$hariIniLabel = date('l, d F Y');
?>
<section class="adm-overview dash-section" id="panel-dashboard-overview" aria-label="Ringkasan dashboard">
    <div class="adm-welcome">
        <h1 class="adm-welcome__title">Selamat datang, <?php echo $adminName; ?></h1>
        <p class="adm-welcome__sub">Kelola konten publik, perpustakaan digital, dan aktivitas internal Bagian Organisasi dari satu workspace yang terintegrasi.</p>
        <div class="adm-welcome__meta">
            <span class="adm-welcome__chip"><?php echo $adminRoleLabel; ?></span>
            <span class="adm-welcome__chip"><?php echo htmlspecialchars($hariIniLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($showTamuChart): ?>
                <span class="adm-welcome__chip"><?php echo (int) $dashMetrics['tamu_hari_ini']; ?> tamu hari ini</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="adm-stats-grid">
        <?php if (!$isSubAdminPublikasiActor): ?>
        <article class="adm-stat-card adm-stat-card--blue">
            <div class="adm-stat-card__glow" aria-hidden="true"></div>
            <div class="adm-stat-card__icon-wrap" aria-hidden="true"><i data-lucide="file-text"></i></div>
            <div class="adm-stat-card__body">
                <p class="adm-stat-card__label">Dokumen perpustakaan</p>
                <p class="adm-stat-card__value"><?php echo number_format((int) $dashMetrics['dokumen_total'], 0, ',', '.'); ?></p>
                <p class="adm-stat-card__hint"><?php echo number_format((int) $dashMetrics['unduhan_total'], 0, ',', '.'); ?> total unduhan</p>
            </div>
        </article>
        <article class="adm-stat-card adm-stat-card--violet">
            <div class="adm-stat-card__glow" aria-hidden="true"></div>
            <div class="adm-stat-card__icon-wrap" aria-hidden="true"><i data-lucide="users"></i></div>
            <div class="adm-stat-card__body">
                <p class="adm-stat-card__label">Tamu / kunjungan</p>
                <p class="adm-stat-card__value"><?php echo (int) $dashMetrics['tamu_hari_ini']; ?></p>
                <p class="adm-stat-card__hint"><?php echo (int) $dashMetrics['tamu_minggu']; ?> minggu ini (7 hari)</p>
            </div>
        </article>
        <?php endif; ?>
        <article class="adm-stat-card adm-stat-card--emerald">
            <div class="adm-stat-card__glow" aria-hidden="true"></div>
            <div class="adm-stat-card__icon-wrap" aria-hidden="true"><i data-lucide="newspaper"></i></div>
            <div class="adm-stat-card__body">
                <p class="adm-stat-card__label">Pusat informasi</p>
                <p class="adm-stat-card__value"><?php echo (int) $dashMetrics['berita_total'] + (int) $dashMetrics['pengumuman_total']; ?></p>
                <p class="adm-stat-card__hint"><?php echo (int) $dashMetrics['berita_total']; ?> berita · <?php echo (int) $dashMetrics['pengumuman_total']; ?> pengumuman</p>
            </div>
        </article>
        <article class="adm-stat-card adm-stat-card--amber">
            <div class="adm-stat-card__glow" aria-hidden="true"></div>
            <div class="adm-stat-card__icon-wrap" aria-hidden="true"><i data-lucide="image"></i></div>
            <div class="adm-stat-card__body">
                <p class="adm-stat-card__label">Galeri &amp; masukan</p>
                <p class="adm-stat-card__value"><?php echo (int) $dashMetrics['galeri_total']; ?></p>
                <p class="adm-stat-card__hint"><?php echo (int) $dashMetrics['saran_total']; ?> saran pengunjung</p>
            </div>
        </article>
    </div>

    <div class="adm-panel-grid">
        <?php if ($showTamuChart): ?>
        <div class="adm-panel adm-panel--chart adm-chart-card">
            <div class="adm-panel__head adm-chart-card__head">
                <div>
                    <h2 class="adm-panel__title adm-chart-card__title">Kunjungan buku tamu</h2>
                    <p class="adm-panel__desc adm-chart-card__subtitle">Tren 14 hari terakhir — data dari tabel tamu</p>
                </div>
                <span class="adm-chart-card__badge">Area</span>
            </div>
            <div class="adm-chart-card__body">
                <div id="admChartTamu" class="adm-chart" role="img" aria-label="Grafik kunjungan tamu"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($showDownloadChart): ?>
        <div class="adm-panel adm-panel--chart adm-chart-card">
            <div class="adm-panel__head adm-chart-card__head">
                <div>
                    <h2 class="adm-panel__title adm-chart-card__title">Dokumen terpopuler</h2>
                    <p class="adm-panel__desc adm-chart-card__subtitle">Berdasarkan jumlah unduhan</p>
                </div>
                <span class="adm-chart-card__badge">Bar</span>
            </div>
            <div class="adm-chart-card__body">
                <div id="admChartDownloads" class="adm-chart" role="img" aria-label="Grafik unduhan dokumen"></div>
            </div>
        </div>
        <?php elseif ($showTamuChart): ?>
        <div class="adm-panel adm-panel--chart adm-chart-card adm-chart-card--empty">
            <div class="adm-panel__head adm-chart-card__head">
                <div>
                    <h2 class="adm-panel__title adm-chart-card__title">Dokumen terpopuler</h2>
                    <p class="adm-panel__desc adm-chart-card__subtitle">Belum ada data unduhan untuk ditampilkan</p>
                </div>
            </div>
            <p class="adm-chart-card__empty-text mb-0">Unggah dokumen dan tunggu aktivitas unduhan dari halaman publik.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
