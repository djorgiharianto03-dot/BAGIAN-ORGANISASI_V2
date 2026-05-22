<?php

/** @var array<string, mixed> $dashMetrics */
/** @var bool $isSubAdminPublikasiActor */

$chartTamuLabels = json_encode($dashMetrics['tamu_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$chartTamuValues = json_encode($dashMetrics['tamu_values'] ?? [], JSON_UNESCAPED_UNICODE);
$chartDlLabels = json_encode($dashMetrics['download_labels'] ?? [], JSON_UNESCAPED_UNICODE);
$chartDlValues = json_encode($dashMetrics['download_values'] ?? [], JSON_UNESCAPED_UNICODE);
$showTamuChart = !$isSubAdminPublikasiActor;
$showDlChart = !$isSubAdminPublikasiActor && count($dashMetrics['download_labels'] ?? []) > 0;
?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
(function () {
    'use strict';

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }

  /**
   * Tema ApexCharts premium — dashboard admin SaaS.
   */
    var AdmChartTheme = {
        colors: {
            primary: '#2563EB',
            primaryLight: '#60A5FA',
            primarySoft: '#93C5FD',
            text: '#0F172A',
            muted: '#64748B',
            grid: '#E2E8F0',
            gridFade: '#F1F5F9'
        },

        animations: function () {
            return {
                enabled: true,
                easing: 'easeinout',
                speed: 900,
                animateGradually: { enabled: true, delay: 120 },
                dynamicAnimation: { enabled: true, speed: 380 }
            };
        },

        chartBase: function (type, height) {
            return {
                type: type,
                height: height,
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                foreColor: '#64748B',
                background: 'transparent',
                toolbar: { show: false },
                zoom: { enabled: false },
                redrawOnParentResize: true,
                redrawOnWindowResize: true,
                animations: this.animations()
            };
        },

        legend: function () {
            return {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                fontWeight: 500,
                fontFamily: 'Inter, system-ui, sans-serif',
                labels: { colors: '#64748B' },
                markers: {
                    width: 10,
                    height: 10,
                    radius: 4,
                    offsetX: -4
                },
                itemMargin: { horizontal: 12, vertical: 4 }
            };
        },

        grid: function () {
            return {
                show: true,
                borderColor: 'transparent',
                strokeDashArray: 0,
                padding: { left: 4, right: 12, top: 4, bottom: 0 },
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } }
            };
        },

        axisStyle: function () {
            return {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#94A3B8',
                        fontSize: '11px',
                        fontWeight: 500,
                        fontFamily: 'Inter, system-ui, sans-serif'
                    }
                }
            };
        },

        yaxis: function () {
            var base = this.axisStyle();
            return {
                min: 0,
                forceNiceScale: true,
                labels: Object.assign({}, base.labels, { offsetX: -6 })
            };
        },

        states: function () {
            return {
                hover: { filter: { type: 'lighten', value: 0.06 } },
                active: { filter: { type: 'none' } }
            };
        },

        glassTooltip: function (unit) {
            return {
                enabled: true,
                shared: true,
                intersect: false,
                followCursor: true,
                custom: function (ctx) {
                    var w = ctx.w;
                    var i = ctx.dataPointIndex;
                    if (i < 0 || !w.globals.categoryLabels) {
                        return '';
                    }
                    var label = w.globals.categoryLabels[i] || '';
                    var seriesName = (w.globals.seriesNames && w.globals.seriesNames[ctx.seriesIndex]) || 'Nilai';
                    var val = ctx.series[ctx.seriesIndex][i];
                    if (val === undefined && ctx.series.length) {
                        val = ctx.series[0][i];
                    }
                    var num = Number(val) || 0;
                    return ''
                        + '<div class="adm-apex-tooltip">'
                        + '<div class="adm-apex-tooltip__label">' + label + '</div>'
                        + '<div class="adm-apex-tooltip__row">'
                        + '<span class="adm-apex-tooltip__dot"></span>'
                        + '<span class="adm-apex-tooltip__series">' + seriesName + '</span>'
                        + '<span class="adm-apex-tooltip__value">' + num.toLocaleString('id-ID') + (unit ? ' ' + unit : '') + '</span>'
                        + '</div>'
                        + '</div>';
                }
            };
        },

        area: function (categories, data, seriesName) {
            var c = this.colors;
            return {
                series: [{ name: seriesName, data: data }],
                chart: this.chartBase('area', 300),
                colors: [c.primary],
                legend: this.legend(),
                grid: this.grid(),
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    lineCap: 'round'
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.35,
                        gradientToColors: [c.primaryLight],
                        opacityFrom: 0.5,
                        opacityTo: 0.04,
                        stops: [0, 55, 100]
                    }
                },
                markers: {
                    size: 0,
                    strokeWidth: 0,
                    hover: {
                        size: 6,
                        sizeOffset: 2,
                        strokeWidth: 2,
                        strokeColors: '#fff',
                        fillColors: c.primary
                    }
                },
                dataLabels: { enabled: false },
                xaxis: Object.assign({ categories: categories }, this.axisStyle()),
                yaxis: this.yaxis(),
                tooltip: this.glassTooltip('kunjungan'),
                states: this.states()
            };
        },

        bar: function (categories, data, seriesName) {
            var c = this.colors;
            return {
                series: [{ name: seriesName, data: data }],
                chart: this.chartBase('bar', 300),
                colors: [c.primary],
                legend: this.legend(),
                grid: this.grid(),
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        borderRadiusApplication: 'end',
                        columnWidth: '48%',
                        distributed: false
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.2,
                        gradientToColors: [c.primaryLight],
                        opacityFrom: 1,
                        opacityTo: 0.72,
                        stops: [0, 100]
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent'],
                    lineCap: 'round'
                },
                dataLabels: { enabled: false },
                xaxis: Object.assign({
                    categories: categories,
                    labels: Object.assign({}, this.axisStyle().labels, {
                        rotate: -28,
                        trim: true,
                        hideOverlappingLabels: true,
                        maxHeight: 80
                    })
                }, { axisBorder: { show: false }, axisTicks: { show: false } }),
                yaxis: this.yaxis(),
                tooltip: this.glassTooltip('unduhan'),
                states: this.states()
            };
        }
    };

    function renderChart(el, options) {
        if (!el || typeof ApexCharts === 'undefined') {
            return null;
        }
        var chart = new ApexCharts(el, options);
        chart.render();
        return chart;
    }

    function chartHeight() {
        if (window.innerWidth < 576) {
            return 260;
        }
        if (window.innerWidth < 992) {
            return 280;
        }
        return 300;
    }

    var charts = [];

    <?php if ($showTamuChart): ?>
    (function () {
        var el = document.getElementById('admChartTamu');
        if (!el) return;
        var opts = AdmChartTheme.area(
            <?php echo $chartTamuLabels; ?>,
            <?php echo $chartTamuValues; ?>,
            'Kunjungan tamu'
        );
        opts.chart.height = chartHeight();
        charts.push(renderChart(el, opts));
    })();
    <?php endif; ?>

    <?php if ($showDlChart): ?>
    (function () {
        var el = document.getElementById('admChartDownloads');
        if (!el) return;
        var opts = AdmChartTheme.bar(
            <?php echo $chartDlLabels; ?>,
            <?php echo $chartDlValues; ?>,
            'Jumlah unduhan'
        );
        opts.chart.height = chartHeight();
        charts.push(renderChart(el, opts));
    })();
    <?php endif; ?>

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var h = chartHeight();
            charts.forEach(function (ch) {
                if (ch && typeof ch.updateOptions === 'function') {
                    ch.updateOptions({ chart: { height: h } }, false, true);
                }
            });
        }, 180);
    }, { passive: true });
}());
</script>
