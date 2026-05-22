<?php
declare(strict_types=1);

/**
 * Persiapan database lokal (Laragon): buat DB & tabel users, seed admin jika kosong.
 * Hanya di environment dev (localhost / Laragon). Di VPS production blok ini dilewati.
 * Paksa aktif/nonaktif: set ORG_DEV_BOOTSTRAP=1 atau =0 di environment PHP.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

if (org_is_dev_environment()) {
mysqli_report(MYSQLI_REPORT_OFF);
$__orgDbHost = '127.0.0.1';
$__orgDbUser = 'root';
$__orgDbPass = '';
$__orgDbName = 'db_organisasi';
$__orgConn = mysqli_connect($__orgDbHost, $__orgDbUser, $__orgDbPass);
if ($__orgConn instanceof mysqli) {
    mysqli_query(
        $__orgConn,
        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $__orgDbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    mysqli_select_db($__orgConn, $__orgDbName);
    $sqlCreateUsers = 'CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(191) NOT NULL DEFAULT \'\',
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email_google` VARCHAR(255) NOT NULL DEFAULT \'\',
  `level` VARCHAR(64) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    mysqli_query($__orgConn, $sqlCreateUsers);
    mysqli_query($__orgConn, "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `level` VARCHAR(64) NULL DEFAULT NULL");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = `role` WHERE (`level` IS NULL OR `level` = '') AND `role` IS NOT NULL");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'sub_admin_eorganisasi' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('sub admin', 'sub_admin', 'subadmin')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'admin' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('admin', 'administrator')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'super_admin' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('super admin', 'super_admin')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'admin' WHERE LOWER(`username`) = 'djorgi' AND (`level` IS NULL OR `level` = '')");
    mysqli_query(
        $__orgConn,
        "UPDATE `users` SET `level` = 'kabag_organisasi'
         WHERE LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(`username`), ' ', ''), '-', ''), '_', ''), '.', '')) = 'kabagorganisasi'
            OR LOWER(TRIM(`username`)) IN ('kabag_organisasi', 'kabag organisasi', 'kabag-organisasi', 'kabag')"
    );
    mysqli_query(
        $__orgConn,
        "UPDATE `users` SET `level` = 'kabag_organisasi'
         WHERE LOWER(TRIM(COALESCE(`nama`, ''))) LIKE '%kabag%'
           AND LOWER(TRIM(COALESCE(`nama`, ''))) LIKE '%organisasi%'"
    );
    $chkNama = mysqli_query($__orgConn, "SHOW COLUMNS FROM `users` LIKE 'nama'");
    $namaColExists = $chkNama && mysqli_num_rows($chkNama) > 0;
    if ($chkNama) {
        mysqli_free_result($chkNama);
    }
    if (!$namaColExists) {
        $chkLegacy = mysqli_query($__orgConn, "SHOW COLUMNS FROM `users` LIKE 'nama_staf'");
        if ($chkLegacy && mysqli_num_rows($chkLegacy) > 0) {
            mysqli_query($__orgConn, 'ALTER TABLE `users` CHANGE `nama_staf` `nama` VARCHAR(191) NOT NULL DEFAULT \'\'');
        }
        if ($chkLegacy) {
            mysqli_free_result($chkLegacy);
        }
    }
    $resCnt = mysqli_query($__orgConn, 'SELECT COUNT(*) AS `cnt` FROM `users`');
    $rowCnt = $resCnt ? mysqli_fetch_assoc($resCnt) : null;
    if ($resCnt) {
        mysqli_free_result($resCnt);
    }
    $userCount = (int) ($rowCnt['cnt'] ?? 0);
    if ($userCount === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        if ($hash !== false) {
            $stmt = mysqli_prepare(
                $__orgConn,
                'INSERT INTO `users` (`nama`, `username`, `password`, `email_google`, `level`) VALUES (?, ?, ?, ?, ?)'
            );
            if ($stmt) {
                $seedNama = 'Administrator';
                $seedUser = 'admin';
                $seedEmail = '';
                $seedLevel = 'admin';
                mysqli_stmt_bind_param($stmt, 'sssss', $seedNama, $seedUser, $hash, $seedEmail, $seedLevel);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    mysqli_close($__orgConn);
}
}

define('ORG_BERANDA_PAGE', true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

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

$berandaVisitStats = org_beranda_fetch_visit_stats(org_db());
$berandaVisitLabels = $berandaVisitStats['labels'];
$berandaVisitValues = $berandaVisitStats['values'];
$berandaTotalToday = $berandaVisitStats['total_today'];
$berandaTotalWeek = $berandaVisitStats['total_week'];

$sgPortalDocCount = $berandaLibraryDocCount !== null
    ? (int) $berandaLibraryDocCount
    : count($libraryDocumentFiles ?? []);
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
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
$extraHeadMarkup = org_beranda_bundle_stylesheet_link();
if ($extraHeadMarkup === '') {
    $sgAssetBaseEarly = org_asset_web_base();
    $berandaPageCss = $sgAssetBaseEarly . '/assets/css/beranda-page.css';
    $berandaPageCssFs = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-page.css';
    if (is_file($berandaPageCssFs)) {
        $berandaPageCss .= '?v=' . rawurlencode((string) filemtime($berandaPageCssFs));
    }
    $extraHeadMarkup = '<link rel="stylesheet" href="' . htmlspecialchars($berandaPageCss, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

$extraFooterMarkup = org_portal_footer_markup('');

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
    function startTeamCharts() {
        if (!initGovTeamTargetCharts()) {
            var tries = 0;
            var timer = setInterval(function () {
                tries++;
                if (initGovTeamTargetCharts() || tries > 40) clearInterval(timer);
            }, 150);
        }
    }
    document.addEventListener('beranda:apex-ready', startTeamCharts);
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts !== 'undefined') startTeamCharts();
    });
}());
</script>
HTML;
}

$extraHeadMarkup = org_portal_head_markup_beranda($extraHeadMarkup);
$sgAssetBase = org_asset_web_base();
$chartJsLocal = htmlspecialchars(org_vendor_url(org_vendor_chartjs_js()), ENT_QUOTES, 'UTF-8');
if (!empty($berandaTeamTargetsVisible)) {
    $extraHeadMarkup .= org_vendor_script_preload(org_vendor_apexcharts_js());
}
$extraFooterMarkup .= '<script>window.ORG_VENDOR_BASE=' . json_encode(org_vendor_web_base(), JSON_UNESCAPED_SLASHES) . ';</script>' . "\n";
$extraFooterMarkup .= '<script src="' . $chartJsLocal . '" defer></script>' . "\n"
    . '<script>
(function () {
    function ready() { document.dispatchEvent(new Event("beranda:chart-ready")); }
    function loadChart() {
        if (typeof Chart !== "undefined") { ready(); return; }
        var s = document.createElement("script");
        s.src = "' . $chartJsLocal . '";
        s.defer = true;
        s.onload = ready;
        s.onerror = ready;
        document.head.appendChild(s);
    }
    if ("IntersectionObserver" in window) {
        var el = document.getElementById("berandaVisitChart");
        if (!el) { loadChart(); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { io.disconnect(); loadChart(); }
            });
        }, { rootMargin: "120px" });
        io.observe(el);
    } else {
        loadChart();
    }
}());
</script>' . "\n";
$berandaLazyJs = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'beranda-lazy-extras.js';
if (is_file($berandaLazyJs)) {
    $lazyUrl = $sgAssetBase . '/assets/js/beranda-lazy-extras.js?v=' . rawurlencode((string) filemtime($berandaLazyJs));
    $extraFooterMarkup .= '<script src="' . htmlspecialchars($lazyUrl, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}

/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_smart_hero.php';
echo '<main class="site-layout-main">';
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
