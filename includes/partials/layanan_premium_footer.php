<?php
?>
<script>
(function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (typeof AOS !== 'undefined' && !window.__ORG_AOS_INIT__) {
        window.__ORG_AOS_INIT__ = true;
        AOS.init({ once: true, duration: 520, easing: 'ease-out-cubic', offset: 56, disable: reduced });
    }
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind('[data-fancybox^="layanan-"]', {
            animated: true,
            dragToClose: true,
            backdropClick: 'close',
            Carousel: { transition: 'fade' },
            Toolbar: { display: { left: [], middle: [], right: ['close'] } }
        });
    }
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
}());
</script>
