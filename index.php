<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

define('ORG_BERANDA_PAGE', true);
define('ORG_BERANDA_LAZY_SECTIONS', true);
define('ORG_BERANDA_LITE_FIRST', true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_runtime_cache.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_dev_bootstrap_once.php';
org_run_dev_database_bootstrap_once();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

$pageTitle = 'Beranda — Bagian Organisasi';
$navActive = 'beranda';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-index-redesign sg-portal-page sg-homepage is-lite-render is-perf-lite';
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

$berandaTotalToday = 0;
$berandaTotalWeek = 0;

$sgPortalDocCount = $berandaLibraryDocCount !== null
    ? (int) $berandaLibraryDocCount
    : count($libraryDocumentFiles ?? []);
$sgPortalInfoCount = count($pusatInformasiPosts ?? []);
$sgPortalGaleriCount = 0;
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
$orgWebRootJs = defined('ORG_WEB_ROOT') ? ORG_WEB_ROOT : '';
$extraFooterMarkup .= '<script>window.ORG_VENDOR_BASE=' . json_encode(org_vendor_web_base(), JSON_UNESCAPED_SLASHES)
    . ';window.ORG_ASSET_BASE=' . json_encode($sgAssetBase, JSON_UNESCAPED_SLASHES)
    . ';window.ORG_WEB_ROOT=' . json_encode($orgWebRootJs, JSON_UNESCAPED_SLASHES) . ';</script>' . "\n";
$extraFooterMarkup .= org_beranda_lite_render_script_tag();
$extraFooterMarkup .= org_beranda_deferred_script_tag();

/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_smart_hero.php';
?>
<p class="beranda-scroll-hint mb-0" id="beranda-scroll-hint" aria-hidden="true">
    <i class="fa-solid fa-chevron-down me-1" aria-hidden="true"></i>
    Gulir ke bawah untuk informasi, dashboard, dan grafik kinerja
</p>
<?php
echo '<main class="site-layout-main">';
?>
<div class="sg-portal-main sg-dash-main">
    <div class="container-global site-main" id="beranda-root">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show section-spacing" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="section-spacing beranda-section beranda-section--surface-white beranda-lite-section" id="beranda-pusat-informasi" aria-labelledby="home-pusat-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2">
                <div>
                    <h2 id="home-pusat-title" class="beranda-section__title mb-0">Pusat Informasi &amp; Pengumuman</h2>
                    <p class="beranda-section__desc">Pengumuman resmi, berita, dan informasi terbaru dari Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="berita.php">Lihat semua <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
            </div>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_pusat_informasi.php'; ?>
        </section>

        <?php
        $berandaLazySectionId = 'smart-gov';
        $berandaLazySectionLabel = 'Memuat Smart Governance…';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell.php';
        ?>
            <div id="beranda-chunk-dashboard" class="beranda-chunk-slot" data-beranda-chunk="dashboard" aria-busy="true" aria-live="polite">
                <section class="beranda-section beranda-chunk-skeleton" id="beranda-dashboard-widgets" aria-labelledby="beranda-dashboard-widgets-title">
                    <div class="beranda-chunk-skeleton__bar" style="width:42%"></div>
                    <div class="beranda-chunk-skeleton__grid">
                        <div class="beranda-chunk-skeleton__card"></div>
                        <div class="beranda-chunk-skeleton__card"></div>
                        <div class="beranda-chunk-skeleton__card"></div>
                    </div>
                </section>
            </div>
            <div id="beranda-chunk-team" class="beranda-chunk-slot" data-beranda-chunk="team" data-beranda-tahun="<?php echo (int) ($berandaTeamTargetsTahun ?? (int) date('Y')); ?>" aria-busy="true" aria-live="polite">
                <section class="beranda-section beranda-chunk-skeleton" id="beranda-team-targets" aria-labelledby="beranda-team-targets-title">
                    <div class="beranda-chunk-skeleton__bar" style="width:55%"></div>
                    <div class="beranda-chunk-skeleton__chart"></div>
                </section>
            </div>
        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell_end.php'; ?>

        <?php
        $berandaLazySectionId = 'visit';
        $berandaLazySectionLabel = 'Memuat statistik kunjungan…';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell.php';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell_end.php';
        $berandaLazySectionId = 'eksekutif';
        $berandaLazySectionLabel = 'Memuat ringkasan eksekutif…';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell.php';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell_end.php';
        $berandaLazySectionId = 'galeri';
        $berandaLazySectionLabel = 'Memuat galeri kegiatan…';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell.php';
        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_lazy_section_shell_end.php';
        ?>

    </div>
</div>
<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
?>
