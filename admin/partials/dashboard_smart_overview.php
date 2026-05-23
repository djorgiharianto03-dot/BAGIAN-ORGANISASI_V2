<?php

/** @var string $adminName */
/** @var string $adminRoleLabel */
/** @var array<string, mixed> $dashMetrics */
/** @var bool $isSubAdminPublikasiActor @var bool $canManagePerpustakaanDokumen */
/** @var bool $auditRiwayatVisible */

$showTamuChart = !$isSubAdminPublikasiActor;
$showDownloadChart = !$isSubAdminPublikasiActor && count($dashMetrics['download_labels'] ?? []) > 0;
$hariIniLabel = date('l, d F Y');
$orgScore = (int) ($dashMetrics['org_score'] ?? 0);
$kpiPelayanan = (int) ($dashMetrics['kpi_pelayanan'] ?? 0);
$teamPct = (int) ($dashMetrics['team_progress_pct'] ?? 0);
$teamDone = (int) ($dashMetrics['team_selesai'] ?? 0);
$teamTotal = (int) ($dashMetrics['team_total'] ?? 0);
$timLabels = $dashMetrics['team_tim_labels'] ?? [];
$timPct = $dashMetrics['team_tim_pct'] ?? [];
$activityItems = $dashMetrics['recent_activity'] ?? [];
$hasHeatmap = count($dashMetrics['heatmap_series'] ?? []) > 0;
$hasTarget = count($dashMetrics['target_labels'] ?? []) > 0;
?>
<section class="sg-overview dash-section" id="panel-dashboard-overview" aria-label="Smart Governance Dashboard">
    <div class="sg-hero sg-fade-in">
        <p class="sg-hero__eyebrow">Smart Governance · Bagian Organisasi</p>
        <h1 class="sg-hero__title">Selamat datang, <?php echo $adminName; ?></h1>
        <p class="sg-hero__sub">Platform digital terintegrasi untuk monitoring kinerja, pelayanan publik, dan administrasi organisasi Sekretariat Daerah.</p>
        <div class="sg-hero__chips">
            <span class="sg-hero__chip"><?php echo $adminRoleLabel; ?></span>
            <span class="sg-hero__chip"><?php echo htmlspecialchars($hariIniLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($showTamuChart): ?>
                <span class="sg-hero__chip"><?php echo (int) $dashMetrics['tamu_hari_ini']; ?> tamu hari ini</span>
            <?php endif; ?>
            <span class="sg-hero__chip">Skor organisasi <?php echo $orgScore; ?>%</span>
        </div>
    </div>

    <div class="sg-kpi-grid sg-fade-in" style="animation-delay: 0.05s">
        <?php if (!$isSubAdminPublikasiActor): ?>
        <article class="sg-kpi-card sg-kpi-card--blue">
            <div class="sg-kpi-card__icon" aria-hidden="true"><i data-lucide="file-text"></i></div>
            <div>
                <p class="sg-kpi-card__label">Dokumen perpustakaan</p>
                <p class="sg-kpi-card__value" data-sg-counter="<?php echo (int) $dashMetrics['dokumen_total']; ?>">0</p>
                <p class="sg-kpi-card__hint"><?php echo number_format((int) $dashMetrics['unduhan_total'], 0, ',', '.'); ?> total unduhan</p>
            </div>
        </article>
        <article class="sg-kpi-card sg-kpi-card--violet">
            <div class="sg-kpi-card__icon" aria-hidden="true"><i data-lucide="users"></i></div>
            <div>
                <p class="sg-kpi-card__label">Tamu / kunjungan</p>
                <p class="sg-kpi-card__value" data-sg-counter="<?php echo (int) $dashMetrics['tamu_hari_ini']; ?>">0</p>
                <p class="sg-kpi-card__hint"><?php echo (int) $dashMetrics['tamu_minggu']; ?> minggu ini (7 hari)</p>
            </div>
        </article>
        <?php endif; ?>
        <article class="sg-kpi-card sg-kpi-card--emerald">
            <div class="sg-kpi-card__icon" aria-hidden="true"><i data-lucide="newspaper"></i></div>
            <div>
                <p class="sg-kpi-card__label">Pusat informasi</p>
                <p class="sg-kpi-card__value" data-sg-counter="<?php echo (int) $dashMetrics['berita_total'] + (int) $dashMetrics['pengumuman_total']; ?>">0</p>
                <p class="sg-kpi-card__hint"><?php echo (int) $dashMetrics['berita_total']; ?> berita · <?php echo (int) $dashMetrics['pengumuman_total']; ?> pengumuman</p>
            </div>
        </article>
        <article class="sg-kpi-card sg-kpi-card--amber">
            <div class="sg-kpi-card__icon" aria-hidden="true"><i data-lucide="image"></i></div>
            <div>
                <p class="sg-kpi-card__label">Galeri &amp; masukan</p>
                <p class="sg-kpi-card__value" data-sg-counter="<?php echo (int) $dashMetrics['galeri_total']; ?>">0</p>
                <p class="sg-kpi-card__hint"><?php echo (int) $dashMetrics['saran_total']; ?> saran pengunjung</p>
            </div>
        </article>
    </div>

    <div class="sg-metrics-row sg-metrics-row--triple sg-fade-in" style="animation-delay: 0.1s">
        <div class="sg-glass-panel sg-metric-card">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Skor organisasi</h2>
                    <p class="sg-panel-head__sub">Indeks kesiapan digital &amp; konten</p>
                </div>
                <span class="sg-badge sg-badge--live">Live</span>
            </div>
            <div class="sg-metric-card__body">
                <div class="sg-gauge-wrap">
                    <div class="sg-gauge sg-gauge--blue" data-sg-gauge="<?php echo $orgScore; ?>" style="--sg-pct: 0" role="img" aria-label="Skor organisasi <?php echo $orgScore; ?> persen">
                        <div class="sg-gauge__inner">
                            <span class="sg-gauge__value"><span data-sg-gauge-counter="<?php echo $orgScore; ?>">0</span><span class="sg-gauge__unit">%</span></span>
                        </div>
                    </div>
                </div>
                <div class="sg-metric-card__details">
                    <p class="sg-metric-card__desc">Komposit staf, konten, galeri, dan KPI pelayanan.</p>
                    <div class="sg-progress-item">
                        <div class="sg-progress-item__head"><span>KPI Pelayanan</span><span><?php echo $kpiPelayanan; ?>%</span></div>
                        <div class="sg-progress-bar"><div class="sg-progress-bar__fill" data-sg-progress="<?php echo $kpiPelayanan; ?>"></div></div>
                    </div>
                    <div class="sg-progress-item">
                        <div class="sg-progress-item__head"><span>Progress tim kerja</span><span><?php echo $teamPct; ?>%</span></div>
                        <div class="sg-progress-bar"><div class="sg-progress-bar__fill sg-progress-bar__fill--success" data-sg-progress="<?php echo $teamPct; ?>"></div></div>
                    </div>
                    <?php if ($teamTotal > 0): ?>
                        <p class="sg-metric-card__footnote"><?php echo $teamDone; ?> dari <?php echo $teamTotal; ?> target selesai</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="sg-glass-panel sg-metric-card">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">KPI pelayanan publik</h2>
                    <p class="sg-panel-head__sub">Unduhan, kunjungan, dan informasi</p>
                </div>
            </div>
            <div class="sg-metric-card__body sg-metric-card__body--center">
                <div class="sg-gauge-wrap">
                    <div class="sg-gauge sg-gauge--emerald" data-sg-gauge="<?php echo $kpiPelayanan; ?>" style="--sg-pct: 0" role="img" aria-label="KPI pelayanan <?php echo $kpiPelayanan; ?> persen">
                        <div class="sg-gauge__inner">
                            <span class="sg-gauge__value"><span data-sg-gauge-counter="<?php echo $kpiPelayanan; ?>">0</span><span class="sg-gauge__unit">%</span></span>
                        </div>
                    </div>
                </div>
                <p class="sg-metric-card__desc sg-metric-card__desc--center">Indeks gabungan aktivitas layanan digital publik.</p>
            </div>
        </div>

        <?php if ($teamTotal > 0 || count($timLabels) > 0): ?>
        <div class="sg-glass-panel sg-metric-card">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Progress tim kerja</h2>
                    <p class="sg-panel-head__sub">Per tim — tahun berjalan</p>
                </div>
                <span class="sg-metric-card__total"><?php echo $teamPct; ?>%</span>
            </div>
            <div class="sg-metric-card__body sg-metric-card__body--list">
            <?php foreach ($timLabels as $ti => $timLabel): ?>
                <?php $pct = (int) ($timPct[$ti] ?? 0); ?>
                <div class="sg-progress-item">
                    <div class="sg-progress-item__head">
                        <span><?php echo htmlspecialchars((string) $timLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo $pct; ?>%</span>
                    </div>
                    <div class="sg-progress-bar">
                        <div class="sg-progress-bar__fill" data-sg-progress="<?php echo $pct; ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="sg-glass-panel sg-metric-card sg-metric-card--empty">
            <p class="sg-panel-head__title mb-1">Target tim kerja</p>
            <p class="sg-panel-head__sub mb-0">Atur target di <a href="<?php echo org_href('admin/kelola_team_targets.php'); ?>">Target Tim Kerja</a></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="sg-metrics-row sg-fade-in" style="animation-delay: 0.15s">
        <?php if ($showTamuChart): ?>
        <div class="sg-glass-panel">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Kunjungan buku tamu</h2>
                    <p class="sg-panel-head__sub">Tren 14 hari terakhir</p>
                </div>
                <span class="sg-badge">Area</span>
            </div>
            <div id="admChartTamu" class="sg-chart adm-chart" role="img" aria-label="Grafik kunjungan tamu"></div>
        </div>
        <?php endif; ?>

        <?php if ($showDownloadChart): ?>
        <div class="sg-glass-panel">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Dokumen terpopuler</h2>
                    <p class="sg-panel-head__sub">Berdasarkan jumlah unduhan</p>
                </div>
                <span class="sg-badge">Bar</span>
            </div>
            <div id="admChartDownloads" class="sg-chart adm-chart" role="img" aria-label="Grafik unduhan dokumen"></div>
        </div>
        <?php elseif ($showTamuChart): ?>
        <div class="sg-glass-panel">
            <h2 class="sg-panel-head__title">Dokumen terpopuler</h2>
            <p class="sg-panel-head__sub mb-0">Belum ada data unduhan untuk ditampilkan.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="sg-metrics-row sg-fade-in" style="animation-delay: 0.2s">
        <?php if ($hasHeatmap): ?>
        <div class="sg-glass-panel">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Heatmap aktivitas</h2>
                    <p class="sg-panel-head__sub">Audit log 7 hari — jam operasional</p>
                </div>
                <span class="sg-badge">Realtime</span>
            </div>
            <div id="sgChartHeatmap" class="sg-chart" role="img" aria-label="Heatmap aktivitas"></div>
        </div>
        <?php endif; ?>

        <?php if ($hasTarget): ?>
        <div class="sg-glass-panel">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Target vs realisasi</h2>
                    <p class="sg-panel-head__sub">Capaian per tim kerja (%)</p>
                </div>
            </div>
            <div id="sgChartTargetRealisasi" class="sg-chart" role="img" aria-label="Target vs realisasi"></div>
        </div>
        <?php endif; ?>

        <?php if ($auditRiwayatVisible): ?>
        <div class="sg-glass-panel" id="panel-aktivitas-live">
            <div class="sg-panel-head">
                <div>
                    <h2 class="sg-panel-head__title">Monitoring aktivitas</h2>
                    <p class="sg-panel-head__sub">Riwayat audit terbaru</p>
                </div>
                <a href="#panel-audit" class="sg-btn-ghost btn btn-sm" data-sidebar-link data-section-target="panel-audit">Semua</a>
            </div>
            <div class="sg-activity-live" data-adm-activity-feed>
                <?php if (count($activityItems) === 0): ?>
                    <p class="text-muted small mb-0">Belum ada entri audit.</p>
                <?php else: ?>
                    <?php foreach ($activityItems as $act): ?>
                    <div class="sg-activity-live__item">
                        <span class="sg-activity-live__pulse" aria-hidden="true"></span>
                        <div>
                            <strong><?php echo htmlspecialchars((string) $act['admin'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            — <?php echo htmlspecialchars((string) $act['aksi'], ENT_QUOTES, 'UTF-8'); ?>
                            <div class="text-muted small"><?php echo htmlspecialchars((string) $act['waktu_rel'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="sg-glass-panel sg-fade-in" style="animation-delay: 0.25s">
        <div class="sg-panel-head">
            <div>
                <h2 class="sg-panel-head__title">Smart quick actions</h2>
                <p class="sg-panel-head__sub">Akses cepat modul utama</p>
            </div>
        </div>
        <div class="sg-quick-grid">
            <?php if ($canManagePerpustakaanDokumen): ?>
            <a class="sg-quick-btn" href="#panel-unggah-dokumen" data-sidebar-link data-section-target="panel-unggah-dokumen" data-sg-module="dokumen">
                <span class="sg-quick-btn__icon"><i data-lucide="cloud-upload"></i></span>
                Unggah dokumen
            </a>
            <?php endif; ?>
            <a class="sg-quick-btn" href="#tab-pusat" data-sidebar-link data-tab-target="tab-pusat" data-bs-toggle="tab" data-bs-target="#tab-pusat" role="tab">
                <span class="sg-quick-btn__icon"><i data-lucide="newspaper"></i></span>
                Berita &amp; info
            </a>
            <a class="sg-quick-btn" href="#tab-galeri" data-sidebar-link data-tab-target="tab-galeri" data-bs-toggle="tab" data-bs-target="#tab-galeri" role="tab">
                <span class="sg-quick-btn__icon"><i data-lucide="images"></i></span>
                Galeri
            </a>
            <a class="sg-quick-btn" href="<?php echo org_href('admin/kelola_team_targets.php'); ?>">
                <span class="sg-quick-btn__icon"><i data-lucide="target"></i></span>
                Target tim
            </a>
        </div>
    </div>
</section>
