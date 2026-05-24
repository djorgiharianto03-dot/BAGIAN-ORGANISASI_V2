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

    return org_asset_stylesheet_link('assets/css/smart-governance-portal-nav.css');
}

/** Panel navbar — lock paling akhir (timpa site_styles legacy di subhalaman). */
function org_portal_nav_panel_lock_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/sg-portal-navbar-panel-lock.css');
}

/**
 * CSS kritis inline panel navbar — fallback jika cache CSS lama.
 */
function org_portal_nav_panel_critical_markup(): string
{
    return '<style id="sg-portal-nav-panel-critical">'
        . 'body.sg-portal-page .navbar-panel,body.sg-portal-page .site-header__nav-wrap.navbar-panel{'
        . 'width:100%!important;max-width:100%!important;min-height:88px!important;margin:0!important;'
        . 'padding:.35rem 0!important;border-radius:20px!important;display:flex!important;'
        . 'background:var(--sg-nav-panel-bg,rgba(2,22,48,.72))!important;'
        . 'border:1px solid var(--sg-nav-panel-border,rgba(147,197,253,.18))!important;'
        . 'box-shadow:inset 0 1px 0 rgba(255,255,255,.07),0 10px 32px rgba(0,10,28,.42)!important;'
        . 'position:static!important;transform:none!important;opacity:1!important;animation:none!important'
        . '}'
        . 'body.sg-portal-page .site-header__rail .navbar-wrapper{margin-top:clamp(.5rem,1.2vw,1.125rem)!important;padding:0!important}'
        . 'body.sg-portal-page .site-header__nav-wrap .site-header__nav-row{min-height:56px!important;padding:.12rem 0!important}'
        . 'body.sg-portal-page .site-header__nav a.is-active{background:rgba(37,99,235,.4)!important;box-shadow:inset 0 -2px 0 0 #60a5fa!important}'
        . 'body.sg-portal-page .site-header--sg-portal .btn-header-dashboard{background:linear-gradient(135deg,#1e40af 0%,#2563eb 45%,#38bdf8 100%)!important}'
        . '@media(max-width:991.98px){body.sg-portal-page .navbar-panel,body.sg-portal-page .site-header__nav-wrap.navbar-panel{min-height:0!important;height:0!important;padding:0!important;border:none!important;background:transparent!important;box-shadow:none!important}}'
        . '</style>' . "\n";
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
        $base .= org_asset_stylesheet_link('assets/css/beranda-nav-hero.css?v=4')
            . org_beranda_viewport_align_stylesheet_link();
    } else {
        $base .= org_beranda_govtech_styles_async_markup()
            . org_asset_stylesheet_link('assets/css/beranda-nav-hero.css?v=4')
            . org_beranda_viewport_align_stylesheet_link()
            . org_beranda_mobile_stylesheet_link()
            . org_beranda_premium_polish_stylesheet_link();
    }

    /* portal-nav dimuat paling akhir di header.php (setelah unify + hero-reference) */

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
