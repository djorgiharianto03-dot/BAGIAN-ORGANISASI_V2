<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

define('ORG_BERANDA_PAGE', true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_dev_bootstrap_once.php';
org_run_dev_database_bootstrap_once();

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
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';

$sgAssetBase = org_asset_web_base();
$extraHeadMarkup = org_portal_head_markup_beranda(org_beranda_bundle_stylesheet_async_link());
$extraFooterMarkup = org_portal_footer_markup_beranda('');
$extraFooterMarkup .= '<script>window.ORG_VENDOR_BASE=' . json_encode(org_vendor_web_base(), JSON_UNESCAPED_SLASHES)
    . ';window.ORG_ASSET_BASE=' . json_encode($sgAssetBase, JSON_UNESCAPED_SLASHES) . ';</script>' . "\n";
$extraFooterMarkup .= org_beranda_deferred_script_tag();

/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_smart_hero.php';
echo '<main class="site-layout-main">';
?>
<div class="sg-portal-main sg-dash-main">
    <?php $sgAmbientVariant = 'floor'; $sgParticleCount = 8; require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'sg_ambient_layer.php'; ?>
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
