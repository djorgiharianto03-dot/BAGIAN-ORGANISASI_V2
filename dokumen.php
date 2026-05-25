<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$pageTitle = 'Perpustakaan Digital — Bagian Organisasi';
$navActive = 'dokumen';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-digital-library page-digital-library--hero-panel';
$digitalLibrarySectionExtraClass = 'digital-library--portal digital-library--hero-panel';
/* Tampilkan intro panel (judul + subtitle + search) di dalam section, mini-hero lama disembunyikan. */
$digitalLibraryHideIntroHeader = false;
$digitalLibraryShowMiniHero = false;
$digitalLibraryHeroSubtitle = 'Bagian Organisasi Sekretariat Daerah Kabupaten Kepulauan Aru';

$extraHeadMarkup = '';
$extraFooterMarkup = '';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
$__docCenterAssetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
$extraHeadMarkup .= "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($__docCenterAssetBase . '/assets/css/smart-governance-doc-center.css?v=10', ENT_QUOTES, 'UTF-8') . '">' . "\n";
$extraFooterMarkup .= "\n" . '<script src="' . htmlspecialchars($__docCenterAssetBase . '/assets/js/doc-center-lite.js?v=1', ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
/* Subhero biru standar (judul kecil "Dokumen") DITIADAKAN di halaman ini.
   Sebagai gantinya partial digital_library_section.php akan merender
   panel light-blue (intro + search) sesuai referensi gambar. */
$portalHeroEyebrow = '';
$portalHeroTitle = '';
$portalHeroTitleHtml = '';
$portalHeroLead = '';
$portalHeroStats = [];
$portalHeroBreadcrumb = '';

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<div class="sg-portal-main-inner">
    <div class="container-global site-main">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show section-spacing" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'digital_library_section.php'; ?>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
