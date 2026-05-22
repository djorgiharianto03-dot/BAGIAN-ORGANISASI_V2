<?php

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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_seo.php';

$pageTitle = org_beranda_seo_page_title();
$navActive = 'beranda';
$siteLogoAlt = 'Logo Bagian Organisasi — Sekretariat Daerah Kabupaten Kepulauan Aru';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-index-redesign sg-portal-page sg-homepage is-lite-render is-perf-lite beranda-ssr-content';
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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';
$berandaVisitDb = ($dbApp ?? null) instanceof mysqli ? $dbApp : org_db();
$berandaVisitStats = org_beranda_fetch_visit_stats($berandaVisitDb instanceof mysqli ? $berandaVisitDb : null);
$berandaVisitLabels = $berandaVisitStats['labels'];
$berandaVisitValues = $berandaVisitStats['values'];
$berandaTotalToday = (int) $berandaVisitStats['total_today'];
$berandaTotalWeek = (int) $berandaVisitStats['total_week'];

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
$pageSeoHeadMarkup = org_beranda_seo_head_markup((string) ($logoWebPath ?? ''));
$extraHeadMarkup = org_portal_head_markup_beranda(
    org_beranda_bundle_stylesheet_async_link() . org_beranda_home_layout_stylesheet_link()
);
$extraFooterMarkup = org_portal_footer_markup_beranda('');
$orgWebRootJs = defined('ORG_WEB_ROOT') ? ORG_WEB_ROOT : '';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_production_assets.php';
$orgUseMinJs = is_file(ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'beranda-deferred-load.min.js');
$extraFooterMarkup .= '<script>window.ORG_VENDOR_BASE=' . json_encode(org_vendor_web_base(), JSON_UNESCAPED_SLASHES)
    . ';window.ORG_ASSET_BASE=' . json_encode($sgAssetBase, JSON_UNESCAPED_SLASHES)
    . ';window.ORG_WEB_ROOT=' . json_encode($orgWebRootJs, JSON_UNESCAPED_SLASHES)
    . ';window.ORG_USE_MIN_JS=' . ($orgUseMinJs ? 'true' : 'false') . ';</script>' . "\n";
$extraFooterMarkup .= org_beranda_lite_render_script_tag();
$extraFooterMarkup .= org_beranda_deferred_script_tag();

/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
echo '<main class="site-layout-main" id="main-content" role="main">';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_smart_hero.php';
?>
    <div class="container-global site-main" id="beranda-root">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show section-spacing" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="beranda-section beranda-section--surface-white beranda-lite-section" id="beranda-pusat-informasi" aria-labelledby="home-pusat-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2">
                <div>
                    <h2 id="home-pusat-title" class="beranda-section__title mb-0">Pusat Informasi &amp; Pengumuman</h2>
                    <p class="beranda-section__desc">Pengumuman resmi, berita, dan informasi terbaru dari Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="berita.php">Lihat semua <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
            </div>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_pusat_informasi.php'; ?>
        </section>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_dashboard_widgets.php'; ?>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_team_targets.php'; ?>

        <section class="beranda-section beranda-section--surface-muted beranda-ssr-section" id="beranda-ringkasan-eksekutif" aria-labelledby="beranda-exec-title">
            <header class="beranda-exec-section__head">
                <h2 id="beranda-exec-title" class="beranda-section__title mb-0">Ringkasan eksekutif</h2>
                <p class="beranda-exec-section__eyebrow">Visi · Misi · Struktur organisasi</p>
                <p class="beranda-section__desc mb-0 mt-2">Gambaran singkat arah organisasi dan tata kelola unit kerja.</p>
            </header>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_ringkasan_eksekutif.php'; ?>
            <p class="small text-muted mb-0 mt-3 beranda-exec-section__foot"><a href="profil.php" class="text-decoration-none">Halaman Profil</a> berisi Visi, Misi, struktur, dan ringkasan organisasi secara lengkap.</p>
        </section>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_kunjungan_web.php'; ?>

        <section class="beranda-section beranda-section--surface-muted beranda-ssr-section" id="beranda-galeri-kegiatan" aria-labelledby="beranda-galeri-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2">
                <div>
                    <h2 id="beranda-galeri-title" class="beranda-section__title mb-0">Galeri Kegiatan Terbaru</h2>
                    <p class="beranda-section__desc mb-0 mt-1">Dokumentasi visual kegiatan dan program Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="galeri.php">Lihat galeri lengkap <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
            </div>
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_galeri_kegiatan.php'; ?>
        </section>

    </div>
<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php';
?>
