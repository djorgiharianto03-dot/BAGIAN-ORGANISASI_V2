/**
 * Beranda — mode ringan first load (IO section + efek + stabilisasi).
 */
(function () {
    'use strict';

    var docEl = document.documentElement;
    var body = document.body;
    if (!body || !body.classList.contains('sg-homepage')) {
        return;
    }

    body.classList.add('is-lite-render');

    document.querySelectorAll('.site-layout-main > .org-hero.sg-subhero .sg-reveal').forEach(function (el) {
        el.classList.add('is-visible');
    });

    function isLowEndDevice() {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return true;
        }
        var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
        var narrow = window.matchMedia && window.matchMedia('(max-width: 767.98px)').matches;
        var saveData = navigator.connection && navigator.connection.saveData;
        var slowNet = navigator.connection && /(^2g$)|(^slow-2g$)/.test(navigator.connection.effectiveType || '');
        var lowMem = navigator.deviceMemory && navigator.deviceMemory <= 4;
        var lowCpu = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4;

        return !!(coarse && narrow) || !!saveData || !!slowNet || !!lowMem || !!lowCpu;
    }

    if (isLowEndDevice()) {
        body.classList.add('is-effects-off');
        docEl.classList.add('is-effects-off');
    }

    function dispatchSectionReveal(sectionId, el) {
        document.dispatchEvent(new CustomEvent('beranda:section-reveal', {
            bubbles: true,
            detail: { section: sectionId, element: el }
        }));
    }

    function revealSection(el) {
        if (!el || el.classList.contains('is-section-revealed')) {
            return;
        }
        el.classList.add('is-section-revealed');
        var sectionId = el.getAttribute('data-beranda-lazy-section') || '';
        dispatchSectionReveal(sectionId, el);
    }

    function sectionInView(el) {
        if (!el || !el.getBoundingClientRect) {
            return false;
        }
        var rect = el.getBoundingClientRect();
        var vh = window.innerHeight || document.documentElement.clientHeight;
        return rect.top < vh + 240 && rect.bottom > -120;
    }

    function revealSectionsInView() {
        document.querySelectorAll('[data-beranda-lazy-section]').forEach(function (el) {
            if (!el.classList.contains('is-section-revealed') && sectionInView(el)) {
                revealSection(el);
            }
        });
    }

    function observeLazySections() {
        var sections = document.querySelectorAll('[data-beranda-lazy-section]');
        if (!sections.length) {
            return;
        }
        if (!('IntersectionObserver' in window)) {
            sections.forEach(revealSection);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    io.unobserve(entry.target);
                    revealSection(entry.target);
                }
            });
        }, { rootMargin: '280px 0px 200px 0px', threshold: 0 });
        sections.forEach(function (el) {
            io.observe(el);
        });
        revealSectionsInView();
        window.addEventListener('scroll', revealSectionsInView, { passive: true });
    }

    function injectHeroAmbient() {
        /* Partikel/glow hero dimatikan — hemat GPU & repaint */
    }

    function markSectionLoaded(el) {
        if (el) {
            el.classList.add('is-section-loaded');
        }
    }

    document.addEventListener('beranda:section-loaded', function (e) {
        if (e.detail && e.detail.element) {
            markSectionLoaded(e.detail.element);
        }
    });

    function stabilizePage() {
        body.classList.remove('is-lite-render');
        body.classList.add('is-lite-ready');
        docEl.classList.add('is-lite-ready');
        document.dispatchEvent(new Event('beranda:lite-ready'));
        revealSectionsInView();
    }

    function enableAiWidget() {
        var ai = document.getElementById('ai-chat-widget');
        if (!ai) {
            return;
        }
        ai.classList.add('is-ai-ready');
        if (typeof window.orgBerandaLoadAiChat === 'function') {
            var toggle = document.getElementById('ai-chat-toggle');
            if (toggle) {
                toggle.addEventListener('mouseenter', window.orgBerandaLoadAiChat, { once: true, passive: true });
            }
        }
    }

    function scheduleAi() {
        var aiSection = document.getElementById('beranda-galeri-kegiatan')
            || document.querySelector('[data-beranda-lazy-section="galeri"]')
            || document.getElementById('beranda-pusat-informasi');
        if (aiSection && 'IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) {
                        io.disconnect();
                        enableAiWidget();
                    }
                });
            }, { rootMargin: '0px', threshold: 0 });
            io.observe(aiSection);
        }
        if (typeof requestIdleCallback === 'function') {
            requestIdleCallback(enableAiWidget, { timeout: 6000 });
        } else {
            setTimeout(enableAiWidget, 5000);
        }
    }

    function onReady() {
        observeLazySections();
        var runStabilize = function () {
            stabilizePage();
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(runStabilize, 40);
            }, { once: true });
        } else {
            setTimeout(runStabilize, 40);
        }
        scheduleAi();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
