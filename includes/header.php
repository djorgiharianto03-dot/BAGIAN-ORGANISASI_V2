<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
if (!function_exists('org_is_dev_environment')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
}
org_force_https_redirect();

$pageTitle = $pageTitle ?? 'Bagian Organisasi — Sekretariat Daerah Kab. Kepulauan Aru';
$pageMetaDescription = isset($pageMetaDescription) && is_string($pageMetaDescription) ? trim($pageMetaDescription) : '';
$pageSeoHeadMarkup = isset($pageSeoHeadMarkup) && is_string($pageSeoHeadMarkup) ? $pageSeoHeadMarkup : '';
$siteLogoAlt = isset($siteLogoAlt) && is_string($siteLogoAlt) && $siteLogoAlt !== ''
    ? $siteLogoAlt
    : 'Logo Bagian Organisasi — Sekretariat Daerah Kabupaten Kepulauan Aru';
$navActive = $navActive ?? '';
$bodyClass = isset($bodyClass) && is_string($bodyClass) ? trim($bodyClass) : '';
$htmlClass = isset($htmlClass) && is_string($htmlClass) ? trim($htmlClass) : '';
$holidayThemeClass = function_exists('org_theme_hari_besar_class') ? org_theme_hari_besar_class() : '';
$holidayThemeMeta = function_exists('org_theme_hari_besar_meta')
    ? org_theme_hari_besar_meta()
    : ['class' => $holidayThemeClass, 'label' => '', 'icon' => '', 'ucapan' => '', 'badge' => ''];
$holidayDecoIcon = (string) ($holidayThemeMeta['icon'] ?? '');
$holidayDecoLabel = (string) ($holidayThemeMeta['label'] ?? '');
$holidayUcapan = trim((string) ($holidayThemeMeta['ucapan'] ?? ''));
$holidayBadge = trim((string) ($holidayThemeMeta['badge'] ?? ''));
$holidayUcapanMain = $holidayUcapan;
$holidayUcapanSub = '';
if ($holidayUcapan !== '' && strpos($holidayUcapan, ' — ') !== false) {
    $holidayUcapanParts = explode(' — ', $holidayUcapan, 2);
    $holidayUcapanMain = trim((string) ($holidayUcapanParts[0] ?? $holidayUcapan));
    $holidayUcapanSub = trim((string) ($holidayUcapanParts[1] ?? ''));
}
$holidayThemePreviewActive = trim((string) ($_GET['theme_preview'] ?? '')) !== ''
    && function_exists('org_theme_preview_allowed')
    && org_theme_preview_allowed()
    && $holidayThemeClass !== '';
$bodyClasses = preg_split('/\s+/u', $bodyClass, -1, PREG_SPLIT_NO_EMPTY);
if (!is_array($bodyClasses)) {
    $bodyClasses = [];
}
if ($holidayThemeClass !== '') {
    $bodyClasses[] = $holidayThemeClass;
}
$bodyClasses = array_values(array_unique($bodyClasses));
$bodyClassAttr = trim(implode(' ', $bodyClasses));
?>
<!doctype html>
<html lang="id"<?php echo $htmlClass !== '' ? ' class="' . htmlspecialchars($htmlClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
<?php if (str_contains($bodyClassAttr, 'sg-portal-page')): ?>
<script>
(function () {
    var d = document.documentElement;
    d.classList.add('sg-portal-html');
    if (<?php echo str_contains($bodyClassAttr, 'sg-homepage') ? 'true' : 'false'; ?>) {
        d.classList.add('sg-portal-html-home');
    }
    /* Jangan scrollbar-gutter: stable — header position:fixed lebih lebar dari #sg-hero in-flow */
})();
</script>
<?php endif; ?>
<head>
    <meta charset="UTF-8">
<?php
if ($orgHeaderBeranda ?? (defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true)) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
    echo org_beranda_lite_boot_script();
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_assets.php';
echo org_theme_boot_script();
?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(function_exists('org_csrf_token') ? org_csrf_token() : '', ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<?php
if ($pageMetaDescription !== '') {
    echo '    <meta name="description" content="' . htmlspecialchars($pageMetaDescription, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
if ($pageSeoHeadMarkup !== '') {
    echo $pageSeoHeadMarkup;
}
?>
<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
$orgHeaderBeranda = defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true;
if ($orgHeaderBeranda) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
    echo org_beranda_header_vendor_markup();
} else {
    echo org_vendor_stylesheet(org_vendor_bootstrap_css());
}
if (!$orgHeaderBeranda) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    echo org_assets_fonts_portal_markup();
    echo org_vendor_stylesheet_preload(org_vendor_swiper_css());
    echo org_vendor_stylesheet_preload(org_vendor_aos_css());
}
/* Beranda: AOS/Fancybox dimuat lazy via beranda-deferred-load.js */
if (!$orgHeaderBeranda) {
    echo org_vendor_stylesheet_preload(org_vendor_fontawesome_css());
}
if ($orgHeaderBeranda) {
    echo org_beranda_site_styles_markup();
} else {
    require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php';
}
?>
<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_mobile_assets.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
if (!$orgHeaderBeranda) {
    echo org_mobile_stylesheet_link();
    echo org_motion_stylesheet_link();
    echo org_tailwind_stylesheet_link();
}
if (!$orgHeaderBeranda) {
    echo org_theme_stylesheet_link();
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_modal_layer_assets.php';
    echo org_navbar_stylesheet_link();
    echo org_modal_layer_stylesheet_link();
}
if (str_contains($bodyClassAttr, 'sg-portal-page') && !$orgHeaderBeranda) {
    $sgPortalLayoutBase = defined('ORG_WEB_ROOT') && ORG_WEB_ROOT !== '' ? rtrim(ORG_WEB_ROOT, '/') : '';
    echo '<link rel="stylesheet" href="' . htmlspecialchars($sgPortalLayoutBase . '/assets/css/smart-governance-portal-layout-fix.css?v=19', ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
if (!empty($extraHeadMarkup) && is_string($extraHeadMarkup)) {
    echo $extraHeadMarkup;
}
if (str_contains($bodyClassAttr, 'sg-portal-page')) {
    if ($orgHeaderBeranda) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
        echo org_beranda_shell_stylesheet_async_link();
    } else {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_container_global_assets.php';
        echo org_container_global_stylesheet_link();
    }
}
if (str_contains($bodyClassAttr, 'sg-portal-page')) {
    echo '<style id="sg-portal-shell-critical">'
        . 'body.sg-portal-page .site-header--sg-portal{position:fixed!important;top:0;left:0;right:0;width:100%!important;max-width:none!important;z-index:1200;pointer-events:auto}'
        . 'body.sg-portal-page .site-header__nav a{pointer-events:auto!important;cursor:pointer;position:relative;z-index:3}'
        . 'body.sg-portal-page:not(.sg-homepage) .site-layout-main{padding-top:var(--sg-portal-header-offset,6.5rem)}'
        . 'body.sg-portal-page .site-layout-main>.org-hero.sg-subhero,body.sg-portal-page .site-layout-main>.sg-subhero{padding-top:0!important;width:100%!important;max-width:none!important;margin-left:0!important;margin-right:0!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page>#sg-hero{padding-top:var(--sg-portal-header-offset,7.5rem)!important;width:100%!important;max-width:none!important;margin:0!important;padding-left:0!important;padding-right:0!important;min-height:0!important;height:auto!important}'
        . 'body.sg-homepage.sg-portal-page .site-header--sg-portal{left:0!important;right:0!important;width:auto!important;max-width:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__gradient{width:100%!important;max-width:none!important}'
        . '</style>' . "\n";
}
?>
</head>
<body<?php echo $bodyClassAttr !== '' ? ' class="' . htmlspecialchars($bodyClassAttr, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
<?php if ($holidayThemePreviewActive): ?>
    <div class="org-theme-preview-banner alert alert-success border-0 rounded-0 py-2 px-3 mb-0 text-center small shadow-sm" role="status">
        Pratinjau tema aktif: <strong><?php echo htmlspecialchars($holidayDecoLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
        — hapus <code>?theme_preview=...</code> dari URL untuk kembali normal.
    </div>
<?php endif; ?>
<?php if (!$orgHeaderBeranda) {
    require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'cursor_follower_markup.php';
} ?>
<?php
/* Pencarian dokumen hanya di halaman Perpustakaan Digital, bukan di header */
$hideHeaderDocSearch = true;
$hideHeaderSubtitle = !empty($hideHeaderSubtitle);
$publikasiAllowedRoles = ['super_admin', 'admin', 'sub_admin_publikasi'];
$currentAdminRole = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$canAccessEOrganisasi = ($isAdmin ?? false) && org_eorg_session_can_access_hub();
$canAccessPublikasi = ($isAdmin ?? false) && in_array($currentAdminRole, $publikasiAllowedRoles, true);
org_tailwind_bootstrap();
org_component('navbar', [
    'navActive' => $navActive,
    'logoWebPath' => $logoWebPath ?? '',
    'searchQuery' => $searchQuery ?? '',
    'hideHeaderDocSearch' => $hideHeaderDocSearch,
    'hideHeaderSubtitle' => $hideHeaderSubtitle,
    'isAdmin' => $isAdmin ?? false,
    'canAccessPublikasi' => $canAccessPublikasi,
    'canAccessEOrganisasi' => $canAccessEOrganisasi,
    'smartPortalNav' => !empty($smartPortalNav),
    'holidayUcapan' => $holidayUcapan,
    'holidayUcapanMain' => $holidayUcapanMain,
    'holidayUcapanSub' => $holidayUcapanSub,
    'holidayBadge' => $holidayBadge,
    'holidayDecoIcon' => $holidayDecoIcon,
    'siteLogoAlt' => $siteLogoAlt,
]);
if (!defined('ORG_DEFER_LAYOUT_MAIN') || ORG_DEFER_LAYOUT_MAIN !== true) {
    echo '<main class="site-layout-main">';
}
