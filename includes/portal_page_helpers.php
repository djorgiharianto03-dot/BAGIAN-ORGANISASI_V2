<?php
declare(strict_types=1);

/**
 * Aset & helper halaman dalam Smart Governance Portal (profil, layanan, dokumen, dll.).
 */
function org_portal_head_markup(string $existing = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    if (!function_exists('org_asset_url')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    /* Font: sudah dimuat di header.php — hindari duplikasi */
    /* smart-governance-subpages.css → org_portal_subpages_stylesheet_link() di header.php (paling akhir) */
    $base = '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/smart-governance-portal.css?v=16'), ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/smart-governance-enterprise.css?v=3'), ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/smart-governance-portal-nav.css?v=17'), ENT_QUOTES, 'UTF-8') . '">'
        . "\n";

    return $base . $existing;
}

/**
 * Stylesheet subhalaman hero compact — v=3 (juga dipanggil ulang paling akhir di header.php).
 */
function org_portal_subpages_stylesheet_link(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    if (!function_exists('org_asset_url')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $href = org_asset_url('assets/css/smart-governance-subpages.css?v=6');

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" data-sg-subpages-css="6">' . "\n";
}

/**
 * Navbar portal — muat paling akhir di head beranda (setelah unify/viewport).
 */
function org_portal_nav_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/smart-governance-portal-nav.css?v=17');
}

/**
 * Head beranda — portal nav + CSS govtech non-blocking (desain tetap, muat ringan).
 */
function org_portal_head_markup_beranda(string $existing = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';

    $base = org_asset_stylesheet_async('assets/css/smart-governance-portal.css');

    if (org_assets_beranda_css_bundle_available()) {
        $base .= org_beranda_hero_fix_active_stylesheet_link()
            . org_asset_stylesheet_link('assets/css/beranda-nav-hero.css')
            . org_beranda_header_nav_unify_stylesheet_link()
            . org_beranda_viewport_align_stylesheet_link();
    } else {
        $base .= org_beranda_govtech_styles_async_markup()
            . org_beranda_hero_fix_active_stylesheet_link()
            . org_beranda_viewport_align_stylesheet_link()
            . org_beranda_mobile_stylesheet_link()
            . org_beranda_premium_polish_stylesheet_link()
            . org_beranda_header_nav_unify_stylesheet_link();
    }

    /* Sync paling akhir — branding header identik Profil */
    $base .= org_portal_nav_stylesheet_link();

    return $base . $existing;
}

/**
 * Lapisan visual govtech beranda — async (bundle atau per-file).
 */
function org_beranda_govtech_styles_async_markup(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
    org_beranda_assets_prepare_builds();

    if (org_assets_beranda_css_bundle_available()) {
        return '';
    }

    $files = [
        'assets/css/smart-governance-homepage.css',
        'assets/css/smart-governance-beranda-ultra.css',
        'assets/css/smart-governance-beranda-premium.css?v=5',
        'assets/css/smart-governance-beranda-govtech.css?v=5',
        'assets/css/smart-governance-beranda-polish.css?v=2',
    ];
    $out = '';
    foreach ($files as $rel) {
        $out .= org_asset_stylesheet_async($rel, str_contains($rel, '?'));
    }

    return $out;
}

function org_portal_footer_markup(string $existing = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    if (!function_exists('org_asset_url')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $script = '<script src="' . htmlspecialchars(org_asset_url('assets/js/smart-governance-portal.js?v=14'), ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";

    return $existing . $script;
}

/**
 * Aktifkan header sticky portal + kelas body.
 *
 * @param string $bodyClass Kelas body yang sudah ada
 * @param bool   $isSubpage true = halaman dalam (profil/layanan/dokumen), false = beranda penuh
 */
function org_portal_prepare_page(string &$bodyClass, bool $isSubpage = true): void
{
    global $smartPortalNav;
    $smartPortalNav = true;
    if (!str_contains($bodyClass, 'sg-portal-page')) {
        $bodyClass = trim($bodyClass . ' sg-portal-page');
    }
    if ($isSubpage && !str_contains($bodyClass, 'sg-portal-subpage')) {
        $bodyClass = trim($bodyClass . ' sg-portal-subpage');
    }
}

/**
 * Gabungkan CSS/JS portal ke head & footer halaman.
 */
function org_portal_apply_assets(string &$bodyClass, string &$extraHeadMarkup, string &$extraFooterMarkup, bool $isSubpage = true): void
{
    org_portal_prepare_page($bodyClass, $isSubpage);
    $extraHeadMarkup = org_portal_head_markup($extraHeadMarkup);
    $extraFooterMarkup = org_portal_footer_markup($extraFooterMarkup);
}

/**
 * Set variabel untuk partial portal_subpage_hero.php.
 *
 * @param list<array{value: int|string, label: string}> $stats
 */
function org_portal_set_hero(
    string $title,
    string $lead = '',
    string $eyebrow = 'Smart Governance Portal',
    string $icon = 'fa-building-columns',
    array $stats = []
): void {
    global $portalHeroEyebrow, $portalHeroTitle, $portalHeroLead, $portalHeroIcon, $portalHeroStats;
    $portalHeroEyebrow = $eyebrow;
    $portalHeroTitle = $title;
    $portalHeroLead = $lead;
    $portalHeroIcon = $icon;
    $portalHeroStats = $stats;
}
