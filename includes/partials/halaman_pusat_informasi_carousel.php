<?php

/** @var list<array<string, mixed>> $pusatCarouselPosts */
/** @var callable(string, string): string|null $pusatHighlightSearch */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'pusat_informasi_ui.php';

$posts = $pusatCarouselPosts ?? [];
$highlight = $pusatHighlightSearch ?? null;
?>
            <?php if (count($posts) > 0): ?>
                <div class="pub-pi-swiper-shell pub-float-panel" data-aos="fade-up" data-aos-duration="700">
                    <div class="swiper pub-pi-swiper" id="halamanPusatSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($posts as $slideIdx => $pi): ?>
                                <?php
                                if (!is_array($pi)) {
                                    continue;
                                }
                                $piId = (int) ($pi['id'] ?? 0);
                                $badge = beranda_pi_badge_meta($pi);
                                $isFeatured = !empty((int) ($pi['is_featured'] ?? 0));
                                $gfile = trim((string) ($pi['nama_gambar'] ?? ''));
                                $imgUrl = $gfile !== '' ? org_pusat_informasi_upload_web_prefix() . rawurlencode($gfile) : '';
                                $rawT = (string) ($pi['isi_teks'] ?? '');
                                $excerpt = trim(preg_replace('/\s+/u', ' ', strip_tags($rawT)));
                                if (strlen($excerpt) > 120) {
                                    $excerpt = function_exists('mb_substr')
                                        ? mb_substr($excerpt, 0, 117, 'UTF-8') . '…'
                                        : substr($excerpt, 0, 117) . '…';
                                }
                                $judul = (string) ($pi['judul'] ?? '');
                                $judulHtml = is_callable($highlight)
                                    ? $highlight($judul, (string) ($pusatSearchQuery ?? ''))
                                    : htmlspecialchars($judul, ENT_QUOTES, 'UTF-8');
                                $excerptHtml = is_callable($highlight)
                                    ? $highlight($excerpt, (string) ($pusatSearchQuery ?? ''))
                                    : htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
                                $tgl = (string) ($pi['created_at'] ?? '');
                                $tglFmt = $tgl !== '' ? date('d M Y', strtotime($tgl)) : '';
                                ?>
                                <div class="swiper-slide" data-aos="fade-up" data-aos-delay="<?php echo min(300, (int) $slideIdx * 60); ?>">
                                    <a href="<?php echo org_href('informasi.php', 'id=' . $piId); ?>" class="pub-pi-card-link" aria-label="<?php echo htmlspecialchars($judul, ENT_QUOTES, 'UTF-8'); ?>">
                                        <article class="pub-pi-card<?php echo $isFeatured ? ' pub-pi-card--featured' : ''; ?>">
                                            <div class="pub-pi-card__media">
                                                <?php if ($imgUrl !== ''): ?>
                                                    <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pub-pi-card__img" alt="" width="640" height="400" loading="lazy">
                                                <?php else: ?>
                                                    <div class="pub-pi-card__img pub-pi-card__img--placeholder" aria-hidden="true"><i class="fa-regular fa-image"></i></div>
                                                <?php endif; ?>
                                                <span class="pub-pi-card__media-gradient" aria-hidden="true"></span>
                                                <span class="pub-pi-card__badge <?php echo htmlspecialchars($badge['class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($badge['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <div class="pub-pi-card__overlay">
                                                    <h3 class="pub-pi-card__title-overlay"><?php echo $judulHtml; ?></h3>
                                                    <span class="pub-pi-card__cta">Baca selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                                                </div>
                                            </div>
                                            <div class="pub-pi-card__glass">
                                                <p class="pub-pi-card__meta-cat"><?php echo htmlspecialchars(beranda_pi_kategori_label($pi), ENT_QUOTES, 'UTF-8'); ?></p>
                                                <?php if ($tglFmt !== ''): ?>
                                                    <p class="pub-pi-card__meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?></p>
                                                <?php endif; ?>
                                                <p class="pub-pi-card__excerpt"><?php echo $excerptHtml; ?></p>
                                            </div>
                                        </article>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination pub-pi-swiper__pagination"></div>
                        <button type="button" class="swiper-button-prev pub-pi-swiper__nav" aria-label="Sebelumnya"></button>
                        <button type="button" class="swiper-button-next pub-pi-swiper__nav" aria-label="Berikutnya"></button>
                    </div>
                </div>
            <?php else: ?>
                <div class="card section-card pub-float-panel" data-aos="fade-up" role="status">
                    <div class="card-body p-4">
                        <p class="text-muted mb-0"><?php echo ($pusatSearchQuery ?? '') !== '' ? 'Tidak ada entri yang cocok dengan pencarian.' : 'Belum ada publikasi di Pusat Informasi.'; ?></p>
                    </div>
                </div>
            <?php endif; ?>
