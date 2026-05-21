<?php
declare(strict_types=1);
/** @var array<string, mixed> $dashMetrics @var bool $isSubAdminPublikasiActor */

$chartTamuLabels = json_encode($dashMetrics['tamu_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$chartTamuValues = json_encode($dashMetrics['tamu_values'] ?? [], JSON_UNESCAPED_UNICODE);
$chartDlLabels = json_encode($dashMetrics['download_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$chartDlValues = json_encode($dashMetrics['download_values'] ?? [], JSON_UNESCAPED_UNICODE);
$heatmapJson = json_encode($dashMetrics['heatmap_series'] ?? [], JSON_UNESCAPED_UNICODE);
$targetLabels = json_encode($dashMetrics['target_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$targetValues = json_encode($dashMetrics['target_values'] ?? [], JSON_UNESCAPED_UNICODE);
$realisasiValues = json_encode($dashMetrics['realisasi_values'] ?? [], JSON_UNESCAPED_UNICODE);
$timLabels = json_encode($dashMetrics['team_tim_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$timPct = json_encode($dashMetrics['team_tim_pct'] ?? [], JSON_UNESCAPED_UNICODE);
$donutLabels = json_encode(['Dokumen', 'Informasi', 'Galeri', 'Layanan'], JSON_UNESCAPED_UNICODE);
$donutValues = json_encode([
    (int) ($dashMetrics['dokumen_total'] ?? 0),
    (int) ($dashMetrics['berita_total'] ?? 0) + (int) ($dashMetrics['pengumuman_total'] ?? 0),
    (int) ($dashMetrics['galeri_total'] ?? 0),
    (int) ($dashMetrics['layanan_total'] ?? 0),
], JSON_UNESCAPED_UNICODE);
$hasHeatmap = count($dashMetrics['heatmap_series'] ?? []) > 0;
$hasTarget = count($dashMetrics['target_labels'] ?? []) > 0;
$showTamu = !$isSubAdminPublikasiActor;
?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
(function () {
    'use strict';
    if (typeof ApexCharts === 'undefined') return;

    var P = '#1D4ED8';
    var S = '#2563EB';
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    var fg = dark ? '#94a3b8' : '#64748B';
    var grid = dark ? '#334155' : '#E2E8F0';

    function base(type, h) {
        return {
            type: type,
            height: h,
            fontFamily: 'Inter, system-ui, sans-serif',
            foreColor: fg,
            background: 'transparent',
            toolbar: { show: false },
            animations: { enabled: true, speed: 700, easing: 'easeinout' }
        };
    }

    function render(id, opts) {
        var el = document.getElementById(id);
        if (!el || typeof ApexCharts === 'undefined') return null;
        el.innerHTML = '';
        var ch = new ApexCharts(el, opts);
        ch.render();
        el._sgChart = ch;
        return ch;
    }

    <?php if ($showTamu): ?>
    render('sgChartTrend', {
        series: [{ name: 'Kunjungan', data: <?php echo $chartTamuValues; ?> }],
        chart: base('area', 300),
        colors: [P],
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: 0.45, opacityTo: 0.05, shade: 'light' }
        },
        dataLabels: { enabled: false },
        xaxis: { categories: <?php echo $chartTamuLabels; ?> },
        yaxis: { min: 0 },
        grid: { borderColor: grid, strokeDashArray: 4 }
    });
    <?php endif; ?>

    render('sgChartDonut', {
        series: <?php echo $donutValues; ?>,
        chart: base('donut', 280),
        labels: <?php echo $donutLabels; ?>,
        colors: [P, '#8B5CF6', '#F59E0B', '#10B981'],
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '12px' },
                        value: { show: true, fontSize: '22px', fontWeight: 700 },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false }
    });

    <?php if ($hasTarget): ?>
    render('sgChartTargetRealisasi', {
        series: [
            { name: 'Target', data: <?php echo $targetValues; ?> },
            { name: 'Realisasi', data: <?php echo $realisasiValues; ?> }
        ],
        chart: base('bar', 280),
        colors: [S, '#10B981'],
        plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 8 } },
        xaxis: { categories: <?php echo $targetLabels; ?> },
        yaxis: { max: 100 },
        dataLabels: { enabled: false },
        legend: { position: 'top' }
    });
    <?php endif; ?>

    render('sgChartProgressKerja', {
        series: <?php echo $timPct; ?>,
        chart: base('radialBar', 280),
        labels: <?php echo $timLabels; ?>,
        colors: [P, '#8B5CF6', '#10B981'],
        plotOptions: {
            radialBar: {
                hollow: { size: '42%' },
                track: { background: grid },
                dataLabels: {
                    name: { fontSize: '11px' },
                    value: { fontSize: '16px', fontWeight: 700, formatter: function (v) { return Math.round(v) + '%'; } },
                    total: {
                        show: true,
                        label: 'Rata-rata',
                        formatter: function (w) {
                            var s = w.globals.series;
                            if (!s.length) return '0%';
                            var sum = s.reduce(function (a, b) { return a + b; }, 0);
                            return Math.round(sum / s.length) + '%';
                        }
                    }
                }
            }
        }
    });

    <?php if (count($dashMetrics['download_labels'] ?? []) > 0): ?>
    render('admChartDownloads', {
        series: [{ name: 'Unduhan', data: <?php echo $chartDlValues; ?> }],
        chart: base('bar', 280),
        colors: [P],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
        xaxis: { categories: <?php echo $chartDlLabels; ?>, labels: { rotate: -25 } },
        dataLabels: { enabled: false },
        grid: { borderColor: grid }
    });
    <?php endif; ?>

    <?php if ($hasHeatmap): ?>
    render('sgChartHeatmap', {
        series: <?php echo $heatmapJson; ?>,
        chart: Object.assign(base('heatmap', 300), { type: 'heatmap' }),
        dataLabels: { enabled: false },
        colors: [P],
        xaxis: { categories: ['00-03', '03-06', '06-09', '09-12', '12-15', '15-18', '18-21', '21-24'] },
        plotOptions: {
            heatmap: {
                radius: 6,
                colorScale: {
                    ranges: [
                        { from: 0, to: 0, color: grid },
                        { from: 1, to: 4, color: '#93C5FD' },
                        { from: 5, to: 10, color: S },
                        { from: 11, to: 100, color: P }
                    ]
                }
            }
        }
    });
    <?php endif; ?>
}());

(function () {
    'use strict';

    document.querySelectorAll('[data-sg-counter]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-sg-counter') || '0', 10);
        var t0 = performance.now();
        var duration = 900;
        function tick(now) {
            var p = Math.min(1, (now - t0) / duration);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    window.setTimeout(function () {
        document.querySelectorAll('[data-sg-progress]').forEach(function (bar) {
            bar.style.width = (bar.getAttribute('data-sg-progress') || '0') + '%';
        });
    }, 200);

    function updateClock() {
        var wrap = document.getElementById('sgRealtimeClock');
        if (!wrap) return;
        var now = new Date();
        var timeEl = wrap.querySelector('.sg-clock__time');
        var dateEl = wrap.querySelector('.sg-clock__date');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    }
    updateClock();
    window.setInterval(updateClock, 1000);

    var themeBtn = document.getElementById('sgThemeToggle');
    function syncThemeIcons() {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('.sg-theme-icon-dark').forEach(function (i) { i.classList.toggle('d-none', dark); });
        document.querySelectorAll('.sg-theme-icon-light').forEach(function (i) { i.classList.toggle('d-none', !dark); });
    }
    syncThemeIcons();
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            if (next === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            else document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('sg-dashboard-theme', next);
            localStorage.setItem('org-color-theme', next);
            syncThemeIcons();
            window.location.reload();
        });
    }

    var app = document.getElementById('sgApp');
    var backdrop = document.getElementById('sgSidebarBackdrop');
    if (localStorage.getItem('sg-sidebar-collapsed') === '1' && app) app.classList.add('is-sidebar-collapsed');
    var toggleBtn = document.getElementById('sgSidebarToggle');
    var collapseBtn = document.getElementById('sgSidebarCollapse');
    if (toggleBtn && app) {
        toggleBtn.addEventListener('click', function () {
            app.classList.toggle('is-sidebar-open');
            if (backdrop) backdrop.hidden = !app.classList.contains('is-sidebar-open');
        });
    }
    if (backdrop) backdrop.addEventListener('click', function () {
        if (app) app.classList.remove('is-sidebar-open');
        backdrop.hidden = true;
    });
    if (collapseBtn && app) {
        collapseBtn.addEventListener('click', function () {
            app.classList.toggle('is-sidebar-collapsed');
            localStorage.setItem('sg-sidebar-collapsed', app.classList.contains('is-sidebar-collapsed') ? '1' : '0');
        });
    }

    var searchInput = document.getElementById('sgGlobalSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = (searchInput.value || '').toLowerCase().trim();
            document.querySelectorAll('#sgSidebarNav .sg-nav-item').forEach(function (item) {
                item.classList.toggle('sg-search-hidden', q.length > 0 && (item.textContent || '').toLowerCase().indexOf(q) === -1);
            });
        });
    }

    window.sgUpdateBreadcrumb = function (label) {
        var el = document.getElementById('sgBreadcrumbCurrent');
        if (el && label) el.textContent = label;
    };

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();

    document.querySelectorAll('.sg-formula-details, .sg-formula-guide').forEach(function (el) {
        el.addEventListener('toggle', function () {
            if (el.open && typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });
    });
}());
</script>
