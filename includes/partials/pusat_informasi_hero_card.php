<?php

/** @var array<string, mixed> $pi */
/** @var callable|null $highlight */

require __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_card_data.php';
if (!is_array($pi) || $piId < 1) {
    return;
}
?>
<section class="np-hero" aria-labelledby="np-hero-title">
    <div class="np-hero__label">
        <span class="np-hero__label-dot" aria-hidden="true"></span>
        <span id="np-hero-title"><?php echo $isFeatured ? 'Sorotan Utama' : 'Terbaru'; ?></span>
    </div>
    <a href="<?php echo org_href('informasi.php', 'id=' . $piId); ?>" class="np-hero__link" aria-label="<?php echo htmlspecialchars($judul, ENT_QUOTES, 'UTF-8'); ?>">
        <article class="np-hero__card<?php echo $isFeatured ? ' np-hero__card--featured' : ''; ?>">
            <div class="np-hero__media">
                <?php if ($imgUrl !== ''): ?>
                    <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>" class="np-hero__img" alt="" width="960" height="540" loading="eager" fetchpriority="high">
                <?php else: ?>
                    <div class="np-hero__img np-hero__img--placeholder" aria-hidden="true"><i class="fa-regular fa-image"></i></div>
                <?php endif; ?>
                <span class="np-hero__badge np-card__badge <?php echo htmlspecialchars($badge['class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($badge['text'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="np-hero__body">
                <div class="np-hero__meta">
                    <span class="np-card__cat <?php echo htmlspecialchars($katClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($katLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($tglFmt !== ''): ?>
                        <time class="np-card__date" datetime="<?php echo htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?>
                        </time>
                    <?php endif; ?>
                </div>
                <h2 class="np-hero__title"><?php echo $judulHtml; ?></h2>
                <?php if ($excerpt !== ''): ?>
                    <p class="np-hero__excerpt"><?php echo $excerptHtml; ?></p>
                <?php endif; ?>
                <span class="np-hero__cta">Baca selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </div>
        </article>
    </a>
</section>
