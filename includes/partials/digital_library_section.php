<?php
declare(strict_types=1);

/** @var list<string> $libraryDocumentFiles */
$libRowsRaw = isset($filteredLibraryDocuments) && is_array($filteredLibraryDocuments) ? $filteredLibraryDocuments : $libraryDocumentFiles;
$docMap = $libraryDocumentStatsMap ?? [];
$libRows = array_values(array_filter($libRowsRaw, static function ($f) use ($docMap): bool {
    $fn = (string) $f;
    $stat = $docMap[$fn] ?? null;
    $kat = $stat !== null ? (string) ($stat['kategori'] ?? '') : org_dokumen_kategori_from_filename($fn);

    return !org_dokumen_is_visual_kategori($kat);
}));
$uploadFsBase = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perpustakaan_digital';
if (!function_exists('org_dokumen_download_url')) {
    require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';
}
$sectionIdAttr = '';
if (isset($digitalLibrarySectionId) && is_string($digitalLibrarySectionId) && $digitalLibrarySectionId !== '') {
    $sectionIdAttr = ' id="' . htmlspecialchars($digitalLibrarySectionId, ENT_QUOTES, 'UTF-8') . '"';
}
$heroTitle = 'PERPUSTAKAAN DIGITAL';
$heroSubtitle = '';
if (isset($digitalLibraryHeroSubtitle) && is_string($digitalLibraryHeroSubtitle)) {
    $heroSubtitle = trim($digitalLibraryHeroSubtitle);
}
$hideIntroEyebrow = !empty($digitalLibraryHideIntroEyebrow);
$hideIntroHeader = !empty($digitalLibraryHideIntroHeader);
$showFullPageLink = !empty($digitalLibraryShowFullPageLink);
$sectionClasses = 'section-spacing digital-library digital-library--intl digital-library--doc-center';
if (isset($digitalLibrarySectionExtraClass) && is_string($digitalLibrarySectionExtraClass) && $digitalLibrarySectionExtraClass !== '') {
    $sectionClasses .= ' ' . trim($digitalLibrarySectionExtraClass);
}
$kategoriCounts = [
    'semua' => count($libRows),
    'kelembagaan' => 0,
    'pelayanan-publik' => 0,
    'sakip-rb' => 0,
    'regulasi' => 0,
    'lainnya' => 0,
];
foreach ($libRows as $countFile) {
    $countStatRow = $docMap[$countFile] ?? null;
    $countKategori = $countStatRow !== null ? (string) ($countStatRow['kategori'] ?? 'Kelembagaan') : org_dokumen_kategori_from_filename($countFile);
    $countKategori = org_dokumen_normalize_tim_kategori($countKategori);
    $countSlug = org_dokumen_tim_kategori_slug($countKategori);
    if (isset($kategoriCounts[$countSlug])) {
        $kategoriCounts[$countSlug]++;
    } else {
        $kategoriCounts['lainnya']++;
    }
}
?>
        <section class="<?php echo htmlspecialchars($sectionClasses, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $sectionIdAttr; ?> aria-labelledby="digital-library-heading">
            <?php if (!$hideIntroHeader): ?>
            <header class="doc-center__intro">
                <?php if (!$hideIntroEyebrow): ?>
                    <p class="doc-center__eyebrow">Perpustakaan digital</p>
                <?php endif; ?>
                <h2 id="digital-library-heading" class="doc-center__title"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php if ($heroSubtitle !== ''): ?>
                    <p class="doc-center__subtitle"><?php echo htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </header>
            <?php else: ?>
            <h2 id="digital-library-heading" class="visually-hidden"><?php echo htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php endif; ?>

            <div class="doc-center__sticky">
                <div class="doc-center__search-block">
                    <div id="library-document-search" class="doc-center-search library-doc-search-header library-doc-search-header--prominent">
                        <label class="visually-hidden" for="libraryDocumentSearch">Cari dokumen</label>
                        <div class="library-doc-search-header__combo doc-center-search__combo" role="search">
                            <div class="library-doc-search-header__field doc-center-search__field">
                                <span class="library-doc-search-header__icon doc-center-search__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                </span>
                                <input
                                    type="search"
                                    id="libraryDocumentSearch"
                                    class="library-doc-search-header__input doc-center-search__input"
                                    placeholder="Cari judul, jenis dokumen, atau kata kunci (mis. Perbup, Perda, SAKIP, Anjab…)"
                                    autocomplete="off"
                                    value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                                    aria-controls="libraryDocTableBody"
                                >
                                <button type="button" class="library-doc-search-header__clear doc-center-search__clear d-none" id="libraryDocumentSearchClear" aria-label="Hapus pencarian">×</button>
                            </div>
                            <button type="button" class="library-doc-search-header__submit doc-center-search__submit" id="libraryDocumentSearchSubmit">Cari</button>
                        </div>
                    </div>
                </div>

                <div class="library-doc-category-filter doc-center__pills" role="tablist" aria-label="Filter kategori dokumen">
                    <button type="button" class="library-doc-category-filter__btn is-active" data-lib-cat="semua" role="tab" aria-selected="true">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <path d="m12 3 9 4.5-9 4.5L3 7.5 12 3Z"></path>
                                <path d="m3 12.5 9 4.5 9-4.5"></path>
                                <path d="m3 17 9 4.5 9-4.5"></path>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">Semua</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['semua'] ?? 0); ?></span>
                        </span>
                    </button>
                    <button type="button" class="library-doc-category-filter__btn" data-lib-cat="kelembagaan" role="tab" aria-selected="false">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                                <path d="M8 7h.01M12 7h.01M16 7h.01M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01"></path>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">Kelembagaan</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['kelembagaan'] ?? 0); ?></span>
                        </span>
                    </button>
                    <button type="button" class="library-doc-category-filter__btn" data-lib-cat="pelayanan-publik" role="tab" aria-selected="false">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <path d="m8.5 12 2.2 2.2a2 2 0 0 0 2.8 0l2.8-2.8a2 2 0 0 1 2.8 0L22 14.1"></path>
                                <path d="M2 10.3 5.1 7a2 2 0 0 1 2.8 0l3.1 3.1"></path>
                                <path d="m5 20 4.5-4.5"></path>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">Pelayanan</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['pelayanan-publik'] ?? 0); ?></span>
                        </span>
                    </button>
                    <button type="button" class="library-doc-category-filter__btn" data-lib-cat="sakip-rb" role="tab" aria-selected="false">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <path d="M3 3v18h18"></path>
                                <path d="m7 14 4-4 3 3 5-5"></path>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">SAKIP &amp; RB</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['sakip-rb'] ?? 0); ?></span>
                        </span>
                    </button>
                    <button type="button" class="library-doc-category-filter__btn" data-lib-cat="regulasi" role="tab" aria-selected="false">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <path d="M8 3h8l5 5v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"></path>
                                <path d="M16 3v5h5"></path>
                                <path d="M9 13h6M9 17h6"></path>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">Regulasi</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['regulasi'] ?? 0); ?></span>
                        </span>
                    </button>
                    <button type="button" class="library-doc-category-filter__btn" data-lib-cat="lainnya" role="tab" aria-selected="false">
                        <span class="library-doc-category-filter__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                <circle cx="5" cy="12" r="1.5"></circle>
                                <circle cx="12" cy="12" r="1.5"></circle>
                                <circle cx="19" cy="12" r="1.5"></circle>
                            </svg>
                        </span>
                        <span class="library-doc-category-filter__text-wrap">
                            <span class="library-doc-category-filter__label">Lainnya</span>
                            <span class="library-doc-category-filter__count"><?php echo (int) ($kategoriCounts['lainnya'] ?? 0); ?></span>
                        </span>
                    </button>
                </div>
            </div>

            <div class="doc-center__panel">
                <div class="doc-center__panel-head">
                    <div>
                        <h3 class="doc-center__panel-title">Arsip dokumen</h3>
                        <p class="doc-center__panel-lead">Pratinjau aman di tab baru; unduhan mencatat statistik akses publik.</p>
                    </div>
                    <span class="doc-center__panel-badge"><?php echo (int) count($libRows); ?> berkas</span>
                </div>

                <?php if (count($libRows) > 0): ?>
                    <div class="doc-center-table-wrap digital-library__table-wrap table-responsive">
                        <table class="table doc-center-table digital-library__table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="doc-center-table__th doc-center-table__th--num text-center">#</th>
                                    <th scope="col" class="doc-center-table__th">Dokumen</th>
                                    <th scope="col" class="doc-center-table__th doc-center-table__th--cat">Kategori</th>
                                    <th scope="col" class="doc-center-table__th doc-center-table__th--size">Ukuran</th>
                                    <th scope="col" class="doc-center-table__th doc-center-table__th--actions text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="libraryDocTableBody">
                                <?php
                                $rowNo = 0;
                                foreach ($libRows as $uploadedFile):
                                    $rowNo++;
                                    $fileOnDisk = org_dokumen_resolve_realpath($uploadedFile) !== null;
                                    $nama_file = $storedDocumentBasename($uploadedFile);
                                    $nama_tampilan = str_replace('_', ' ', $nama_file);
                                    $statRow = $docMap[$uploadedFile] ?? null;
                                    $kategori = $statRow !== null ? (string) ($statRow['kategori'] ?? 'Kelembagaan') : org_dokumen_kategori_from_filename($uploadedFile);
                                    $kategori = org_dokumen_normalize_tim_kategori($kategori);
                                    $kategoriSlug = org_dokumen_tim_kategori_slug($kategori);
                                    $kategoriBadgeClass = 'digital-library__cat--kelembagaan';
                                    if ($kategoriSlug === 'pelayanan-publik') {
                                        $kategoriBadgeClass = 'digital-library__cat--pelayanan';
                                    } elseif ($kategoriSlug === 'sakip-rb') {
                                        $kategoriBadgeClass = 'digital-library__cat--sakip';
                                    } elseif ($kategoriSlug === 'regulasi') {
                                        $kategoriBadgeClass = 'digital-library__cat--kelembagaan';
                                    }
                                    $kategoriLabelDisplay = $kategori;
                                    if ($kategoriSlug === 'kelembagaan') {
                                        $kategoriLabelDisplay = 'Kelembagaan dan Anjab';
                                    } elseif ($kategoriSlug === 'pelayanan-publik') {
                                        $kategoriLabelDisplay = 'Pelayanan Publik dan Tata Laksana';
                                    } elseif ($kategoriSlug === 'sakip-rb') {
                                        $kategoriLabelDisplay = 'Kinerja dan RB';
                                    } elseif ($kategoriSlug === 'regulasi') {
                                        $kategoriLabelDisplay = 'Regulasi';
                                    }
                                    $judulDb = $statRow !== null ? trim((string) ($statRow['judul'] ?? '')) : '';
                                    $deskDb = $statRow !== null ? trim((string) ($statRow['deskripsi'] ?? '')) : '';
                                    $judulTampilan = $judulDb !== '' ? $judulDb : $nama_tampilan;
                                    $fsPath = org_dokumen_resolve_realpath($uploadedFile);
                                    $bytes = $fsPath !== null ? (int) filesize($fsPath) : 0;
                                    $unduhCount = $statRow !== null ? (int) ($statRow['jumlah_unduh'] ?? 0) : 0;
                                    [$faIcon, $faColor] = org_dokumen_icon_for_extension($uploadedFile);
                                    $viewHref = htmlspecialchars(org_dokumen_view_url($uploadedFile), ENT_QUOTES, 'UTF-8');
                                    $dlHref = htmlspecialchars(org_dokumen_download_url($uploadedFile), ENT_QUOTES, 'UTF-8');
                                    $rowHay = [
                                        'nama_file' => $uploadedFile,
                                        'kategori' => $kategori,
                                        'judul' => $judulDb,
                                        'deskripsi' => $deskDb,
                                    ];
                                    $filterHaystack = org_dokumen_library_search_haystack($uploadedFile, $rowHay, $storedDocumentBasename, $displayUploadFilename);
                                    $kategoriFilterSlug = $kategoriSlug;
                                    if (!in_array($kategoriFilterSlug, ['kelembagaan', 'pelayanan-publik', 'sakip-rb', 'regulasi'], true)) {
                                        $kategoriFilterSlug = 'lainnya';
                                    }
                                    $deskShort = $deskDb;
                                    if ($deskShort !== '') {
                                        if (function_exists('mb_strlen') && mb_strlen($deskShort, 'UTF-8') > 160) {
                                            $deskShort = mb_substr($deskShort, 0, 157, 'UTF-8') . '…';
                                        } elseif (strlen($deskShort) > 160) {
                                            $deskShort = substr($deskShort, 0, 157) . '…';
                                        }
                                    }
                                    ?>
                                    <tr
                                        class="js-lib-doc-row doc-center-table__row"
                                        data-lib-filter="<?php echo htmlspecialchars($filterHaystack, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-doc-title-plain="<?php echo htmlspecialchars($judulTampilan, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-doc-cat-plain="<?php echo htmlspecialchars($kategoriLabelDisplay, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-doc-teamcat="<?php echo htmlspecialchars($kategoriFilterSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <td class="doc-center-table__td doc-center-table__td--num text-center text-muted small js-lib-doc-no" data-label="No."><?php echo (int) $rowNo; ?></td>
                                        <td class="doc-center-table__td doc-center-table__td--doc" data-label="Dokumen">
                                            <div class="doc-center-doc">
                                                <span class="doc-center-file-badge <?php echo htmlspecialchars($faColor, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true">
                                                    <i class="fa-solid <?php echo htmlspecialchars($faIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                                                </span>
                                                <div class="doc-center-doc__body">
                                                    <span class="digital-library__doc-title doc-center-doc__title" title="<?php echo htmlspecialchars($uploadedFile, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <span class="js-lib-doc-title-text"><?php echo htmlspecialchars($judulTampilan, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </span>
                                                    <?php if ($deskShort !== ''): ?>
                                                        <p class="doc-center-doc__desc"><?php echo htmlspecialchars($deskShort, ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php endif; ?>
                                                    <p class="doc-center-doc__file">
                                                        <span class="doc-center-doc__file-label">Berkas</span>
                                                        <code class="doc-center-doc__file-name"><?php echo htmlspecialchars($uploadedFile, ENT_QUOTES, 'UTF-8'); ?></code>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="doc-center-table__td doc-center-table__td--cat" data-label="Kategori">
                                            <span class="digital-library__cat <?php echo htmlspecialchars($kategoriBadgeClass, ENT_QUOTES, 'UTF-8'); ?> doc-center-cat-pill">
                                                <span class="js-lib-doc-cat-text"><?php echo htmlspecialchars($kategoriLabelDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </span>
                                        </td>
                                        <td class="doc-center-table__td doc-center-table__td--size" data-label="Ukuran">
                                            <span class="digital-library__size doc-center-meta">
                                                <span class="doc-center-meta__val"><?php echo htmlspecialchars(org_format_file_size($bytes), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="doc-center-meta__sub"><?php echo (int) $unduhCount; ?> unduhan</span>
                                            </span>
                                        </td>
                                        <td class="doc-center-table__td doc-center-table__td--actions text-end" data-label="Aksi">
                                            <div class="doc-center-actions">
                                                <?php if ($fileOnDisk): ?>
                                                    <a class="doc-center-btn doc-center-btn--preview" href="<?php echo $viewHref; ?>" target="_blank" rel="noopener">
                                                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                                        <span>Pratinjau</span>
                                                    </a>
                                                    <a class="doc-center-btn doc-center-btn--download js-digital-lib-download" href="<?php echo $dlHref; ?>">
                                                        <i class="fa-solid fa-arrow-down-to-line" aria-hidden="true"></i>
                                                        <span>Unduh</span>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge text-bg-warning">Berkas tidak ditemukan</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tbody id="libraryDocEmptyFilter" class="d-none">
                                <tr>
                                    <td colspan="5" class="library-doc-empty-filter doc-center-empty-filter border-0 py-5">
                                        <p class="text-muted mb-0 text-center">Maaf, dokumen dengan kata kunci tersebut tidak ditemukan di Perpustakaan Digital kami.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav class="library-doc-pagination-wrap doc-center-pagination mt-4" aria-label="Navigasi halaman dokumen">
                        <ul class="library-doc-pagination mb-0" id="libraryDocPagination"></ul>
                    </nav>
                <?php else: ?>
                    <div class="doc-center-empty-page" role="status">
                        <svg class="doc-center-empty-page__svg" viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <rect x="36" y="28" width="128" height="94" rx="12" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/>
                            <path d="M52 102h96" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>
                            <path d="M52 88h72" stroke="#e2e8f0" stroke-width="2" stroke-linecap="round"/>
                            <rect x="56" y="44" width="40" height="28" rx="4" fill="#e0e7ff"/>
                            <path d="M64 56h24M64 62h16" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <h3 class="doc-center-empty-page__title">Belum ada dokumen</h3>
                        <p class="doc-center-empty-page__text">Unggah berkas resmi melalui dashboard admin agar masyarakat dapat mengaksesnya dari halaman ini.</p>
                        <?php if (!empty($isAdmin)): ?>
                            <a class="doc-center-btn doc-center-btn--download doc-center-empty-page__cta" href="admin/dashboard.php#panel-unggah-dokumen">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span>Unggah dokumen</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($showFullPageLink): ?>
                    <p class="text-muted small mb-0 mt-3"><a href="dokumen.php">Halaman Dokumen lengkap</a> (unggah &amp; kelola untuk admin).</p>
                <?php endif; ?>
            </div>
        </section>
