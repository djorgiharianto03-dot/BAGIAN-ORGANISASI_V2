/**
 * Beranda — muat AOS, Fancybox, dan ApexCharts lokal hanya saat dibutuhkan.
 */
(function () {
    'use strict';

    var base = (typeof window.ORG_VENDOR_BASE === 'string' && window.ORG_VENDOR_BASE !== '')
        ? window.ORG_VENDOR_BASE.replace(/\/$/, '')
        : '/assets/vendor';

    function vendorUrl(path) {
        return base + '/' + path.replace(/^\//, '');
    }

    var AOS_JS = vendorUrl('aos/2.3.4/aos.js');
    var FANCY_JS = vendorUrl('fancybox/5.0/fancybox.umd.js');
    var APEX_JS = vendorUrl('apexcharts/3.49.1/apexcharts.min.js');

    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.defer = true;
        s.onload = function () { if (cb) cb(); };
        s.onerror = function () { if (cb) cb(); };
        document.head.appendChild(s);
    }

    function whenIdle(fn) {
        if (typeof requestIdleCallback === 'function') {
            requestIdleCallback(fn, { timeout: 2200 });
        } else {
            setTimeout(fn, 1);
        }
    }

    function initAos() {
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (typeof AOS === 'undefined') {
            loadScript(AOS_JS, initAos);
            return;
        }
        if (reduced) {
            AOS.init({ disable: true });
        } else {
            AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic', offset: 48 });
        }
    }

    function initFancybox() {
        if (typeof Fancybox === 'undefined') {
            loadScript(FANCY_JS, initFancybox);
            return;
        }
        Fancybox.bind('[data-fancybox="beranda-galeri-kegiatan"]', {
            animated: true,
            dragToClose: true,
            backdropClick: 'close',
            Carousel: { transition: 'fade' },
            Thumbs: { type: 'classic' },
            Toolbar: { display: { left: [], middle: [], right: ['close'] } }
        });
    }

    function observeLazy(target, fn) {
        if (!target) {
            return;
        }
        if (!('IntersectionObserver' in window)) {
            fn();
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    io.disconnect();
                    fn();
                }
            });
        }, { rootMargin: '100px' });
        io.observe(target);
    }

    function boot() {
        whenIdle(initAos);

        var galeri = document.getElementById('beranda-galeri-kegiatan');
        if (galeri) {
            observeLazy(galeri, initFancybox);
        }

        var apexData = document.getElementById('gov-team-target-charts-data');
        if (apexData) {
            observeLazy(apexData, function () {
                if (typeof ApexCharts !== 'undefined') {
                    document.dispatchEvent(new Event('beranda:apex-ready'));
                    return;
                }
                loadScript(APEX_JS, function () {
                    document.dispatchEvent(new Event('beranda:apex-ready'));
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
