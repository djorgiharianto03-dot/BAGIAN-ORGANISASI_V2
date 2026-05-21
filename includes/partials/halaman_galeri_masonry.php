<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $galeriMasonryItems */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'galeri_portal_helpers.php';

$items = $galeriMasonryItems ?? [];
$itemCount = count($items);
$filterTabs = org_galeri_portal_year_categories($items);
$hasLainnya = false;
foreach ($items as $row) {
    if (!is_array($row)) {
        continue;
    }
    if (org_galeri_portal_item_year_slug($row) === 'lainnya') {
        $hasLainnya = true;
        break;
    }
}
if ($hasLainnya && !isset($filterTabs['lainnya'])) {
    $filterTabs['lainnya'] = 'Lainnya';
}
?>
            <?php if ($itemCount > 0): ?>
                <div class="org-gallery gl-portal">
                    <div class="org-gallery__toolbar gl-toolbar">
                        <div class="org-gallery__search gl-toolbar__search">
                            <label class="org-sr-only visually-hidden" for="glSearchInput">Cari foto galeri</label>
                            <span class="org-gallery__search-icon gl-toolbar__search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input
                                type="search"
                                class="org-gallery__search-input gl-toolbar__input"
                                id="glSearchInput"
                                placeholder="Cari judul kegiatan…"
                                autocomplete="off"
                            >
                            <button type="button" class="gl-toolbar__clear" id="glSearchClear" hidden aria-label="Hapus pencarian">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>
                        <?php if (count($filterTabs) > 1): ?>
                        <div class="org-gallery__filters gl-filters" role="tablist" aria-label="Filter tahun kegiatan">
                            <?php foreach ($filterTabs as $slug => $label): ?>
                                <?php
                                $filterSlug = (string) $slug;
                                $filterLabel = (string) $label;
                                ?>
                                <button
                                    type="button"
                                    class="org-gallery__filter gl-filters__tab<?php echo $filterSlug === 'all' ? ' is-active' : ''; ?>"
                                    role="tab"
                                    aria-selected="<?php echo $filterSlug === 'all' ? 'true' : 'false'; ?>"
                                    data-gl-filter="<?php echo htmlspecialchars($filterSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                ><?php echo htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8'); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <p class="org-gallery__meta gl-toolbar__meta" id="glResultMeta" aria-live="polite">
                            <span id="glResultCount"><?php echo (int) $itemCount; ?></span> foto
                        </p>
                    </div>

                    <div class="org-gallery__empty gl-empty gl-empty--hidden" id="glEmptyFilter" role="status" hidden>
                        <div class="gl-empty__icon" aria-hidden="true"><i class="fa-regular fa-images"></i></div>
                        <p class="gl-empty__text mb-0">Tidak ada foto yang cocok dengan filter atau pencarian.</p>
                    </div>

                    <div class="org-gallery__grid gl-masonry" id="halamanGaleriGrid">
                        <?php foreach ($items as $idx => $gItem): ?>
                            <?php
                            if (!is_array($gItem)) {
                                continue;
                            }
                            $gJudul = (string) ($gItem['judul'] ?? '');
                            $gFile = basename((string) ($gItem['nama_file'] ?? ''));
                            $gTglRaw = (string) ($gItem['tgl_upload'] ?? '');
                            $gTglFmt = $gTglRaw !== '' ? date('d M Y', strtotime($gTglRaw)) : '';
                            $gImgSrc = org_galeri_kegiatan_image_url($gFile);
                            $gCaption = trim($gJudul . ($gTglFmt !== '' ? "\n" . $gTglFmt : ''));
                            $yearSlug = org_galeri_portal_item_year_slug($gItem);
                            $sizeClass = org_galeri_portal_item_size_class((int) $idx);
                            $searchBlob = strtolower($gJudul . ' ' . $gTglFmt . ' ' . $yearSlug);
                            ?>
                            <a
                                href="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                class="gl-item<?php echo htmlspecialchars($sizeClass, ENT_QUOTES, 'UTF-8'); ?>"
                                data-fancybox="galeri-kegiatan"
                                data-caption="<?php echo htmlspecialchars($gCaption, ENT_QUOTES, 'UTF-8'); ?>"
                                data-gl-year="<?php echo htmlspecialchars($yearSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                data-gl-search="<?php echo htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <figure class="gl-item__frame">
                                    <img
                                        class="gl-item__img"
                                        src="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($gJudul !== '' ? $gJudul : 'Foto kegiatan', ENT_QUOTES, 'UTF-8'); ?>"
                                        width="640"
                                        height="480"
                                        loading="lazy"
                                    >
                                    <figcaption class="gl-item__overlay">
                                        <span class="gl-item__overlay-icon" aria-hidden="true"><i class="fa-solid fa-expand"></i></span>
                                        <?php if ($gJudul !== ''): ?>
                                            <span class="gl-item__title"><?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($gTglFmt !== ''): ?>
                                            <time class="gl-item__date" datetime="<?php echo htmlspecialchars($gTglRaw, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                <?php echo htmlspecialchars($gTglFmt, ENT_QUOTES, 'UTF-8'); ?>
                                            </time>
                                        <?php endif; ?>
                                    </figcaption>
                                </figure>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="gl-empty gl-empty--page" role="status">
                    <div class="gl-empty__icon" aria-hidden="true"><i class="fa-regular fa-images"></i></div>
                    <p class="gl-empty__text mb-2">Belum ada foto kegiatan yang dipublikasikan.</p>
                    <p class="gl-empty__hint mb-0 text-muted small">Unggah foto melalui <strong>Dashboard Admin → tab Galeri</strong>. Berkas disimpan di <code>assets/img/galeri/</code>.</p>
                </div>
            <?php endif; ?>
