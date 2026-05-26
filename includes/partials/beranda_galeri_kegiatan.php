<?php

declare(strict_types=1);

/**
 * Beranda — Galeri Kegiatan Terbaru
 * -----------------------------------------------------------------------------
 * Tampilan: Grid 3 kolom kartu foto modern (gaya portal pemerintahan).
 *   - Desktop:  3 kolom
 *   - Tablet:   2 kolom
 *   - Mobile:   1 kolom
 *   - Foto di atas (aspect 4:3, object-fit cover)
 *   - Judul + tanggal + CTA "Lihat foto" di bawah, terpusat
 *   - Hover ringan translateY(-3px) + shadow halus + foto zoom 1.04
 *   - Tetap kompatibel Fancybox (data-fancybox attribute)
 *
 * Kelas dipakai: `.beranda-galeri-cards` (wrapper) + `.beranda-galeri-card`
 * (item). Sengaja kelas baru agar tidak menabrak CSS lama
 * (.beranda-galeri-scroll / .beranda-galeri-item) yang masih dipakai untuk
 * layout horizontal scroll lama bila diperlukan.
 */

/** @var list<array<string, mixed>> $berandaGaleriKegiatan */
$items = $berandaGaleriKegiatan ?? [];
?>
<?php if (count($items) > 0): ?>
    <div class="beranda-galeri-cards" role="region" aria-label="Galeri kegiatan terbaru">
        <?php foreach ($items as $gItem): ?>
            <?php
            if (!is_array($gItem)) {
                continue;
            }
            $gJudul  = (string) ($gItem['judul'] ?? '');
            $gFile   = basename((string) ($gItem['nama_file'] ?? ''));
            $gTglRaw = (string) ($gItem['tgl_upload'] ?? '');
            $gTglFmt = $gTglRaw !== '' ? date('d M Y', strtotime($gTglRaw)) : '';
            $gImgSrc = org_galeri_kegiatan_image_url($gFile);
            if ($gImgSrc === '') {
                continue;
            }
            $gCaption = trim($gJudul . ($gTglFmt !== '' ? "\n" . $gTglFmt : ''));
            $gAria    = $gJudul !== '' ? ($gJudul . ' — perbesar foto') : 'Perbesar foto kegiatan';
            ?>
            <a
                href="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                class="beranda-galeri-card"
                data-fancybox="beranda-galeri-kegiatan"
                data-caption="<?php echo htmlspecialchars($gCaption, ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars($gAria, ENT_QUOTES, 'UTF-8'); ?>"
            >
                <div class="beranda-galeri-card__media">
                    <img
                        class="beranda-galeri-card__img"
                        src="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?>"
                        width="640"
                        height="480"
                        loading="lazy"
                        decoding="async"
                    >
                    <span class="beranda-galeri-card__zoom" aria-hidden="true" title="Perbesar">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </span>
                </div>
                <div class="beranda-galeri-card__body">
                    <?php if ($gJudul !== ''): ?>
                        <h3 class="beranda-galeri-card__title"><?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <?php endif; ?>
                    <?php if ($gTglFmt !== ''): ?>
                        <p class="beranda-galeri-card__date">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($gTglFmt, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endif; ?>
                    <span class="beranda-galeri-card__cta">
                        Lihat foto <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="beranda-galeri-empty rounded-3 border border-light bg-light px-3 py-4 text-center">
        <p class="text-muted small mb-2 mb-md-0">Belum ada foto kegiatan untuk ditampilkan.</p>
        <a class="small fw-semibold text-decoration-none" href="<?php echo org_href('galeri.php'); ?>">Buka halaman Galeri</a>
    </div>
<?php endif; ?>
