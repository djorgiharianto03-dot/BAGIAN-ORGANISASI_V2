<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'profil_org_helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$pageTitle = 'Profil — Bagian Organisasi';
$bodyClass = 'page-profil-org';
$profilVisiHtml = org_profil_visi_display_html((string) ($siteSettings['profile_visi'] ?? ''));
$profilMisiPoints = org_profil_misi_to_points((string) ($siteSettings['profile_misi'] ?? ''));
$profilOrgIntro = trim((string) ($siteSettings['organisasi_intro'] ?? ''));
$navActive = 'profil';
$includePersonnelModals = true;
$includeNewsModals = false;

/** Gambar struktur opsional (unggah ke uploads/ atau assets). */
$profilStrukturImgWeb = '';
$profilStrukturCandidates = [
    'uploads' . DIRECTORY_SEPARATOR . 'struktur-organisasi.webp',
    'uploads' . DIRECTORY_SEPARATOR . 'struktur-organisasi.jpg',
    'uploads' . DIRECTORY_SEPARATOR . 'struktur-organisasi.jpeg',
    'uploads' . DIRECTORY_SEPARATOR . 'struktur-organisasi.png',
    'uploads' . DIRECTORY_SEPARATOR . 'struktur.jpg',
    'uploads' . DIRECTORY_SEPARATOR . 'struktur.png',
    'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'struktur-organisasi.webp',
    'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'struktur-organisasi.jpg',
];
foreach ($profilStrukturCandidates as $relFs) {
    $abs = ORG_ROOT . DIRECTORY_SEPARATOR . $relFs;
    if (is_file($abs)) {
        $web = str_replace(DIRECTORY_SEPARATOR, '/', $relFs);
        $profilStrukturImgWeb = $web . '?v=' . rawurlencode((string) filemtime($abs));
        break;
    }
}

/** Jabatan Kepala Bagian Organisasi ditampilkan di atas daftar personel lain. */
$orgProfilJabatanKepalaBagian = static function (string $position): bool {
    $n = mb_strtoupper(preg_replace('/\s+/u', ' ', trim($position)), 'UTF-8');
    return $n !== '' && str_contains($n, 'KEPALA BAGIAN ORGANISASI');
};
$personnelKepalaList = [];
$personnelLainList = [];
foreach ($personnelData as $pEntry) {
    if ($orgProfilJabatanKepalaBagian((string) ($pEntry['position'] ?? ''))) {
        $personnelKepalaList[] = $pEntry;
    } else {
        $personnelLainList[] = $pEntry;
    }
}

ob_start();
?>
<style>
    .profile-full .profil-body-rich p { margin-bottom: 0.65rem; }
    .profile-full .profil-body-rich p:last-child { margin-bottom: 0; }
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'profil_org_premium_styles.php'; ?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'profil_struktur_personel_styles.php'; ?>
</style>
<?php
$extraHeadMarkup = (string) ob_get_clean();

$extraFooterMarkup = <<<'HTML'
<script>
(function () {
    function initProfilOrgMotion() {
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (typeof AOS !== 'undefined') {
            AOS.init({
                once: true,
                duration: reduced ? 0 : 420,
                easing: 'ease-out-cubic',
                offset: 48,
                delay: 0,
                disable: reduced
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfilOrgMotion);
    } else {
        initProfilOrgMotion();
    }
}());
</script>
HTML;
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
$__profilAssetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
$extraHeadMarkup .= "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($__profilAssetBase . '/assets/css/smart-governance-profil-institutional.css', ENT_QUOTES, 'UTF-8') . '">' . "\n";
org_portal_set_hero(
    'Profil Organisasi',
    '',
    'Smart Governance Portal',
    'fa-building-columns',
    [
        ['value' => count($personnelData), 'label' => 'Personel'],
        ['value' => count($profilMisiPoints), 'label' => 'Poin misi'],
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

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'profil_visi_misi_ringkasan.php'; ?>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'profil_struktur_personel.php'; ?>

    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
