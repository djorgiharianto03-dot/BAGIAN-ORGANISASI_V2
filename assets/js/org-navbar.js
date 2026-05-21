/**
 * Navbar mobile panel — tanpa Bootstrap Collapse (hindari height:0 di desktop)
 */
(function () {
    'use strict';

    function initNavbarPanel() {
        var toggle = document.querySelector('.site-header__nav-toggle');
        var closeBtn = document.querySelector('.site-header__nav-close');
        var panel = document.getElementById('siteHeaderNavPanel');
        if (!panel) {
            return;
        }

        var desktopMq = window.matchMedia('(min-width: 768px)');

        function setOpen(open) {
            panel.classList.toggle('is-open', open);
            document.body.classList.toggle('site-header-nav-open', open && !desktopMq.matches);
            if (toggle) {
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
        }

        function isOpen() {
            return panel.classList.contains('is-open');
        }

        function toggleOpen() {
            if (desktopMq.matches) {
                return;
            }
            setOpen(!isOpen());
        }

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                toggleOpen();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                setOpen(false);
            });
        }

        panel.querySelectorAll('.site-header__nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (!desktopMq.matches) {
                    setOpen(false);
                }
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) {
                setOpen(false);
            }
        });

        desktopMq.addEventListener('change', function () {
            if (desktopMq.matches) {
                setOpen(false);
            }
        });
    }

    function initOrgModals() {
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

    function bootNavbar() {
        initNavbarPanel();
        initOrgModals();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootNavbar);
    } else {
        bootNavbar();
    }
})();
