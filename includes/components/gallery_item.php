<?php

/**
 * Single gallery masonry item.
 *
 * @var string $gImgSrc
 * @var string $gJudul
 * @var string $gTglFmt
 * @var string $gYearSlug
 * @var int $idx
 */

org_tailwind_bootstrap();

$gImgSrc = (string) ($gImgSrc ?? '');
$gJudul = (string) ($gJudul ?? '');
$gTglFmt = (string) ($gTglFmt ?? '');
$gYearSlug = (string) ($gYearSlug ?? '');
$idx = (int) ($idx ?? 0);
?>
<article
    class="org-gallery__item gl-masonry__item"
    data-gl-year="<?php echo htmlspecialchars($gYearSlug, ENT_QUOTES, 'UTF-8'); ?>"
    data-gl-title="<?php echo htmlspecialchars(mb_strtolower($gJudul), ENT_QUOTES, 'UTF-8'); ?>"
>
    <a href="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>" class="org-gallery__link gl-card" data-fancybox="galeri" data-caption="<?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?>">
        <img src="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?>" class="org-gallery__img gl-card__img" loading="<?php echo $idx < 4 ? 'eager' : 'lazy'; ?>" width="640" height="480">
        <div class="org-gallery__caption gl-card__body">
            <p class="mb-0 font-semibold text-org-text"><?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if ($gTglFmt !== ''): ?>
                <p class="org-text-muted mb-0 mt-1"><?php echo htmlspecialchars($gTglFmt, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
