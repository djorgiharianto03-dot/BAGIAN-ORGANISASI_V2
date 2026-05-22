<?php
declare(strict_types=1);

/**
 * Fragment HTML section beranda (dimuat saat scroll).
 */
if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', __DIR__);
}

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, max-age=120');
header('X-Robots-Tag: noindex');

$section = strtolower(trim((string) ($_GET['section'] ?? '')));
$allowed = ['visit', 'eksekutif', 'galeri'];
if (!in_array($section, $allowed, true)) {
    http_response_code(400);
    echo '<!-- invalid section -->';
    exit;
}

$siteSettingsFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'site_settings.json';
$siteSettings = [
    'profile_visi' => '',
    'profile_misi' => '',
    'profile_struktur' => '',
    'struktur_blurb' => '',
    'organisasi_intro' => '',
    'pengumuman' => '',
];
if (is_file($siteSettingsFile)) {
    $siteRaw = file_get_contents($siteSettingsFile);
    if ($siteRaw !== false && $siteRaw !== '') {
        $decodedSite = json_decode($siteRaw, true);
        if (is_array($decodedSite)) {
            $siteSettings = array_merge($siteSettings, $decodedSite);
        }
    }
}

$db = org_db();
if ($db instanceof mysqli) {
    require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';
    org_beranda_ensure_table_once($db, 'site_content', static function () use ($db): void {
        org_site_content_ensure_installed($db);
    });
    $siteSettings = org_beranda_merge_site_settings($siteSettings, $db);
}

if ($section === 'visit') {
    $berandaVisitStats = org_beranda_fetch_visit_stats($db);
    $berandaVisitLabels = $berandaVisitStats['labels'];
    $berandaVisitValues = $berandaVisitStats['values'];
    $berandaTotalToday = $berandaVisitStats['total_today'];
    $berandaTotalWeek = $berandaVisitStats['total_week'];
    ?>
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
    <?php
    require ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_visit_chart_script.php';
    exit;
}

if ($section === 'eksekutif') {
    ?>
<section class="section-spacing beranda-section beranda-section--surface-muted" id="beranda-ringkasan-eksekutif" aria-labelledby="beranda-exec-title">
    <header class="beranda-exec-section__head" data-aos="fade-up" data-aos-duration="700">
        <h2 id="beranda-exec-title" class="beranda-section__title mb-0">Ringkasan eksekutif</h2>
        <p class="beranda-exec-section__eyebrow">Visi · Misi · Struktur organisasi</p>
        <p class="beranda-section__desc mb-0 mt-2">Gambaran singkat arah organisasi dan tata kelola unit kerja.</p>
    </header>
    <?php require ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_ringkasan_eksekutif.php'; ?>
    <p class="small text-muted mb-0 mt-3 position-relative beranda-exec-section__foot" style="z-index:1" data-aos="fade-up" data-aos-delay="150"><a href="profil.php" class="text-decoration-none">Halaman Profil</a> berisi Visi, Misi, struktur, dan ringkasan organisasi secara lengkap.</p>
</section>
    <?php
    exit;
}

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
$berandaGaleriKegiatan = [];
if ($db instanceof mysqli) {
    org_beranda_ensure_table_once($db, 'galeri', static function () use ($db): void {
        org_galeri_ensure_table($db);
    });
    $berandaGaleriKegiatan = org_beranda_fetch_galeri_cached($db, 6);
}
?>
<section class="section-spacing beranda-section beranda-section--surface-muted" id="beranda-galeri-kegiatan" aria-labelledby="beranda-galeri-title">
    <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h2 id="beranda-galeri-title" class="beranda-section__title mb-0">Galeri Kegiatan Terbaru</h2>
            <p class="beranda-section__desc mb-0 mt-1">Dokumentasi visual kegiatan dan program Bagian Organisasi.</p>
        </div>
        <a class="small text-decoration-none beranda-section__link-all" href="galeri.php">Lihat galeri lengkap <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
    </div>
    <?php require ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_galeri_kegiatan.php'; ?>
</section>
