/**
 * Tombol share Pusat Informasi (berita & pengumuman).
 *
 * Strategi:
 *   1. Klik tombol [data-org-share] ⇒ stop propagation (jangan ikut buka link
 *      kartu) ⇒ panggil navigator.share() bila tersedia (mobile/HTTPS).
 *   2. Bila tidak tersedia / user batalkan ⇒ tampilkan popover internal
 *      dengan opsi WhatsApp / Facebook / X / Telegram / Email / Copy Link.
 *   3. Copy link menggunakan Clipboard API + fallback textarea, lalu tampil
 *      toast singkat.
 */
(function () {
    'use strict';

    if (window.__orgShareBooted) {
        return;
    }
    window.__orgShareBooted = true;

    var refs = { backdrop: null, popover: null, toast: null, toastTimer: 0, lastTrigger: null };

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildSocialUrl(target, title, text, url) {
        var t = encodeURIComponent(title || '');
        var u = encodeURIComponent(url || '');
        var body = encodeURIComponent((text ? text + '\n\n' : '') + (url || ''));
        switch (target) {
            case 'whatsapp':
                return 'https://api.whatsapp.com/send?text=' + body;
            case 'facebook':
                return 'https://www.facebook.com/sharer/sharer.php?u=' + u;
            case 'x':
                return 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title || text || '') + '&url=' + u;
            case 'telegram':
                return 'https://t.me/share/url?url=' + u + '&text=' + encodeURIComponent(text || title || '');
            case 'email':
                return 'mailto:?subject=' + t + '&body=' + body;
            default:
                return '';
        }
    }

    function ensurePopover() {
        if (refs.popover) {
            return refs.popover;
        }
        var backdrop = document.createElement('div');
        backdrop.className = 'org-share-popover-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(backdrop);
        refs.backdrop = backdrop;

        var pop = document.createElement('div');
        pop.className = 'org-share-popover';
        pop.setAttribute('role', 'dialog');
        pop.setAttribute('aria-modal', 'true');
        pop.setAttribute('aria-labelledby', 'orgShareTitle');
        pop.innerHTML =
            '<div class="org-share-popover__head">'
                + '<h3 id="orgShareTitle" class="org-share-popover__title">Bagikan</h3>'
                + '<button type="button" class="org-share-popover__close" aria-label="Tutup dialog bagikan">'
                    + '<i class="fa-solid fa-xmark" aria-hidden="true"></i>'
                + '</button>'
            + '</div>'
            + '<div class="org-share-popover__body">'
                + '<div class="org-share-popover__url">'
                    + '<span class="org-share-popover__url-text" data-share-url-text></span>'
                    + '<button type="button" class="org-share-popover__copy" data-share-copy>'
                        + '<i class="fa-regular fa-copy" aria-hidden="true"></i>'
                        + '<span data-share-copy-label>Salin</span>'
                    + '</button>'
                + '</div>'
                + '<div class="org-share-popover__grid" data-share-grid></div>'
            + '</div>';
        document.body.appendChild(pop);
        refs.popover = pop;

        var grid = pop.querySelector('[data-share-grid]');
        var items = [
            { id: 'whatsapp', label: 'WhatsApp', icon: 'fa-brands fa-whatsapp' },
            { id: 'facebook', label: 'Facebook', icon: 'fa-brands fa-facebook-f' },
            { id: 'x',        label: 'X',        icon: 'fa-brands fa-x-twitter' },
            { id: 'telegram', label: 'Telegram', icon: 'fa-brands fa-telegram' },
            { id: 'email',    label: 'Email',    icon: 'fa-solid fa-envelope' },
            { id: 'copy',     label: 'Salin URL', icon: 'fa-solid fa-link' }
        ];
        items.forEach(function (item) {
            var node;
            if (item.id === 'copy') {
                node = document.createElement('button');
                node.type = 'button';
                node.setAttribute('data-share-target', 'copy');
            } else {
                node = document.createElement('a');
                node.target = '_blank';
                node.rel = 'noopener noreferrer';
                node.setAttribute('data-share-target', item.id);
            }
            node.className = 'org-share-popover__item';
            node.innerHTML =
                '<span class="org-share-popover__item-icon org-share-popover__item-icon--' + item.id + '">'
                    + '<i class="' + item.icon + '" aria-hidden="true"></i>'
                + '</span>'
                + '<span>' + escapeHtml(item.label) + '</span>';
            grid.appendChild(node);
        });

        backdrop.addEventListener('click', closePopover);
        pop.querySelector('.org-share-popover__close').addEventListener('click', closePopover);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && pop.classList.contains('is-open')) {
                closePopover();
            }
        });

        grid.addEventListener('click', function (e) {
            var copyBtn = e.target.closest('[data-share-target="copy"]');
            if (copyBtn) {
                e.preventDefault();
                var url = pop.getAttribute('data-share-url') || '';
                copyToClipboard(url).then(function (ok) {
                    if (ok) {
                        showToast('Link berhasil disalin');
                        closePopover();
                    } else {
                        showToast('Gagal menyalin link');
                    }
                });
            }
            /* Untuk anchor sosial: biarkan default (buka tab baru). */
        });

        pop.querySelector('[data-share-copy]').addEventListener('click', function () {
            var url = pop.getAttribute('data-share-url') || '';
            var label = pop.querySelector('[data-share-copy-label]');
            var btn = pop.querySelector('[data-share-copy]');
            copyToClipboard(url).then(function (ok) {
                if (!ok) {
                    showToast('Gagal menyalin link');
                    return;
                }
                if (label) label.textContent = 'Tersalin';
                if (btn) btn.classList.add('is-copied');
                setTimeout(function () {
                    if (label) label.textContent = 'Salin';
                    if (btn) btn.classList.remove('is-copied');
                }, 1800);
            });
        });

        return pop;
    }

    function openPopover(data, triggerEl) {
        var pop = ensurePopover();
        refs.lastTrigger = triggerEl || null;

        pop.setAttribute('data-share-url', data.url || '');
        var titleEl = pop.querySelector('#orgShareTitle');
        if (titleEl) {
            titleEl.textContent = data.title ? ('Bagikan: ' + data.title) : 'Bagikan';
        }
        var urlTextEl = pop.querySelector('[data-share-url-text]');
        if (urlTextEl) {
            urlTextEl.textContent = data.url || '';
            urlTextEl.title = data.url || '';
        }
        /* Update href anchor sosial dengan URL final. */
        pop.querySelectorAll('a[data-share-target]').forEach(function (a) {
            var target = a.getAttribute('data-share-target');
            a.href = buildSocialUrl(target, data.title, data.text, data.url);
        });
        /* Reset state copy button */
        var label = pop.querySelector('[data-share-copy-label]');
        var copyBtn = pop.querySelector('[data-share-copy]');
        if (label) label.textContent = 'Salin';
        if (copyBtn) copyBtn.classList.remove('is-copied');

        if (refs.backdrop) refs.backdrop.classList.add('is-visible');
        pop.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        var firstFocusable = pop.querySelector('.org-share-popover__close');
        if (firstFocusable) {
            setTimeout(function () { firstFocusable.focus(); }, 40);
        }
    }

    function closePopover() {
        if (!refs.popover) return;
        refs.popover.classList.remove('is-open');
        if (refs.backdrop) refs.backdrop.classList.remove('is-visible');
        document.body.style.overflow = '';
        if (refs.lastTrigger && typeof refs.lastTrigger.focus === 'function') {
            refs.lastTrigger.focus();
        }
        refs.lastTrigger = null;
    }

    function copyToClipboard(text) {
        if (!text) {
            return Promise.resolve(false);
        }
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(function () {
                return true;
            }).catch(function () {
                return fallbackCopy(text);
            });
        }
        return Promise.resolve(fallbackCopy(text));
    }

    function fallbackCopy(text) {
        try {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            var ok = document.execCommand('copy');
            document.body.removeChild(ta);
            return !!ok;
        } catch (e) {
            return false;
        }
    }

    function showToast(message) {
        if (!refs.toast) {
            refs.toast = document.createElement('div');
            refs.toast.className = 'org-share-toast';
            refs.toast.setAttribute('role', 'status');
            refs.toast.setAttribute('aria-live', 'polite');
            document.body.appendChild(refs.toast);
        }
        refs.toast.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span></span>';
        refs.toast.querySelector('span').textContent = message;
        clearTimeout(refs.toastTimer);
        requestAnimationFrame(function () {
            refs.toast.classList.add('is-visible');
        });
        refs.toastTimer = setTimeout(function () {
            refs.toast.classList.remove('is-visible');
        }, 2200);
    }

    function attemptNativeShare(data, triggerEl) {
        if (!navigator.share) {
            return Promise.resolve(false);
        }
        var shareData = { title: data.title || '', text: data.text || '', url: data.url || '' };
        return navigator.share(shareData).then(function () {
            return true;
        }).catch(function (err) {
            /* AbortError = user cancel: jangan fallback ke popover. */
            if (err && (err.name === 'AbortError' || err.code === 20)) {
                return true;
            }
            return false;
        });
    }

    function handleShareClick(e) {
        var btn = e.target.closest('[data-org-share]');
        if (!btn) return;
        /* Tombol mungkin berada di dalam <a> kartu — cegah navigasi. */
        e.preventDefault();
        e.stopPropagation();

        var data = {
            title: btn.getAttribute('data-org-share-title') || document.title || '',
            text: btn.getAttribute('data-org-share-text') || '',
            url: btn.getAttribute('data-org-share-url') || window.location.href
        };

        attemptNativeShare(data, btn).then(function (handled) {
            if (!handled) {
                openPopover(data, btn);
            }
        });
    }

    function boot() {
        document.addEventListener('click', handleShareClick, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
