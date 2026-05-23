<?php

declare(strict_types=1);



/** @var list<array<string, mixed>> $pusatInformasiPosts */



$posts = $pusatInformasiPosts ?? [];

?>

            <?php if (count($posts) > 0): ?>
                <?php $piGridMany = count($posts) > 2 ? ' pi-portal-grid--many' : ''; ?>

                <div class="pi-portal-grid pi-portal-grid--beranda<?php echo $piGridMany; ?>">

                    <?php foreach ($posts as $pi): ?>

                        <?php

                        if (!is_array($pi)) {

                            continue;

                        }

                        $piId = (int) ($pi['id'] ?? 0);

                        $piKat = (string) ($pi['kategori'] ?? 'berita');

                        $isPeng = ($piKat === 'pengumuman');

                        $badgeLabel = $isPeng ? 'Pengumuman' : 'Berita';

                        $isFeatured = !empty((int) ($pi['is_featured'] ?? 0));

                        if ($isFeatured) {

                            $badgeMediaText = $isPeng ? 'PENGUMUMAN UTAMA' : 'BERITA UTAMA';

                            $badgeMediaClass = 'pi-portal-card__badge pi-portal-card__badge--utama';

                            $metaCatLabel = $isPeng ? 'Pengumuman utama' : 'Berita utama';

                        } else {

                            $badgeMediaText = $isPeng ? 'PENGUMUMAN' : 'BERITA';

                            $badgeMediaClass = 'pi-portal-card__badge ' . ($isPeng ? 'pi-portal-card__badge--pengumuman' : 'pi-portal-card__badge--berita');

                            $metaCatLabel = $badgeLabel;

                        }

                        $gfile = trim((string) ($pi['nama_gambar'] ?? ''));

                        $imgUrl = $gfile !== '' ? org_pusat_informasi_upload_web_prefix() . rawurlencode($gfile) : '';

                        $rawT = (string) ($pi['isi_teks'] ?? '');

                        $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags($rawT)));

                        if (strlen($excerpt) > 160) {

                            $excerpt = substr($excerpt, 0, 157) . '…';

                        }

                        $tgl = (string) ($pi['created_at'] ?? '');

                        $tglFmt = $tgl !== '' ? date('d/m/Y', strtotime($tgl)) : '';

                        $tglFmtHover = $tgl !== '' ? date('d M Y', strtotime($tgl)) : '';

                        $piJudul = (string) ($pi['judul'] ?? '');

                        $cardHeadlineClass = $isFeatured ? ' pi-portal-card--headline' : '';

                        ?>

                        <div class="pi-portal-grid__cell">

                            <a href="<?php echo org_href('informasi.php', 'id=' . $piId); ?>" class="pi-portal-card-link w-100" aria-label="<?php echo htmlspecialchars($piJudul, ENT_QUOTES, 'UTF-8'); ?> — baca selengkapnya">

                                <article class="card pi-portal-card h-100 border-0<?php echo $cardHeadlineClass; ?>">

                                    <div class="pi-portal-card__media">

                                        <span class="<?php echo htmlspecialchars($badgeMediaClass, ENT_QUOTES, 'UTF-8'); ?>">

                                            <?php if ($isFeatured): ?>

                                                <i class="fa-solid fa-star" aria-hidden="true"></i>

                                            <?php endif; ?>

                                            <?php echo htmlspecialchars($badgeMediaText, ENT_QUOTES, 'UTF-8'); ?>

                                        </span>

                                        <?php if ($imgUrl !== ''): ?>

                                            <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pi-portal-card__img" alt="" width="640" height="360" loading="lazy">

                                        <?php else: ?>

                                            <div class="pi-portal-card__img pi-portal-card__img--placeholder" aria-hidden="true"><i class="fa-regular fa-image"></i></div>

                                        <?php endif; ?>

                                        <div class="pi-portal-card__hover-panel" aria-hidden="true">

                                            <?php if ($piJudul !== ''): ?>

                                                <h3 class="pi-portal-card__hover-title"><?php echo htmlspecialchars($piJudul, ENT_QUOTES, 'UTF-8'); ?></h3>

                                            <?php endif; ?>

                                            <?php if ($tglFmtHover !== ''): ?>

                                                <p class="pi-portal-card__hover-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($tglFmtHover, ENT_QUOTES, 'UTF-8'); ?></p>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                    <div class="card-body pi-portal-card__body">

                                        <div class="pi-portal-card__meta">

                                            <?php if ($tglFmt !== ''): ?>

                                                <span class="pi-portal-card__meta-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?></span>

                                                <span class="pi-portal-card__meta-sep" aria-hidden="true">·</span>

                                            <?php endif; ?>

                                            <span class="pi-portal-card__meta-cat"><?php echo htmlspecialchars($metaCatLabel, ENT_QUOTES, 'UTF-8'); ?></span>

                                        </div>

                                        <h3 class="pi-portal-card__title"><?php echo htmlspecialchars($piJudul, ENT_QUOTES, 'UTF-8'); ?></h3>

                                        <p class="pi-portal-card__excerpt"><?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?></p>

                                        <div class="pi-portal-card__footer">

                                            <span class="pi-portal-card__read-more">Baca selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>

                                        </div>

                                    </div>

                                </article>

                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="pi-portal-empty card border-0 shadow-sm" role="status">

                    <div class="card-body text-center py-5 px-4 px-md-5">

                        <div class="pi-portal-empty__icon text-primary mb-3" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></div>

                        <h3 class="h5 text-dark mb-2 fw-semibold">Belum ada entri Pusat Informasi</h3>

                        <p class="text-muted small mb-4 mx-auto" style="max-width: 28rem;">Unggah judul, kategori, teks, dan gambar dari Dashboard Admin → Pusat Informasi &amp; Pengumuman.</p>

                        <a class="btn btn-primary btn-sm px-4" href="<?php echo org_href('admin/dashboard.php', '', 'panel-pusat-informasi'); ?>"><i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>Kelola di Dashboard</a>

                    </div>

                </div>

            <?php endif; ?>

