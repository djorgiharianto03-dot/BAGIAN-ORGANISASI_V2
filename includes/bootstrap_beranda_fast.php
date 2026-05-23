<?php

/**
 * Bootstrap ringan GET beranda — hindari ratusan baris bootstrap penuh.
 */
if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_upload_dirs.php';
org_ensure_upload_directories(ORG_ROOT);
if (function_exists('org_runtime_cache_ensure_dir')) {
    org_runtime_cache_ensure_dir();
}
if (function_exists('org_beranda_purge_empty_list_caches')) {
    org_beranda_purge_empty_list_caches();
}
if (function_exists('org_beranda_purge_stale_team_targets_caches')) {
    org_beranda_purge_stale_team_targets_caches();
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_hari_besar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'site_content_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'dokumen_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'team_targets_db.php';

$message = '';
$messageType = '';
$searchQuery = '';

if (isset($_SESSION['flash_message'], $_SESSION['flash_type'])) {
    $message = (string) $_SESSION['flash_message'];
    $messageType = (string) $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

$isAdmin = !empty($_SESSION['is_admin']);
$currentLevel = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if ($currentLevel !== '' && (!isset($_SESSION['level']) || $_SESSION['level'] !== $currentLevel)) {
    $_SESSION['level'] = $currentLevel;
    $_SESSION['admin_role'] = $currentLevel;
}

if ($isAdmin && !isset($_SESSION['admin_role'])) {
    $_SESSION['admin_role'] = $currentLevel !== '' ? $currentLevel : 'super_admin';
    $_SESSION['level'] = $_SESSION['admin_role'];
}

if ($isAdmin && !isset($_SESSION['org_is_kabag'])) {
    $_SESSION['org_is_kabag'] = org_staff_session_is_kabag_cached();
}

$siteSettingsFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'site_settings.json';
$defaultSiteSettings = [
    'profile_visi' => '',
    'profile_misi' => '',
    'profile_struktur' => '',
    'struktur_blurb' => '',
    'organisasi_intro' => '',
    'pengumuman' => '',
];
$siteSettings = $defaultSiteSettings;
if (is_file($siteSettingsFile)) {
    $siteRaw = file_get_contents($siteSettingsFile);
    if ($siteRaw !== false && $siteRaw !== '') {
        $decodedSite = json_decode($siteRaw, true);
        if (is_array($decodedSite)) {
            $siteSettings = array_merge($defaultSiteSettings, $decodedSite);
        }
    }
}

$dbApp = org_db();
if ($dbApp instanceof mysqli) {
    org_beranda_ensure_table_once($dbApp, 'site_content', static function () use ($dbApp): void {
        org_site_content_ensure_installed($dbApp);
    });
    org_beranda_ensure_table_once($dbApp, 'galeri', static function () use ($dbApp): void {
        org_galeri_ensure_table($dbApp);
    });
    $siteSettings = org_beranda_merge_site_settings($siteSettings, $dbApp);
}

$berandaLibraryDocCount = 0;
$pusatInformasiPosts = [];
$berandaGaleriKegiatan = [];
$berandaDashboardWidgets = [];
$berandaWidgetDetailsMap = [];
$berandaTeamTargetsTahun = org_team_targets_normalize_tahun($_GET['tahun'] ?? (int) date('Y'));
$berandaTeamTargetsYears = [];
$berandaTeamTargetsGrouped = org_team_targets_empty_grouped();
$berandaTeamTargetsVisible = false;

if ($dbApp instanceof mysqli) {
    org_beranda_ensure_table_once($dbApp, 'dokumen', static function () use ($dbApp): void {
        org_dokumen_ensure_table($dbApp);
    });
    $berandaLibraryDocCount = org_beranda_dokumen_count_cached($dbApp);

    org_beranda_ensure_table_once($dbApp, 'pusat_informasi', static function () use ($dbApp): void {
        org_pusat_informasi_ensure_table($dbApp);
    });
    $pusatInformasiPosts = org_beranda_fetch_pusat_informasi_cached($dbApp, 4, 12);

    if (!defined('ORG_BERANDA_LITE_FIRST') || ORG_BERANDA_LITE_FIRST !== true) {
        $berandaGaleriKegiatan = org_beranda_fetch_galeri_cached($dbApp, 6);
    }

    /* index.php SSR partial indikator + target tim — selalu muat (bukan hanya beranda_chunk.php) */
    $berandaHeavy = org_beranda_load_dashboard_and_team($dbApp, $berandaTeamTargetsTahun);
    $berandaDashboardWidgets = $berandaHeavy['widgets'];
    $berandaWidgetDetailsMap = $berandaHeavy['details'];
    $berandaTeamTargetsTahun = (int) $berandaHeavy['teamTahun'];
    $berandaTeamTargetsYears = $berandaHeavy['teamYears'];
    $berandaTeamTargetsGrouped = $berandaHeavy['teamGrouped'];
    $berandaTeamTargetsVisible = (bool) $berandaHeavy['teamVisible'];
}

$uploadedFiles = [];
$libraryDocumentFiles = [];
$libraryDocumentStatsMap = [];
$pengumumanCards = [];
$pusatInformasiPostsAll = [];
$personnelData = [];
$personnelIds = [];
$personnelSlugs = [];

$logoWebPath = '';
foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $logoExt) {
    $logoFs = ORG_ROOT . DIRECTORY_SEPARATOR . 'logo.' . $logoExt;
    if (is_file($logoFs)) {
        $logoWebPath = 'logo.' . $logoExt . '?v=' . rawurlencode((string) filemtime($logoFs));
        break;
    }
}

define('ORG_BOOTSTRAP_BERANDA_FAST_DONE', true);

/**
 * Kabag flag tanpa query DB berulang.
 */
function org_staff_session_is_kabag_cached(): bool
{
    if (isset($_SESSION['org_is_kabag'])) {
        return (bool) $_SESSION['org_is_kabag'];
    }

    return org_staff_session_is_kabag(org_db());
}
