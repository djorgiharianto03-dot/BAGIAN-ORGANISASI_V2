/**
 * Premium dark / light theme — localStorage + smooth transition
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'org-color-theme';
    var TRANSITION_MS = 380;

    function getStored() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function setStored(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) {
            /* ignore */
        }
    }

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function applyTheme(mode, animate) {
        var root = document.documentElement;
        var isDark = mode === 'dark';

        if (animate) {
            root.classList.add('org-theme-transition');
        }

        if (isDark) {
            root.setAttribute('data-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
        }

        root.style.colorScheme = isDark ? 'dark' : 'light';

        updateToggleUi(isDark);

        if (animate) {
            window.setTimeout(function () {
                root.classList.remove('org-theme-transition');
            }, TRANSITION_MS);
        }
    }

    function updateToggleUi(isDark) {
        var btn = document.getElementById('orgThemeToggle');
        if (!btn) {
            return;
        }
        btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        btn.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        btn.setAttribute('title', isDark ? 'Mode terang' : 'Mode gelap');
        btn.classList.toggle('is-dark', isDark);
    }

    function toggleTheme() {
        var next = getTheme() === 'dark' ? 'light' : 'dark';
        setStored(next);
        applyTheme(next, true);
    }

    function bindToggle() {
        var btn = document.getElementById('orgThemeToggle');
        if (!btn || btn.getAttribute('data-org-theme-bound') === '1') {
            return;
        }
        btn.setAttribute('data-org-theme-bound', '1');
        btn.addEventListener('click', toggleTheme);
        updateToggleUi(getTheme() === 'dark');
    }

    function boot() {
        var stored = getStored();
        if (stored === 'dark' || stored === 'light') {
            applyTheme(stored, false);
        } else {
            updateToggleUi(getTheme() === 'dark');
        }
        bindToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.OrgTheme = {
        get: getTheme,
        set: function (mode) {
            if (mode !== 'dark' && mode !== 'light') {
                return;
            }
            setStored(mode);
            applyTheme(mode, true);
        },
        toggle: toggleTheme
    };
})();
