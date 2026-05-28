<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_subpage_seo.php';

$pageTitle = 'Galeri Kegiatan — Bagian Organisasi Setda Kabupaten Kepulauan Aru';
$navActive = 'galeri';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'mode-publikasi page-publikasi-premium page-galeri-premium';

$galeriRows = org_galeri_kegiatan_load_public($dbApp instanceof mysqli ? $dbApp : null);

ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'publikasi_premium_styles.php';
$extraHeadMarkup = (string) ob_get_clean();
$extraHeadMarkup = org_subpage_seo_head_markup(
    $pageTitle,
    'Galeri kegiatan Bagian Organisasi Setda Kabupaten Kepulauan Aru. Dokumentasi foto resmi kegiatan kelembagaan, pelayanan publik, dan reformasi birokrasi.',
    'galeri',
    'Galeri'
) . $extraHeadMarkup;
$extraHeadMarkup .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">' . "\n";
/* Card galeri page disamakan dengan card beranda — load setelah
   publikasi_premium_styles agar override aturan legacy .gl-item / .gl-masonry.
   Gunakan org_asset_url() (BUKAN org_href) karena ini static asset, bukan PHP page,
   agar query string ?v= tidak ter-URL-encode jadi %3F%3D. */
$extraHeadMarkup .= '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/galeri-page-cards.css?v=4'), ENT_QUOTES, 'UTF-8') . '">' . "\n";

$publikasiPremiumInitSwiper = false;
ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'publikasi_premium_footer.php';
$extraFooterMarkup = (string) ob_get_clean();
$extraFooterMarkup .= '<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>' . "\n";
$extraFooterMarkup .= <<<'HTML'
<script>
(function () {
    var fancyboxOpts = {
        animated: true,
        dragToClose: true,
        backdropClick: 'close',
        placeFocusBack: false,
        Carousel: { transition: 'fade' },
        Images: { zoom: true },
        Thumbs: { type: 'classic' },
        Toolbar: {
            display: {
                left: ['infobar'],
                middle: [],
                right: ['slideshow', 'thumbs', 'close']
            }
        }
    };

    function bindVisibleFancybox() {
        if (typeof Fancybox === 'undefined') return;
        Fancybox.unbind('[data-fancybox="galeri-kegiatan"]');
        Fancybox.bind('[data-fancybox="galeri-kegiatan"]:not(.gl-item--hidden)', fancyboxOpts);
    }

    var grid = document.getElementById('halamanGaleriGrid');
    var searchInput = document.getElementById('glSearchInput');
    var searchClear = document.getElementById('glSearchClear');
    var emptyEl = document.getElementById('glEmptyFilter');
    var countEl = document.getElementById('glResultCount');
    if (!grid) return;

    var items = Array.prototype.slice.call(grid.querySelectorAll('.gl-item'));
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.gl-filters__tab'));
    var activeTab = document.querySelector('.gl-filters__tab.is-active');
    var activeFilter = activeTab ? (activeTab.getAttribute('data-gl-filter') || 'all') : 'all';

    function normalize(str) {
        return (str || '').toLowerCase().trim();
    }

    function applyFilters() {
        var q = normalize(searchInput ? searchInput.value : '');
        var visible = 0;
        items.forEach(function (el) {
            el.classList.remove('gl-item--hidden');
            var year = el.getAttribute('data-gl-year') || '';
            var blob = el.getAttribute('data-gl-search') || '';
            var matchYear = activeFilter === 'all' || year === activeFilter;
            var matchSearch = q === '' || blob.indexOf(q) !== -1;
            var show = matchYear && matchSearch;
            if (!show) {
                el.classList.add('gl-item--hidden');
            }
            el.setAttribute('aria-hidden', show ? 'false' : 'true');
            if (show) visible += 1;
        });
        if (countEl) countEl.textContent = String(visible);
        if (emptyEl) {
            var noResults = visible === 0;
            emptyEl.hidden = !noResults;
            emptyEl.classList.toggle('gl-empty--hidden', !noResults);
        }
        if (searchClear && searchInput) {
            searchClear.hidden = searchInput.value === '';
        }
        bindVisibleFancybox();
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activeFilter = tab.getAttribute('data-gl-filter') || 'all';
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (searchClear && searchInput) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            applyFilters();
        });
    }

    bindVisibleFancybox();
    applyFilters();
}());
</script>
HTML;

org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
org_portal_set_hero(
    'Galeri',
    '',
    'Publikasi Digital',
    'fa-images',
    [
        ['value' => count($galeriRows), 'label' => 'Foto kegiatan'],
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <section class="galeri-portal-section">
            <?php
            $galeriMasonryItems = $galeriRows;
            require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'halaman_galeri_masonry.php';
            ?>
        </section>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
