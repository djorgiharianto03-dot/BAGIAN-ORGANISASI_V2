    <script>
        (function () {
            const adminSearchInput = document.getElementById('adminDocumentSearch');
            const tableBody = document.getElementById('adminDocumentTableBody');
            const quickFilterWrap = document.getElementById('adminDocumentQuickFilter');
            const quickFilterBtns = quickFilterWrap ? quickFilterWrap.querySelectorAll('[data-admin-doc-filter]') : [];
            let activeType = 'all';
            if (!adminSearchInput || !tableBody) {
                return;
            }
            function applyAdminDocFilter() {
                const keyword = adminSearchInput.value.toLowerCase().trim();
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(function (row) {
                    const fileName = row.getAttribute('data-file-name') || '';
                    const fileType = (row.getAttribute('data-file-type') || 'pdf').toLowerCase();
                    const typeOk = activeType === 'all' || fileType === activeType;
                    row.style.display = (fileName.includes(keyword) && typeOk) ? '' : 'none';
                });
            }
            adminSearchInput.addEventListener('input', function () {
                applyAdminDocFilter();
            });
            if (quickFilterBtns.length) {
                quickFilterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        activeType = String(btn.getAttribute('data-admin-doc-filter') || 'all').toLowerCase();
                        quickFilterBtns.forEach(function (x) {
                            x.classList.remove('active');
                        });
                        btn.classList.add('active');
                        applyAdminDocFilter();
                    });
                });
            }
            applyAdminDocFilter();
        }());

        (function () {
            const editButtons = document.querySelectorAll('.js-edit-person');
            const idInput = document.getElementById('edit_person_id');
            const nameInput = document.getElementById('edit_person_name');
            const nipInput = document.getElementById('edit_person_nip');
            const positionInput = document.getElementById('edit_person_position');
            if (!editButtons.length || !idInput || !nameInput || !nipInput || !positionInput) {
                return;
            }
            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    idInput.value = button.getAttribute('data-id') || '';
                    nameInput.value = button.getAttribute('data-name') || '';
                    nipInput.value = button.getAttribute('data-nip') || '';
                    positionInput.value = button.getAttribute('data-position') || '';
                });
            });
        }());

        (function () {
            var form = document.getElementById('formSaranPublik');
            if (!form) {
                return;
            }
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var nama = (document.getElementById('saran_nama').value || '').trim();
                var email = (document.getElementById('saran_email').value || '').trim();
                var pesan = (document.getElementById('saran_pesan').value || '').trim();
                var st = document.getElementById('saranPublikStatus');
                var btn = document.getElementById('btnSaranPublik');
                if (!nama || !email || !pesan) {
                    st.textContent = 'Lengkapi nama, email, dan pesan.';
                    st.className = 'site-footer-form__status small text-warning mt-2 mb-0';
                    return;
                }
                btn.disabled = true;
                st.textContent = 'Mengirim...';
                st.className = 'site-footer-form__status small text-secondary mt-2 mb-0';
                var endpoint = form.getAttribute('data-saran-endpoint') || form.getAttribute('action') || 'proses_saran.php';
                var csrf = typeof window.orgCsrfToken === 'function' ? window.orgCsrfToken() : '';
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ nama: nama, email: email, pesan: pesan, csrf_token: csrf }),
                })
                    .then(function (r) {
                        return r.text().then(function (t) {
                            var j = null;
                            try {
                                j = t ? JSON.parse(t) : null;
                            } catch (e) {
                                j = null;
                            }
                            return { ok: r.ok, j: j };
                        });
                    })
                    .then(function (x) {
                        if (x.j && x.j.ok) {
                            st.textContent = x.j.message || 'Terima kasih, saran Anda telah terkirim!';
                            st.className = 'site-footer-form__status small text-success fw-semibold mt-2 mb-0';
                            form.reset();
                        } else {
                            st.textContent = (x.j && x.j.message) ? x.j.message : 'Gagal mengirim.';
                            st.className = 'site-footer-form__status small text-danger mt-2 mb-0';
                        }
                    })
                    .catch(function () {
                        st.textContent = 'Kesalahan jaringan.';
                        st.className = 'site-footer-form__status small text-danger mt-2 mb-0';
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        }());

        (function () {
            try {
                var p = new URLSearchParams(window.location.search);
                if (p.get('saran') === 'sukses') {
                    window.alert('Terima kasih, saran Anda telah terkirim!');
                    p.delete('saran');
                    var q = p.toString();
                    window.history.replaceState({}, document.title, window.location.pathname + (q ? '?' + q : '') + window.location.hash);
                } else if (p.get('saran') === 'gagal') {
                    window.alert('Maaf, saran tidak dapat disimpan. Silakan periksa koneksi atau isian lalu coba lagi.');
                    p.delete('saran');
                    p.delete('alasan');
                    var q2 = p.toString();
                    window.history.replaceState({}, document.title, window.location.pathname + (q2 ? '?' + q2 : '') + window.location.hash);
                }
            } catch (e) { /* ignore */ }
        }());

        (function () {
            var root = document.getElementById('cursor-follower');
            if (!root || window.matchMedia('(pointer: coarse)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var ringSlot = root.querySelector('.cursor-follower__ring-slot');
            var dot = root.querySelector('.cursor-follower__dot');
            if (!ringSlot || !dot) {
                return;
            }
            root.removeAttribute('hidden');

            var targetX = -100;
            var targetY = -100;
            var ringX = -100;
            var ringY = -100;
            var dotX = -100;
            var dotY = -100;
            var ringLerp = 0.14;
            var dotLerp = 0.38;
            var raf = 0;

            var interactiveSel = [
                'a[href]',
                'button',
                '[role="button"]',
                'input[type="submit"]',
                'input[type="button"]',
                'input[type="reset"]',
                'input[type="checkbox"]',
                'input[type="radio"]',
                'select',
                'textarea',
                'summary',
                '.btn',
                'label[for]',
                '.site-header__nav a',
                '.site-footer__nav a',
                '.site-footer a',
                '.pi-portal-card',
                '.site-header-doc-search__submit',
                '.site-header-doc-search__input',
            ].join(',');

            function elementUnderPointer(x, y) {
                var el = document.elementFromPoint(x, y);
                if (!el || root.contains(el)) {
                    return null;
                }
                return el;
            }

            function setHover(on) {
                if (on) {
                    root.classList.add('is-hover');
                } else {
                    root.classList.remove('is-hover');
                }
            }

            function setDownloadHover(on) {
                if (on) {
                    root.classList.add('is-download-hover');
                } else {
                    root.classList.remove('is-download-hover');
                }
            }

            function updateHoverFromPoint(x, y) {
                var el = elementUnderPointer(x, y);
                if (!el) {
                    setHover(false);
                    setDownloadHover(false);
                    return;
                }
                if (el.closest('.js-digital-lib-download')) {
                    setDownloadHover(true);
                    setHover(true);
                    return;
                }
                setDownloadHover(false);
                setHover(!!el.closest(interactiveSel));
            }

            function tick() {
                ringX += (targetX - ringX) * ringLerp;
                ringY += (targetY - ringY) * ringLerp;
                dotX += (targetX - dotX) * dotLerp;
                dotY += (targetY - dotY) * dotLerp;
                ringSlot.style.transform = 'translate3d(' + ringX + 'px,' + ringY + 'px,0)';
                dot.style.transform = 'translate3d(' + dotX + 'px,' + dotY + 'px,0) translate(-50%,-50%)';
                raf = window.requestAnimationFrame(tick);
            }

            function onMove(e) {
                targetX = e.clientX;
                targetY = e.clientY;
                updateHoverFromPoint(targetX, targetY);
                if (!root.classList.contains('is-ready')) {
                    ringX = targetX;
                    ringY = targetY;
                    dotX = targetX;
                    dotY = targetY;
                    ringSlot.style.transform = 'translate3d(' + ringX + 'px,' + ringY + 'px,0)';
                    dot.style.transform = 'translate3d(' + dotX + 'px,' + dotY + 'px,0) translate(-50%,-50%)';
                    root.classList.add('is-ready');
                }
            }

            document.addEventListener('mousemove', onMove, { passive: true });
            document.addEventListener('scroll', function () {
                updateHoverFromPoint(targetX, targetY);
            }, { passive: true, capture: true });

            document.documentElement.addEventListener('mouseleave', function () {
                root.classList.add('is-hidden');
            });
            document.documentElement.addEventListener('mouseenter', function () {
                root.classList.remove('is-hidden');
            });

            raf = window.requestAnimationFrame(tick);
        }());

        (function () {
            var inpLib = document.getElementById('libraryDocumentSearch');
            var inpHead = document.getElementById('headerDocSearch');
            var tbody = document.getElementById('libraryDocTableBody');
            var emptyTbody = document.getElementById('libraryDocEmptyFilter');
            var clearBtn = document.getElementById('libraryDocumentSearchClear');
            var headerForm = document.getElementById('headerDocSearchForm');
            var catFilterBtns = document.querySelectorAll('.library-doc-category-filter__btn');
            var paginationRoot = document.getElementById('libraryDocPagination');
            var CAT_STORAGE_KEY = 'orgLibraryActiveCategory';
            var activeCat = 'semua';
            var currentPage = 1;
            var rowsPerPage = 10;
            var debounceTimer = null;
            var DEBOUNCE_MS = 220;

            if (!tbody) {
                return;
            }

            function escapeRegExp(s) {
                return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function escapeHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function highlightPlain(plain, q) {
                var esc = escapeHtml(plain || '');
                var t = q.trim().split(/\s+/).filter(Boolean);
                if (!t.length) {
                    return esc;
                }
                var pattern = t.map(escapeRegExp).join('|');
                var re = new RegExp('(' + pattern + ')', 'gi');
                return esc.replace(re, '<mark class="library-doc-hit">$1</mark>');
            }

            /** Selaras dengan org_dokumen_search_synonym_groups() di PHP */
            var DOC_SEARCH_SYN_GROUPS = [
                ['perbub', 'peraturan bupati'],
                ['perda', 'peraturan daerah'],
                ['perwali', 'peraturan walikota'],
                ['pergub', 'peraturan gubernur'],
                ['anjab', 'analisis jabatan'],
                ['evab', 'evaluasi abk'],
                ['abk', 'analisis beban kerja'],
                ['sakip', 'sistem akuntabilitas kinerja']
            ];

            function normalizeQueryToken(tok) {
                return String(tok).replace(/\.(pdf|docx?)$/i, '').trim().toLowerCase();
            }

            function uniqueStrings(arr) {
                var seen = {};
                var r = [];
                for (var i = 0; i < arr.length; i++) {
                    if (arr[i] && !seen[arr[i]]) {
                        seen[arr[i]] = true;
                        r.push(arr[i]);
                    }
                }
                return r;
            }

            function variantsForToken(tok) {
                var t = normalizeQueryToken(tok);
                if (!t) {
                    return [];
                }
                var out = [t];
                var g;
                var i;
                var j;
                for (g = 0; g < DOC_SEARCH_SYN_GROUPS.length; g++) {
                    var grp = DOC_SEARCH_SYN_GROUPS[g];
                    for (i = 0; i < grp.length; i++) {
                        if (t === String(grp[i]).toLowerCase()) {
                            for (j = 0; j < grp.length; j++) {
                                out.push(String(grp[j]).toLowerCase());
                            }
                            return uniqueStrings(out);
                        }
                    }
                }
                return uniqueStrings(out);
            }

            function tokenMatchesHaystack(hayLower, tok) {
                var vars = variantsForToken(tok);
                if (!vars.length) {
                    return true;
                }
                var vi;
                for (vi = 0; vi < vars.length; vi++) {
                    if (vars[vi] && hayLower.indexOf(vars[vi]) !== -1) {
                        return true;
                    }
                }
                return false;
            }

            function hayMatches(hayLower, q) {
                var raw = q.trim().split(/\s+/).filter(Boolean);
                if (!raw.length) {
                    return true;
                }
                var i;
                for (i = 0; i < raw.length; i++) {
                    if (!tokenMatchesHaystack(hayLower, raw[i])) {
                        return false;
                    }
                }
                return true;
            }

            function syncInputs(fromEl) {
                var v = fromEl ? fromEl.value : '';
                if (inpLib && fromEl !== inpLib) {
                    inpLib.value = v;
                }
                if (inpHead && fromEl !== inpHead) {
                    inpHead.value = v;
                }
            }

            function toggleClear() {
                if (!clearBtn) {
                    return;
                }
                var q = inpLib ? inpLib.value : (inpHead ? inpHead.value : '');
                if (q.length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }
            }

            function replaceUrlQuery(q) {
                if (!window.history || typeof window.history.replaceState !== 'function') {
                    return;
                }
                try {
                    var url = new URL(window.location.href);
                    var t = q.trim();
                    if (t === '') {
                        url.searchParams.delete('q');
                    } else {
                        url.searchParams.set('q', t);
                    }
                    window.history.replaceState({}, '', url.pathname + url.search + url.hash);
                } catch (e1) {
                    /* IE / file: fallback */
                }
            }

            function applyHighlights(q) {
                var rows = tbody.querySelectorAll('tr.js-lib-doc-row');
                rows.forEach(function (tr) {
                    if (tr.style.display === 'none') {
                        return;
                    }
                    var titleEl = tr.querySelector('.js-lib-doc-title-text');
                    var catEl = tr.querySelector('.js-lib-doc-cat-text');
                    var tPlain = tr.getAttribute('data-doc-title-plain') || '';
                    var cPlain = tr.getAttribute('data-doc-cat-plain') || '';
                    if (titleEl) {
                        titleEl.innerHTML = highlightPlain(tPlain, q);
                    }
                    if (catEl) {
                        catEl.innerHTML = highlightPlain(cPlain, q);
                    }
                });
            }

            function applyFilter() {
                var inp = inpLib || inpHead;
                if (!inp) {
                    return;
                }
                var q = inp.value;
                var rows = tbody.querySelectorAll('tr[data-lib-filter]');
                var visibleRows = [];
                rows.forEach(function (tr) {
                    var hay = (tr.getAttribute('data-lib-filter') || '').toLowerCase();
                    var rowCat = (tr.getAttribute('data-doc-teamcat') || '').toLowerCase();
                    var catOk = activeCat === 'semua' || activeCat === rowCat;
                    var ok = hayMatches(hay, q) && catOk;
                    if (ok) {
                        visibleRows.push(tr);
                    }
                });
                var totalVisible = visibleRows.length;
                var totalPages = Math.max(1, Math.ceil(totalVisible / rowsPerPage));
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }
                if (currentPage < 1) {
                    currentPage = 1;
                }
                var pageStart = (currentPage - 1) * rowsPerPage;
                var pageEnd = pageStart + rowsPerPage;
                rows.forEach(function (tr) {
                    tr.style.display = 'none';
                });
                visibleRows.forEach(function (tr, idx) {
                    var isOnPage = idx >= pageStart && idx < pageEnd;
                    tr.style.display = isOnPage ? '' : 'none';
                    if (isOnPage) {
                        var noCell = tr.querySelector('.js-lib-doc-no');
                        if (noCell) {
                            noCell.textContent = String(idx + 1);
                        }
                    }
                });
                if (emptyTbody) {
                    if (rows.length === 0) {
                        emptyTbody.classList.add('d-none');
                    } else {
                        emptyTbody.classList.toggle('d-none', totalVisible !== 0);
                    }
                }
                renderPagination(totalVisible, totalPages);
                applyHighlights(q);
                toggleClear();
                replaceUrlQuery(q);
            }

            function makePageButton(label, page, isActive, isDisabled) {
                var li = document.createElement('li');
                li.className = 'library-doc-pagination__item';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'library-doc-pagination__btn' + (isActive ? ' is-active' : '');
                btn.textContent = label;
                btn.disabled = !!isDisabled;
                if (!isDisabled && !isActive) {
                    btn.addEventListener('click', function () {
                        currentPage = page;
                        applyFilter();
                        var sec = document.querySelector('.doc-center-table-wrap')
                            || document.querySelector('.digital-library__table-wrap');
                        if (sec) {
                            sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    });
                }
                li.appendChild(btn);
                return li;
            }

            function renderPagination(totalVisible, totalPages) {
                if (!paginationRoot) {
                    return;
                }
                paginationRoot.innerHTML = '';
                if (totalVisible <= rowsPerPage) {
                    return;
                }
                paginationRoot.appendChild(makePageButton('Prev', currentPage - 1, false, currentPage <= 1));
                for (var p = 1; p <= totalPages; p++) {
                    paginationRoot.appendChild(makePageButton(String(p), p, p === currentPage, false));
                }
                paginationRoot.appendChild(makePageButton('Next', currentPage + 1, false, currentPage >= totalPages));
            }

            function scheduleApply(fromEl) {
                if (fromEl) {
                    syncInputs(fromEl);
                }
                currentPage = 1;
                clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(applyFilter, DEBOUNCE_MS);
            }

            if (inpLib) {
                inpLib.addEventListener('input', function () {
                    scheduleApply(inpLib);
                });
                inpLib.addEventListener('search', function () {
                    syncInputs(inpLib);
                    applyFilter();
                });
            }
            if (inpHead) {
                inpHead.addEventListener('input', function () {
                    scheduleApply(inpHead);
                });
                inpHead.addEventListener('search', function () {
                    syncInputs(inpHead);
                    applyFilter();
                });
            }

            if (headerForm && inpLib) {
                headerForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (inpHead) {
                        syncInputs(inpHead);
                    }
                    currentPage = 1;
                    applyFilter();
                    var sec = document.getElementById('beranda-library-dokumen')
                        || document.querySelector('.doc-center-table-wrap')
                        || document.querySelector('.digital-library--intl');
                    if (sec) {
                        sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    if (inpLib) {
                        inpLib.focus({ preventScroll: true });
                    }
                });
            }

            if (clearBtn && inpLib) {
                clearBtn.addEventListener('click', function () {
                    inpLib.value = '';
                    syncInputs(inpLib);
                    inpLib.focus();
                    currentPage = 1;
                    applyFilter();
                });
            }
            var submitBtn = document.getElementById('libraryDocumentSearchSubmit');
            if (submitBtn && inpLib) {
                submitBtn.addEventListener('click', function () {
                    currentPage = 1;
                    applyFilter();
                    inpLib.focus({ preventScroll: true });
                });
            }
            if (catFilterBtns && catFilterBtns.length) {
                try {
                    var storedCat = String(window.localStorage.getItem(CAT_STORAGE_KEY) || '').toLowerCase();
                    var hasStored = false;
                    if (storedCat) {
                        catFilterBtns.forEach(function (btn0) {
                            var c0 = String(btn0.getAttribute('data-lib-cat') || '').toLowerCase();
                            if (c0 === storedCat) {
                                hasStored = true;
                            }
                        });
                    }
                    if (hasStored) {
                        activeCat = storedCat;
                        catFilterBtns.forEach(function (b0) {
                            var c1 = String(b0.getAttribute('data-lib-cat') || '').toLowerCase();
                            var on = c1 === activeCat;
                            b0.classList.toggle('is-active', on);
                            b0.setAttribute('aria-selected', on ? 'true' : 'false');
                        });
                    }
                } catch (eStorageRead) {
                    /* ignore localStorage read error */
                }
                catFilterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        activeCat = String(btn.getAttribute('data-lib-cat') || 'semua').toLowerCase();
                        catFilterBtns.forEach(function (b2) {
                            b2.classList.remove('is-active');
                            b2.setAttribute('aria-selected', 'false');
                        });
                        btn.classList.add('is-active');
                        btn.setAttribute('aria-selected', 'true');
                        try {
                            window.localStorage.setItem(CAT_STORAGE_KEY, activeCat);
                        } catch (eStorageWrite) {
                            /* ignore localStorage write error */
                        }
                        currentPage = 1;
                        applyFilter();
                    });
                });
            }
            applyFilter();
        }());
        (function () {
            if (document.body.classList.contains('sg-portal-page')) {
                return;
            }
            var navWrap = document.querySelector('.site-header__nav-wrap');
            if (!navWrap) {
                return;
            }
            var desktopMq = window.matchMedia('(min-width: 992px)');
            var spacer = document.createElement('div');
            spacer.className = 'site-header__nav-wrap-spacer';
            navWrap.parentNode.insertBefore(spacer, navWrap.nextSibling);
            var lockStart = 0;
            function syncScrolled() {
                var scrolled = window.scrollY > 8 || navWrap.classList.contains('is-features-fixed');
                navWrap.classList.toggle('is-nav-scrolled', scrolled);
            }
            function syncElevated() {
                /* Hanya saat floating fixed — hindari perubahan warna/shadow saat scroll awal */
                navWrap.classList.toggle('is-nav-elevated', navWrap.classList.contains('is-features-fixed'));
            }
            function resetFixed() {
                navWrap.classList.remove('is-features-fixed');
                spacer.classList.remove('is-active');
                spacer.style.height = '0px';
                syncElevated();
                syncScrolled();
            }
            function recalcStart() {
                resetFixed();
                var rect = navWrap.getBoundingClientRect();
                lockStart = window.scrollY + rect.top;
            }
            function syncFixed() {
                if (!desktopMq.matches) {
                    resetFixed();
                    return;
                }
                if (window.scrollY >= lockStart) {
                    if (!navWrap.classList.contains('is-features-fixed')) {
                        spacer.style.height = navWrap.offsetHeight + 'px';
                        spacer.classList.add('is-active');
                        navWrap.classList.add('is-features-fixed');
                    }
                } else {
                    resetFixed();
                }
                syncElevated();
                syncScrolled();
            }
            window.addEventListener('resize', function () {
                recalcStart();
                syncFixed();
            });
            window.addEventListener('scroll', function () {
                syncFixed();
                syncElevated();
                syncScrolled();
            }, { passive: true });
            recalcStart();
            syncFixed();
            syncElevated();
            syncScrolled();
        }());

        (function () {
            function csrfTokenFromMeta() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? (meta.getAttribute('content') || '') : '';
            }
            function syncCsrfInputs() {
                var token = csrfTokenFromMeta();
                if (token === '') {
                    return token;
                }
                document.querySelectorAll('input[name="csrf_token"]').forEach(function (input) {
                    input.value = token;
                });
                return token;
            }
            document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    syncCsrfInputs();
                }, true);
            });
            var loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.addEventListener('show.bs.modal', syncCsrfInputs);
            }
            syncCsrfInputs();
            window.orgCsrfToken = csrfTokenFromMeta;
            window.orgSyncCsrfInputs = syncCsrfInputs;
        }());
    </script>
