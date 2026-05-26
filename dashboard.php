<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);

$pageTitle = 'Dashboard E-Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-eorg-dashboard mode-eorganisasi';
$extraHeadMarkup = <<<'HTML'
<style>
    .page-eorg-dashboard { font-family: var(--font-sans); background: #f3f7fd; }
    .page-eorg-dashboard .site-main { max-width: 1280px; }
    .eorg-card { border: 0; border-radius: 15px; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1); }
    .eorg-stat { background: linear-gradient(135deg, #1d4ed8, #0ea5e9); color: #fff; }
    .eorg-stat__num { font-size: 1.9rem; font-weight: 700; line-height: 1; }
    .eorg-chart-title {
        margin: 0 0 0.75rem;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #0f2744;
    }
    .eorg-chart-wrap {
        position: relative;
        width: 100%;
        height: clamp(200px, 28vw, 260px);
    }
    .eorg-chart-wrap--md { height: clamp(220px, 30vw, 300px); }
    .eorg-chart-wrap--donut { height: clamp(180px, 22vw, 220px); }
    .eorg-chart-wrap canvas {
        position: absolute;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
    }
    .eorg-donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.1rem;
        pointer-events: none;
        font-family: 'Inter', 'Plus Jakarta Sans', system-ui, sans-serif;
    }
    .eorg-donut-center__num {
        font-size: clamp(1.35rem, 2.2vw, 1.75rem);
        font-weight: 800;
        color: #0f2744;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .eorg-donut-center__label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .eorg-donut-legend {
        list-style: none;
        margin: 0.75rem 0 0;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem 1rem;
        justify-content: center;
        font-size: 0.8rem;
        color: #475569;
    }
    .eorg-donut-legend li {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .eorg-donut-legend strong {
        color: #0f2744;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        margin-left: 0.15rem;
    }
    .eorg-donut-legend__dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .eorg-kiosk-bar {
        border: 1px solid #dbe7f8;
        border-radius: 12px;
        background: #fff;
        padding: 0.6rem 0.8rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }
    .eorg-kiosk-clockbox {
        display: grid;
        gap: 0.45rem 1.15rem;
        align-items: center;
        padding: 0.75rem 1.15rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #0ea5e9 100%);
        color: #fff;
        box-shadow: 0 10px 28px rgba(29, 78, 216, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-variant-numeric: tabular-nums;
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "wd wd"
            "date clock";
    }
    @media (max-width: 575.98px) {
        .eorg-kiosk-clockbox {
            grid-template-columns: 1fr;
            grid-template-areas:
                "wd"
                "date"
                "clock";
        }
        .eorg-kiosk-clockbox__time {
            justify-self: stretch;
            text-align: center;
        }
    }
    .eorg-kiosk-clockbox__wd {
        grid-area: wd;
        font-size: clamp(0.88rem, 1.9vw, 1.12rem);
        font-weight: 600;
        letter-spacing: 0.02em;
        opacity: 0.96;
        line-height: 1.25;
        border-bottom: 1px solid rgba(255, 255, 255, 0.22);
        padding-bottom: 0.4rem;
    }
    .eorg-kiosk-clockbox__date {
        grid-area: date;
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.45rem 0.65rem;
        line-height: 1;
        min-width: 0;
    }
    .eorg-kiosk-clockbox__daynum {
        font-size: clamp(2rem, 5.5vw, 2.85rem);
        font-weight: 800;
        line-height: 1;
        text-shadow: 0 2px 14px rgba(0, 0, 0, 0.15);
        letter-spacing: -0.03em;
    }
    .eorg-kiosk-clockbox__monyr {
        display: flex;
        flex-direction: column;
        gap: 0.06rem;
        font-size: clamp(1rem, 3vw, 1.35rem);
        font-weight: 700;
        line-height: 1.15;
        opacity: 0.98;
    }
    .eorg-kiosk-clockbox__year {
        font-weight: 600;
        font-size: 0.92em;
        opacity: 0.93;
    }
    .eorg-kiosk-clockbox__time {
        grid-area: clock;
        justify-self: end;
        font-size: clamp(1.55rem, 4.5vw, 2.35rem);
        font-weight: 800;
        line-height: 1;
        letter-spacing: 0.045em;
        padding: 0.45rem 0.85rem;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.32);
        white-space: nowrap;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    }
</style>
HTML;

$db = org_db();
$today = date('Y-m-d');
$todayStart = $today . ' 00:00:00';

$totalTamuHariIni = 0;
$totalSuratMasuk = 0;
$totalSuratKeluar = 0;
$tamuByTujuan = [
    'Kepala Bagian Organisasi' => 0,
    'Tim Kerja Kelembagaan & Anjab' => 0,
    'Pelayanan Publik' => 0,
    'Kinerja & RB' => 0,
    'Kepegawaian' => 0,
    'Keuangan' => 0,
];
$webVisitLabels = [];
$webVisitValues = [];

$arsipDirMap = [
    'masuk' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk',
    'keluar' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar',
];
$suratMonthly = [];
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-{$i} months"));
    $suratMonthly[$ym] = ['masuk' => 0, 'keluar' => 0];
}
foreach ($arsipDirMap as $jenis => $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $items = scandir($dir);
    if (!is_array($items)) {
        continue;
    }
    foreach ($items as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $fp = $dir . DIRECTORY_SEPARATOR . $f;
        if (!is_file($fp) || strtolower((string) pathinfo($f, PATHINFO_EXTENSION)) !== 'pdf') {
            continue;
        }
        $ym = date('Y-m', (int) filemtime($fp));
        if (isset($suratMonthly[$ym])) {
            $suratMonthly[$ym][$jenis]++;
        }
        if ($jenis === 'masuk') {
            $totalSuratMasuk++;
        } else {
            $totalSuratKeluar++;
        }
    }
}

if ($db instanceof mysqli) {
    $tableTamu = $db->query("SHOW TABLES LIKE 'tamu'");
    if ($tableTamu !== false && $tableTamu->num_rows > 0) {
        $cols = [];
        $colRes = $db->query("SHOW COLUMNS FROM `tamu`");
        if ($colRes !== false) {
            while ($r = $colRes->fetch_assoc()) {
                $field = (string) ($r['Field'] ?? '');
                if ($field !== '') {
                    $cols[$field] = true;
                }
            }
        }
        $dateField = isset($cols['created_at']) ? 'created_at' : (isset($cols['tanggal']) ? 'tanggal' : (isset($cols['tanggal_kunjungan']) ? 'tanggal_kunjungan' : ''));
        $tujuanField = isset($cols['tujuan_bertamu']) ? 'tujuan_bertamu' : (isset($cols['unit_tujuan']) ? 'unit_tujuan' : (isset($cols['bidang_tujuan']) ? 'bidang_tujuan' : (isset($cols['tujuan']) ? 'tujuan' : '')));
        if ($dateField !== '') {
            $stmtToday = $db->prepare("SELECT COUNT(*) AS c FROM `tamu` WHERE `$dateField` >= ?");
            if ($stmtToday !== false) {
                $stmtToday->bind_param('s', $todayStart);
                if ($stmtToday->execute()) {
                    $res = $stmtToday->get_result();
                    if ($res !== false) {
                        $row = $res->fetch_assoc();
                        $totalTamuHariIni = (int) ($row['c'] ?? 0);
                    }
                }
                $stmtToday->close();
            }
            for ($i = 13; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $webVisitLabels[] = date('d M', strtotime($d));
                $stmtDay = $db->prepare("SELECT COUNT(*) AS c FROM `tamu` WHERE DATE(`$dateField`) = ?");
                $dayCount = 0;
                if ($stmtDay !== false) {
                    $stmtDay->bind_param('s', $d);
                    if ($stmtDay->execute()) {
                        $res = $stmtDay->get_result();
                        if ($res !== false) {
                            $row = $res->fetch_assoc();
                            $dayCount = (int) ($row['c'] ?? 0);
                        }
                    }
                    $stmtDay->close();
                }
                $webVisitValues[] = $dayCount;
            }
        }
        if ($tujuanField !== '') {
            $agg = $db->query("SELECT `$tujuanField` AS tujuan, COUNT(*) AS c FROM `tamu` GROUP BY `$tujuanField`");
            if ($agg !== false) {
                while ($row = $agg->fetch_assoc()) {
                    $raw = strtolower(trim((string) ($row['tujuan'] ?? '')));
                    $cnt = (int) ($row['c'] ?? 0);
                    if (str_contains($raw, 'kepala')) {
                        $tamuByTujuan['Kepala Bagian Organisasi'] += $cnt;
                    } elseif (str_contains($raw, 'kelembagaan') || str_contains($raw, 'anjab')) {
                        $tamuByTujuan['Tim Kerja Kelembagaan & Anjab'] += $cnt;
                    } elseif (str_contains($raw, 'pelayanan')) {
                        $tamuByTujuan['Pelayanan Publik'] += $cnt;
                    } elseif (str_contains($raw, 'kinerja') || str_contains($raw, 'rb')) {
                        $tamuByTujuan['Kinerja & RB'] += $cnt;
                    } elseif (str_contains($raw, 'kepegawaian')) {
                        $tamuByTujuan['Kepegawaian'] += $cnt;
                    } elseif (str_contains($raw, 'keuangan')) {
                        $tamuByTujuan['Keuangan'] += $cnt;
                    }
                }
            }
        }
    }
}

if (count($webVisitLabels) === 0) {
    for ($i = 13; $i >= 0; $i--) {
        $webVisitLabels[] = date('d M', strtotime("-{$i} days"));
        $webVisitValues[] = 0;
    }
}

$suratMonthLabels = array_map(static fn(string $ym): string => date('M Y', strtotime($ym . '-01')), array_keys($suratMonthly));
$suratMasukSeries = array_map(static fn(array $row): int => (int) ($row['masuk'] ?? 0), array_values($suratMonthly));
$suratKeluarSeries = array_map(static fn(array $row): int => (int) ($row['keluar'] ?? 0), array_values($suratMonthly));

$extraHeadMarkup = $extraHeadMarkup ?? '';
$extraFooterMarkup = $extraFooterMarkup ?? '';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup, true);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div class="container site-main">
    <section class="section-spacing">
        <div class="eorg-kiosk-bar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="eorg-kiosk-clockbox" aria-live="polite" role="status" id="eorgKioskDateTime">
                    <div class="eorg-kiosk-clockbox__wd" id="eorgDashHariWrap"><span id="eorgDashHari">—</span></div>
                    <div class="eorg-kiosk-clockbox__date">
                        <span class="eorg-kiosk-clockbox__daynum" id="eorgDashTanggal">—</span>
                        <span class="eorg-kiosk-clockbox__monyr">
                            <span id="eorgDashBulan">—</span>
                            <span class="eorg-kiosk-clockbox__year" id="eorgDashTahun">—</span>
                        </span>
                    </div>
                    <div class="eorg-kiosk-clockbox__time" id="eorgDashJam">—</div>
                </div>
                <div class="small text-muted border-start ps-3">
                    Mode Lobi Aktif • Auto-refresh setiap <strong>60 detik</strong> • Update berikutnya: <span id="kioskCountdown">60s</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnEnterFullscreen">
                    <i class="fa-solid fa-expand me-1" aria-hidden="true"></i>Fullscreen
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExitFullscreen">
                    <i class="fa-solid fa-compress me-1" aria-hidden="true"></i>Keluar Fullscreen
                </button>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4"><div class="card eorg-card eorg-stat"><div class="card-body"><div>Total Tamu Hari Ini</div><div class="eorg-stat__num"><?php echo (int) $totalTamuHariIni; ?></div></div></div></div>
            <div class="col-12 col-md-4"><div class="card eorg-card eorg-stat"><div class="card-body"><div>Total Surat Masuk</div><div class="eorg-stat__num"><?php echo (int) $totalSuratMasuk; ?></div></div></div></div>
            <div class="col-12 col-md-4"><div class="card eorg-card eorg-stat"><div class="card-body"><div>Total Surat Keluar</div><div class="eorg-stat__num"><?php echo (int) $totalSuratKeluar; ?></div></div></div></div>
        </div>
        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card eorg-card"><div class="card-body">
                    <h2 class="h5 eorg-chart-title">Grafik Kunjungan Web</h2>
                    <div class="eorg-chart-wrap"><canvas id="chartWebVisit" aria-label="Grafik kunjungan web 14 hari"></canvas></div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card eorg-card"><div class="card-body">
                    <h2 class="h5 eorg-chart-title">Ringkasan Surat</h2>
                    <div class="eorg-chart-wrap eorg-chart-wrap--donut">
                        <canvas id="chartSuratPie" aria-label="Ringkasan surat masuk dan surat keluar"></canvas>
                        <div class="eorg-donut-center" aria-hidden="true">
                            <span class="eorg-donut-center__num"><?php echo number_format($totalSuratMasuk + $totalSuratKeluar, 0, ',', '.'); ?></span>
                            <span class="eorg-donut-center__label">Total Surat</span>
                        </div>
                    </div>
                    <ul class="eorg-donut-legend" role="list">
                        <li><span class="eorg-donut-legend__dot" style="background:#2563eb"></span>Masuk <strong><?php echo number_format($totalSuratMasuk, 0, ',', '.'); ?></strong></li>
                        <li><span class="eorg-donut-legend__dot" style="background:#22c55e"></span>Keluar <strong><?php echo number_format($totalSuratKeluar, 0, ',', '.'); ?></strong></li>
                    </ul>
                </div></div>
            </div>
            <div class="col-12">
                <div class="card eorg-card"><div class="card-body">
                    <h2 class="h5 eorg-chart-title">Tamu Offline (Kantor) per Tujuan</h2>
                    <div class="eorg-chart-wrap eorg-chart-wrap--md"><canvas id="chartTamuBar" aria-label="Jumlah tamu offline per tujuan"></canvas></div>
                </div></div>
            </div>
            <div class="col-12">
                <div class="card eorg-card"><div class="card-body">
                    <h2 class="h5 eorg-chart-title">Tren Surat Masuk &amp; Keluar per Bulan</h2>
                    <div class="eorg-chart-wrap eorg-chart-wrap--md"><canvas id="chartSuratMonthly" aria-label="Tren surat masuk dan keluar per bulan"></canvas></div>
                </div></div>
            </div>
        </div>
    </section>
</div>
<script src="<?php echo htmlspecialchars(org_asset_url('assets/vendor/chartjs/chart.umd.min.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<script>
window.EORG_CHART_DATA = {
    web: { labels: <?php echo json_encode($webVisitLabels); ?>, data: <?php echo json_encode($webVisitValues); ?> },
    tujuan: { labels: <?php echo json_encode(array_keys($tamuByTujuan)); ?>, data: <?php echo json_encode(array_values($tamuByTujuan)); ?> },
    suratPie: <?php echo json_encode([$totalSuratMasuk, $totalSuratKeluar]); ?>,
    suratMonth: { labels: <?php echo json_encode($suratMonthLabels); ?>, masuk: <?php echo json_encode($suratMasukSeries); ?>, keluar: <?php echo json_encode($suratKeluarSeries); ?> }
};
(function () {
    var data = window.EORG_CHART_DATA || {};
    var inited = {};
    var defaults = {
        font: { family: "Inter, 'Plus Jakarta Sans', system-ui, sans-serif", size: 11 },
        color: '#475569'
    };
    var nf = new Intl.NumberFormat('id-ID');
    function applyChartDefaults() {
        if (typeof Chart === 'undefined' || !Chart.defaults) return;
        Chart.defaults.font.family = defaults.font.family;
        Chart.defaults.font.size = defaults.font.size;
        Chart.defaults.color = defaults.color;
        Chart.defaults.devicePixelRatio = Math.min(window.devicePixelRatio || 1, 2);
        Chart.defaults.animation = { duration: 350, easing: 'easeOutQuart' };
        Chart.defaults.plugins = Chart.defaults.plugins || {};
        Chart.defaults.plugins.legend = Object.assign({}, Chart.defaults.plugins.legend, {
            labels: { boxWidth: 10, boxHeight: 10, padding: 12, usePointStyle: true, font: { size: 11 } }
        });
        Chart.defaults.plugins.tooltip = Object.assign({}, Chart.defaults.plugins.tooltip, {
            backgroundColor: 'rgba(15, 23, 42, 0.94)',
            titleFont: { size: 11, weight: '600' },
            bodyFont: { size: 11 },
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 4,
            callbacks: {
                label: function (ctx) {
                    var lbl = ctx.dataset.label || ctx.label || '';
                    var v = ctx.parsed && typeof ctx.parsed.y === 'number' ? ctx.parsed.y : (typeof ctx.parsed === 'number' ? ctx.parsed : ctx.raw);
                    return (lbl ? lbl + ': ' : '') + nf.format(v || 0);
                }
            }
        });
    }
    function buildWeb() {
        var c = document.getElementById('chartWebVisit');
        if (!c) return;
        return new Chart(c, {
            type: 'line',
            data: {
                labels: data.web.labels || [],
                datasets: [{
                    label: 'Kunjungan',
                    data: data.web.data || [],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.14)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.18)' }, ticks: { precision: 0, callback: function (v) { return nf.format(v); } } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
    function buildTujuan() {
        var c = document.getElementById('chartTamuBar');
        if (!c) return;
        return new Chart(c, {
            type: 'bar',
            data: {
                labels: data.tujuan.labels || [],
                datasets: [{
                    label: 'Jumlah Tamu',
                    data: data.tujuan.data || [],
                    backgroundColor: 'rgba(14, 165, 233, 0.78)',
                    borderRadius: 6,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.18)' }, ticks: { precision: 0, callback: function (v) { return nf.format(v); } } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
    function buildPie() {
        var c = document.getElementById('chartSuratPie');
        if (!c) return;
        var v = data.suratPie || [0, 0];
        var hasData = (Number(v[0]) || 0) + (Number(v[1]) || 0) > 0;
        return new Chart(c, {
            type: 'doughnut',
            data: {
                labels: ['Surat Masuk', 'Surat Keluar'],
                datasets: [{
                    data: hasData ? v : [1, 1],
                    backgroundColor: hasData ? ['#2563eb', '#22c55e'] : ['#e2e8f0', '#f1f5f9'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: hasData,
                        callbacks: {
                            label: function (ctx) {
                                var total = (data.suratPie[0] || 0) + (data.suratPie[1] || 0);
                                var val = ctx.parsed || 0;
                                var pct = total > 0 ? Math.round((val / total) * 1000) / 10 : 0;
                                return ctx.label + ': ' + nf.format(val) + ' (' + String(pct).replace('.', ',') + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    function buildMonthly() {
        var c = document.getElementById('chartSuratMonthly');
        if (!c) return;
        return new Chart(c, {
            type: 'bar',
            data: {
                labels: data.suratMonth.labels || [],
                datasets: [
                    { label: 'Masuk', data: data.suratMonth.masuk || [], backgroundColor: 'rgba(37, 99, 235, 0.85)', borderRadius: 5, maxBarThickness: 22 },
                    { label: 'Keluar', data: data.suratMonth.keluar || [], backgroundColor: 'rgba(34, 197, 94, 0.85)', borderRadius: 5, maxBarThickness: 22 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.18)' }, ticks: { precision: 0, callback: function (v) { return nf.format(v); } } }
                },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, padding: 14, usePointStyle: true } } }
            }
        });
    }
    var builders = {
        chartWebVisit: buildWeb,
        chartTamuBar: buildTujuan,
        chartSuratPie: buildPie,
        chartSuratMonthly: buildMonthly
    };
    function initWhenReady(id) {
        if (inited[id]) return;
        var el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        inited[id] = true;
        try { builders[id](); } catch (e) { /* noop */ }
    }
    function start() {
        applyChartDefaults();
        var ids = Object.keys(builders);
        if (!('IntersectionObserver' in window)) {
            ids.forEach(initWhenReady);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    var id = en.target.id;
                    initWhenReady(id);
                    io.unobserve(en.target);
                }
            });
        }, { rootMargin: '120px 0px' });
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) io.observe(el);
        });
    }
    function waitForChart() {
        if (typeof Chart !== 'undefined') { start(); return; }
        var t = 0;
        var iv = setInterval(function () {
            if (typeof Chart !== 'undefined') { clearInterval(iv); start(); return; }
            if (++t > 50) { clearInterval(iv); }
        }, 100);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForChart);
    } else {
        waitForChart();
    }
}());

(function () {
    const elHari = document.getElementById('eorgDashHari');
    const elTgl = document.getElementById('eorgDashTanggal');
    const elBln = document.getElementById('eorgDashBulan');
    const elThn = document.getElementById('eorgDashTahun');
    const elJam = document.getElementById('eorgDashJam');
    const locale = 'id-ID';
    function updateClock() {
        if (!elHari || !elTgl || !elBln || !elThn || !elJam) return;
        const d = new Date();
        elHari.textContent = d.toLocaleDateString(locale, { weekday: 'long' });
        elTgl.textContent = d.toLocaleDateString(locale, { day: 'numeric' });
        elBln.textContent = d.toLocaleDateString(locale, { month: 'long' });
        elThn.textContent = d.toLocaleDateString(locale, { year: 'numeric' });
        elJam.textContent = d.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    }
    updateClock();
    setInterval(updateClock, 1000);

    const refreshIntervalSec = 60;
    const countdownEl = document.getElementById('kioskCountdown');
    const btnEnter = document.getElementById('btnEnterFullscreen');
    const btnExit = document.getElementById('btnExitFullscreen');
    let remain = refreshIntervalSec;

    const updateCountdown = function () {
        if (countdownEl) {
            countdownEl.textContent = String(remain) + 's';
        }
    };
    updateCountdown();

    setInterval(function () {
        remain -= 1;
        if (remain <= 0) {
            window.location.reload();
            return;
        }
        updateCountdown();
    }, 1000);

    if (btnEnter) {
        btnEnter.addEventListener('click', function () {
            const root = document.documentElement;
            if (root.requestFullscreen) {
                root.requestFullscreen();
            }
        });
    }
    if (btnExit) {
        btnExit.addEventListener('click', function () {
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen();
            }
        });
    }
}());
</script>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
