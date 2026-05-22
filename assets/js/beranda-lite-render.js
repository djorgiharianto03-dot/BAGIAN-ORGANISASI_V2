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
        if (body.classList.contains('is-effects-off')) {
            return;
        }
        var host = document.getElementById('beranda-hero-fx');
        if (!host || host.getAttribute('data-fx-loaded') === '1') {
            return;
        }
        host.setAttribute('data-fx-loaded', '1');
        host.innerHTML = ''
            + '<div class="sg-ambient-layer sg-ambient-layer--hero" aria-hidden="true">'
            + '<span class="sg-ambient-glow sg-ambient-glow--a"></span>'
            + '<span class="sg-ambient-glow sg-ambient-glow--b"></span>'
            + '<span class="sg-ambient-glow sg-ambient-glow--c"></span>'
            + '<div class="sg-particles" data-sg-particles="10"></div>'
            + '</div>';
        document.dispatchEvent(new Event('beranda:hero-fx-ready'));
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

        if (!body.classList.contains('is-effects-off')) {
            if (typeof requestIdleCallback === 'function') {
                requestIdleCallback(injectHeroAmbient, { timeout: 2200 });
            } else {
                setTimeout(injectHeroAmbient, 600);
            }
        }

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
        var aiSection = document.querySelector('[data-beranda-lazy-section="galeri"]')
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
        window.addEventListener('load', function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(stabilizePage);
            });
        }, { once: true });
        scheduleAi();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
