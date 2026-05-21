<?php
declare(strict_types=1);
/** @var list<array{id: string, judul: string, teks: string, nama_gambar: string, created_at: string}> $pengumumanCards */
$pengumumanCards = $pengumumanCards ?? [];
$imgPrefix = org_pengumuman_upload_web_prefix();
?>
<?php if (count($pengumumanCards) > 0): ?>
    <div class="org-info-announce-stack mt-4 pt-3 border-top">
        <p class="fw-semibold mb-3 text-secondary small text-uppercase" style="letter-spacing: 0.06em;">Pengumuman &amp; brosur</p>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($pengumumanCards as $pCard): ?>
                <?php
                $gam = trim((string) ($pCard['nama_gambar'] ?? ''));
                $imgUrl = $gam !== '' ? $imgPrefix . rawurlencode($gam) : '';
                $judulEsc = htmlspecialchars((string) ($pCard['judul'] ?? ''), ENT_QUOTES, 'UTF-8');
                ?>
                <article class="org-info-announce-card org-info-announce-card--hoverlift">
                    <div class="org-info-announce-card__thumb">
                        <?php if ($imgUrl !== ''): ?>
                            <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="org-info-announce-card__img" width="200" height="140" loading="lazy">
                        <?php else: ?>
                            <div class="org-info-announce-card__placeholder" aria-hidden="true">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="org-info-announce-card__body">
                        <?php if ($judulEsc !== ''): ?>
                            <h3 class="org-info-announce-card__title h6 mb-2"><?php echo $judulEsc; ?></h3>
                        <?php endif; ?>
                        <div class="org-info-announce-card__text small text-secondary"><?php echo nl2br(htmlspecialchars((string) ($pCard['teks'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
