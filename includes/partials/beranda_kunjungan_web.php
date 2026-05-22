<?php

/** @var int $berandaTotalToday @var int $berandaTotalWeek @var list<string> $berandaVisitLabels @var list<int> $berandaVisitValues */
$dbVisit = ($dbApp ?? null) instanceof mysqli ? $dbApp : (function_exists('org_db') ? org_db() : null);
if (!isset($berandaVisitLabels, $berandaVisitValues, $berandaTotalToday, $berandaTotalWeek)) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';
    $berandaVisitStats = org_beranda_fetch_visit_stats($dbVisit instanceof mysqli ? $dbVisit : null);
    $berandaVisitLabels = $berandaVisitStats['labels'];
    $berandaVisitValues = $berandaVisitStats['values'];
    $berandaTotalToday = (int) $berandaVisitStats['total_today'];
    $berandaTotalWeek = (int) $berandaVisitStats['total_week'];
}
$berandaVisitLabelsJson = json_encode($berandaVisitLabels ?? [], JSON_UNESCAPED_UNICODE);
$berandaVisitValuesJson = json_encode($berandaVisitValues ?? [], JSON_UNESCAPED_UNICODE);
if ($berandaVisitLabelsJson === false) {
    $berandaVisitLabelsJson = '[]';
}
if ($berandaVisitValuesJson === false) {
    $berandaVisitValuesJson = '[]';
}
?>
<section class="beranda-section beranda-section--surface-muted beranda-ssr-section" id="beranda-kunjungan-web" aria-labelledby="beranda-kunjungan-web-title">
    <div class="beranda-section__head-row d-flex flex-wrap justify-content-between align-items-end gap-2">
        <div>
            <h2 id="beranda-kunjungan-web-title" class="beranda-section__title mb-0">Statistik Kunjungan Tamu Website</h2>
            <p class="beranda-section__desc mb-0 mt-1">Pemantauan aktivitas pengunjung portal secara ringkas.</p>
        </div>
    </div>
    <div class="card beranda-visit-card border-0">
        <div class="card-body">
            <div class="row g-3 beranda-visit-stats">
                <div class="col-12 col-sm-6">
                    <article class="beranda-visit-stat beranda-visit-stat--today">
                        <span class="beranda-visit-stat__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                        <span class="beranda-visit-stat__label">Tamu Hari Ini</span>
                        <p class="beranda-visit-stat__num mb-0"><?php echo (int) $berandaTotalToday; ?></p>
                    </article>
                </div>
                <div class="col-12 col-sm-6">
                    <article class="beranda-visit-stat beranda-visit-stat--week">
                        <span class="beranda-visit-stat__icon" aria-hidden="true"><i class="fa-solid fa-calendar-week"></i></span>
                        <span class="beranda-visit-stat__label">7 Hari Terakhir</span>
                        <p class="beranda-visit-stat__num mb-0"><?php echo (int) $berandaTotalWeek; ?></p>
                    </article>
                </div>
            </div>
            <p class="beranda-visit-caption">Tren kunjungan 14 hari terakhir untuk pemantauan aktivitas pengunjung.</p>
            <div class="beranda-visit-chart-shell" data-beranda-chart="visit">
                <div class="beranda-visit-chart-wrap">
                    <canvas id="berandaVisitChart" aria-label="Grafik kunjungan tamu website"></canvas>
                </div>
                <div id="berandaVisitChartError" class="beranda-visit-chart-error" role="status" aria-live="polite">
                    Grafik statistik sementara belum dapat dimuat. Coba refresh halaman.
                </div>
            </div>
        </div>
    </div>
</section>
<script type="application/json" id="beranda-visit-chart-data"><?php echo '{"labels":' . $berandaVisitLabelsJson . ',"values":' . $berandaVisitValuesJson . '}'; ?></script>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'beranda_visit_chart_script.php'; ?>
