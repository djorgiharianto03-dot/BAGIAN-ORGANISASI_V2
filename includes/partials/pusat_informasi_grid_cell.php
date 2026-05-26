<?php

/** @var array<string, mixed> $pi */
/** @var callable|null $highlight */
/** @var int $cardIdx */

require __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_card_data.php';
if (!is_array($pi) || $piId < 1) {
    return;
}
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_share_helpers.php';

$aosDelay = min(320, (int) ($cardIdx ?? 0) * 48);
?>
                            <div class="np-grid__cell" data-aos="fade-up" data-aos-delay="<?php echo $aosDelay; ?>">
                                <?php echo org_share_button_html($pi, 'card'); ?>
                                <a href="<?php echo org_href('informasi.php', 'id=' . $piId); ?>" class="np-card__link" aria-label="<?php echo htmlspecialchars($judul, ENT_QUOTES, 'UTF-8'); ?>">
                                    <article class="np-card<?php echo $isFeatured ? ' np-card--featured' : ''; ?>">
                                        <div class="np-card__media">
                                            <?php if ($imgUrl !== ''): ?>
                                                <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>" class="np-card__img" alt="" width="640" height="427" loading="lazy">
                                            <?php else: ?>
                                                <div class="np-card__img np-card__img--placeholder" aria-hidden="true"><i class="fa-regular fa-image"></i></div>
                                            <?php endif; ?>
                                            <span class="np-card__badge <?php echo htmlspecialchars($badge['class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($badge['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="np-card__body">
                                            <div class="np-card__meta">
                                                <span class="np-card__cat <?php echo htmlspecialchars($katClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($katLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php if ($tglFmt !== ''): ?>
                                                    <time class="np-card__date" datetime="<?php echo htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                        <?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?>
                                                    </time>
                                                <?php endif; ?>
                                            </div>
                                            <h3 class="np-card__title"><?php echo $judulHtml; ?></h3>
                                            <?php if ($excerpt !== ''): ?>
                                                <p class="np-card__excerpt"><?php echo $excerptHtml; ?></p>
                                            <?php endif; ?>
                                            <span class="np-card__more">Selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                                        </div>
                                    </article>
                                </a>
                            </div>
