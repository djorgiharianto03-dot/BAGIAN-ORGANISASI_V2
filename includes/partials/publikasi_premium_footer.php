<?php
declare(strict_types=1);

/** @var bool $publikasiPremiumInitSwiper */
$initSwiper = !empty($publikasiPremiumInitSwiper);
?>
<script>
(function () {
    function initPublikasiPremium() {
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (typeof AOS !== 'undefined' && !window.__ORG_AOS_INIT__) {
            window.__ORG_AOS_INIT__ = true;
            AOS.init({ once: true, duration: 520, easing: 'ease-out-cubic', offset: 48, disable: reduced });
        }
<?php if ($initSwiper): ?>
        var piEl = document.getElementById('halamanPusatSwiper');
        if (piEl && typeof Swiper !== 'undefined' && piEl.getAttribute('data-swiper-ready') !== '1') {
            var slideCount = piEl.querySelectorAll('.swiper-slide').length;
            new Swiper(piEl, {
                slidesPerView: 'auto',
                centeredSlides: true,
                spaceBetween: 20,
                grabCursor: true,
                speed: 650,
                loop: slideCount > 3,
                pagination: { el: piEl.querySelector('.swiper-pagination'), clickable: true },
                navigation: {
                    nextEl: piEl.querySelector('.swiper-button-next'),
                    prevEl: piEl.querySelector('.swiper-button-prev')
                }
            });
            piEl.setAttribute('data-swiper-ready', '1');
        }
<?php endif; ?>
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPublikasiPremium);
    } else {
        initPublikasiPremium();
    }
})();
</script>
