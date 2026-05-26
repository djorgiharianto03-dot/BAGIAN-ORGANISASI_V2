<?php

declare(strict_types=1);

/**
 * Beranda — Pusat Informasi & Pengumuman
 * -----------------------------------------------------------------------------
 * Tampilan: Horizontal News Card modern (portal berita pemerintahan).
 *   - Layout 2 kolom di desktop, vertical di mobile
 *   - Gambar kiri, konten kanan
 *   - Floating date badge (day + month) di atas gambar
 *   - Category badge (Berita / Pengumuman / Utama) di sudut kanan-atas gambar
 *   - Hover ringan translateY(-3px), shadow halus
 *   - Tipografi var(--font-sans) global (Plus Jakarta Sans)
 *
 * Data source: $pusatInformasiPosts (dipersiapkan oleh bootstrap beranda).
 */

/** @var list<array<string, mixed>> $pusatInformasiPosts */

$posts = $pusatInformasiPosts ?? [];

$bulanMap = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

?>

<?php if (count($posts) > 0): ?>
    <div class="org-news-grid" role="list">
        <?php foreach ($posts as $pi): ?>
            <?php
            if (!is_array($pi)) {
                continue;
            }

            $piId       = (int) ($pi['id'] ?? 0);
            $piKat      = (string) ($pi['kategori'] ?? 'berita');
            $isPeng     = ($piKat === 'pengumuman');
            $isFeatured = !empty((int) ($pi['is_featured'] ?? 0));

            // Label kategori + class warna badge
            if ($isFeatured) {
                $catLabel    = $isPeng ? 'Pengumuman Utama' : 'Berita Utama';
                $catClass    = 'org-news-card__category org-news-card__category--utama';
                $catIconHtml = '<i class="fa-solid fa-star" aria-hidden="true"></i>';
                $metaCat     = $isPeng ? 'Pengumuman utama' : 'Berita utama';
            } else {
                $catLabel    = $isPeng ? 'Pengumuman' : 'Berita';
                $catClass    = 'org-news-card__category ' . ($isPeng ? 'org-news-card__category--pengumuman' : 'org-news-card__category--berita');
                $catIconHtml = $isPeng
                    ? '<i class="fa-solid fa-bullhorn" aria-hidden="true"></i>'
                    : '<i class="fa-regular fa-newspaper" aria-hidden="true"></i>';
                $metaCat     = $catLabel;
            }

            // Gambar
            $gfile  = trim((string) ($pi['nama_gambar'] ?? ''));
            $imgUrl = $gfile !== '' ? org_pusat_informasi_upload_web_prefix() . rawurlencode($gfile) : '';

            // Excerpt
            $rawT    = (string) ($pi['isi_teks'] ?? '');
            $excerpt = trim((string) preg_replace('/\s+/', ' ', strip_tags($rawT)));
            if (function_exists('mb_strlen') && mb_strlen($excerpt) > 140) {
                $excerpt = mb_substr($excerpt, 0, 137) . '…';
            } elseif (strlen($excerpt) > 140) {
                $excerpt = substr($excerpt, 0, 137) . '…';
            }

            // Tanggal
            $tglRaw   = (string) ($pi['created_at'] ?? '');
            $tglTs    = $tglRaw !== '' ? strtotime($tglRaw) : false;
            $dayNum   = $tglTs ? (int) date('j', $tglTs) : 0;
            $monthYr  = '';
            if ($tglTs) {
                $monthIdx = (int) date('n', $tglTs);
                $yearStr  = date('Y', $tglTs);
                $monthYr  = ($bulanMap[$monthIdx] ?? '') . ' ' . $yearStr;
            }
            $tglFmt = $tglTs ? date('d/m/Y', $tglTs) : '';

            $piJudul = trim((string) ($pi['judul'] ?? ''));
            $aria    = $piJudul !== '' ? ($piJudul . ' — baca selengkapnya') : 'Baca selengkapnya';
            ?>
            <article class="org-news-card-wrap" role="listitem">
                <a class="org-news-card" href="<?php echo org_href('informasi.php', 'id=' . $piId); ?>" aria-label="<?php echo htmlspecialchars($aria, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="org-news-card__media">
                        <?php if ($dayNum > 0): ?>
                            <span class="org-news-card__date-badge" aria-hidden="true">
                                <span class="org-news-card__date-day"><?php echo (int) $dayNum; ?></span>
                                <span class="org-news-card__date-month"><?php echo htmlspecialchars($monthYr, ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        <?php endif; ?>
                        <span class="<?php echo htmlspecialchars($catClass, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $catIconHtml; ?>
                            <?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php if ($imgUrl !== ''): ?>
                            <img
                                src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                class="org-news-card__img"
                                alt=""
                                width="640"
                                height="360"
                                loading="lazy"
                                decoding="async">
                        <?php else: ?>
                            <div class="org-news-card__img org-news-card__img--placeholder" aria-hidden="true">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="org-news-card__body">
                        <div>
                            <div class="org-news-card__meta">
                                <?php if ($tglFmt !== ''): ?>
                                    <span class="org-news-card__meta-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="org-news-card__meta-sep" aria-hidden="true">·</span>
                                <?php endif; ?>
                                <span class="org-news-card__meta-cat"><?php echo htmlspecialchars($metaCat, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <h3 class="org-news-card__title"><?php echo htmlspecialchars($piJudul, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if ($excerpt !== ''): ?>
                                <p class="org-news-card__excerpt"><?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="org-news-card__cta">Baca selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="org-news-grid">
        <div class="org-news-empty" role="status">
            <div class="org-news-empty__icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></div>
            <h3 class="org-news-empty__title">Belum ada entri Pusat Informasi</h3>
            <p class="org-news-empty__desc">Unggah judul, kategori, teks, dan gambar dari Dashboard Admin → Pusat Informasi &amp; Pengumuman.</p>
            <a class="btn btn-primary btn-sm px-4" href="<?php echo org_href('admin/dashboard.php', '', 'panel-pusat-informasi'); ?>">
                <i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>Kelola di Dashboard
            </a>
        </div>
    </div>
<?php endif; ?>
