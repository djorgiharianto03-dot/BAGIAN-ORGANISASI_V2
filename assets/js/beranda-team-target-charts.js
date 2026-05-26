/**
 * Grafik Target Tim Kerja (beranda) — ApexCharts
 */
(function () {
    'use strict';

    function readChartStore() {
        var dataEl = document.getElementById('gov-team-target-charts-data');
        if (!dataEl) {
            return null;
        }
        try {
            return JSON.parse(dataEl.textContent || '{}');
        } catch (e) {
            return {};
        }
    }

    function showEmpty(el, message) {
        if (!el || el.getAttribute('data-chart-ready') === '1') {
            return;
        }
        el.innerHTML = '';
        var p = document.createElement('p');
        p.className = 'gov-team-target-chart-empty text-muted small mb-0 text-center py-4';
        p.setAttribute('role', 'status');
        p.textContent = message;
        el.appendChild(p);
        el.setAttribute('data-chart-ready', '1');
    }

    function initGovTeamTargetCharts() {
        if (typeof ApexCharts === 'undefined') {
            return false;
        }

        var store = readChartStore();
        if (!store) {
            return false;
        }

        var teams = store.teams || {};
        var overview = store.overview || [];
        var teamKeys = Object.keys(teams);
        var hasTeamCharts = teamKeys.length > 0;
        var hasOverview = Array.isArray(overview) && overview.length > 0;

        var overviewEl = document.getElementById('govTeamTargetOverviewChart');
        if (overviewEl && overviewEl.getAttribute('data-chart-ready') !== '1') {
            if (!hasOverview) {
                showEmpty(overviewEl, 'Data perbandingan tim belum tersedia.');
            } else {
                var overviewLabels = overview.map(function (row) { return row.label || ''; });
                var overviewData = overview.map(function (row) {
                    return Math.max(0, Math.min(100, Number(row.pct) || 0));
                });
                var overviewColors = overview.map(function (row) { return row.color || '#8CB8EB'; });
                var overviewColorsDeep = overview.map(function (row) { return row.colorDeep || row.color || '#1A3F6E'; });
                var overviewLabelColors = overviewData.map(function (v) {
                    return (Number(v) || 0) >= 22 ? '#ffffff' : '#0f2744';
                });
                var overviewCounts = overview.map(function (row) { return Number(row.count) || 0; });
                var overviewFull = overview.map(function (row) { return row.fullLabel || row.label || ''; });

                var overviewChart = new ApexCharts(overviewEl, {
                    series: [{ name: 'Rata-rata capaian', data: overviewData }],
                    chart: {
                        type: 'bar',
                        height: Math.max(280, overview.length * 84 + 56),
                        toolbar: { show: false },
                        fontFamily: 'Plus Jakarta Sans, Inter, system-ui, sans-serif',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 950,
                            animateGradually: { enabled: true, delay: 140 },
                            dynamicAnimation: { enabled: true, speed: 400 }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 10,
                            borderRadiusApplication: 'end',
                            barHeight: '72%',
                            distributed: true,
                            dataLabels: { position: 'center', hideOverflowingLabels: false }
                        }
                    },
                    colors: overviewColors,
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.35,
                            gradientFromColors: overviewColors,
                            gradientToColors: overviewColorsDeep,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 55, 100]
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'middle',
                        offsetX: 0,
                        formatter: function (val) { return Math.round(Number(val) || 0) + '%'; },
                        style: {
                            fontSize: '12px',
                            fontWeight: 700,
                            fontFamily: 'Plus Jakarta Sans, Inter, system-ui, sans-serif',
                            colors: overviewLabelColors,
                            textShadow: '0 1px 2px rgba(15, 39, 68, 0.22)'
                        }
                    },
                    legend: { show: false },
                    grid: {
                        show: true,
                        borderColor: 'transparent',
                        xaxis: { lines: { show: false } },
                        yaxis: { lines: { show: false } },
                        padding: { left: 12, right: 44, top: 8, bottom: 8 }
                    },
                    xaxis: {
                        categories: overviewLabels,
                        min: 0,
                        max: 100,
                        tickAmount: 5,
                        labels: { show: false },
                        axisBorder: { show: true, color: '#d8e2ef', height: 1 },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#1e3a5f', fontWeight: 600, fontSize: '12px' },
                            maxWidth: 240
                        }
                    },
                    states: {
                        hover: { filter: { type: 'darken', value: 0.08 } },
                        active: { filter: { type: 'none' } }
                    },
                    tooltip: {
                        theme: 'light',
                        intersect: true,
                        shared: false,
                        custom: function (ctx) {
                            var i = ctx.dataPointIndex;
                            if (i < 0) return '';
                            var pct = overviewData[i] || 0;
                            var cnt = overviewCounts[i] || 0;
                            var full = overviewFull[i] || overviewLabels[i] || '';
                            var dot = overviewColorsDeep[i] || '#1A3F6E';
                            var dotLight = overviewColors[i] || '#8CB8EB';
                            return '<div style="padding:11px 14px;background:linear-gradient(165deg,#fff 0%,#f8fafc 100%);border:1px solid #d8e2ef;border-radius:12px;box-shadow:0 10px 28px rgba(15,39,68,.12);font-family:var(--font-sans,system-ui);">'
                                + '<div style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#0f2744;margin-bottom:6px;"><span style="width:11px;height:11px;border-radius:4px;background:linear-gradient(135deg,' + dotLight + ',' + dot + ');box-shadow:0 1px 4px rgba(15,39,68,.2);"></span>' + full + '</div>'
                                + '<div style="font-size:16px;font-weight:700;color:#0a2f63;letter-spacing:-0.02em;">' + Math.round(pct) + '%</div>'
                                + '<div style="font-size:11px;color:#64748b;margin-top:4px;">' + cnt + ' kegiatan</div>'
                                + '</div>';
                        }
                    }
                });
                overviewChart.render().then(function () {
                    if (typeof overviewChart.resize === 'function') {
                        overviewChart.resize();
                    }
                });
                overviewEl.setAttribute('data-chart-ready', '1');
            }
        }

        if (!hasTeamCharts) {
            document.querySelectorAll('.gov-team-target-dash-card__chart').forEach(function (el) {
                showEmpty(el, 'Belum ada kegiatan untuk grafik tim ini.');
            });
        } else {
            var REDUCED_MOTION = window.matchMedia
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            teamKeys.forEach(function (tim) {
                var pack = teams[tim];
                if (!pack) return;
                var el = document.getElementById('govTeamTargetChart-' + tim);
                if (!el || el.getAttribute('data-chart-ready') === '1') return;

                var pct = Math.max(0, Math.min(100, Number(pack.pct) || 0));

                /* Donat distribusi status: Direncanakan / Berjalan / Selesai.
                   Palet modern (amber → biru → emerald) dengan kontras tinggi
                   namun tetap selaras dengan badge legenda di bawah kartu.
                   Lebih ringan dari radialBar gauge (3 slice maks, tanpa
                   gradient fill, animateGradually off). */
                var sc = pack.statusCounts || {};
                var rawSeries = [
                    Number(sc.direncanakan) || 0,
                    Number(sc.berjalan) || 0,
                    Number(sc.selesai) || 0
                ];
                var rawLabels = ['Direncanakan', 'Berjalan', 'Selesai'];
                /* Palet vibrant: amber 500, blue 600, emerald 500 — kontras
                   lebih kuat dibanding palet teal-tua sebelumnya. */
                var rawColors = ['#F59E0B', '#2563EB', '#10B981'];

                /* Buang slice nol agar tooltip & legend hanya menampilkan
                   status yang relevan; juga sedikit mempercepat render. */
                var series = [];
                var labels = [];
                var colors = [];
                rawSeries.forEach(function (v, i) {
                    if (v > 0) {
                        series.push(v);
                        labels.push(rawLabels[i]);
                        colors.push(rawColors[i]);
                    }
                });

                var hasRealData = series.length > 0;
                if (!hasRealData) {
                    /* Fallback: tidak ada slice yang valid — tampilkan donat
                       polos dengan satu segmen 'Belum ada data'. */
                    series = [1];
                    labels = ['Belum ada data'];
                    colors = ['#cbd5e1'];
                }

                var fontStack = 'Plus Jakarta Sans, Inter, system-ui, sans-serif';

                /* Konfigurasi center label mengikuti perilaku natural ApexCharts:
                     - IDLE (tanpa hover) → tampilkan `total` saja:
                         baris kecil "Rata-rata" + angka besar "75%".
                     - HOVER pada slice → ApexCharts otomatis ganti ke
                         baris kecil "Direncanakan" (name) + angka besar
                         "2 kegiatan" (value).
                   Tidak ada offsetY manual — biarkan ApexCharts menata vertikal
                   agar tidak terjadi tumpukan teks pada saat hover. */
                var chart = new ApexCharts(el, {
                    series: series,
                    chart: {
                        type: 'donut',
                        height: 188,
                        fontFamily: fontStack,
                        toolbar: { show: false },
                        redrawOnParentResize: false,
                        animations: REDUCED_MOTION
                            ? { enabled: false }
                            : { enabled: true, speed: 600, easing: 'easeinout', animateGradually: { enabled: false } }
                    },
                    labels: labels,
                    colors: colors,
                    stroke: { width: 2, colors: ['#ffffff'] },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '11px',
                                        fontWeight: 600,
                                        color: '#64748b',
                                        formatter: function (seriesName) { return seriesName; }
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '1.4rem',
                                        fontWeight: 700,
                                        color: '#0f2744',
                                        formatter: function (val) {
                                            if (!hasRealData) return '0 keg.';
                                            return Number(val) + ' keg.';
                                        }
                                    },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Rata-rata',
                                        color: '#64748b',
                                        fontSize: '11px',
                                        fontWeight: 600,
                                        formatter: function () { return Math.round(pct) + '%'; }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        theme: 'light',
                        fillSeriesColor: false,
                        y: {
                            formatter: function (val) {
                                if (!hasRealData) return '';
                                return val + ' kegiatan';
                            }
                        }
                    },
                    states: {
                        hover: { filter: { type: 'darken', value: 0.08 } },
                        active: { filter: { type: 'none' } }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: { height: 168 },
                            plotOptions: { pie: { donut: { size: '64%' } } }
                        }
                    }]
                });
                chart.render().then(function () {
                    if (typeof chart.resize === 'function') {
                        chart.resize();
                    }
                });
                el.setAttribute('data-chart-ready', '1');
            });
        }

        return true;
    }

    function startTeamCharts() {
        if (initGovTeamTargetCharts()) {
            return;
        }
        var tries = 0;
        var timer = setInterval(function () {
            tries += 1;
            if (initGovTeamTargetCharts() || tries > 50) {
                clearInterval(timer);
            }
        }, 150);
    }

    function onBootstrap() {
        if (typeof ApexCharts !== 'undefined') {
            startTeamCharts();
        }
    }

    document.addEventListener('beranda:apex-ready', startTeamCharts);
    document.addEventListener('beranda:team-chunk-ready', startTeamCharts);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onBootstrap);
    } else {
        onBootstrap();
    }

    window.addEventListener('load', onBootstrap);

    window.__orgInitGovTeamTargetCharts = initGovTeamTargetCharts;
})();
