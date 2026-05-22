/**
 * Beranda — muat aset berat setelah konten utama (IO / idle).
 */
(function () {
    'use strict';

    var base = (typeof window.ORG_VENDOR_BASE === 'string' && window.ORG_VENDOR_BASE !== '')
        ? window.ORG_VENDOR_BASE.replace(/\/$/, '')
        : '/assets/vendor';

    var assetBase = (typeof window.ORG_ASSET_BASE === 'string' && window.ORG_ASSET_BASE !== '')
        ? window.ORG_ASSET_BASE.replace(/\/$/, '')
        : '';

    function vendorUrl(path) {
        return base + '/' + path.replace(/^\//, '');
    }

    function assetUrl(path) {
        return (assetBase ? assetBase : '') + '/assets/' + path.replace(/^\//, '');
    }

    var AOS_CSS = vendorUrl('aos/2.3.4/aos.css');
    var AOS_JS = vendorUrl('aos/2.3.4/aos.js');
    var FANCY_CSS = vendorUrl('fancybox/5.0/fancybox.css');
    var FANCY_JS = vendorUrl('fancybox/5.0/fancybox.umd.js');
    var CHART_JS = vendorUrl('chartjs/4.4.1/chart.umd.min.js');
    var APEX_JS = vendorUrl('apexcharts/3.49.1/apexcharts.min.js');

    var loaded = {
        aos: false,
        fancy: false,
        chart: false,
        apex: false,
        portal: false,
        kpi: false,
        teamCharts: false,
        ai: false
    };

    var apexDispatchPending = false;

    function dispatchApexReady() {
        if (apexDispatchPending) {
            return;
        }
        apexDispatchPending = true;
        document.dispatchEvent(new Event('beranda:apex-ready'));
    }

    function loadStylesheet(href) {
        if (!href || document.querySelector('link[data-beranda-lazy-css="' + href + '"]')) {
            return;
        }
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.setAttribute('data-beranda-lazy-css', href);
        document.head.appendChild(link);
    }

    function loadScript(src, cb) {
        if (!src) {
            if (cb) cb();
            return;
        }
        var existing = document.querySelector('script[data-beranda-lazy-src="' + src + '"]');
        if (existing) {
            if (cb) {
                if (existing.getAttribute('data-beranda-loaded') === '1') {
                    cb();
                } else {
                    existing.addEventListener('load', cb, { once: true });
                }
            }
            return;
        }
        var s = document.createElement('script');
        s.src = src;
        s.defer = true;
        s.setAttribute('data-beranda-lazy-src', src);
        s.onload = function () {
            s.setAttribute('data-beranda-loaded', '1');
            if (cb) cb();
        };
        s.onerror = function () {
            s.setAttribute('data-beranda-loaded', '1');
            if (cb) cb();
        };
        document.head.appendChild(s);
    }

    function whenIdle(fn, timeout) {
        timeout = timeout || 2400;
        if (typeof requestIdleCallback === 'function') {
            requestIdleCallback(fn, { timeout: timeout });
        } else {
            setTimeout(fn, Math.min(timeout, 400));
        }
    }

    function observeLazy(target, fn, rootMargin) {
        if (!target) {
            return;
        }
        if (!('IntersectionObserver' in window)) {
            whenIdle(fn, 1200);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    io.disconnect();
                    fn();
                }
            });
        }, { rootMargin: rootMargin || '160px' });
        io.observe(target);
    }

    function loadChartJs() {
        if (loaded.chart) {
            document.dispatchEvent(new Event('beranda:chart-ready'));
            return;
        }
        loaded.chart = true;
        loadScript(CHART_JS, function () {
            document.dispatchEvent(new Event('beranda:chart-ready'));
        });
    }

    function loadApexCharts() {
        if (loaded.apex) {
            dispatchApexReady();
            return;
        }
        loaded.apex = true;
        loadScript(APEX_JS, dispatchApexReady);
    }

    function loadTeamTargetCharts() {
        if (loaded.teamCharts) {
            return;
        }
        loaded.teamCharts = true;
        loadApexCharts();
        loadScript(assetUrl('js/beranda-team-target-charts.js'));
    }

    function initAos() {
        if (loaded.aos) {
            return;
        }
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var run = function () {
            if (typeof AOS === 'undefined') {
                return;
            }
            loaded.aos = true;
            if (reduced) {
                AOS.init({ disable: true });
            } else {
                AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic', offset: 48 });
            }
        };
        loadStylesheet(AOS_CSS);
        if (typeof AOS !== 'undefined') {
            run();
            return;
        }
        loadScript(AOS_JS, run);
    }

    function initFancybox() {
        if (loaded.fancy) {
            return;
        }
        var bind = function () {
            if (typeof Fancybox === 'undefined') {
                return;
            }
            loaded.fancy = true;
            Fancybox.bind('[data-fancybox="beranda-galeri-kegiatan"]', {
                animated: true,
                dragToClose: true,
                backdropClick: 'close',
                Carousel: { transition: 'fade' },
                Thumbs: { type: 'classic' },
                Toolbar: { display: { left: [], middle: [], right: ['close'] } }
            });
        };
        loadStylesheet(FANCY_CSS);
        if (typeof Fancybox !== 'undefined') {
            bind();
            return;
        }
        loadScript(FANCY_JS, bind);
    }

    function loadGovKpiModal() {
        if (loaded.kpi || !document.getElementById('gov-kpi-details-data')) {
            return;
        }
        loaded.kpi = true;
        loadScript(assetUrl('js/beranda-gov-kpi-modal.js'));
    }

    function loadPortalEnhancements() {
        if (loaded.portal) {
            return;
        }
        loaded.portal = true;
        loadScript(assetBase + '/assets/js/smart-governance-portal.js?v=17');
    }

    function scheduleAos() {
        var aosTarget = document.querySelector('#beranda-root [data-aos], #beranda-pusat-informasi [data-aos]');
        if (aosTarget) {
            observeLazy(aosTarget, initAos, '200px');
        } else {
            whenIdle(initAos, 3000);
        }
    }

    function loadBerandaChunk(slot) {
        if (!slot || slot.getAttribute('data-beranda-chunk-loaded') === '1') {
            return;
        }
        var section = slot.getAttribute('data-beranda-chunk');
        if (!section) {
            return;
        }
        var root = (typeof window.ORG_WEB_ROOT === 'string') ? window.ORG_WEB_ROOT.replace(/\/$/, '') : '';
        var url = root + '/beranda_chunk.php?section=' + encodeURIComponent(section);
        if (section === 'team') {
            var tahun = slot.getAttribute('data-beranda-tahun');
            if (tahun) {
                url += '&tahun=' + encodeURIComponent(tahun);
            }
        }
        slot.setAttribute('data-beranda-chunk-loaded', '1');
        fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html' } })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('chunk');
                }
                return res.text();
            })
            .then(function (html) {
                var trimmed = (html || '').trim();
                if (trimmed === '') {
                    slot.remove();
                    return;
                }
                slot.innerHTML = trimmed;
                slot.removeAttribute('aria-busy');
                slot.classList.remove('beranda-chunk-slot');
                if (section === 'dashboard') {
                    loadGovKpiModal();
                }
                if (section === 'team') {
                    var teamEl = document.getElementById('beranda-team-targets')
                        || document.getElementById('govTeamTargetOverviewChart');
                    if (teamEl) {
                        loadTeamTargetCharts();
                    }
                }
                scheduleAos();
            })
            .catch(function () {
                slot.removeAttribute('data-beranda-chunk-loaded');
                slot.setAttribute('aria-busy', 'false');
                slot.classList.add('beranda-chunk-slot--error');
            });
    }

    function loadBerandaChunks() {
        var slots = document.querySelectorAll('[data-beranda-chunk]');
        if (!slots.length) {
            return;
        }
        slots.forEach(function (slot) {
            observeLazy(slot, function () {
                loadBerandaChunk(slot);
            }, '280px');
        });
    }

    function boot() {
        loadBerandaChunks();

        var visitSection = document.getElementById('beranda-kunjungan-web');
        if (visitSection) {
            observeLazy(visitSection, loadChartJs, '180px');
        }

        var teamSection = document.getElementById('beranda-team-targets')
            || document.getElementById('govTeamTargetOverviewChart');
        if (teamSection) {
            observeLazy(teamSection, loadTeamTargetCharts, '200px');
        }

        var dashSection = document.getElementById('beranda-dashboard-widgets');
        if (dashSection) {
            observeLazy(dashSection, loadGovKpiModal, '200px');
        }

        var galeri = document.getElementById('beranda-galeri-kegiatan');
        if (galeri) {
            observeLazy(galeri, initFancybox, '220px');
        }

        scheduleAos();

        whenIdle(loadPortalEnhancements, 2800);

        if (typeof window.orgBerandaLoadAiChat === 'function') {
            whenIdle(window.orgBerandaLoadAiChat, 4500);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
