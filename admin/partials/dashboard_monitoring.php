<?php

/** @var string $adminName @var string $adminRoleLabel */
/** @var array<string, mixed> $dashMetrics */
/** @var bool $isSubAdminPublikasiActor @var bool $auditRiwayatVisible */

$orgScore = (int) ($dashMetrics['org_score'] ?? 0);
$kpiPelayanan = (int) ($dashMetrics['kpi_pelayanan'] ?? 0);
$layananTotal = (int) ($dashMetrics['layanan_total'] ?? 0);
$kinerjaPct = (int) ($dashMetrics['kinerja_pegawai_pct'] ?? 0);
$kepuasan = (int) ($dashMetrics['kepuasan_publik'] ?? 0);
$tamuHari = (int) ($dashMetrics['tamu_hari_ini'] ?? 0);
$teamPct = (int) ($dashMetrics['team_progress_pct'] ?? 0);
$activityItems = $dashMetrics['recent_activity'] ?? [];
$hasHeatmap = count($dashMetrics['heatmap_series'] ?? []) > 0;
$hasTarget = count($dashMetrics['target_labels'] ?? []) > 0;
$showTamu = !$isSubAdminPublikasiActor;
$saranTotal = (int) ($dashMetrics['saran_total'] ?? 0);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dashboard_formula_breakdown.php';
$fb = admin_dashboard_formula_breakdown($dashMetrics, $saranTotal);

$insights = [];
if ($teamPct >= 80) {
    $insights[] = ['tone' => 'success', 'title' => 'Target tim on-track', 'text' => 'Capaian tim kerja ' . $teamPct . '% — pertahankan momentum penyelesaian target.'];
} elseif ($teamPct > 0) {
    $insights[] = ['tone' => 'warning', 'title' => 'Percepat target tim', 'text' => 'Progress tim ' . $teamPct . '%. Tinjau modul Tim Kerja di sidebar.'];
}
if ($tamuHari > 0) {
    $insights[] = ['tone' => 'info', 'title' => 'Kunjungan aktif', 'text' => $tamuHari . ' tamu hari ini — layanan digital mendapat perhatian publik.'];
}
if ($kpiPelayanan < 30) {
    $insights[] = ['tone' => 'warning', 'title' => 'Tingkatkan pelayanan digital', 'text' => 'KPI pelayanan ' . $kpiPelayanan . '%. Pertimbangkan penambahan konten dan promosi dokumen.'];
} else {
    $insights[] = ['tone' => 'success', 'title' => 'Kinerja layanan stabil', 'text' => 'Indeks pelayanan publik berada di ' . $kpiPelayanan . '%.'];
}
if (count($insights) < 2) {
    $insights[] = ['tone' => 'info', 'title' => 'Smart Governance aktif', 'text' => 'Dashboard ini khusus monitoring. Kelola data operasional melalui menu sidebar.'];
}
?>
<div class="sg-monitor" id="panel-dashboard-overview">
    <header class="sg-monitor-hero sg-fade-in">
        <div class="sg-monitor-hero__copy">
            <p class="sg-monitor-hero__eyebrow">Smart Governance · Monitoring Center</p>
            <h1 class="sg-monitor-hero__title">Dashboard Monitoring</h1>
            <p class="sg-monitor-hero__sub">Analitik real-time kinerja organisasi, layanan publik, dan aktivitas sistem — tanpa form operasional di halaman ini.</p>
        </div>
        <div class="sg-monitor-hero__meta">
            <span class="sg-monitor-hero__chip"><?php echo $adminRoleLabel; ?></span>
            <span class="sg-monitor-hero__chip"><?php echo date('d M Y'); ?></span>
            <span class="sg-monitor-hero__chip">Skor <?php echo $orgScore; ?>%</span>
        </div>
    </header>

    <details class="sg-formula-guide sg-fade-in">
        <summary class="sg-formula-guide__toggle">
            <i data-lucide="book-open" aria-hidden="true"></i>
            Panduan membaca indikator &amp; rumus perhitungan
        </summary>
        <div class="sg-formula-guide__body">
            <p class="mb-2">Semua angka di halaman ini adalah <strong>indeks internal dashboard</strong> (bukan survei resmi). Klik <em>Rumus &amp; data</em> pada setiap kartu/grafik untuk melihat sumber data dan hitungan saat ini.</p>
            <div class="row g-2 small">
                <div class="col-md-6"><span class="sg-formula-tag">KPI</span> Kartu angka utama — nilai 0–100 atau jumlah</div>
                <div class="col-md-6"><span class="sg-formula-tag sg-formula-tag--chart">Grafik</span> Visualisasi tren &amp; perbandingan</div>
            </div>
        </div>
    </details>

    <section class="sg-monitor-kpis sg-fade-in" aria-label="KPI utama">
        <article class="sg-monitor-kpi sg-monitor-kpi--blue">
            <div class="sg-monitor-kpi__icon"><i data-lucide="handshake"></i></div>
            <div>
                <p class="sg-monitor-kpi__label">Total layanan</p>
                <p class="sg-monitor-kpi__value" data-sg-counter="<?php echo $layananTotal; ?>">0</p>
                <p class="sg-monitor-kpi__hint">Entri layanan terdaftar</p>
                <?php
                sg_render_formula_details('Rumus & data', [
                    '<strong>Sumber:</strong> file <code>layanan_data.json</code> (Manajemen Layanan)',
                    '<strong>Nilai tampil:</strong> ' . (int) $fb['layanan'] . ' entri layanan aktif',
                    'Bukan persentase — ini jumlah data layanan yang terdaftar di sistem.',
                ]);
                ?>
            </div>
        </article>
        <article class="sg-monitor-kpi sg-monitor-kpi--indigo">
            <div class="sg-monitor-kpi__icon"><i data-lucide="building-2"></i></div>
            <div>
                <p class="sg-monitor-kpi__label">Progress organisasi</p>
                <p class="sg-monitor-kpi__value" data-sg-counter="<?php echo $orgScore; ?>">0</p>
                <p class="sg-monitor-kpi__hint">Indeks kesiapan digital %</p>
                <?php
                sg_render_formula_details('Rumus & data', [
                    '<strong>Rumus:</strong> min(100, Staf×12×20% + Info×8×25% + Galeri×15×15% + KPI pelayanan×40%)',
                    'Staf: ' . $fb['staf'] . ' akun → +' . $fb['org_staf'] . ' poin',
                    'Info: ' . $fb['berita'] . ' berita + ' . $fb['pengumuman'] . ' pengumuman → +' . $fb['org_info'] . ' poin',
                    'Galeri: ' . $fb['galeri'] . ' item → +' . $fb['org_galeri'] . ' poin',
                    'KPI pelayanan (' . $fb['kpi_pelayanan'] . '%) × 40% → +' . $fb['org_kpi'] . ' poin',
                    '<strong>Total skor organisasi: ' . $fb['org_score'] . '%</strong>',
                ], 'Bukan penilaian SKP resmi — indeks kesiapan konten & layanan digital.');
                ?>
            </div>
        </article>
        <article class="sg-monitor-kpi sg-monitor-kpi--violet">
            <div class="sg-monitor-kpi__icon"><i data-lucide="users"></i></div>
            <div>
                <p class="sg-monitor-kpi__label">Kinerja pegawai</p>
                <p class="sg-monitor-kpi__value" data-sg-counter="<?php echo $kinerjaPct; ?>">0</p>
                <p class="sg-monitor-kpi__hint">Capaian tim / staf %</p>
                <?php
                if ($fb['kinerja_uses_team']) {
                    sg_render_formula_details('Rumus & data', [
                        '<strong>Sumber:</strong> tabel Target Tim Kerja (tahun berjalan)',
                        '<strong>Rumus:</strong> (target status Selesai ÷ total target) × 100',
                        'Selesai: <strong>' . $fb['team_selesai'] . '</strong> / Total: <strong>' . $fb['team_total'] . '</strong> → <strong>' . $fb['kinerja'] . '%</strong>',
                    ]);
                } else {
                    sg_render_formula_details('Rumus & data', [
                        '<strong>Belum ada target tim</strong> — memakai perkiraan dari jumlah staf',
                        '<strong>Rumus cadangan:</strong> min(100, jumlah staf × 15)',
                        'Staf: ' . $fb['staf'] . ' → <strong>' . $fb['kinerja'] . '%</strong>',
                    ], 'Isi target di menu Tim Kerja agar memakai capaian riil.');
                }
                ?>
            </div>
        </article>
        <?php if ($showTamu): ?>
        <article class="sg-monitor-kpi sg-monitor-kpi--cyan">
            <div class="sg-monitor-kpi__icon"><i data-lucide="footprints"></i></div>
            <div>
                <p class="sg-monitor-kpi__label">Pengunjung</p>
                <p class="sg-monitor-kpi__value" data-sg-counter="<?php echo $tamuHari; ?>">0</p>
                <p class="sg-monitor-kpi__hint"><?php echo (int) $dashMetrics['tamu_minggu']; ?> minggu ini</p>
                <?php
                sg_render_formula_details('Rumus & data', [
                    '<strong>Sumber:</strong> tabel <code>tamu</code> (buku tamu digital)',
                    '<strong>Hari ini:</strong> ' . $fb['tamu_hari'] . ' kunjungan',
                    '<strong>7 hari terakhir:</strong> ' . $fb['tamu_week'] . ' kunjungan',
                    'Masuk ke KPI pelayanan: min(100, tamu_minggu×8) × 35% → +' . $fb['kpi_tamu'] . ' poin (dari max 35)',
                ]);
                ?>
            </div>
        </article>
        <?php endif; ?>
        <article class="sg-monitor-kpi sg-monitor-kpi--emerald">
            <div class="sg-monitor-kpi__icon"><i data-lucide="heart"></i></div>
            <div>
                <p class="sg-monitor-kpi__label">Kepuasan publik</p>
                <p class="sg-monitor-kpi__value" data-sg-counter="<?php echo $kepuasan; ?>">0</p>
                <p class="sg-monitor-kpi__hint">Indeks estimasi %</p>
                <?php
                sg_render_formula_details('Rumus & data', [
                    '<strong>Rumus:</strong> (KPI pelayanan × 55%) + (Skor saran × 45%)',
                    'KPI pelayanan ' . $fb['kpi_pelayanan'] . '% × 55% = <strong>+' . $fb['kepuasan_kpi'] . ' poin</strong>',
                    'Saran masuk: ' . $fb['saran'] . ' → skor saran ' . $fb['skor_saran'] . '% × 45% = <strong>+' . $fb['kepuasan_saran'] . ' poin</strong>',
                    '0 saran = kontribusi maks 45 poin; banyak saran menurunkan skor',
                    '<strong>Total kepuasan: ' . $fb['kepuasan'] . '%</strong>',
                ], 'Bukan hasil survei kepuasan masyarakat — estimasi dari aktivitas digital & masukan.');
                ?>
            </div>
        </article>
    </section>

    <article class="sg-monitor-formula-banner sg-fade-in">
        <div class="sg-monitor-kpi__icon"><i data-lucide="calculator"></i></div>
        <div>
            <p class="sg-monitor-kpi__label">Rincian KPI pelayanan publik</p>
            <p class="sg-monitor-kpi__hint mb-2">Komponen yang memengaruhi Progress organisasi &amp; Kepuasan publik</p>
            <div class="sg-formula-breakdown-grid">
                <span>Unduhan: (<?php echo $fb['unduh']; ?> ÷ <?php echo $fb['doc_total']; ?>) × 35 = <strong><?php echo $fb['kpi_unduh']; ?></strong></span>
                <span>Tamu minggu: min(100, <?php echo $fb['tamu_week']; ?>×8) × 35% = <strong><?php echo $fb['kpi_tamu']; ?></strong></span>
                <span>Info: min(100, <?php echo $fb['info_total']; ?>×5) × 30% = <strong><?php echo $fb['kpi_info']; ?></strong></span>
                <span class="sg-formula-breakdown-total">Total KPI pelayanan = <strong><?php echo $fb['kpi_pelayanan']; ?>%</strong></span>
            </div>
            <p class="small text-muted mb-0 mt-2">1 unduhan tambah ±<?php echo number_format(35 / max(1, $fb['doc_total']), 2, ',', '.'); ?> poin KPI (tergantung jumlah dokumen). Banyak unggah tanpa unduhan bisa menurunkan rasio.</p>
            <?php
            sg_render_formula_details('Rumus lengkap KPI pelayanan', [
                '<strong>Rumus:</strong> min(100, Unduh + Tamu + Info)',
                'Unduh: (total unduhan ÷ jumlah dokumen) × 35 → <strong>' . $fb['kpi_unduh'] . '</strong>',
                'Tamu: min(100, tamu_minggu×8) × 35% → <strong>' . $fb['kpi_tamu'] . '</strong>',
                'Info: min(100, (berita+pengumuman)×5) × 30% → <strong>' . $fb['kpi_info'] . '</strong>',
            ]);
            ?>
        </div>
    </article>

    <section class="sg-monitor-charts" id="sgSectionAnalytics" aria-label="Grafik analytics">
        <?php if ($showTamu): ?>
        <div class="sg-monitor-chart sg-monitor-chart--wide sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Trend pelayanan publik</h2>
                    <p class="sg-monitor-chart__sub">Kunjungan buku tamu 14 hari</p>
                </div>
                <span class="sg-badge">Area</span>
            </div>
            <div id="sgChartTrend" class="sg-chart-box" role="img" aria-label="Trend pelayanan"></div>
            <?php
            sg_render_formula_details('Cara membaca grafik', [
                '<strong>Sumber:</strong> tabel <code>tamu</code>, agregasi per hari (14 hari terakhir)',
                '<strong>Sumbu X:</strong> tanggal · <strong>Sumbu Y:</strong> jumlah kunjungan/hari',
                'Puncak grafik = hari dengan kunjungan buku tamu terbanyak',
                'Terkait KPI: min(100, tamu_minggu×8) × 35% → saat ini +' . $fb['kpi_tamu'] . ' poin',
            ]);
            ?>
        </div>
        <?php endif; ?>

        <div class="sg-monitor-chart sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Distribusi layanan</h2>
                    <p class="sg-monitor-chart__sub">Konten &amp; aset digital</p>
                </div>
                <span class="sg-badge">Donut</span>
            </div>
            <div id="sgChartDonut" class="sg-chart-box sg-chart-box--donut" role="img"></div>
            <?php
            sg_render_formula_details('Cara membaca grafik', [
                '<strong>Sumber:</strong> jumlah aktual di sistem',
                'Dokumen: <strong>' . $fb['doc_total'] . '</strong> · Info: <strong>' . $fb['info_total'] . '</strong> · Galeri: <strong>' . $fb['galeri'] . '</strong> · Layanan: <strong>' . $fb['layanan'] . '</strong>',
                'Donut = proporsi relatif keempat jenis aset (bukan persentase capaian)',
            ]);
            ?>
        </div>

        <?php if ($hasTarget): ?>
        <div class="sg-monitor-chart sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Target vs realisasi</h2>
                    <p class="sg-monitor-chart__sub">Capaian per tim kerja</p>
                </div>
                <span class="sg-badge">Stacked</span>
            </div>
            <div id="sgChartTargetRealisasi" class="sg-chart-box" role="img"></div>
            <?php
            $timLabels = $dashMetrics['team_tim_labels'] ?? $dashMetrics['target_labels'] ?? [];
            $timPct = $dashMetrics['team_tim_pct'] ?? $dashMetrics['target_values'] ?? [];
            $targetLines = ['<strong>Sumber:</strong> Target Tim Kerja (tahun ' . date('Y') . ')'];
            if (count($timLabels) === 0) {
                $targetLines[] = 'Batang biru = realisasi % · Abu = sisa menuju target 100%';
            } else {
                foreach ($timLabels as $ti => $tl) {
                    $targetLines[] = htmlspecialchars((string) $tl, ENT_QUOTES, 'UTF-8') . ': realisasi <strong>' . (int) ($timPct[$ti] ?? 0) . '%</strong> (target acuan 100%)';
                }
            }
            sg_render_formula_details('Cara membaca grafik', $targetLines);
            ?>
        </div>
        <?php endif; ?>

        <div class="sg-monitor-chart sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Progress kerja</h2>
                    <p class="sg-monitor-chart__sub">Tim kelembagaan, RB, Yanlik</p>
                </div>
                <span class="sg-badge">Radial</span>
            </div>
            <div id="sgChartProgressKerja" class="sg-chart-box" role="img"></div>
            <?php
            sg_render_formula_details('Cara membaca grafik', [
                '<strong>Sumber:</strong> sama dengan Target Tim Kerja',
                '<strong>Rumus per tim:</strong> (target status Selesai ÷ total target tim) × 100',
                'Radial = capaian % tiap tim (Kelembagaan, RB, Yanlik)',
                'Sama dengan kartu Kinerja pegawai bila memakai data tim',
            ]);
            ?>
        </div>

        <div class="sg-monitor-chart sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Statistik dokumen</h2>
                    <p class="sg-monitor-chart__sub">Unduhan per dokumen</p>
                </div>
                <span class="sg-badge">Bar</span>
            </div>
            <div id="admChartDownloads" class="sg-chart-box" role="img"></div>
            <?php
            sg_render_formula_details('Cara membaca grafik', [
                '<strong>Sumber:</strong> perpustakaan digital — jumlah unduhan per file',
                'Menampilkan top 6 dokumen terbanyak diunduh',
                'Tinggi batang = dokumen lebih sering diakses publik',
                'Terkait KPI: (total unduhan ÷ jumlah dokumen) × 35 poin',
            ]);
            ?>
        </div>

        <?php if ($hasHeatmap): ?>
        <div class="sg-monitor-chart sg-monitor-chart--wide sg-fade-in">
            <div class="sg-monitor-chart__head">
                <div>
                    <h2 class="sg-monitor-chart__title">Aktivitas pengguna</h2>
                    <p class="sg-monitor-chart__sub">Heatmap audit 7 hari</p>
                </div>
                <span class="sg-badge sg-badge--live">Realtime</span>
            </div>
            <div id="sgChartHeatmap" class="sg-chart-box" role="img"></div>
            <?php
            sg_render_formula_details('Cara membaca grafik', [
                '<strong>Sumber:</strong> tabel <code>audit_logs</code> (7 hari terakhir)',
                '<strong>Baris:</strong> hari (Min–Sab) · <strong>Kolom:</strong> blok jam 3 jam',
                'Warna gelap = lebih banyak aktivitas admin (login, ubah data, dll.)',
                'Kosong/abu = tidak ada aktivitas pada slot waktu tersebut',
            ]);
            ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="sg-monitor-panels" id="sgSectionMonitoring">
        <div class="sg-monitor-panel sg-fade-in">
            <div class="sg-monitor-panel__head">
                <div>
                    <h2 class="sg-monitor-panel__title">Realtime activity</h2>
                    <p class="sg-monitor-panel__sub">Log audit terbaru</p>
                </div>
                <?php if ($auditRiwayatVisible): ?>
                <button type="button" class="sg-btn-ghost btn btn-sm" data-sg-module="audit">Lihat modul Audit</button>
                <?php endif; ?>
            </div>
            <div class="sg-activity-stream" data-adm-activity-feed>
                <?php if (count($activityItems) === 0): ?>
                    <p class="text-muted small mb-0">Belum ada aktivitas tercatat.</p>
                <?php else: ?>
                    <?php foreach ($activityItems as $act): ?>
                    <div class="sg-activity-stream__item">
                        <span class="sg-activity-stream__dot"></span>
                        <div>
                            <strong><?php echo htmlspecialchars((string) $act['admin'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            — <?php echo htmlspecialchars((string) $act['aksi'], ENT_QUOTES, 'UTF-8'); ?>
                            <div class="text-muted small"><?php echo htmlspecialchars((string) $act['waktu_rel'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
            sg_render_formula_details('Cara membaca panel', [
                '<strong>Sumber:</strong> tabel <code>audit_logs</code>, 8 entri terbaru',
                'Diurutkan dari yang paling lama ke terbaru (scroll ke bawah untuk terbaru)',
                'Dot hijau = aktivitas aktif / baru masuk',
            ]);
            ?>
        </div>

        <div class="sg-monitor-panel sg-fade-in">
            <div class="sg-monitor-panel__head">
                <div>
                    <h2 class="sg-monitor-panel__title">Smart insights</h2>
                    <p class="sg-monitor-panel__sub">Rekomendasi berbasis data</p>
                </div>
            </div>
            <ul class="sg-insight-list">
                <?php foreach (array_slice($insights, 0, 4) as $ins): ?>
                <li class="sg-insight-list__item sg-insight-list__item--<?php echo htmlspecialchars((string) $ins['tone'], ENT_QUOTES, 'UTF-8'); ?>">
                    <i data-lucide="sparkles" aria-hidden="true"></i>
                    <div>
                        <strong><?php echo htmlspecialchars((string) $ins['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p><?php echo htmlspecialchars((string) $ins['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            sg_render_formula_details('Cara membaca panel', [
                'Rekomendasi otomatis dari KPI pelayanan, tamu, dan capaian tim',
                'Contoh: KPI &lt; 30% → saran tingkatkan konten digital',
                'Bukan keputusan resmi — panduan tindakan untuk admin',
            ]);
            ?>
        </div>
    </section>
</div>
