<?php

/** @var array<string, mixed> $layananItem */
/** @var array{slug: string, card_mod: string} $layananTheme */
/** @var string $layananFancyboxGroup */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layanan_ui.php';

$item = $layananItem ?? [];
$theme = $layananTheme ?? layanan_category_theme('Kelembagaan');
$fbGroup = $layananFancyboxGroup ?? 'layanan-media';

$cardMod = (string) ($theme['card_mod'] ?? 'layanan-premium-card--kelembagaan');
$status = layanan_status_badge($item);
$nama = trim((string) ($item['nama'] ?? ''));
$desk = trim((string) ($item['deskripsi'] ?? ''));
$pinShow = trim((string) ($item['pin_label'] ?? ''));
$pinPos = (string) ($item['pin_position'] ?? '');
if ($pinPos !== 'before' && $pinPos !== 'after') {
    $pinPos = '';
}
if ($pinShow === '') {
    $pinPos = '';
}
$pinEsc = $pinShow !== '' ? htmlspecialchars($pinShow, ENT_QUOTES, 'UTF-8') : '';

$itemImage = trim((string) ($item['media_image'] ?? ''));
$itemDocs = layanan_collect_documents($item);
$itemLink = trim((string) ($item['link'] ?? ''));
$itemImageHref = $itemImage !== '' ? htmlspecialchars($itemImage, ENT_QUOTES, 'UTF-8') : '';
$itemLinkNorm = org_layanan_integrasi_url_normalize($itemLink);
$itemLinkHref = $itemLinkNorm !== '' ? htmlspecialchars($itemLinkNorm, ENT_QUOTES, 'UTF-8') : '';
$caption = $nama !== '' ? htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') : 'Lampiran layanan';

$hasMedia = $itemImage !== '' || $itemDocs !== [] || $itemLinkHref !== '';

$layananDirectoryMode = !empty($layananDirectoryMode);
$catSlugForChip = (string) ($theme['slug'] ?? 'kelembagaan');
$catLabelForChip = trim((string) ($layananCategoryLabel ?? ''));
$statusSlug = $itemLinkNorm !== '' ? 'digital' : 'manual';
$searchBlob = '';
if ($layananDirectoryMode) {
    $rawSearch = trim($nama . ' ' . $desk . ' ' . $pinShow . ' ' . $catLabelForChip);
    $rawSearch = preg_replace('/\s+/u', ' ', $rawSearch);
    $searchBlob = function_exists('mb_strtolower') ? mb_strtolower($rawSearch, 'UTF-8') : strtolower($rawSearch);
}
$articleExtraClass = $layananDirectoryMode ? ' layanan-dir-card' : '';
$catChipMod = 'layanan-dir-card__cat--' . preg_replace('/[^a-z0-9-]+/i', '', $catSlugForChip);
$layananCardAosDelay = isset($layananCardAosDelay) ? (int) $layananCardAosDelay : 0;
if ($layananDirectoryMode) {
    echo '<div class="layanan-dir__cell" role="listitem" data-layanan-cat="' . htmlspecialchars($catSlugForChip, ENT_QUOTES, 'UTF-8') . '" data-layanan-status="' . htmlspecialchars($statusSlug, ENT_QUOTES, 'UTF-8') . '" data-layanan-q="' . htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') . '" data-aos="fade-up" data-aos-delay="' . (int) $layananCardAosDelay . '" data-aos-duration="650">';
}
?>
                            <article class="layanan-premium-card<?php echo htmlspecialchars($articleExtraClass, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($cardMod, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($layananDirectoryMode && $catLabelForChip !== ''): ?>
                                    <span class="layanan-dir-card__cat <?php echo htmlspecialchars($catChipMod, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($catLabelForChip, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <span class="<?php echo htmlspecialchars($status['class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <div class="layanan-premium-card__headline">
                                    <?php if ($pinPos === 'before' && $pinEsc !== ''): ?>
                                        <span class="layanan-premium-card__pin"><?php echo $pinEsc; ?></span>
                                    <?php endif; ?>
                                    <h3 class="layanan-premium-card__title"><?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <?php if ($pinPos === 'after' && $pinEsc !== ''): ?>
                                        <span class="layanan-premium-card__pin"><?php echo $pinEsc; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($desk !== ''): ?>
                                    <p class="layanan-premium-card__desc"><?php echo nl2br(htmlspecialchars($desk, ENT_QUOTES, 'UTF-8')); ?></p>
                                <?php endif; ?>
                                <?php if ($hasMedia): ?>
                                <div class="layanan-premium-card__lower">
                                    <div class="layanan-premium-card__media">
                                        <?php if ($itemImage !== ''): ?>
                                            <a
                                                href="<?php echo $itemImageHref; ?>"
                                                class="layanan-premium-card__media-link"
                                                data-fancybox="<?php echo htmlspecialchars($fbGroup, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-caption="<?php echo $caption; ?>"
                                                aria-label="Perbesar gambar layanan"
                                            >
                                                <img
                                                    src="<?php echo $itemImageHref; ?>"
                                                    alt="<?php echo $caption; ?>"
                                                    class="layanan-premium-card__img"
                                                    width="640"
                                                    height="360"
                                                    loading="lazy"
                                                >
                                            </a>
                                        <?php else: ?>
                                            <div class="layanan-premium-card__media-placeholder" aria-hidden="true">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="layanan-premium-card__fabs">
                                            <?php if ($itemImage !== ''): ?>
                                                <a
                                                    href="<?php echo $itemImageHref; ?>"
                                                    class="layanan-premium-fab layanan-premium-fab--image"
                                                    data-fancybox="<?php echo htmlspecialchars($fbGroup, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-caption="<?php echo $caption; ?>"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Lihat gambar"
                                                    aria-label="Lihat gambar"
                                                ><i class="fa-solid fa-expand" aria-hidden="true"></i></a>
                                            <?php endif; ?>
                                            <?php foreach ($itemDocs as $docIdx => $docHrefRaw): ?>
                                                <?php $docHref = htmlspecialchars((string) $docHrefRaw, ENT_QUOTES, 'UTF-8'); ?>
                                                <a
                                                    href="<?php echo $docHref; ?>"
                                                    class="layanan-premium-fab layanan-premium-fab--doc"
                                                    target="_blank"
                                                    rel="noopener"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Unduh dokumen <?php echo (int) ($docIdx + 1); ?>"
                                                    aria-label="Unduh dokumen <?php echo (int) ($docIdx + 1); ?>"
                                                ><i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i></a>
                                            <?php endforeach; ?>
                                            <?php if ($itemLinkHref !== ''): ?>
                                                <a
                                                    href="<?php echo $itemLinkHref; ?>"
                                                    class="layanan-premium-fab layanan-premium-fab--link layanan-premium-fab--primary"
                                                    target="_blank"
                                                    rel="noopener"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Buka layanan digital"
                                                    aria-label="Buka layanan digital"
                                                ><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </article>
<?php if ($layananDirectoryMode) {
    echo '</div>';
} ?>
