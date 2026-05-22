<?php
declare(strict_types=1);

/** @var list<string> $berandaVisitLabels @var list<int> $berandaVisitValues */
$labelsJson = json_encode($berandaVisitLabels ?? [], JSON_UNESCAPED_UNICODE);
$valuesJson = json_encode($berandaVisitValues ?? [], JSON_UNESCAPED_UNICODE);
if ($labelsJson === false) {
    $labelsJson = '[]';
}
if ($valuesJson === false) {
    $valuesJson = '[]';
}
?>
<script>
(function () {
    const chartEl = document.getElementById('berandaVisitChart');
    const chartErrorEl = document.getElementById('berandaVisitChartError');
    if (!chartEl) return;

    let rendered = false;
    let chartInstance = null;
    const labels = <?php echo $labelsJson; ?>;
    const values = <?php echo $valuesJson; ?>;
    const hasData = values.some(function (n) { return Number(n) > 0; });

    const renderNativeFallback = function () {
        const wrap = chartEl.parentElement;
        const w = (wrap && wrap.clientWidth > 0 ? wrap.clientWidth : chartEl.clientWidth) || 800;
        const h = (wrap && wrap.clientHeight > 0 ? wrap.clientHeight : chartEl.clientHeight) || 235;
        if (w < 40 || h < 40) return false;

        const ctx = chartEl.getContext('2d');
        if (!ctx) return false;
        const dpr = window.devicePixelRatio || 1;
        chartEl.width = Math.floor(w * dpr);
        chartEl.height = Math.floor(h * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, w, h);

        const pad = { left: 38, right: 6, top: 16, bottom: 38 };
        const cw = w - pad.left - pad.right;
        const ch = h - pad.top - pad.bottom;
        const maxVal = Math.max(5, ...values.map(function (v) { return Number(v) || 0; }));

        ctx.strokeStyle = 'rgba(148,163,184,0.2)';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 5; i++) {
            const y = pad.top + (ch * i / 5);
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(w - pad.right, y);
            ctx.stroke();

            const yVal = Math.round(maxVal - (maxVal * i / 5));
            ctx.fillStyle = '#7b8ca4';
            ctx.font = '11px Inter, system-ui, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillText(String(yVal), pad.left - 8, y);
        }

        const pts = values.map(function (v, i) {
            const x = pad.left + (cw * (labels.length <= 1 ? 0 : i / (labels.length - 1)));
            const y = pad.top + ch - ((Number(v) || 0) / maxVal) * ch;
            return { x: x, y: y };
        });

        const xLabelCount = Math.min(7, labels.length);
        const xLabelStep = Math.max(1, Math.ceil((labels.length - 1) / Math.max(1, xLabelCount - 1)));
        for (let idx = 0; idx < labels.length; idx += xLabelStep) {
            const x = pad.left + (cw * (labels.length <= 1 ? 0 : idx / (labels.length - 1)));
            ctx.fillStyle = '#64748b';
            ctx.font = '500 10.5px Inter, system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillText(String(labels[idx] || ''), x, h - pad.bottom + 8);
        }

        const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + ch);
        grad.addColorStop(0, 'rgba(26, 63, 110, 0.24)');
        grad.addColorStop(1, 'rgba(26, 63, 110, 0.03)');
        ctx.beginPath();
        pts.forEach(function (p, i) {
            if (i === 0) ctx.moveTo(p.x, p.y); else ctx.lineTo(p.x, p.y);
        });
        ctx.lineTo(pad.left + cw, pad.top + ch);
        ctx.lineTo(pad.left, pad.top + ch);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        ctx.beginPath();
        pts.forEach(function (p, i) {
            if (i === 0) ctx.moveTo(p.x, p.y); else ctx.lineTo(p.x, p.y);
        });
        ctx.strokeStyle = '#1a3f6e';
        ctx.lineWidth = 2.4;
        ctx.stroke();

        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#1a3f6e';
        ctx.lineWidth = 2;
        pts.forEach(function (p) {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        });

        if (!hasData) {
            ctx.fillStyle = '#94a3b8';
            ctx.font = '12px Inter, system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Belum ada kunjungan pada periode ini', pad.left + cw / 2, pad.top + ch / 2);
        }
        return true;
    };

    const renderChartJs = function () {
        if (rendered || typeof Chart === 'undefined') return false;
        const wrap = chartEl.parentElement;
        const w = wrap ? wrap.clientWidth : chartEl.clientWidth;
        const h = wrap ? wrap.clientHeight : chartEl.clientHeight;
        if (!w || !h) return false;

        const ctx = chartEl.getContext('2d');
        if (!ctx) return false;

        if (chartInstance && typeof chartInstance.destroy === 'function') {
            chartInstance.destroy();
            chartInstance = null;
        }

        const gradientFill = ctx.createLinearGradient(0, 0, 0, 280);
        gradientFill.addColorStop(0, 'rgba(26, 63, 110, 0.28)');
        gradientFill.addColorStop(1, 'rgba(26, 63, 110, 0.02)');

        try {
            chartInstance = new Chart(chartEl, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: values,
                        borderColor: '#1a3f6e',
                        backgroundColor: gradientFill,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#1a3f6e',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#1a3f6e',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2,
                        pointRadius: 3.5,
                        fill: true,
                        borderWidth: 2.5,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { left: 2, right: 4, top: 6, bottom: 2 } },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 39, 68, 0.94)',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            borderColor: 'rgba(212, 220, 232, 0.35)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            offset: false,
                            border: { display: false },
                            grid: { display: false, drawOnChartArea: false },
                            ticks: {
                                color: '#64748b',
                                maxRotation: 0,
                                minRotation: 0,
                                autoSkip: true,
                                autoSkipPadding: 14,
                                maxTicksLimit: 7,
                                padding: 10,
                                font: { size: 11, weight: '500', family: 'Inter, system-ui, sans-serif' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: hasData ? undefined : 5,
                            border: { display: false },
                            ticks: {
                                precision: 0,
                                color: '#94a3b8',
                                padding: 8,
                                stepSize: hasData ? undefined : 1,
                                font: { size: 11, family: 'Inter, system-ui, sans-serif' }
                            },
                            grid: { color: 'rgba(148, 163, 184, 0.12)', drawBorder: false }
                        }
                    }
                }
            });
        } catch (err) {
            if (chartErrorEl) {
                chartErrorEl.textContent = 'Grafik gagal dimuat: ' + (err && err.message ? err.message : 'error tidak diketahui');
                chartErrorEl.classList.add('is-visible');
            }
            return false;
        }
        rendered = true;
        if (chartErrorEl) chartErrorEl.classList.remove('is-visible');
        return true;
    };

    const tryRender = function () {
        if (rendered) return true;
        if (renderChartJs()) return true;
        if (renderNativeFallback()) {
            rendered = true;
            if (chartErrorEl) chartErrorEl.classList.remove('is-visible');
            return true;
        }
        return false;
    };

    const schedule = function () {
        if (tryRender()) return;
        requestAnimationFrame(function () {
            if (tryRender()) return;
            window.setTimeout(tryRender, 120);
        });
    };

    const runWhenVisible = function (fn) {
        const host = chartEl.closest('.beranda-visit-chart-wrap') || chartEl;
        if (!('IntersectionObserver' in window)) {
            fn();
            return;
        }
        const io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                io.disconnect();
                fn();
            });
        }, { rootMargin: '100px 0px', threshold: 0.06 });
        io.observe(host);
    };

    document.addEventListener('beranda:chart-ready', function () {
        runWhenVisible(schedule);
    }, { once: true });

    runWhenVisible(function () {
        if (typeof Chart !== 'undefined') {
            schedule();
        }
    });
}());
</script>
