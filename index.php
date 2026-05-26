<?php
declare(strict_types=1);

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', __DIR__);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';

/** Jika mod_rewrite gagal, /profil dll. tidak boleh jatuh ke beranda. */
org_dispatch_clean_url_from_index();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_dev_bootstrap_once.php';
org_run_dev_database_bootstrap_once();

/** Harus sebelum bootstrap agar jalur bootstrap_beranda_fast.php aktif (GET beranda). */
define('ORG_BERANDA_PAGE', true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_seo.php';

$pageTitle = org_beranda_seo_page_title();
$siteLogoAlt = org_beranda_seo_logo_alt();
$navActive = 'beranda';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-index-redesign sg-portal-page sg-homepage sg-portal-subpage';
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

$sgPortalDocCount = (int) ($berandaLibraryDocCount ?? 0);
if ($sgPortalDocCount <= 0) {
    $sgPortalDocCount = count($libraryDocumentFiles ?? []);
}
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

if (!defined('ORG_BERANDA_NEED_APEX')) {
    $orgBerandaHasTeamChartData = function_exists('org_beranda_team_targets_has_chart_data')
        && org_beranda_team_targets_has_chart_data($berandaTeamTargetsGrouped ?? []);
    define('ORG_BERANDA_NEED_APEX', $orgBerandaHasTeamChartData);
}

$extraHeadMarkup = org_beranda_seo_head_markup((string) ($logoWebPath ?? ''))
    . org_beranda_index_extra_head_markup(
        count($berandaDashboardWidgets) > 0 || !empty($berandaTeamTargetsVisible)
    );

/** Chart / Fancybox / Apex: dimuat lazy oleh beranda-deferred-load.js (saat section terlihat). */
$extraFooterMarkup = org_portal_footer_markup('');

$extraHeadMarkup = org_portal_head_markup_beranda($extraHeadMarkup);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_share_helpers.php';
$extraHeadMarkup .= org_share_assets_html();
/** Portal beranda: lebar shell header/hero — org-container-global.css */
$htmlClass = 'sg-portal-html-home';

org_portal_set_hero(
    'Portal Smart Governance Bagian Organisasi',
    'Mewujudkan Tata Kelola Pemerintahan Digital.',
    'SEKRETARIAT DAERAH · KEPULAUAN ARU',
    'fa-building-columns',
    [
        ['value' => (int) $berandaTotalToday, 'label' => 'Tamu Hari Ini'],
        ['value' => (int) $berandaTotalWeek, 'label' => 'Kunjungan 7 Hari'],
        ['value' => (int) $sgPortalDocCount, 'label' => 'Dokumen Digital'],
        ['value' => (int) $sgPortalInfoCount, 'label' => 'Publikasi Aktif'],
    ],
    /* Title HTML dengan aksen warna "Smart Governance" sesuai referensi gambar.
       <br> memaksa "Bagian Organisasi" turun ke baris baru di bawah
       "Portal Smart Governance" persis seperti gambar. */
    'Portal <span class="sg-subhero__title-accent">Smart Governance</span><br>Bagian Organisasi'
);
$portalHeroBreadcrumb = '';

/* Primary CTA hero — sekali klik ke informasi/profil bagian organisasi.
   Aman: helper baru, tidak mengubah signature org_portal_set_hero(). */
org_portal_set_hero_cta(
    'Profil Bagian Organisasi',
    org_href('profil.php'),
    'fa-arrow-right'
);

define('ORG_DEFER_LAYOUT_MAIN', true);
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
echo '<main class="site-layout-main">';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_portal_loader.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'portal_subpage_hero.php';
?>
<div class="sg-portal-main sg-dash-main">
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
                <a class="small text-decoration-none beranda-section__link-all" href="<?php echo org_href('berita.php'); ?>">Lihat semua <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
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
            <p class="small text-muted mb-0 mt-3 position-relative beranda-exec-section__foot" style="z-index:1" data-aos="fade-up" data-aos-delay="150"><a href="<?php echo org_href('profil.php'); ?>" class="text-decoration-none">Halaman Profil</a> berisi Visi, Misi, struktur, dan ringkasan organisasi secara lengkap.</p>
        </section>

        <section class="section-spacing beranda-section beranda-section--surface-muted" id="beranda-galeri-kegiatan" aria-labelledby="beranda-galeri-title">
            <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
                <div>
                    <h2 id="beranda-galeri-title" class="beranda-section__title mb-0">Galeri Kegiatan Terbaru</h2>
                    <p class="beranda-section__desc mb-0 mt-1">Dokumentasi visual kegiatan dan program Bagian Organisasi.</p>
                </div>
                <a class="small text-decoration-none beranda-section__link-all" href="<?php echo org_href('galeri.php'); ?>">Lihat galeri lengkap <i class="fa-solid fa-arrow-right ms-1 small" aria-hidden="true"></i></a>
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
