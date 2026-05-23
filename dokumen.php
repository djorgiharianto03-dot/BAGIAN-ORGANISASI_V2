<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$pageTitle = 'Digital Library — Bagian Organisasi';
$navActive = 'dokumen';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-digital-library';
$digitalLibrarySectionExtraClass = 'digital-library--portal';
$digitalLibraryHideIntroHeader = true;

$extraHeadMarkup = '';
$extraFooterMarkup = '';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
$__docCenterAssetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
$extraHeadMarkup .= "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($__docCenterAssetBase . '/assets/css/smart-governance-doc-center.css?v=7', ENT_QUOTES, 'UTF-8') . '">' . "\n";
$extraFooterMarkup .= "\n" . '<script src="' . htmlspecialchars($__docCenterAssetBase . '/assets/js/doc-center-lite.js?v=1', ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
org_portal_set_hero(
    'Perpustakaan Digital',
    '',
    'Perpustakaan Digital',
    'fa-folder-open',
    [
        ['value' => count($libraryDocumentFiles ?? []), 'label' => 'Dokumen'],
        ['value' => (int) array_sum(array_map(static function ($fn) use ($libraryDocumentStatsMap): int {
            $stat = $libraryDocumentStatsMap[(string) $fn] ?? [];

            return (int) ($stat['download_count'] ?? 0);
        }, $libraryDocumentFiles ?? [])), 'label' => 'Total unduhan'],
    ]
);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'portal_subpage_hero.php'; ?>

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
