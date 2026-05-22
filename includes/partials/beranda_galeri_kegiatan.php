<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $berandaGaleriKegiatan */
$items = $berandaGaleriKegiatan ?? [];
?>
            <?php if (count($items) > 0): ?>
                <div class="beranda-galeri-grid">
                    <?php foreach ($items as $gItem): ?>
                        <?php
                        if (!is_array($gItem)) {
                            continue;
                        }
                        $gJudul = (string) ($gItem['judul'] ?? '');
                        $gFile = basename((string) ($gItem['nama_file'] ?? ''));
                        $gTglRaw = (string) ($gItem['tgl_upload'] ?? '');
                        $gTglFmt = $gTglRaw !== '' ? date('d M Y', strtotime($gTglRaw)) : '';
                        $gImgSrc = org_galeri_kegiatan_image_url($gFile);
                        if ($gImgSrc === '') {
                            continue;
                        }
                        $gCaption = trim($gJudul . ($gTglFmt !== '' ? "\n" . $gTglFmt : ''));
                        ?>
                        <a
                            href="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                            class="beranda-galeri-item"
                            data-fancybox="beranda-galeri-kegiatan"
                            data-caption="<?php echo htmlspecialchars($gCaption, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <span class="beranda-galeri-item__frame">
                                <img
                                    class="beranda-galeri-item__img"
                                    src="<?php echo htmlspecialchars($gImgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?>"
                                    width="640"
                                    height="480"
                                    loading="lazy"
                                >
                                <span class="beranda-galeri-item__glass" aria-hidden="true">
                                    <?php if ($gJudul !== ''): ?>
                                        <span class="beranda-galeri-item__hover-title"><?php echo htmlspecialchars($gJudul, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($gTglFmt !== ''): ?>
                                        <span class="beranda-galeri-item__hover-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($gTglFmt, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="beranda-galeri-item__zoom" aria-hidden="true" title="Perbesar">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="beranda-galeri-empty rounded-3 border border-light bg-light px-3 py-4 text-center">
                    <p class="text-muted small mb-2 mb-md-0">Belum ada foto kegiatan untuk ditampilkan.</p>
                    <a class="small fw-semibold text-decoration-none" href="galeri.php">Buka halaman Galeri</a>
                </div>
            <?php endif; ?>
