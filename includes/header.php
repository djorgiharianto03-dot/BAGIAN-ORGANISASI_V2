<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
if (!function_exists('org_is_dev_environment')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
}
org_force_https_redirect();

$pageTitle = $pageTitle ?? 'Bagian Organisasi — Sekretariat Daerah Kab. Kepulauan Aru';
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
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_assets.php';
echo org_theme_boot_script();
?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(function_exists('org_csrf_token') ? org_csrf_token() : '', ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<?php
$orgHeaderBerandaPage = defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true;
if ($orgHeaderBerandaPage) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
    echo org_beranda_header_vendor_markup();
    echo org_beranda_site_global_stylesheet_link();
    echo org_beranda_shell_stylesheet_async_link();
    echo org_beranda_bundle_stylesheet_async_link();
} else {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Public+Sans:wght@400;500;600;700;800&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php'; ?>
<?php
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_mobile_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
    echo org_mobile_stylesheet_link();
    echo org_motion_stylesheet_link();
    echo org_theme_stylesheet_link();
    echo org_tailwind_stylesheet_link();
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_modal_layer_assets.php';
    echo org_navbar_stylesheet_link();
    echo org_modal_layer_stylesheet_link();
    if (str_contains($bodyClassAttr, 'sg-portal-page')) {
        $sgPortalLayoutBase = defined('ORG_WEB_ROOT') && ORG_WEB_ROOT !== '' ? rtrim(ORG_WEB_ROOT, '/') : '';
        echo '<link rel="stylesheet" href="' . htmlspecialchars($sgPortalLayoutBase . '/assets/css/smart-governance-portal-layout-fix.css?v=22', ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
}

if (!empty($extraHeadMarkup) && is_string($extraHeadMarkup)) {
    echo $extraHeadMarkup;
}
if (str_contains($bodyClassAttr, 'sg-portal-page') && !$orgHeaderBerandaPage) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_container_global_assets.php';
    echo org_container_global_stylesheet_link();
}
if (str_contains($bodyClassAttr, 'sg-portal-page') && !$orgHeaderBerandaPage) {
    echo '<style id="sg-portal-shell-critical">'
        . 'body.sg-portal-page .site-header--sg-portal{position:fixed!important;top:0;left:0;right:0;width:100%!important;max-width:100%!important;z-index:1100!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page .sg-portal-main.sg-dash-main{width:100%!important;max-width:min(1320px,100%)!important;margin-left:auto!important;margin-right:auto!important}'
        . ($orgHeaderBerandaPage ? '' : 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title{font-size:clamp(1.25rem,1rem+1vw,1.75rem)!important;line-height:1.2!important}')
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__tagline{font-size:clamp(.9rem,.85rem+.3vw,1.05rem)!important;line-height:1.5!important;max-width:100%!important}'
        . 'body.sg-portal-page .site-header__rail.header-inner{display:flex!important;flex-direction:column!important;align-items:stretch!important;width:100%!important}'
        . 'body.sg-portal-page .site-header__rail .navbar-wrapper{display:block!important;width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important}'
        . 'body.sg-portal-page:not(.sg-homepage) .site-layout-main>.org-hero.sg-subhero,body.sg-portal-page:not(.sg-homepage) .site-layout-main>.sg-subhero{padding-top:calc(var(--sg-portal-header-offset,6.25rem) + clamp(.35rem,.9vw,.5rem))!important;padding-bottom:clamp(.75rem,1.6vw,1rem)!important;width:100%!important;max-width:none!important;margin-left:0!important;margin-right:0!important}'
        . 'body.sg-homepage.sg-portal-page>#sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero{display:block!important;width:100%!important;max-width:100%!important;margin:0!important;overflow-x:hidden!important;overflow-y:visible!important;box-sizing:border-box!important}'
        . 'html.sg-portal-html-home{overflow-y:auto!important;overflow-x:hidden!important}body.sg-portal-page{overflow-y:visible!important;overflow-x:hidden!important;max-width:100%!important}'
        . 'body.sg-portal-page .site-header--sg-portal,body.sg-portal-page .site-header--sg-portal .site-header__gradient{width:100%!important;max-width:100%!important}'
        . ($orgHeaderBerandaPage ? '' : 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__bg,body.sg-homepage.sg-portal-page>#sg-hero .sg-hero__bg{position:absolute!important;inset:0!important;left:0!important;right:0!important;width:100%!important;max-width:100%!important;overflow:hidden!important}')
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page>#sg-hero{overflow-x:hidden!important;overflow-y:visible!important;max-width:100%!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-secondary,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__copy{overflow:visible!important;clip:auto!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__holo,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__holo .sg-command-center{max-height:none!important;overflow:visible!important}'
        . '</style>' . "\n";
}
?>
</head>
<body<?php echo $bodyClassAttr !== '' ? ' class="' . htmlspecialchars($bodyClassAttr, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
<?php
if ($orgHeaderBerandaPage) {
    echo org_beranda_lite_boot_script();
}
?>
<?php if ($holidayThemePreviewActive): ?>
    <div class="org-theme-preview-banner alert alert-success border-0 rounded-0 py-2 px-3 mb-0 text-center small shadow-sm" role="status">
        Pratinjau tema aktif: <strong><?php echo htmlspecialchars($holidayDecoLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
        — hapus <code>?theme_preview=...</code> dari URL untuk kembali normal.
    </div>
<?php endif; ?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'cursor_follower_markup.php'; ?>
<?php
/* Pencarian dokumen hanya di halaman Perpustakaan Digital, bukan di header */
$hideHeaderDocSearch = true;
$hideHeaderSubtitle = !empty($hideHeaderSubtitle);
$publikasiAllowedRoles = ['super_admin', 'admin', 'sub_admin_publikasi'];
$currentAdminRole = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$canAccessEOrganisasi = ($isAdmin ?? false) && org_eorg_session_can_access_hub();
$canAccessPublikasi = ($isAdmin ?? false) && in_array($currentAdminRole, $publikasiAllowedRoles, true);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
if (function_exists('org_tailwind_bootstrap')) {
    org_tailwind_bootstrap();
}
if (function_exists('org_component')) {
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
    ]);
} else {
    $navbarPartial = __DIR__ . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'navbar.php';
    if (is_file($navbarPartial)) {
        extract([
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
        ], EXTR_SKIP);
        require $navbarPartial;
    }
}
if (!defined('ORG_DEFER_LAYOUT_MAIN') || ORG_DEFER_LAYOUT_MAIN !== true) {
    echo '<main class="site-layout-main">';
}
