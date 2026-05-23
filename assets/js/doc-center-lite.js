/**
 * Perpustakaan Digital — pratinjau ringan (tab baru, tanpa embed PDF di halaman daftar).
 */
(function () {
    'use strict';

    if (!document.body.classList.contains('page-digital-library')) {
        return;
    }

    document.addEventListener('click', function (ev) {
        var link = ev.target && ev.target.closest ? ev.target.closest('a.js-doc-center-preview') : null;
        if (!link || !link.href) {
            return;
        }
        ev.preventDefault();
        var w = window.open(link.href, '_blank', 'noopener,noreferrer');
        if (w) {
            w.opener = null;
        }
    });
})();
