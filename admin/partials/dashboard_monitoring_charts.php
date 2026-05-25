<?php
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

$apexCdnLocal = function_exists('org_asset_url')
    ? org_asset_url('assets/vendor/apexcharts/3.49.1/apexcharts.min.js')
    : '/assets/vendor/apexcharts/3.49.1/apexcharts.min.js';
?>
<script defer src="<?php echo htmlspecialchars($apexCdnLocal, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script defer src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
/* Lazy-load chart engine: tiap chart hanya dirender saat masuk viewport
   (IntersectionObserver). Mencegah render serentak yang membebani main
   thread di halaman dashboard. Hormati prefers-reduced-motion. */
(function () {
    'use strict';

    var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var P = '#1D4ED8';
    var S = '#2563EB';
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    var fg = dark ? '#94a3b8' : '#64748B';
    var grid = dark ? '#334155' : '#E2E8F0';

    /* Palet 8 warna untuk Tim Kerja — mendukung hingga 8 tim tanpa bentrok */
    var TEAM_COLORS = [P, '#8B5CF6', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899', '#84CC16'];

    function base(type, h) {
        return {
            type: type,
            height: h,
            fontFamily: 'Inter, system-ui, sans-serif',
            foreColor: fg,
            background: 'transparent',
            toolbar: { show: false },
            redrawOnParentResize: false,
            redrawOnWindowResize: true,
            animations: REDUCED
                ? { enabled: false }
                : { enabled: true, speed: 600, easing: 'easeinout', animateGradually: { enabled: false } }
        };
    }

    function fmtIdNumber(v) {
        try { return Number(v).toLocaleString('id-ID'); } catch (e) { return String(v); }
    }

    /* Spec semua chart didefinisikan dulu (tanpa eksekusi). Render dipicu
       hanya saat container masuk viewport. Setiap chart hanya dirender sekali. */
    var specs = {};

    <?php if ($showTamu): ?>
    specs['sgChartTrend'] = {
        series: [{ name: 'Kunjungan', data: <?php echo $chartTamuValues; ?> }],
        chart: base('area', 300),
        colors: [P],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.42, opacityTo: 0.05, shade: 'light' } },
        dataLabels: { enabled: false },
        xaxis: { categories: <?php echo $chartTamuLabels; ?> },
        yaxis: { min: 0, labels: { formatter: fmtIdNumber } },
        tooltip: { y: { formatter: fmtIdNumber } },
        grid: { borderColor: grid, strokeDashArray: 4 }
    };
    <?php endif; ?>

    specs['sgChartDonut'] = {
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
                        value: { show: true, fontSize: '22px', fontWeight: 700, formatter: fmtIdNumber },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return fmtIdNumber(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0));
                            }
                        }
                    }
                }
            }
        },
        stroke: { width: 2, colors: ['#ffffff'] },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: fmtIdNumber } }
    };

    <?php if ($hasTarget): ?>
    specs['sgChartTargetRealisasi'] = {
        series: [
            { name: 'Target', data: <?php echo $targetValues; ?> },
            { name: 'Realisasi', data: <?php echo $realisasiValues; ?> }
        ],
        chart: base('bar', 280),
        colors: [S, '#10B981'],
        plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 8 } },
        xaxis: { categories: <?php echo $targetLabels; ?> },
        yaxis: { max: 100, labels: { formatter: function (v) { return Math.round(v) + '%'; } } },
        tooltip: { y: { formatter: function (v) { return Math.round(v) + '%'; } } },
        dataLabels: { enabled: false },
        legend: { position: 'top' }
    };
    <?php endif; ?>

    /* Capaian target tahunan Tim Kerja — DONUT (ringan, jelas, total di tengah).
       Sebelumnya: radialBar dengan banyak ring (mahal saat 5+ tim). */
    specs['sgChartProgressKerja'] = {
        series: <?php echo $timPct; ?>,
        chart: base('donut', 300),
        labels: <?php echo $timLabels; ?>,
        colors: TEAM_COLORS,
        legend: {
            position: 'bottom',
            fontSize: '12px',
            itemMargin: { horizontal: 8, vertical: 4 },
            formatter: function (seriesName, opts) {
                var v = opts.w.globals.series[opts.seriesIndex];
                return seriesName + ' — ' + Math.round(v) + '%';
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', color: fg },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 700,
                            formatter: function (v) { return Math.round(v) + '%'; }
                        },
                        total: {
                            show: true,
                            label: 'Rata-rata',
                            color: fg,
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
        },
        stroke: { width: 2, colors: ['#ffffff'] },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (v) { return Math.round(v) + '%'; } } }
    };

    <?php if (count($dashMetrics['download_labels'] ?? []) > 0): ?>
    specs['admChartDownloads'] = {
        series: [{ name: 'Unduhan', data: <?php echo $chartDlValues; ?> }],
        chart: base('bar', 280),
        colors: [P],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
        xaxis: { categories: <?php echo $chartDlLabels; ?>, labels: { rotate: -25 } },
        yaxis: { labels: { formatter: fmtIdNumber } },
        tooltip: { y: { formatter: fmtIdNumber } },
        dataLabels: { enabled: false },
        grid: { borderColor: grid }
    };
    <?php endif; ?>

    <?php if ($hasHeatmap): ?>
    specs['sgChartHeatmap'] = {
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
    };
    <?php endif; ?>

    var rendered = Object.create(null);

    function renderChart(id) {
        if (rendered[id]) return;
        var el = document.getElementById(id);
        if (!el) return;
        var spec = specs[id];
        if (!spec || typeof ApexCharts === 'undefined') return;
        rendered[id] = true;
        el.innerHTML = '';
        try {
            var ch = new ApexCharts(el, spec);
            ch.render();
            el._sgChart = ch;
        } catch (e) {
            /* swallow — chart gagal render tidak boleh merusak page */
            rendered[id] = false;
        }
    }

    function boot() {
        if (typeof ApexCharts === 'undefined') {
            /* script ApexCharts (defer) belum siap; coba lagi sebentar */
            window.setTimeout(boot, 60);
            return;
        }

        var ids = Object.keys(specs);
        if (!('IntersectionObserver' in window)) {
            ids.forEach(renderChart);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.id;
                    renderChart(id);
                    io.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: '120px 0px', threshold: 0.05 });

        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) io.observe(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
}());

(function () {
    'use strict';

    function bootShell() {

    var SHELL_REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-sg-counter]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-sg-counter') || '0', 10);
        if (SHELL_REDUCED) {
            el.textContent = target.toLocaleString('id-ID');
            return;
        }
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

    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootShell, { once: true });
    } else {
        bootShell();
    }
}());
</script>
