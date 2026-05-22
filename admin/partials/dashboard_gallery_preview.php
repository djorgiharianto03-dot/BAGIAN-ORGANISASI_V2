<?php

/** @var list<string> $galleryUrls */
?>
<div class="gallery-wrap adm-gallery-preview mb-4">
    <div class="swiper dash-gallery-swiper">
        <div class="swiper-wrapper">
            <?php if (count($galleryUrls) === 0): ?>
                <div class="swiper-slide">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1200' height='400'%3E%3Crect fill='%23cbd5e1' width='1200' height='400'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23475569' font-family='sans-serif' font-size='24'%3EUnggah foto kegiatan menggunakan formulir di bawah%3C/text%3E%3C/svg%3E" alt="Placeholder galeri">
                </div>
            <?php else: ?>
                <?php foreach ($galleryUrls as $gurl): ?>
                    <div class="swiper-slide">
                        <img src="<?php echo htmlspecialchars($gurl, ENT_QUOTES, 'UTF-8'); ?>" alt="Galeri kegiatan">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>
