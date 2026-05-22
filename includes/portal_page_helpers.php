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
    $assetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    $base = '<link rel="preconnect" href="https://fonts.googleapis.com">'
        . "\n" . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . "\n" . '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600;700;800&amp;family=Poppins:wght@500;600;700;800&amp;display=swap" rel="stylesheet">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($assetBase . '/assets/css/smart-governance-portal.css', ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($assetBase . '/assets/css/smart-governance-dashboard.css', ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($assetBase . '/assets/css/smart-governance-premium-ui.css', ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($assetBase . '/assets/css/smart-governance-enterprise.css', ENT_QUOTES, 'UTF-8') . '">'
        . "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($assetBase . '/assets/css/smart-governance-portal-nav.css?v=4', ENT_QUOTES, 'UTF-8') . '">'
        . "\n";

    return $base . $existing;
}

/** Head markup ringan khusus beranda (kurangi CSS/font duplikat). */
function org_portal_head_markup_beranda(string $existing = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $assetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $fonts = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&amp;family=Inter:wght@400;600;700&amp;display=swap';

    $base = '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars($fonts, ENT_QUOTES, 'UTF-8') . '" media="print" onload="this.media=\'all\'">' . "\n"
        . '<noscript><link rel="stylesheet" href="' . htmlspecialchars($fonts, ENT_QUOTES, 'UTF-8') . '"></noscript>' . "\n"
        . org_asset_stylesheet_async('assets/css/smart-governance-portal.css')
        . org_asset_stylesheet_async('assets/css/smart-governance-portal-nav.css?v=4');

    return $base . $existing;
}

function org_portal_footer_markup(string $existing = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $assetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $script = '<script src="' . htmlspecialchars($assetBase . '/assets/js/smart-governance-portal.js?v=17', ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";

    return $existing . $script;
}

/** Footer beranda: offset header segera; portal.js dimuat via beranda-deferred-load.js */
function org_portal_footer_markup_beranda(string $existing = ''): string
{
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';

    return $existing . org_beranda_portal_header_offset_script();
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
