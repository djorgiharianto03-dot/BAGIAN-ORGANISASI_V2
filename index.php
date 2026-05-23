<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_dev_bootstrap_once.php';
org_run_dev_database_bootstrap_once();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

define('ORG_BERANDA_PAGE', true);

$pageTitle = 'Beranda — Bagian Organisasi';
$navActive = 'beranda';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-index-redesign sg-portal-page sg-homepage';
$smartPortalNav = true;

/** Satu kalimat inti untuk kartu Visi beranda (dari HTML ke plain). */
$orgBerandaKalimatPertama = static function (string $html): string {
    $t = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($t === '') {
        return '';
    }
    if (preg_match('/^(.{1,400}?[.!?])(\s|$)/u', $t, $m)) {
        return trim($m[1]);
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($t, 'UTF-8') > 140) {
        return mb_substr($t, 0, 137, 'UTF-8') . '…';
    }
    if (strlen($t) > 140) {
        return substr($t, 0, 137) . '…';
    }

    return $t;
};
$berandaVisiPlain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($siteSettings['profile_visi'] ?? ''))));
$berandaMisiPlain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($siteSettings['profile_misi'] ?? ''))));
$berandaVisiRingkas = $berandaVisiPlain !== '' ? $berandaVisiPlain : $orgBerandaKalimatPertama((string) ($siteSettings['profile_visi'] ?? ''));

$berandaVisitLabels = [];
$berandaVisitValues = [];
$berandaTotalToday = 0;
$berandaTotalWeek = 0;
$dbBerandaVisit = org_db();
if ($dbBerandaVisit instanceof mysqli) {
    $tableTamuRes = $dbBerandaVisit->query("SHOW TABLES LIKE 'tamu'");
    if ($tableTamuRes !== false && $tableTamuRes->num_rows > 0) {
        $tamuCols = [];
        $tamuColRes = $dbBerandaVisit->query("SHOW COLUMNS FROM `tamu`");
        if ($tamuColRes !== false) {
            while ($col = $tamuColRes->fetch_assoc()) {
                $field = (string) ($col['Field'] ?? '');
                if ($field !== '') {
                    $tamuCols[$field] = true;
                }
            }
        }
        $dateField = isset($tamuCols['created_at']) ? 'created_at' : (isset($tamuCols['tanggal']) ? 'tanggal' : (isset($tamuCols['tanggal_kunjungan']) ? 'tanggal_kunjungan' : ''));
        if ($dateField !== '') {
            $startDate = date('Y-m-d', strtotime('-13 days'));
            $endDate = date('Y-m-d');
            $countsByDate = [];

            $dateColSql = '`' . str_replace('`', '``', $dateField) . '`';
            $stmtTrend = $dbBerandaVisit->prepare(
                "SELECT DATE({$dateColSql}) AS d, COUNT(*) AS c
                 FROM `tamu`
                 WHERE DATE({$dateColSql}) BETWEEN ? AND ?
                 GROUP BY DATE({$dateColSql})"
            );
            if ($stmtTrend !== false) {
                $stmtTrend->bind_param('ss', $startDate, $endDate);
                if ($stmtTrend->execute()) {
                    $resTrend = $stmtTrend->get_result();
                    if ($resTrend !== false) {
                        while ($trendRow = $resTrend->fetch_assoc()) {
                            $d = (string) ($trendRow['d'] ?? '');
                            if ($d !== '') {
                                $countsByDate[$d] = (int) ($trendRow['c'] ?? 0);
                            }
                        }
                    }
                }
                $stmtTrend->close();
            }

            for ($i = 13; $i >= 0; $i--) {
                $dateKey = date('Y-m-d', strtotime("-{$i} days"));
                $berandaVisitLabels[] = date('d M', strtotime($dateKey));
                $berandaVisitValues[] = (int) ($countsByDate[$dateKey] ?? 0);
            }

            $todayDate = date('Y-m-d');
            $weekStartDate = date('Y-m-d', strtotime('-6 days'));
            $berandaTotalToday = (int) ($countsByDate[$todayDate] ?? 0);
            foreach ($countsByDate as $dateKey => $countDay) {
                if ($dateKey >= $weekStartDate && $dateKey <= $todayDate) {
                    $berandaTotalWeek += (int) $countDay;
                }
            }
        }
    }
}
if (count($berandaVisitLabels) === 0) {
    for ($i = 13; $i >= 0; $i--) {
        $berandaVisitLabels[] = date('d M', strtotime("-{$i} days"));
        $berandaVisitValues[] = 0;
    }
}

$sgPortalDocCount = count($libraryDocumentFiles ?? []);
$sgPortalInfoCount = count($pusatInformasiPosts ?? []);
$sgPortalGaleriCount = count($berandaGaleriKegiatan ?? []);
$sgPortalLayananCount = 0;
$sgLayananFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'layanan_data.json';
if (is_file($sgLayananFile)) {
    $sgLayananRaw = file_get_contents($sgLayananFile);
    if ($sgLayananRaw !== false && $sgLayananRaw !== '') {
        $sgLayananParsed = json_decode($sgLayananRaw, true);
        if (is_array($sgLayananParsed)) {
            $sgPortalLayananCount = count($sgLayananParsed);
        }
    }
}
$prosesSaranUrl = defined('ORG_PROSES_SARAN_URL') ? ORG_PROSES_SARAN_URL : org_proses_saran_url();
$prosesSaranUrlEsc = htmlspecialchars($prosesSaranUrl, ENT_QUOTES, 'UTF-8');

org_portal_prepare_page($bodyClass, false);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
$extraHeadMarkup = org_beranda_index_extra_head_markup(
    count($berandaDashboardWidgets) > 0 || !empty($berandaTeamTargetsVisible)
);

if (!empty($berandaTeamTargetsVisible)) {
    $extraHeadMarkup .= '<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js" defer></script>' . "\n";
}

$extraFooterMarkup = org_portal_footer_markup(<<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
(function () {
    if (typeof Fancybox === 'undefined') return;
    Fancybox.bind('[data-fancybox="beranda-galeri-kegiatan"]', {
        animated: true,
        dragToClose: true,
        backdropClick: 'close',
        Carousel: { transition: 'fade' },
        Thumbs: { type: 'classic' },
        Toolbar: { display: { left: [], middle: [], right: ['close'] } }
    });
}());
</script>
<script>
(function () {
    function initBerandaMotion() {
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (typeof AOS !== 'undefined' && !reduced) {
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic', offset: 48 });
        } else if (typeof AOS !== 'undefined') {
            AOS.init({ disable: true });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBerandaMotion);
    } else {
        initBerandaMotion();
    }
}());
</script>
HTML);

if (!empty($berandaTeamTargetsVisible)) {
    $extraFooterMarkup .= <<<'HTML'
<script>
(function () {
    function initGovTeamTargetCharts() {
        if (typeof ApexCharts === 'undefined') return false;
        var dataEl = document.getElementById('gov-team-target-charts-data');
        if (!dataEl) return true;
        var store = {};
        try { store = JSON.parse(dataEl.textContent || '{}'); } catch (e) { store = {}; }
        var teams = store.teams || store;
        var overview = store.overview || [];

        var overviewEl = document.getElementById('govTeamTargetOverviewChart');
        if (overviewEl && overviewEl.getAttribute('data-chart-ready') !== '1' && overview.length) {
            var overviewLabels = overview.map(function (row) { return row.label || ''; });
            var overviewData = overview.map(function (row) { return Math.max(0, Math.min(100, Number(row.pct) || 0)); });
            var overviewColors = overview.map(function (row) { return row.color || '#8CB8EB'; });
            var overviewColorsDeep = overview.map(function (row) { return row.colorDeep || row.color || '#1A3F6E'; });
            var overviewLabelColors = overviewData.map(function (v) {
                return (Number(v) || 0) >= 22 ? '#ffffff' : '#0f2744';
            });
            var overviewCounts = overview.map(function (row) { return Number(row.count) || 0; });
            var overviewFull = overview.map(function (row) { return row.fullLabel || row.label || ''; });
            var overviewChart = new ApexCharts(overviewEl, {
                series: [{ name: 'Rata-rata capaian', data: overviewData }],
                chart: {
                    type: 'bar',
                    height: Math.max(280, overview.length * 84 + 56),
                    toolbar: { show: false },
                    fontFamily: 'Poppins, system-ui, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 950,
                        animateGradually: { enabled: true, delay: 140 },
                        dynamicAnimation: { enabled: true, speed: 400 }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 10,
                        borderRadiusApplication: 'end',
                        barHeight: '72%',
                        distributed: true,
                        dataLabels: { position: 'center', hideOverflowingLabels: false }
                    }
                },
                colors: overviewColors,
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.35,
                        gradientFromColors: overviewColors,
                        gradientToColors: overviewColorsDeep,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 55, 100]
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'middle',
                    offsetX: 0,
                    formatter: function (val) { return Math.round(Number(val) || 0) + '%'; },
                    style: {
                        fontSize: '12px',
                        fontWeight: 700,
                        fontFamily: 'Poppins, system-ui, sans-serif',
                        colors: overviewLabelColors,
                        textShadow: '0 1px 2px rgba(15, 39, 68, 0.22)'
                    }
                },
                legend: { show: false },
                grid: {
                    show: true,
                    borderColor: 'transparent',
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: false } },
                    padding: { left: 12, right: 44, top: 8, bottom: 8 }
                },
                xaxis: {
                    categories: overviewLabels,
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: { show: false },
                    axisBorder: { show: true, color: '#d8e2ef', height: 1 },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#1e3a5f', fontWeight: 600, fontSize: '12px' },
                        maxWidth: 240
                    }
                },
                states: {
                    hover: { filter: { type: 'darken', value: 0.08 } },
                    active: { filter: { type: 'none' } }
                },
                tooltip: {
                    theme: 'light',
                    intersect: true,
                    shared: false,
                    custom: function (ctx) {
                        var i = ctx.dataPointIndex;
                        if (i < 0) return '';
                        var pct = overviewData[i] || 0;
                        var cnt = overviewCounts[i] || 0;
                        var full = overviewFull[i] || overviewLabels[i] || '';
                        var dot = overviewColorsDeep[i] || '#1A3F6E';
                        var dotLight = overviewColors[i] || '#8CB8EB';
                        return '<div style="padding:11px 14px;background:linear-gradient(165deg,#fff 0%,#f8fafc 100%);border:1px solid #d8e2ef;border-radius:12px;box-shadow:0 10px 28px rgba(15,39,68,.12);font-family:Poppins,system-ui,sans-serif;">'
                            + '<div style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#0f2744;margin-bottom:6px;"><span style="width:11px;height:11px;border-radius:4px;background:linear-gradient(135deg,' + dotLight + ',' + dot + ');box-shadow:0 1px 4px rgba(15,39,68,.2);"></span>' + full + '</div>'
                            + '<div style="font-size:16px;font-weight:700;color:#0a2f63;letter-spacing:-0.02em;">' + Math.round(pct) + '%</div>'
                            + '<div style="font-size:11px;color:#64748b;margin-top:4px;">' + cnt + ' kegiatan</div>'
                            + '</div>';
                    }
                }
            });
            overviewChart.render();
            overviewEl.setAttribute('data-chart-ready', '1');
        }

        Object.keys(teams).forEach(function (tim) {
            var pack = teams[tim];
            if (!pack) return;
            var el = document.getElementById('govTeamTargetChart-' + tim);
            if (!el || el.getAttribute('data-chart-ready') === '1') return;
            var pct = Math.max(0, Math.min(100, Number(pack.pct) || 0));
            var color = pack.color || '#0f2744';
            var colorLight = pack.colorLight || '#60a5fa';
            var chart = new ApexCharts(el, {
                series: [pct],
                chart: {
                    type: 'radialBar',
                    height: 172,
                    sparkline: { enabled: false },
                    fontFamily: 'Poppins, system-ui, sans-serif'
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        hollow: { size: '65%' },
                        track: {
                            background: '#e8edf4',
                            strokeWidth: '100%',
                            margin: 4
                        },
                        dataLabels: {
                            name: { show: false },
                            value: {
                                offsetY: -4,
                                fontSize: '1.4rem',
                                fontWeight: 700,
                                color: color,
                                formatter: function (val) { return Math.round(val) + '%'; }
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientFromColors: [colorLight],
                        gradientToColors: [color],
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 45, 100]
                    }
                },
                colors: [color],
                stroke: {
                    lineCap: 'round'
                },
                labels: ['Progres']
            });
            chart.render();
            el.setAttribute('data-chart-ready', '1');
        });
        return true;
    }
    if (!initGovTeamTargetCharts()) {
        var tries = 0;
        var timer = setInterval(function () {
            tries++;
            if (initGovTeamTargetCharts() || tries > 40) clearInterval(timer);
        }, 150);
    }
    document.addEventListener('DOMContentLoaded', initGovTeamTargetCharts);
}());
</script>
HTML;
}

$extraHeadMarkup = org_portal_head_markup_beranda($extraHeadMarkup);
$sgAssetBase = defined('ORG_WEB_ROOT') && ORG_WEB_ROOT !== '' ? rtrim(ORG_WEB_ROOT, '/') : '';
$chartJsLocal = htmlspecialchars($sgAssetBase . '/assets/vendor/chartjs/chart.umd.min.js', ENT_QUOTES, 'UTF-8');
$chartJsCdn = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
$extraFooterMarkup .= '<script src="' . $chartJsLocal . '" onload="document.dispatchEvent(new Event(\'beranda:chart-ready\'))"></script>' . "\n"
    . '<script>
(function () {
    if (typeof Chart !== "undefined") {
        document.dispatchEvent(new Event("beranda:chart-ready"));
        return;
    }
    var cdnScript = document.createElement("script");
    cdnScript.src = "' . $chartJsCdn . '";
    cdnScript.onload = function () {
        document.dispatchEvent(new Event("beranda:chart-ready"));
    };
    cdnScript.onerror = function () {
        document.dispatchEvent(new Event("beranda:chart-ready"));
    };
    document.head.appendChild(cdnScript);
}());
</script>' . "\n";
/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
echo '<main class="site-layout-main">';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_smart_hero.php';
?>
<div class="sg-portal-main sg-dash-main">
    <?php $sgAmbientVariant = 'floor'; $sgParticleCount = 18; require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'sg_ambient_layer.php'; ?>
    <div class="container-global site-main" id="beranda-root">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show section-spacing" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_dashboard_widgets.php'; ?>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_team_targets.php'; ?>

        <section class="section-spacing beranda-section beranda-section--surface-muted sg-reveal-section" id="beranda-kunjungan-web" aria-labelledby="beranda-kunjungan-web-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
                <div>
                    <h2 id="beranda-kunjungan-web-title" class="beranda-section__title mb-0">Statistik Kunjungan Tamu Website</h2>
                    <p class="beranda-section__desc mb-0 mt-1">Pemantauan aktivitas pengunjung portal secara ringkas.</p>
                </div>
            </div>
            <div class="card beranda-visit-card border-0">
                <div class="card-body">
                    <div class="row g-3 beranda-visit-stats">
                        <div class="col-12 col-sm-6">
                            <article class="beranda-visit-stat beranda-visit-stat--today">
                                <span class="beranda-visit-stat__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                <span class="beranda-visit-stat__label">Tamu Hari Ini</span>
                                <p class="beranda-visit-stat__num mb-0" data-sg-count="<?php echo (int) $berandaTotalToday; ?>"><?php echo (int) $berandaTotalToday; ?></p>
                            </article>
                        </div>
                        <div class="col-12 col-sm-6">
                            <article class="beranda-visit-stat beranda-visit-stat--week">
                                <span class="beranda-visit-stat__icon" aria-hidden="true"><i class="fa-solid fa-calendar-week"></i></span>
                                <span class="beranda-visit-stat__label">7 Hari Terakhir</span>
                                <p class="beranda-visit-stat__num mb-0" data-sg-count="<?php echo (int) $berandaTotalWeek; ?>"><?php echo (int) $berandaTotalWeek; ?></p>
                            </article>
                        </div>
                    </div>
                    <p class="beranda-visit-caption">Tren kunjungan 14 hari terakhir untuk pemantauan aktivitas pengunjung.</p>
                    <div class="beranda-visit-chart-shell">
                        <div class="beranda-visit-chart-wrap">
                            <canvas id="berandaVisitChart" aria-label="Grafik kunjungan tamu website"></canvas>
                        </div>
                        <div id="berandaVisitChartError" class="beranda-visit-chart-error" role="status" aria-live="polite">
                            Grafik statistik sementara belum dapat dimuat. Coba refresh halaman.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-spacing beranda-section beranda-section--surface-white" id="beranda-pusat-informasi" aria-labelledby="home-pusat-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2" data-aos="fade-up" data-aos-duration="700">
                <div>
                    <h2 id="home-pusat-title" class="beranda-section__title mb-0">Pusat Informasi &amp; Pengumuman</h2>
                    <p class="beranda-section__desc">Pengumuman resmi, berita, dan informasi terbaru dari Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="berita.php">Lihat semua <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
            </div>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_pusat_informasi.php'; ?>
        </section>
        <section class="section-spacing beranda-section beranda-section--surface-muted" id="beranda-ringkasan-eksekutif" aria-labelledby="beranda-exec-title">
            <header class="beranda-exec-section__head" data-aos="fade-up" data-aos-duration="700">
                <h2 id="beranda-exec-title" class="beranda-section__title mb-0">Ringkasan eksekutif</h2>
                <p class="beranda-exec-section__eyebrow">Visi · Misi · Struktur organisasi</p>
                <p class="beranda-section__desc mb-0 mt-2">Gambaran singkat arah organisasi dan tata kelola unit kerja.</p>
            </header>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_ringkasan_eksekutif.php'; ?>
            <p class="small text-muted mb-0 mt-3 position-relative beranda-exec-section__foot" style="z-index:1" data-aos="fade-up" data-aos-delay="150"><a href="profil.php" class="text-decoration-none">Halaman Profil</a> berisi Visi, Misi, struktur, dan ringkasan organisasi secara lengkap.</p>
        </section>

        <section class="section-spacing beranda-section beranda-section--surface-muted" id="beranda-galeri-kegiatan" aria-labelledby="beranda-galeri-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
                <div>
                    <h2 id="beranda-galeri-title" class="beranda-section__title mb-0">Galeri Kegiatan Terbaru</h2>
                    <p class="beranda-section__desc mb-0 mt-1">Dokumentasi visual kegiatan dan program Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="galeri.php">Lihat galeri lengkap <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
            </div>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_galeri_kegiatan.php'; ?>
        </section>

    </div>
</div>
<?php
ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_visit_chart_script.php';
$extraFooterMarkup .= ob_get_clean();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
?>
