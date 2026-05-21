<?php
declare(strict_types=1);
/** @var bool $isSubAdminPublikasiActor */
?>
<script>
(function () {
    'use strict';

    var monitoring = document.getElementById('sgMonitoring');
    var workspace = document.getElementById('sgWorkspace');
    var wsBody = document.getElementById('sgWorkspaceBody');
    var titleEl = document.getElementById('sgWorkspaceTitle');
    var subEl = document.getElementById('sgWorkspaceSubtitle');
    var crumb = document.getElementById('sgBreadcrumbCurrent');
    var navItems = document.querySelectorAll('#sgSidebarNav [data-sg-module], #sgSidebarNav [href][data-sg-label]');

    var moduleMeta = {
        monitoring: { title: 'Dashboard Monitoring', sub: 'Analytics & KPI real-time' },
        layanan: { title: 'Layanan Publik', sub: 'Berita, galeri, visi-misi, layanan' },
        dokumen: { title: 'Dokumen', sub: 'Unggah, kelola, statistik perpustakaan' },
        pegawai: { title: 'Pegawai', sub: 'Manajemen akun staf' },
        audit: { title: 'Audit', sub: 'Riwayat aktivitas sistem' },
        pengaturan: { title: 'Pengaturan', sub: 'Konfigurasi dan tautan sistem' }
    };

    var moduleIds = {
        layanan: ['panel-konten-tabs'],
        dokumen: ['panel-unggah-dokumen', 'panel-kelola-dokumen', 'panel-digital-library-stats'],
        pegawai: ['panel-manajemen-staf'],
        audit: ['panel-audit'],
        pengaturan: ['sg-op-pengaturan']
    };

    function setActiveNav(el) {
        document.querySelectorAll('#sgSidebarNav .sg-nav-item').forEach(function (n) {
            n.classList.remove('is-active');
        });
        if (el) el.classList.add('is-active');
    }

    function showMonitoring(scrollId) {
        if (monitoring) {
            monitoring.hidden = false;
            monitoring.classList.add('sg-view--active');
        }
        if (workspace) {
            workspace.hidden = true;
            workspace.classList.remove('sg-view--active');
        }
        if (wsBody) {
            wsBody.className = 'sg-workspace-body';
        }
        if (crumb) crumb.textContent = 'Monitoring';
        if (scrollId) {
            var target = document.getElementById(scrollId);
            if (target) {
                window.setTimeout(function () {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 80);
            }
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function showWorkspace(mod) {
        if (monitoring) {
            monitoring.hidden = true;
            monitoring.classList.remove('sg-view--active');
        }
        if (workspace) {
            workspace.hidden = false;
            workspace.classList.add('sg-view--active');
        }
        if (wsBody) {
            wsBody.className = 'sg-workspace-body sg-ws-mod-' + mod;
        }
        var meta = moduleMeta[mod] || { title: 'Modul', sub: '' };
        if (titleEl) titleEl.textContent = meta.title;
        if (subEl) subEl.textContent = meta.sub;
        if (crumb) crumb.textContent = meta.title;
        window.scrollTo({ top: 0, behavior: 'smooth' });

        var firstId = (moduleIds[mod] || [])[0];
        if (firstId) {
            var first = document.getElementById(firstId);
            if (first) first.style.scrollMarginTop = '100px';
        }
    }

    function activate(link) {
        if (!link) return;
        var mod = link.getAttribute('data-sg-module');
        var scrollId = link.getAttribute('data-sg-scroll');
        var href = link.getAttribute('href') || '';
        if (href && href.indexOf('.php') !== -1 && !mod) {
            return;
        }
        setActiveNav(link);
        if (!mod || mod === 'monitoring') {
            showMonitoring(scrollId || '');
            return;
        }
        showWorkspace(mod);
    }

    navItems.forEach(function (link) {
        link.addEventListener('click', function (ev) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf('.php') !== -1 && !link.getAttribute('data-sg-module')) {
                return;
            }
            ev.preventDefault();
            activate(link);
            var app = document.getElementById('sgApp');
            if (app) app.classList.remove('is-sidebar-open');
            var backdrop = document.getElementById('sgSidebarBackdrop');
            if (backdrop) backdrop.hidden = true;
        });
    });

    document.querySelectorAll('[data-sg-module]').forEach(function (btn) {
        if (btn.closest('#sgSidebarNav')) return;
        btn.addEventListener('click', function (ev) {
            var mod = btn.getAttribute('data-sg-module');
            if (!mod) return;
            ev.preventDefault();
            var side = document.querySelector('#sgSidebarNav [data-sg-module="' + mod + '"]');
            activate(side || btn);
        });
    });

    var backBtn = document.querySelector('[data-sg-back-monitor]');
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            var dash = document.querySelector('#sgSidebarNav [data-sg-module="monitoring"]');
            activate(dash);
        });
    }

    var searchInput = document.getElementById('sgGlobalSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = (searchInput.value || '').toLowerCase().trim();
            document.querySelectorAll('#sgSidebarNav .sg-nav-item').forEach(function (item) {
                var label = (item.textContent || '').toLowerCase();
                item.classList.toggle('sg-search-hidden', q.length > 0 && label.indexOf(q) === -1);
            });
        });
    }

    window.sgShowModule = showWorkspace;
    window.sgShowMonitoring = showMonitoring;

    <?php if ($isSubAdminPublikasiActor): ?>
    showWorkspace('layanan');
    var layananLink = document.querySelector('#sgSidebarNav [data-sg-module="layanan"]');
    setActiveNav(layananLink);
    <?php else: ?>
    showMonitoring('');
    <?php endif; ?>
}());
</script>
