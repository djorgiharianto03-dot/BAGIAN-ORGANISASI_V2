<?php
declare(strict_types=1);
?>
<script>
(function () {
    var root = document.querySelector('.layanan-dir[data-layanan-directory]');
    if (!root) {
        return;
    }
    var cells = root.querySelectorAll('.layanan-dir__cell');
    var tabs = root.querySelectorAll('.layanan-dir__tab');
    var search = root.querySelector('.layanan-dir__search-input');
    var noResults = root.querySelector('.layanan-dir__empty-filter');
    var grid = root.querySelector('.layanan-dir__grid');

    function debounce(fn, ms) {
        var t;
        return function () {
            clearTimeout(t);
            var args = arguments;
            t = setTimeout(function () {
                fn.apply(null, args);
            }, ms);
        };
    }

    function applyFilter() {
        var filter = root.getAttribute('data-active-filter') || 'all';
        var q = (search && search.value ? search.value : '').trim().toLowerCase();
        var n = 0;
        for (var i = 0; i < cells.length; i++) {
            var cell = cells[i];
            var cat = cell.getAttribute('data-layanan-cat') || '';
            var hay = (cell.getAttribute('data-layanan-q') || '').toLowerCase();
            var catOk = filter === 'all' || cat === filter;
            var qOk = q === '' || hay.indexOf(q) !== -1;
            var ok = catOk && qOk;
            cell.hidden = !ok;
            if (ok) {
                n++;
            }
        }
        if (noResults) {
            noResults.hidden = n > 0;
        }
        if (grid) {
            grid.setAttribute('aria-busy', 'false');
        }
    }

    var run = debounce(applyFilter, 120);

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var val = tab.getAttribute('data-filter') || 'all';
            root.setAttribute('data-active-filter', val);
            tabs.forEach(function (t) {
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });
            applyFilter();
        });
    });

    if (search) {
        search.addEventListener('input', run);
    }

    var resetBtn = noResults && noResults.querySelector('.js-layanan-dir-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            root.setAttribute('data-active-filter', 'all');
            tabs.forEach(function (t) {
                t.setAttribute('aria-selected', (t.getAttribute('data-filter') || '') === 'all' ? 'true' : 'false');
            });
            if (search) {
                search.value = '';
            }
            applyFilter();
            if (search) {
                search.focus();
            }
        });
    }

    applyFilter();
})();
</script>
