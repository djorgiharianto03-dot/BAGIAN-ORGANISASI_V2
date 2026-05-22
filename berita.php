<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$pageTitle = 'Pusat Informasi & Pengumuman — Bagian Organisasi';
$navActive = 'berita';
$includePersonnelModals = false;
$bodyClass = 'mode-publikasi page-publikasi-premium page-berita-premium';

ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'publikasi_premium_styles.php';
$extraHeadMarkup = (string) ob_get_clean();

$publikasiPremiumInitSwiper = false;
ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'publikasi_premium_footer.php';
$extraFooterMarkup = (string) ob_get_clean();

/** Filter Pusat Informasi (GET q). */
$needlePi = strtolower(trim($searchQuery));
$highlightSearch = static function (string $text, string $needle): string {
    $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    if ($needle === '') {
        return $safeText;
    }

    $pattern = '/(' . preg_quote($needle, '/') . ')/iu';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts) || count($parts) === 0) {
        return $safeText;
    }

    $html = '';
    foreach ($parts as $idx => $part) {
        $encoded = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
        if ($idx % 2 === 1 && $part !== '') {
            $html .= '<mark class="pi-search-highlight">' . $encoded . '</mark>';
        } else {
            $html .= $encoded;
        }
    }

    return $html;
};
$pusatUntukHalaman = $pusatInformasiPostsAll;
if ($needlePi !== '') {
    $pusatUntukHalaman = array_values(array_filter($pusatInformasiPostsAll, static function ($pi) use ($needlePi) {
        $judul = strtolower((string) ($pi['judul'] ?? ''));
        $isi = strtolower(strip_tags((string) ($pi['isi_teks'] ?? '')));

        return stripos($judul, $needlePi) !== false || stripos($isi, $needlePi) !== false;
    }));
}

usort($pusatUntukHalaman, static function (array $a, array $b): int {
    $rawA = (string) ($a['created_at'] ?? '');
    $rawB = (string) ($b['created_at'] ?? '');
    $tsA = $rawA !== '' ? strtotime($rawA) : 0;
    $tsB = $rawB !== '' ? strtotime($rawB) : 0;

    return $tsB <=> $tsA;
});

$piBeritaCount = 0;
$piPengCount = 0;
foreach ($pusatInformasiPostsAll as $piRow) {
    if (!is_array($piRow)) {
        continue;
    }
    if (($piRow['kategori'] ?? '') === 'pengumuman') {
        $piPengCount++;
    } else {
        $piBeritaCount++;
    }
}

org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
org_portal_set_hero(
    'Pusat Informasi & Pengumuman',
    '',
    'Publikasi Digital',
    'fa-bullhorn',
    [
        ['value' => count($pusatInformasiPostsAll), 'label' => 'Total entri'],
        ['value' => $piBeritaCount, 'label' => 'Berita'],
        ['value' => $piPengCount, 'label' => 'Pengumuman'],
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

        <div class="sg-portal-toolbar">
            <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
                <label class="visually-hidden" for="piSearchQ">Cari di Pusat Informasi</label>
                <input type="search" class="form-control" id="piSearchQ" name="q" value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari judul atau isi…" style="max-width:min(100%, 28rem);">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Cari</button>
            </form>
        </div>

        <section class="berita-list-section news-portal-section">
            <?php
            $pusatCarouselPosts = $pusatUntukHalaman;
            $pusatSearchQuery = $searchQuery;
            $pusatHighlightSearch = $highlightSearch;
            require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'halaman_pusat_informasi_grid.php';
            ?>
        </section>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
