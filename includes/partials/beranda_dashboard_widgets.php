<?php
declare(strict_types=1);

/** @var list<array{id: string, judul: string, tipe_data: string, nilai_kiri: string, nilai_kanan: string, warna_tema: string, urutan: int, aktif: int}> $berandaDashboardWidgets */
/** @var array<string, array{selesai: list<array{id: string, nama_opd: string, alasan: string}>, belum: list<array{id: string, nama_opd: string, alasan: string}>, dalam_pengerjaan: list<array{id: string, nama_opd: string, alasan: string}>}> $berandaWidgetDetailsMap */
$berandaDashboardWidgets = $berandaDashboardWidgets ?? [];
$berandaWidgetDetailsMap = $berandaWidgetDetailsMap ?? [];
if (count($berandaDashboardWidgets) === 0) {
    return;
}
$widgetCount = count($berandaDashboardWidgets);
$govKpiModalPayload = [];
foreach ($berandaDashboardWidgets as $wPayload) {
    $widPayload = (string) ($wPayload['id'] ?? '');
    if ($widPayload === '') {
        continue;
    }
    $detPayload = $berandaWidgetDetailsMap[$widPayload] ?? org_widget_details_empty_grouped();
    $govKpiModalPayload[$widPayload] = [
        'judul' => (string) ($wPayload['judul'] ?? ''),
        'warna' => org_dashboard_widgets_normalize_warna((string) ($wPayload['warna_tema'] ?? 'primary')),
        'selesai' => $detPayload['selesai'] ?? [],
        'dalam_pengerjaan' => $detPayload['dalam_pengerjaan'] ?? [],
        'belum' => $detPayload['belum'] ?? [],
    ];
}
$govKpiModalJson = json_encode($govKpiModalPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($govKpiModalJson === false) {
    $govKpiModalJson = '{}';
}
?>
        <section class="beranda-section gov-kpi-section" id="beranda-dashboard-widgets" aria-labelledby="beranda-dashboard-widgets-title">
            <div class="gov-kpi-section__shell">
                <header class="gov-kpi-section__header">
                    <div class="gov-kpi-section__heading">
                        <h2 id="beranda-dashboard-widgets-title" class="gov-kpi-section__title">Indikator &amp; Statistik</h2>
                        <p class="gov-kpi-section__subtitle">Monitoring Real Time Capaian OPD</p>
                    </div>
                    <div class="gov-kpi-section__meta" aria-hidden="true">
                        <span class="gov-kpi-section__live"><span class="gov-kpi-section__live-dot"></span>Live</span>
                        <span class="gov-kpi-section__count"><?php echo (int) $widgetCount; ?> indikator</span>
                    </div>
                </header>

                <div class="row g-3 gov-kpi-grid row-cols-1<?php echo $widgetCount > 1 ? ' row-cols-lg-2' : ''; ?>">
                    <?php foreach ($berandaDashboardWidgets as $w): ?>
                        <?php
                        $wId = (string) ($w['id'] ?? '');
                        $wJudul = (string) ($w['judul'] ?? '');
                        $wTipe = (string) ($w['tipe_data'] ?? 'progres_angka');
                        $wKiri = (string) ($w['nilai_kiri'] ?? '');
                        $wKanan = (string) ($w['nilai_kanan'] ?? '');
                        $wWarna = org_dashboard_widgets_normalize_warna((string) ($w['warna_tema'] ?? 'primary'));
                        $pct = org_dashboard_widgets_hitung_persen($wKiri, $wKanan);
                        $wIcon = org_dashboard_widgets_icon_class($wTipe, $wWarna);
                        $isCompare = $wTipe === 'perbandingan_nilai';
                        $status = org_dashboard_widgets_progress_status($pct);
                        $trend = org_dashboard_widgets_trend_meta($pct);
                        $infoCaption = org_dashboard_widgets_info_caption($wKiri, $wKanan, $wTipe);
                        $pctInt = (int) round($pct);
                        $pctDisplay = rtrim(rtrim(number_format($pct, 1, ',', '.'), '0'), ',');
                        $trendIcon = $trend['dir'] === 'up' ? 'arrow-trend-up' : ($trend['dir'] === 'down' ? 'arrow-trend-down' : 'minus');
                        $wDetails = $berandaWidgetDetailsMap[$wId] ?? org_widget_details_empty_grouped();
                        $detailCount = count($wDetails['selesai']) + count($wDetails['dalam_pengerjaan']) + count($wDetails['belum']);
                        $hasDetails = $detailCount > 0;
                        ?>
                        <div class="col">
                            <article class="gov-kpi-card gov-kpi-card--<?php echo htmlspecialchars($wWarna, ENT_QUOTES, 'UTF-8'); ?><?php echo $isCompare ? ' gov-kpi-card--compare' : ''; ?>">
                                <div class="gov-kpi-card__row">
                                    <div class="gov-kpi-card__icon" aria-hidden="true">
                                        <i class="<?php echo htmlspecialchars($wIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                                    </div>
                                    <div class="gov-kpi-card__content">
                                        <div class="gov-kpi-card__head">
                                            <h3 class="gov-kpi-card__title"><?php echo htmlspecialchars($wJudul, ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <div class="gov-kpi-card__head-aside">
                                                <?php if (!$isCompare): ?>
                                                    <span class="gov-kpi-card__pct" aria-label="<?php echo htmlspecialchars($pctDisplay . ' persen', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($pctDisplay, ENT_QUOTES, 'UTF-8'); ?><span class="gov-kpi-card__pct-unit">%</span></span>
                                                <?php endif; ?>
                                                <span class="gov-kpi-card__badge gov-kpi-card__badge--<?php echo htmlspecialchars($isCompare ? 'compare' : $status['tone'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo $isCompare ? 'Perbandingan' : htmlspecialchars($status['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <?php if ($isCompare): ?>
                                            <div class="gov-kpi-card__compare" role="group" aria-label="<?php echo htmlspecialchars($wJudul, ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="gov-kpi-card__compare-from"><?php echo htmlspecialchars($wKiri, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="gov-kpi-card__compare-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right-long"></i></span>
                                                <span class="gov-kpi-card__compare-to"><?php echo htmlspecialchars($wKanan, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <?php if ($hasDetails): ?>
                                                <div class="gov-kpi-card__actions mt-2">
                                                    <span></span>
                                                    <button type="button"
                                                            class="btn btn-sm gov-kpi-card__detail-btn"
                                                            data-gov-kpi-detail="<?php echo htmlspecialchars($wId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#govKpiDetailModal"
                                                            aria-label="Lihat detail OPD untuk <?php echo htmlspecialchars($wJudul, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                                        <span>Lihat Detail</span>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($infoCaption !== ''): ?>
                                                <p class="gov-kpi-card__info"><?php echo htmlspecialchars($infoCaption, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                            <div class="gov-kpi-card__progress" role="progressbar"
                                                 aria-valuenow="<?php echo $pctInt; ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"
                                                 aria-label="<?php echo htmlspecialchars($wJudul . ': ' . $pctDisplay . ' persen', ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="gov-kpi-card__progress-fill" style="--gov-kpi-pct: <?php echo htmlspecialchars((string) $pct, ENT_QUOTES, 'UTF-8'); ?>%;"></span>
                                            </div>
                                            <div class="gov-kpi-card__actions">
                                                <div class="gov-kpi-card__trend gov-kpi-card__trend--<?php echo htmlspecialchars($trend['dir'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa-solid fa-<?php echo htmlspecialchars($trendIcon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                                    <span><?php echo htmlspecialchars($trend['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <?php if ($hasDetails): ?>
                                                    <button type="button"
                                                            class="btn btn-sm gov-kpi-card__detail-btn"
                                                            data-gov-kpi-detail="<?php echo htmlspecialchars($wId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#govKpiDetailModal"
                                                            aria-label="Lihat detail OPD untuk <?php echo htmlspecialchars($wJudul, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                                        <span>Lihat Detail</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <script type="application/json" id="gov-kpi-details-data"><?php echo $govKpiModalJson; ?></script>

            <div class="modal fade gov-kpi-modal" id="govKpiDetailModal" tabindex="-1" aria-labelledby="govKpiDetailModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header gov-kpi-modal__header">
                            <div>
                                <p class="gov-kpi-modal__eyebrow mb-1">Rincian Capaian OPD</p>
                                <h2 class="modal-title h5 mb-0" id="govKpiDetailModalTitle">Detail Indikator</h2>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body gov-kpi-modal__body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="gov-kpi-modal__panel gov-kpi-modal__panel--selesai">
                                        <h3 class="gov-kpi-modal__panel-title">
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            Selesai
                                            <span class="badge rounded-pill" id="govKpiDetailCountSelesai">0</span>
                                        </h3>
                                        <ul class="gov-kpi-modal__list list-unstyled mb-0" id="govKpiDetailListSelesai"></ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="gov-kpi-modal__panel gov-kpi-modal__panel--proses">
                                        <h3 class="gov-kpi-modal__panel-title">
                                            <i class="fa-solid fa-spinner" aria-hidden="true"></i>
                                            Dalam Pengerjaan
                                            <span class="badge rounded-pill" id="govKpiDetailCountProses">0</span>
                                        </h3>
                                        <ul class="gov-kpi-modal__list list-unstyled mb-0" id="govKpiDetailListProses"></ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="gov-kpi-modal__panel gov-kpi-modal__panel--belum">
                                        <h3 class="gov-kpi-modal__panel-title">
                                            <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                                            Belum Ditambahkan
                                            <span class="badge rounded-pill" id="govKpiDetailCountBelum">0</span>
                                        </h3>
                                        <ul class="gov-kpi-modal__list list-unstyled mb-0" id="govKpiDetailListBelum"></ul>
                                    </div>
                                </div>
                            </div>
                            <p class="gov-kpi-modal__empty text-muted small mb-0 d-none" id="govKpiDetailEmpty">Belum ada data detail OPD untuk indikator ini.</p>
                        </div>
                        <div class="modal-footer gov-kpi-modal__footer border-0">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
        (function () {
            var dataEl = document.getElementById('gov-kpi-details-data');
            var modalEl = document.getElementById('govKpiDetailModal');
            if (!dataEl || !modalEl) return;
            var store = {};
            try { store = JSON.parse(dataEl.textContent || '{}'); } catch (e) { store = {}; }
            var titleEl = document.getElementById('govKpiDetailModalTitle');
            var listSelesai = document.getElementById('govKpiDetailListSelesai');
            var listProses = document.getElementById('govKpiDetailListProses');
            var listBelum = document.getElementById('govKpiDetailListBelum');
            var countSelesai = document.getElementById('govKpiDetailCountSelesai');
            var countProses = document.getElementById('govKpiDetailCountProses');
            var countBelum = document.getElementById('govKpiDetailCountBelum');
            var emptyEl = document.getElementById('govKpiDetailEmpty');
            function esc(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }
            function renderList(ul, items, showAlasan) {
                ul.innerHTML = '';
                if (!items.length) {
                    var li = document.createElement('li');
                    li.className = 'gov-kpi-modal__list-empty';
                    li.textContent = '— Tidak ada data —';
                    ul.appendChild(li);
                    return;
                }
                items.forEach(function (item) {
                    var li = document.createElement('li');
                    li.className = 'gov-kpi-modal__list-item';
                    var html = '<span class="gov-kpi-modal__opd-name">' + esc(item.nama_opd || '') + '</span>';
                    if (showAlasan && item.alasan) {
                        html += '<span class="gov-kpi-modal__alasan">' + esc(item.alasan) + '</span>';
                    }
                    li.innerHTML = html;
                    ul.appendChild(li);
                });
            }
            document.querySelectorAll('[data-gov-kpi-detail]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-gov-kpi-detail');
                    var pack = store[id] || { judul: '', selesai: [], dalam_pengerjaan: [], belum: [] };
                    if (titleEl) titleEl.textContent = pack.judul || 'Detail Indikator';
                    var selesai = pack.selesai || pack.sudah || [];
                    var proses = pack.dalam_pengerjaan || [];
                    var belum = pack.belum || [];
                    if (countSelesai) countSelesai.textContent = String(selesai.length);
                    if (countProses) countProses.textContent = String(proses.length);
                    if (countBelum) countBelum.textContent = String(belum.length);
                    renderList(listSelesai, selesai, false);
                    renderList(listProses, proses, true);
                    renderList(listBelum, belum, true);
                    if (emptyEl) {
                        emptyEl.classList.toggle('d-none', selesai.length + proses.length + belum.length > 0);
                    }
                });
            });
        }());
        </script>
