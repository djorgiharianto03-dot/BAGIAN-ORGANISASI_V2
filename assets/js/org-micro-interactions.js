/**
 * Premium micro-interactions — reveal, stagger, ripple, AOS sync
 */
(function () {
    'use strict';

    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function isProfilOrgPage() {
        return document.body && document.body.classList.contains('page-profil-org');
    }

    function initAos() {
        if (reduced || typeof AOS === 'undefined' || window.__ORG_AOS_INIT__ || isProfilOrgPage()) {
            return;
        }
        window.__ORG_AOS_INIT__ = true;
        AOS.init({
            once: true,
            duration: 520,
            easing: 'ease-out-cubic',
            offset: 48,
            delay: 0,
            disable: false
        });
    }

    function autoRevealTargets() {
        if (isProfilOrgPage()) {
            return;
        }
        var root = document.querySelector('.site-main') || document.querySelector('.sg-portal-main-inner') || document.body;
        var selectors = [
            '.site-main > section',
            '.site-main .section-card',
            '.site-main .card.shadow-sm',
            '.sg-subhero',
            '.sg-portal-toolbar',
            '.news-portal',
            '.gl-portal',
            '.eo-dash',
            '.eo-modules',
            '.eorg-hub--enterprise',
            '.digital-library--beranda-hero',
            '.beranda-section',
            '.site-footer-card'
        ];
        var seen = new WeakSet();
        selectors.forEach(function (sel) {
            root.querySelectorAll(sel).forEach(function (el) {
                if (seen.has(el) || el.hasAttribute('data-aos') || el.classList.contains('org-reveal')) {
                    return;
                }
                if (el.closest('.modal')) {
                    return;
                }
                seen.add(el);
                el.classList.add('org-reveal');
            });
        });

        root.querySelectorAll('.row.g-3, .row.g-4, .eo-modules__grid, .eo-dash__stats, .np-grid').forEach(function (row) {
            if (row.classList.contains('org-reveal-stagger')) {
                return;
            }
            var kids = row.children;
            if (kids.length < 2 || kids.length > 12) {
                return;
            }
            row.classList.add('org-reveal-stagger');
            Array.prototype.forEach.call(kids, function (child, idx) {
                child.style.setProperty('--org-reveal-i', String(idx));
            });
        });
    }

    function initRevealObserver() {
        if (reduced || isProfilOrgPage()) {
            document.querySelectorAll('.org-reveal, .org-reveal-stagger').forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var nodes = document.querySelectorAll('.org-reveal, .org-reveal-stagger');
        if (!nodes.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            nodes.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var io = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                });
            },
            { root: null, rootMargin: '0px 0px -6% 0px', threshold: 0.12 }
        );

        nodes.forEach(function (el) {
            io.observe(el);
        });
    }

    function addRipple(el) {
        if (reduced || el.classList.contains('org-ripple') || el.disabled) {
            return;
        }
        el.classList.add('org-ripple');
        el.addEventListener('click', function (e) {
            var rect = el.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height) * 1.8;
            var wave = document.createElement('span');
            wave.className = 'org-ripple__wave';
            wave.style.width = wave.style.height = size + 'px';
            wave.style.left = e.clientX - rect.left - size / 2 + 'px';
            wave.style.top = e.clientY - rect.top - size / 2 + 'px';
            el.appendChild(wave);
            wave.addEventListener('animationend', function () {
                wave.remove();
            });
        });
    }

    function initRipples() {
        document.querySelectorAll('.btn:not(.btn-close):not(.btn-link), .site-header-doc-search__submit').forEach(addRipple);
    }

    function initNavToggleAnim() {
        /* Panel menu: org-navbar.js — skip jika Bootstrap Collapse tidak dipakai */
        var collapse = document.getElementById('siteHeaderNavCollapse');
        var toggle = document.querySelector('.site-header__nav-toggle');
        if (!toggle || !collapse || !collapse.classList.contains('collapse')) {
            return;
        }
        collapse.addEventListener('show.bs.collapse', function () {
            toggle.setAttribute('aria-expanded', 'true');
        });
        collapse.addEventListener('hide.bs.collapse', function () {
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function boot() {
        document.documentElement.classList.add('org-motion-ready');
        autoRevealTargets();
        initRevealObserver();
        initRipples();
        initNavToggleAnim();
        initAos();
        if (typeof AOS !== 'undefined' && typeof AOS.refreshHard === 'function') {
            window.addEventListener('load', function () {
                setTimeout(function () {
                    AOS.refreshHard();
                }, 120);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
