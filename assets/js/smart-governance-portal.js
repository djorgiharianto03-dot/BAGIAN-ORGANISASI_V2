/**
 * SMART GOVERNANCE PORTAL — interaksi beranda
 */
(function () {
    'use strict';

    document.documentElement.classList.add('sg-js');

    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Page loader */
    function initLoader() {
        if (document.body.classList.contains('sg-portal-subpage')) {
            return;
        }
        var loader = document.getElementById('sgPortalLoader');
        if (!loader) return;
        var hide = function () {
            loader.classList.add('is-done');
            loader.style.display = 'none';
            loader.setAttribute('aria-hidden', 'true');
            setTimeout(function () {
                if (loader.parentNode) loader.parentNode.removeChild(loader);
            }, 500);
        };
        var hideDelay = reducedMotion ? 0 : 80;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(hide, hideDelay);
            });
        } else {
            setTimeout(hide, hideDelay);
        }
    }

    /* Header fixed — offset hero/sub-hero agar judul tidak tertutup navbar */
    function syncPortalHeaderOffset() {
        var header = document.querySelector('.site-header--sg-portal');
        if (!header || !document.body) return;
        var h = Math.ceil(header.getBoundingClientRect().height);
        if (h > 0) {
            document.body.style.setProperty('--sg-portal-header-offset', h + 'px');
        }
        if (!document.body.classList.contains('sg-portal-page')) return;
        var isHome = document.body.classList.contains('sg-homepage');
        var main = document.querySelector('.site-layout-main');
        if (main) {
            /* Beranda: hero di bawah header fixed; subhalaman: main diberi offset */
            main.style.paddingTop = isHome ? '0' : (h > 0 ? h + 'px' : '');
        }
        var subhero = document.querySelector('.site-layout-main > .sg-subhero, .site-layout-main > .org-hero.sg-subhero');
        if (subhero) {
            subhero.style.paddingTop = '0';
        }
        var hero = document.getElementById('sg-hero');
        if (hero) {
            if (isHome && h > 0) {
                hero.style.paddingTop = h + 'px';
            } else {
                hero.style.removeProperty('padding-top');
            }
        }
    }

    function initHeader() {
        var header = document.querySelector('.site-header--sg-portal');
        if (!header) return;
        if (!document.body.classList.contains('sg-portal-page')) {
            document.body.classList.add('sg-portal-page');
        }
        var navWrap = header.querySelector('.site-header__nav-wrap');
        var holidayUcapan = header.querySelector('.site-header__holiday-ucapan');
        var onScroll = function () {
            var scrolled = window.scrollY > 16;
            header.classList.toggle('is-scrolled', scrolled);
            if (navWrap) {
                navWrap.classList.remove('is-features-fixed');
                navWrap.classList.toggle('is-nav-scrolled', scrolled);
            }
            /* Banner tema hari besar tetap di nav-wrap (ikut navbar), tidak disembunyikan saat scroll */
            if (holidayUcapan && holidayUcapan.classList.contains('site-header__holiday-ucapan--inline')) {
                holidayUcapan.classList.remove('is-collapsed');
                holidayUcapan.classList.toggle('is-nav-stuck', scrolled);
            }
        };
        var onLayout = function () {
            syncPortalHeaderOffset();
            onScroll();
        };
        onLayout();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onLayout, { passive: true });
        if (typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(onLayout);
            ro.observe(header);
        }
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(onLayout);
        }
    }

    /* Animated counters */
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-sg-count') || '0', 10);
        if (isNaN(target) || target < 0) target = 0;
        if (reducedMotion) {
            el.textContent = String(target);
            return;
        }
        var duration = 1400;
        var start = performance.now();
        var from = 0;
        function frame(now) {
            var t = Math.min(1, (now - start) / duration);
            var eased = 1 - Math.pow(1 - t, 3);
            el.textContent = String(Math.round(from + (target - from) * eased));
            if (t < 1) requestAnimationFrame(frame);
            else el.textContent = String(target);
        }
        requestAnimationFrame(frame);
    }

    function initCounters() {
        var els = document.querySelectorAll('[data-sg-count]');
        if (!els.length) return;
        if (!('IntersectionObserver' in window)) {
            els.forEach(animateCounter);
            return;
        }
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.2 });
        els.forEach(function (el) { io.observe(el); });
    }

    /* Progress bars */
    function initProgressBars() {
        var bars = document.querySelectorAll('.sg-progress__fill[data-sg-pct]');
        if (!bars.length) return;
        var run = function (bar) {
            var pct = Math.max(0, Math.min(100, parseFloat(bar.getAttribute('data-sg-pct') || '0')));
            bar.style.setProperty('--sg-pct', pct + '%');
            bar.classList.add('is-animated');
        };
        if (!('IntersectionObserver' in window)) {
            bars.forEach(run);
            return;
        }
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                run(entry.target);
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.15 });
        bars.forEach(function (bar) { io.observe(bar); });
    }

    /* Smooth anchor scroll */
    function initSmoothScroll() {
        document.documentElement.classList.add('sg-smooth-scroll');
        document.querySelectorAll('a[href^="#sg-"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var id = link.getAttribute('href');
                if (!id || id.length < 2) return;
                var target = document.querySelector(id);
                if (!target) return;
                e.preventDefault();
                var offset = 80;
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: reducedMotion ? 'auto' : 'smooth' });
            });
        });
    }

    function initPortalSaranForm() {
        var form = document.getElementById('formSaranPortal');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var nama = (document.getElementById('sg_saran_nama').value || '').trim();
            var email = (document.getElementById('sg_saran_email').value || '').trim();
            var pesan = (document.getElementById('sg_saran_pesan').value || '').trim();
            var st = document.getElementById('sgSaranPortalStatus');
            if (!nama || !email || !pesan) {
                if (st) st.textContent = 'Lengkapi nama, email, dan pesan.';
                return;
            }
            if (st) st.textContent = 'Mengirim...';
            var endpoint = form.getAttribute('data-saran-endpoint') || form.getAttribute('action') || 'proses_saran.php';
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ nama: nama, email: email, pesan: pesan })
            })
                .then(function (r) {
                    return r.text().then(function (t) {
                        var j = null;
                        try { j = t ? JSON.parse(t) : null; } catch (err) { j = null; }
                        return { ok: r.ok, j: j };
                    });
                })
                .then(function (x) {
                    if (st) {
                        if (x.j && x.j.ok) {
                            st.textContent = x.j.message || 'Terima kasih, masukan Anda telah terkirim!';
                            form.reset();
                        } else {
                            st.textContent = (x.j && x.j.message) ? x.j.message : 'Gagal mengirim.';
                        }
                    }
                })
                .catch(function () {
                    if (st) st.textContent = 'Gagal mengirim. Periksa koneksi Anda.';
                });
        });
    }

    function initAmbientParticles() {
        if (reducedMotion) return;
        document.querySelectorAll('.sg-particles[data-sg-particles]').forEach(function (host) {
            if (host.dataset.sgParticlesReady === '1') return;
            var count = parseInt(host.getAttribute('data-sg-particles') || '20', 10);
            count = Math.max(8, Math.min(48, isNaN(count) ? 20 : count));
            var frag = document.createDocumentFragment();
            for (var i = 0; i < count; i++) {
                var p = document.createElement('span');
                p.className = 'sg-particle';
                p.style.setProperty('--sg-p-x', (Math.random() * 100).toFixed(2) + '%');
                p.style.setProperty('--sg-p-y', (Math.random() * 100).toFixed(2) + '%');
                p.style.setProperty('--sg-p-dur', (4 + Math.random() * 7).toFixed(2) + 's');
                p.style.setProperty('--sg-p-delay', (Math.random() * 5).toFixed(2) + 's');
                p.style.setProperty('--sg-p-size', (2 + Math.random() * 2.5).toFixed(1) + 'px');
                frag.appendChild(p);
            }
            host.appendChild(frag);
            host.dataset.sgParticlesReady = '1';
        });
    }

    function initDashboardReveal() {
        if (!document.body.classList.contains('sg-portal-page')) return;
        var targets = document.querySelectorAll(
            '#beranda-root > section, #beranda-root > .beranda-section, .sg-portal-main-inner > .container > section, .gov-kpi-section'
        );
        var floatables = document.querySelectorAll(
            '.gov-kpi-card, #beranda-kunjungan-web .beranda-visit-stat, .pi-portal-card, .beranda-galeri-item'
        );
        if (!targets.length) return;
        targets.forEach(function (el) {
            el.classList.add('sg-dash-reveal');
        });
        floatables.forEach(function (el) {
            el.classList.add('sg-float-widget');
        });
        if (!('IntersectionObserver' in window)) {
            targets.forEach(function (el) { el.classList.add('is-visible'); });
            floatables.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
        targets.forEach(function (el, i) {
            el.style.transitionDelay = (i * 0.06) + 's';
            io.observe(el);
        });
        floatables.forEach(function (el, i) {
            el.style.transitionDelay = (0.1 + i * 0.05) + 's';
            io.observe(el);
        });
    }

    function initHomepageChartsGlow() {
        if (!document.body.classList.contains('sg-homepage')) return;
        document.querySelectorAll('#beranda-root .beranda-visit-chart-wrap').forEach(function (wrap) {
            wrap.classList.add('sg-chart-glow');
        });
    }

    function initBerandaReveal() {
        if (!document.body.classList.contains('sg-homepage')) return;
        var heroReveal = document.querySelectorAll('#sg-hero .sg-reveal');
        heroReveal.forEach(function (el) {
            el.classList.add('is-visible');
        });
        if (reducedMotion) {
            document.querySelectorAll('.sg-reveal, .sg-reveal-section, .sg-dash-reveal').forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }
        var targets = document.querySelectorAll('.sg-reveal:not(.is-visible), .sg-reveal-section, .sg-dash-reveal:not(.is-visible)');
        if (!targets.length) return;
        if (!('IntersectionObserver' in window)) {
            targets.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
        targets.forEach(function (el) { io.observe(el); });
    }

    /** Modal ke body + sembunyikan loader saat dialog terbuka */
    function initPortalModals() {
        document.querySelectorAll('.modal.fade').forEach(function (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });
        document.addEventListener('show.bs.modal', function () {
            var loader = document.getElementById('sgPortalLoader');
            if (loader) {
                loader.classList.add('is-done');
                loader.style.display = 'none';
                loader.setAttribute('aria-hidden', 'true');
            }
        });
    }

    /** Samakan jalur scroll layout dengan navbar fixed (tanpa lapisan bleed). */
    function initPortalLayoutFix() {
        if (!document.body.classList.contains('sg-portal-page')) return;
        document.documentElement.classList.add('sg-portal-html');
        document.documentElement.style.removeProperty('scrollbar-gutter');
        document.documentElement.style.removeProperty('width');
        document.documentElement.style.removeProperty('max-width');
        document.body.style.removeProperty('scrollbar-gutter');
        if (document.body.classList.contains('sg-homepage')) {
            document.documentElement.classList.add('sg-portal-html-home');
        }
        document.querySelectorAll('.sg-portal-viewport-bleed, .sg-hero-viewport-bleed').forEach(function (el) {
            el.remove();
        });
    }

    function boot() {
        initPortalModals();
        initPortalLayoutFix();
        initLoader();
        initHeader();
        initCounters();
        initProgressBars();
        initSmoothScroll();
        initPortalSaranForm();
        initAmbientParticles();
        initDashboardReveal();
        initBerandaReveal();
        initHomepageChartsGlow();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
