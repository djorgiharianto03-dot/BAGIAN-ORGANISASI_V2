<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'eorg_hub_metrics.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

org_require_level_access(org_eorg_hub_page_roles());

$eorgRoleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$eorgHubMetrics = org_eorg_hub_collect_metrics();
$dbEorg = $dbApp instanceof mysqli ? $dbApp : null;
$eorgChart = org_eorg_hub_weekly_series($dbEorg);
$eorgActivity = org_eorg_hub_activity_feed(8);

$pageTitle = 'E-Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-eorg-hub page-eorg-enterprise mode-eorganisasi';

$eorgHubServices = [];
if (org_eorg_hub_can_see_core_admin_modules()) {
    $eorgHubServices[] = [
        'href' => 'dashboard.php',
        'icon' => 'fa-solid fa-chart-line',
        'title' => 'Dashboard Grafik',
        'desc' => 'Statistik kunjungan, surat, dan ringkasan aktivitas internal.',
        'theme' => 'dashboard',
        'badge' => null,
    ];
    $eorgHubServices[] = [
        'href' => 'tamu.php',
        'icon' => 'fa-solid fa-book',
        'title' => 'Buku Tamu',
        'desc' => 'Kelola dan pantau daftar tamu yang berkunjung ke unit.',
        'theme' => 'tamu',
        'badge' => null,
    ];
    $eorgHubServices[] = [
        'href' => 'arsip.php',
        'icon' => 'fa-solid fa-folder-open',
        'title' => 'Arsip Surat',
        'desc' => 'Unggah, klasifikasi, dan arsipkan surat masuk & keluar.',
        'theme' => 'arsip',
        'badge' => null,
    ];
}

$eorgHubServices[] = [
    'href' => 'manajemen_tugas.php',
    'icon' => 'fa-solid fa-clipboard-list',
    'title' => 'Manajemen Tugas',
    'desc' => 'Unggah laporan tugas pegawai dan validasi oleh Kabag Organisasi.',
    'theme' => 'tugas',
    'badge' => null,
];

if ($eorgRoleNorm === 'sub_admin_eorganisasi') {
    $eorgHubServices[] = [
        'href' => 'disposisi_awal_kabag.php',
        'icon' => 'fa-solid fa-signature',
        'title' => 'Disposisi Awal Kabag',
        'desc' => 'Input disposisi awal dan pantau tanda terima Kepala Bagian.',
        'theme' => 'monitoring',
        'badge' => null,
    ];
} else {
    $eorgHubServices[] = [
        'href' => 'monitoring_disposisi.php',
        'icon' => 'fa-solid fa-clipboard-check',
        'title' => 'Monitoring Disposisi',
        'desc' => 'Pantau progres disposisi surat masuk secara menyeluruh.',
        'theme' => 'monitoring',
        'badge' => null,
    ];
}

$eorgHubServices[] = [
    'href' => 'disposisi_terbaru.php',
    'icon' => 'fa-solid fa-list-check',
    'title' => 'Disposisi Terbaru',
    'desc' => 'Lihat disposisi masuk dan surat masuk terbaru yang perlu tindak lanjut.',
    'theme' => 'disposisi',
    'badge' => null,
];

$eorgHubServices = org_eorg_hub_apply_service_badges($eorgHubServices, $eorgHubMetrics);

$pendingTotal = (int) ($eorgHubMetrics['badge_arsip'] ?? 0)
    + (int) ($eorgHubMetrics['badge_disposisi'] ?? 0)
    + (int) ($eorgHubMetrics['badge_tugas'] ?? 0);

$chartJson = json_encode($eorgChart, JSON_UNESCAPED_UNICODE);
if ($chartJson === false) {
    $chartJson = '{"labels":[],"tamu":[],"disposisi":[]}';
}

$chartJs = 'assets/vendor/chartjs/chart.umd.min.js';
if (defined('ORG_WEB_ROOT') && ORG_WEB_ROOT !== '') {
    $chartJs = rtrim(ORG_WEB_ROOT, '/') . '/' . $chartJs;
}

ob_start();
require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'eorg_premium_styles.php';
$eorgCss = trim((string) ob_get_clean());

$extraHeadMarkup = '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
    . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
    . '<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&amp;family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">' . "\n"
    . '<style id="eorg-premium-styles">' . "\n"
    . $eorgCss . "\n"
    . '</style>' . "\n";

$extraFooterMarkup = '<script src="' . htmlspecialchars($chartJs, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
$extraFooterMarkup .= '<script>window.EORG_HUB_CHART = ' . $chartJson . ';</script>' . "\n";
$extraFooterMarkup .= <<<'HTML'
<script>
(function () {
    var locale = 'id-ID';
    var elTime = document.getElementById('eorgHubTime');
    var elDayDate = document.getElementById('eorgHubDayDate');
    var elProgressFill = document.getElementById('eorgHubProgressFill');
    var elProgressPct = document.getElementById('eorgHubProgressPct');
    var elProgressPctDup = document.getElementById('eorgHubProgressPctDup');
    var elProgressBar = document.getElementById('eorgHubProgressBar');

    function setProgress(pct) {
        var v = Math.round(Math.max(0, Math.min(100, pct)));
        if (elProgressFill) elProgressFill.style.width = v + '%';
        var label = v + '%';
        if (elProgressPct) elProgressPct.textContent = label;
        if (elProgressPctDup) elProgressPctDup.textContent = label;
        if (elProgressBar) elProgressBar.setAttribute('aria-valuenow', String(v));
    }

    if (elProgressBar) {
        var target = parseInt(elProgressBar.getAttribute('data-progress') || '0', 10);
        setProgress(0);
        requestAnimationFrame(function () {
            setTimeout(function () { setProgress(target); }, 200);
        });
    }

    if (elTime) {
        function tick() {
            var d = new Date();
            elTime.textContent = d.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            var dayStr = d.toLocaleDateString(locale, { weekday: 'long' });
            var dateStr = d.toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' });
            if (elDayDate) elDayDate.textContent = dayStr + ' · ' + dateStr;
            if (elTime.dateTime !== undefined) elTime.dateTime = d.toISOString();
        }
        tick();
        setInterval(tick, 1000);
    }

    var canvas = document.getElementById('eorgHubChart');
    var data = window.EORG_HUB_CHART || { labels: [], tamu: [], disposisi: [] };
    if (canvas && typeof Chart !== 'undefined') {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels || [],
                datasets: [
                    {
                        label: 'Tamu',
                        data: data.tamu || [],
                        backgroundColor: 'rgba(37, 99, 235, 0.72)',
                        borderRadius: 6,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Disposisi',
                        data: data.disposisi || [],
                        backgroundColor: 'rgba(124, 58, 237, 0.65)',
                        borderRadius: 6,
                        maxBarThickness: 28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 11, family: 'Inter' } } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.2)' } }
                }
            }
        });
    }
}());
</script>
HTML;

org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);
org_portal_set_hero(
    'E-Organisasi',
    'Dashboard layanan internal terintegrasi untuk administrasi, arsip, dan disposisi.',
    'Layanan Internal',
    'fa-network-wired',
    [
        ['value' => (int) ($eorgHubMetrics['tamu_today'] ?? 0), 'label' => 'Tamu hari ini'],
        ['value' => $pendingTotal, 'label' => 'Antrian'],
        ['value' => count($eorgHubServices), 'label' => 'Modul'],
    ]
);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'portal_subpage_hero.php'; ?>

<div class="sg-portal-main-inner">
    <div class="container-global site-main">
        <section class="eorg-hub eorg-hub--enterprise" aria-label="Dashboard E-Organisasi">
            <div class="eorg-hub__container">
                <?php
                require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'eorg_dashboard_widgets.php';
                require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'eorg_hub_modules.php';
                ?>
            </div>
        </section>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
