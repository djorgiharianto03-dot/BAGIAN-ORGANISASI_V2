<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'layanan_ui.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$pageTitle = 'Layanan Publik — Bagian Organisasi';
$navActive = 'layanan';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-layanan-premium page-layanan-directory';

$layananFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'layanan_data.json';
$layananRows = [];
if (is_file($layananFile)) {
    $rawLayanan = file_get_contents($layananFile);
    if ($rawLayanan !== false && $rawLayanan !== '') {
        $parsed = json_decode($rawLayanan, true);
        if (is_array($parsed)) {
            $layananRows = $parsed;
        }
    }
}

$grouped = [
    'Kelembagaan' => [],
    'Pelayanan Publik' => [],
    'SAKIP & RB' => [],
];
$layananSeqInKat = [
    'Kelembagaan' => 0,
    'Pelayanan Publik' => 0,
    'SAKIP & RB' => 0,
];
foreach ($layananRows as $row) {
    if (!is_array($row)) {
        continue;
    }
    $kat = org_dokumen_normalize_tim_kategori((string) ($row['kategori'] ?? 'Kelembagaan'));
    if (!isset($grouped[$kat])) {
        continue;
    }
    $nama = trim((string) ($row['nama'] ?? ''));
    $desk = trim((string) ($row['deskripsi'] ?? ''));
    $img = trim((string) ($row['media_image'] ?? ''));
    $docs = [];
    if (isset($row['media_documents']) && is_array($row['media_documents'])) {
        foreach ($row['media_documents'] as $docItem) {
            if (is_string($docItem) && trim($docItem) !== '') {
                $docs[] = trim($docItem);
            }
        }
    }
    $doc = trim((string) ($row['media_document'] ?? ''));
    if ($doc !== '' && !in_array($doc, $docs, true)) {
        $docs[] = $doc;
    }
    $link = trim((string) ($row['link'] ?? ''));
    $pinLabel = trim((string) ($row['pin_label'] ?? ''));
    $pinPos = (string) ($row['pin_position'] ?? '');
    if ($pinPos !== 'before' && $pinPos !== 'after') {
        $pinPos = '';
    }
    if (trim($pinLabel) === '') {
        $pinPos = '';
    }
    $urutan = (int) ($row['urutan'] ?? 0);
    if ($urutan < 0) {
        $urutan = 0;
    }
    if ($urutan > 9999) {
        $urutan = 9999;
    }
    if ($nama === '' && $desk === '' && $img === '' && $docs === [] && $link === '') {
        continue;
    }
    $layananSeqInKat[$kat] = ($layananSeqInKat[$kat] ?? 0) + 1;
    $grouped[$kat][] = [
        'nama' => $nama,
        'deskripsi' => $desk,
        'media_image' => $img,
        'media_document' => $docs[0] ?? '',
        'media_documents' => $docs,
        'link' => $link,
        'pin_label' => $pinLabel !== '' ? (function_exists('mb_substr') ? mb_substr($pinLabel, 0, 40, 'UTF-8') : substr($pinLabel, 0, 40)) : '',
        'pin_position' => $pinPos,
        'urutan' => $urutan,
        '_seq' => (int) $layananSeqInKat[$kat],
    ];
}
foreach ($grouped as $gk => &$grows) {
    usort($grows, static function (array $a, array $b): int {
        $ua = (int) ($a['urutan'] ?? 0);
        $ub = (int) ($b['urutan'] ?? 0);
        $sa = (int) ($a['_seq'] ?? 0);
        $sb = (int) ($b['_seq'] ?? 0);
        $ka = $ua < 1 ? 99999 : $ua;
        $kb = $ub < 1 ? 99999 : $ub;
        if ($ka !== $kb) {
            return $ka <=> $kb;
        }

        return $sa <=> $sb;
    });
    foreach ($grows as &$gr) {
        unset($gr['_seq']);
    }
    unset($gr);
}
unset($grows);

$layananTotalDisplayed = 0;
$layananCategoriesWithItems = 0;
foreach ($grouped as $grows) {
    $c = count($grows);
    $layananTotalDisplayed += $c;
    if ($c > 0) {
        $layananCategoriesWithItems++;
    }
}

$layananSectionMeta = [
    'Kelembagaan' => [
        'title' => 'Layanan Kelembagaan dan Anjab',
        'icon' => 'fa-diagram-project',
        'section_mod' => 'layanan-premium-section--kelembagaan',
    ],
    'Pelayanan Publik' => [
        'title' => 'Layanan Pelayanan Publik dan Tata Laksana',
        'icon' => 'fa-users-gear',
        'section_mod' => 'layanan-premium-section--pelayanan',
    ],
    'SAKIP & RB' => [
        'title' => 'Layanan Kinerja dan RB',
        'icon' => 'fa-chart-line',
        'section_mod' => 'layanan-premium-section--sakip',
    ],
];

ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'layanan_premium_styles.php';
$extraHeadMarkup = (string) ob_get_clean();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
$extraHeadMarkup .= org_vendor_stylesheet(org_vendor_fancybox_css());
$extraFooterMarkup = org_vendor_script(org_vendor_fancybox_js(), false);
ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'layanan_premium_footer.php';
$extraFooterMarkup .= (string) ob_get_clean();
ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'layanan_directory_script.php';
$extraFooterMarkup .= (string) ob_get_clean();

org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
$__layananAssetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
$extraHeadMarkup .= "\n" . '<link rel="stylesheet" href="' . htmlspecialchars($__layananAssetBase . '/assets/css/smart-governance-layanan-directory.css', ENT_QUOTES, 'UTF-8') . '">' . "\n";

org_portal_set_hero(
    'Layanan Publik',
    '',
    'Pelayanan Publik Digital',
    'fa-handshake-angle',
    [
        ['value' => $layananTotalDisplayed, 'label' => 'Entri layanan'],
        ['value' => $layananCategoriesWithItems, 'label' => 'Kategori aktif'],
    ]
);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'portal_subpage_hero.php'; ?>

<div class="sg-portal-main-inner">
    <div class="container-global site-main">
        <section class="section-spacing d-none" aria-labelledby="layanan-page-title" data-aos="fade-up">
            <div class="layanan-premium-hero" role="presentation">
                <div class="layanan-premium-hero__inner">
                    <span class="layanan-premium-hero__mark" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
                    <div>
                        <h1 id="layanan-page-title" class="layanan-premium-hero__title">Layanan Publik Bagian Organisasi</h1>
                        <p class="layanan-premium-hero__lead">Informasi layanan kelembagaan, pelayanan publik, dan kinerja organisasi — dengan akses digital yang mudah dan transparan.</p>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($layananTotalDisplayed < 1): ?>
        <section class="section-spacing layanan-dir-empty-page" aria-labelledby="layanan-empty-global-title">
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'layanan_empty_global.php'; ?>
        </section>
        <?php else: ?>
        <?php
        $layananDirectoryMode = true;
        $layananDirTabs = [];
        foreach ($layananSectionMeta as $key => $meta) {
            $t = layanan_category_theme($key);
            $layananDirTabs[] = [
                'slug' => (string) ($t['slug'] ?? 'kelembagaan'),
                'label' => (string) $key,
                'count' => count($grouped[$key] ?? []),
            ];
        }
        ?>
        <section
            class="section-spacing layanan-dir"
            id="layanan-direktori"
            aria-labelledby="layanan-dir-title"
            data-layanan-directory="1"
            data-active-filter="all"
            data-aos="fade-up"
        >
            <header class="layanan-dir__intro">
                <h2 id="layanan-dir-title" class="layanan-dir__title">Direktori layanan</h2>
                <p class="layanan-dir__lead">Telusuri layanan kelembagaan, pelayanan publik, dan kinerja organisasi. Gunakan tab kategori atau kotak pencarian untuk menemukan layanan yang Anda butuhkan.</p>
            </header>

            <div class="layanan-dir__toolbar">
                <div class="layanan-dir__tabs-wrap">
                    <span class="layanan-dir__tabs-label" id="layanan-dir-tabs-label">Kategori</span>
                    <div class="layanan-dir__tabs" role="tablist" aria-labelledby="layanan-dir-tabs-label">
                            <button
                                type="button"
                                class="layanan-dir__tab"
                                role="tab"
                                aria-selected="true"
                                data-filter="all"
                                id="layanan-tab-all"
                            >Semua <span class="layanan-dir__tab-count">(<?php echo (int) $layananTotalDisplayed; ?>)</span></button>
                        <?php foreach ($layananDirTabs as $ti => $tab): ?>
                            <button
                                type="button"
                                class="layanan-dir__tab"
                                role="tab"
                                aria-selected="false"
                                data-filter="<?php echo htmlspecialchars($tab['slug'], ENT_QUOTES, 'UTF-8'); ?>"
                                id="layanan-tab-<?php echo htmlspecialchars($tab['slug'], ENT_QUOTES, 'UTF-8'); ?>"
                            ><?php echo htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?> <span class="layanan-dir__tab-count">(<?php echo (int) $tab['count']; ?>)</span></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="layanan-dir__search">
                    <label class="layanan-dir__search-label" for="layanan-dir-search">Cari layanan</label>
                    <input
                        type="search"
                        id="layanan-dir-search"
                        class="layanan-dir__search-input"
                        placeholder="Judul, deskripsi, atau kategori…"
                        autocomplete="off"
                        enterkeyhint="search"
                    >
                </div>
            </div>

            <div class="layanan-dir__grid" role="list" aria-live="polite" aria-relevant="additions removals">
                <?php
                $cardIdx = 0;
                foreach ($layananSectionMeta as $key => $meta):
                    $rows = $grouped[$key] ?? [];
                    $theme = layanan_category_theme($key);
                    $fbGroup = 'layanan-' . $theme['slug'];
                    foreach ($rows as $item):
                        $layananItem = $item;
                        $layananTheme = $theme;
                        $layananFancyboxGroup = $fbGroup;
                        $layananCategoryLabel = (string) $key;
                        $layananCardAosDelay = min(280, $cardIdx * 40);
                        $cardIdx++;
                        require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'layanan_service_card.php';
                    endforeach;
                endforeach;
                ?>
            </div>

            <div class="layanan-dir__empty-filter" id="layanan-dir-no-results" hidden>
                <svg class="layanan-dir__empty-filter__svg" viewBox="0 0 200 140" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="ldFilterEmptyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#cbd5e1"/>
                            <stop offset="100%" stop-color="#e2e8f0"/>
                        </linearGradient>
                    </defs>
                    <rect x="36" y="32" width="128" height="76" rx="12" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                    <circle cx="78" cy="62" r="14" fill="url(#ldFilterEmptyGrad)" opacity="0.9"/>
                    <path d="M72 62 L76 66 L86 54" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="52" y="96" width="96" height="8" rx="4" fill="#e2e8f0"/>
                </svg>
                <h3 class="layanan-dir__empty-filter__title">Tidak ada layanan yang cocok</h3>
                <p class="layanan-dir__empty-filter__text">Coba ubah kata kunci pencarian atau pilih kategori lain untuk menampilkan kembali daftar layanan.</p>
                <div class="layanan-dir__empty-filter__actions">
                    <button type="button" class="layanan-dir__btn layanan-dir__btn--primary js-layanan-dir-reset">Reset filter</button>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>


