<?php
declare(strict_types=1);

/** @var array<string, mixed> $dashMetrics */

$orgScore = (int) ($dashMetrics['org_score'] ?? 0);
$kpiPelayanan = (int) ($dashMetrics['kpi_pelayanan'] ?? 0);
$heatmapJson = json_encode($dashMetrics['heatmap_series'] ?? [], JSON_UNESCAPED_UNICODE);
$targetLabels = json_encode($dashMetrics['target_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$targetValues = json_encode($dashMetrics['target_values'] ?? [], JSON_UNESCAPED_UNICODE);
$realisasiValues = json_encode($dashMetrics['realisasi_values'] ?? [], JSON_UNESCAPED_UNICODE);
$hasHeatmap = count($dashMetrics['heatmap_series'] ?? []) > 0;
$hasTarget = count($dashMetrics['target_labels'] ?? []) > 0;
?>
<script>
(function () {
    'use strict';

    var SG_PRIMARY = '#1D4ED8';
    var SG_SECONDARY = '#2563EB';
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var chartFg = isDark ? '#94a3b8' : '#64748B';
    var chartGrid = isDark ? '#334155' : '#E2E8F0';

    function sgChartFont() {
        return { fontFamily: 'Inter, system-ui, sans-serif' };
    }

    function sgBaseChart(type, height) {
        return {
            type: type,
            height: height,
            fontFamily: 'Inter, system-ui, sans-serif',
            foreColor: chartFg,
            background: 'transparent',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        };
    }

    var sgCharts = [];

    function render(el, opts) {
        if (!el || typeof ApexCharts === 'undefined') return null;
        var ch = new ApexCharts(el, opts);
        ch.render();
        sgCharts.push(ch);
        return ch;
    }

    /* CSS gauge animation */
    document.querySelectorAll('[data-sg-gauge]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-sg-gauge') || '0', 10);
        var start = 0;
        var duration = 1000;
        var t0 = performance.now();
        function tick(now) {
            var p = Math.min(1, (now - t0) / duration);
            var eased = 1 - Math.pow(1 - p, 3);
            var val = Math.round(start + (target - start) * eased);
            el.style.setProperty('--sg-pct', String(val));
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    document.querySelectorAll('[data-sg-gauge-counter]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-sg-gauge-counter') || '0', 10);
        var start = 0;
        var duration = 900;
        var t0 = performance.now();
        function tick(now) {
            var p = Math.min(1, (now - t0) / duration);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = String(Math.round(start + (target - start) * eased));
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    /* Grafik heatmap/target dipindah ke dashboard_monitoring_charts.php — hindari render ganda */

    /* Animated counters (KPI cards) */
    document.querySelectorAll('[data-sg-counter]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-sg-counter') || '0', 10);
        var start = 0;
        var duration = 900;
        var t0 = performance.now();
        function tick(now) {
            var p = Math.min(1, (now - t0) / duration);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (target - start) * eased).toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    /* Progress bars */
    window.setTimeout(function () {
        document.querySelectorAll('[data-sg-progress]').forEach(function (bar) {
            var pct = bar.getAttribute('data-sg-progress') || '0';
            bar.style.width = pct + '%';
        });
    }, 200);

    /* Shell: clock */
    function updateClock() {
        var wrap = document.getElementById('sgRealtimeClock');
        if (!wrap) return;
        var now = new Date();
        var timeEl = wrap.querySelector('.sg-clock__time');
        var dateEl = wrap.querySelector('.sg-clock__date');
        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        }
    }
    updateClock();
    window.setInterval(updateClock, 1000);

    /* Theme toggle */
    var themeBtn = document.getElementById('sgThemeToggle');
    var storedTheme = localStorage.getItem('org-color-theme') || localStorage.getItem('sg-dashboard-theme');
    if (storedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else if (storedTheme === 'light') {
        document.documentElement.removeAttribute('data-theme');
    }
    function syncThemeIcons() {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('.sg-theme-icon-dark').forEach(function (i) {
            i.classList.toggle('d-none', dark);
        });
        document.querySelectorAll('.sg-theme-icon-light').forEach(function (i) {
            i.classList.toggle('d-none', !dark);
        });
    }
    syncThemeIcons();
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            if (next === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
            localStorage.setItem('sg-dashboard-theme', next);
            localStorage.setItem('org-color-theme', next);
            syncThemeIcons();
            window.location.reload();
        });
    }

    /* Sidebar mobile / collapse */
    var app = document.getElementById('sgApp');
    var backdrop = document.getElementById('sgSidebarBackdrop');
    var toggleBtn = document.getElementById('sgSidebarToggle');
    var collapseBtn = document.getElementById('sgSidebarCollapse');

    if (localStorage.getItem('sg-sidebar-collapsed') === '1' && app) {
        app.classList.add('is-sidebar-collapsed');
    }

    function closeMobileSidebar() {
        if (app) app.classList.remove('is-sidebar-open');
        if (backdrop) backdrop.hidden = true;
    }

    if (toggleBtn && app) {
        toggleBtn.addEventListener('click', function () {
            app.classList.toggle('is-sidebar-open');
            if (backdrop) backdrop.hidden = !app.classList.contains('is-sidebar-open');
        });
    }
    if (backdrop) {
        backdrop.addEventListener('click', closeMobileSidebar);
    }
    if (collapseBtn && app) {
        collapseBtn.addEventListener('click', function () {
            app.classList.toggle('is-sidebar-collapsed');
            localStorage.setItem('sg-sidebar-collapsed', app.classList.contains('is-sidebar-collapsed') ? '1' : '0');
        });
    }

    /* Global search — filter nav */
    var searchInput = document.getElementById('sgGlobalSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = (searchInput.value || '').toLowerCase().trim();
            document.querySelectorAll('#sgSidebar .sg-nav-item').forEach(function (item) {
                var label = (item.textContent || '').toLowerCase();
                item.classList.toggle('sg-search-hidden', q.length > 0 && label.indexOf(q) === -1);
            });
        });
    }

    /* Breadcrumb helper */
    window.sgUpdateBreadcrumb = function (label) {
        var el = document.getElementById('sgBreadcrumbCurrent');
        if (el && label) el.textContent = label;
    };

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }
}());
</script>
