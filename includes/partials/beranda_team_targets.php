<?php

/** @var bool $berandaTeamTargetsVisible */
/** @var int $berandaTeamTargetsTahun */
/** @var list<int> $berandaTeamTargetsYears */
/** @var array{kelembagaan: list<array{id: string, kegiatan: string, status: string}>, rb: list<array{id: string, kegiatan: string, status: string}>, yanlik: list<array{id: string, kegiatan: string, status: string}>} $berandaTeamTargetsGrouped */
if (empty($berandaTeamTargetsVisible) && !(defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true)) {
    return;
}
if (empty($berandaTeamTargetsVisible)) {
    $berandaTeamTargetsVisible = true;
    if ($berandaTeamTargetsYears === []) {
        $berandaTeamTargetsYears = [$berandaTeamTargetsTahun];
    }
}
$berandaTeamTargetsTahun = org_team_targets_normalize_tahun($berandaTeamTargetsTahun ?? (int) date('Y'));
$berandaTeamTargetsYears = $berandaTeamTargetsYears ?? [(int) date('Y')];
$berandaTeamTargetsGrouped = $berandaTeamTargetsGrouped ?? org_team_targets_empty_grouped();

$totalTargets = 0;
$govTeamTargetChartPayload = [];
$govTeamTargetOverview = [];
foreach (org_team_targets_tim_list() as $timKey) {
    $items = $berandaTeamTargetsGrouped[$timKey] ?? [];
    $enriched = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $st = (string) ($item['status'] ?? 'direncanakan');
        $kegiatan = (string) ($item['kegiatan'] ?? '');
        $pct = org_team_targets_status_progress_pct($st);
        $enriched[] = [
            'id' => (string) ($item['id'] ?? ''),
            'kegiatan' => $kegiatan,
            'status' => $st,
            'pct' => $pct,
            'status_label' => org_team_targets_status_label($st),
            'target_caption' => org_team_targets_activity_target_caption($kegiatan, $st, $berandaTeamTargetsTahun),
        ];
        $totalTargets++;
    }
    $avgPct = org_team_targets_tim_average_progress($enriched);
    $overviewStatus = org_team_targets_status_from_avg_pct($avgPct);
    $govTeamTargetOverview[] = [
        'tim' => $timKey,
        'label' => org_team_targets_tim_chart_label($timKey),
        'fullLabel' => org_team_targets_tim_label($timKey),
        'pct' => $avgPct,
        'count' => count($enriched),
        'color' => org_team_targets_status_accent_color_light($overviewStatus),
        'colorDeep' => org_team_targets_status_accent_color($overviewStatus),
    ];
    if ($enriched === []) {
        continue;
    }
    $gaugeStatus = org_team_targets_items_dominant_status($enriched);
    $govTeamTargetChartPayload[$timKey] = [
        'label' => org_team_targets_tim_label($timKey),
        'pct' => $avgPct,
        'status' => $gaugeStatus,
        'color' => org_team_targets_status_accent_color($gaugeStatus),
        'colorLight' => org_team_targets_status_accent_color_light($gaugeStatus),
        'count' => count($enriched),
        'items' => $enriched,
    ];
}
$govTeamTargetChartJson = json_encode(
    [
        'overview' => $govTeamTargetOverview,
        'teams' => $govTeamTargetChartPayload,
    ],
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
if ($govTeamTargetChartJson === false) {
    $govTeamTargetChartJson = '{}';
}

$timIcons = [
    'kelembagaan' => 'fa-sitemap',
    'rb' => 'fa-chart-line',
    'yanlik' => 'fa-handshake',
];
$baseUrl = strtok($_SERVER['REQUEST_URI'] ?? 'index.php', '?') ?: 'index.php';
?>
        <section class="beranda-section beranda-section--surface-white gov-team-target-section gov-team-target-dashboard" id="beranda-team-targets" aria-labelledby="beranda-team-targets-title">
            <div class="gov-team-target-section__shell gov-team-target-section__shell--glass">
                <header class="gov-team-target-section__header">
                    <div class="gov-team-target-section__heading">
                        <h2 id="beranda-team-targets-title" class="gov-team-target-section__title">
                            Target Tahun <?php echo (int) $berandaTeamTargetsTahun; ?>
                        </h2>
                        <p class="gov-team-target-section__subtitle">Dashboard capaian target tahunan Tim Kerja</p>
                    </div>
                    <?php if (count($berandaTeamTargetsYears) > 1): ?>
                        <nav class="gov-team-target-section__years" aria-label="Filter tahun target">
                            <?php foreach ($berandaTeamTargetsYears as $y): ?>
                                <?php $y = (int) $y; ?>
                                <a href="<?php echo htmlspecialchars($baseUrl . '?tahun=' . $y, ENT_QUOTES, 'UTF-8'); ?>#beranda-team-targets"
                                   class="gov-team-target-section__year-btn<?php echo $y === $berandaTeamTargetsTahun ? ' is-active' : ''; ?>"
                                   <?php echo $y === $berandaTeamTargetsTahun ? 'aria-current="true"' : ''; ?>>
                                    <?php echo $y; ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </header>

                <?php if ($totalTargets === 0): ?>
                    <p class="gov-team-target-section__empty text-muted small mb-3">
                        Belum ada target kegiatan untuk tahun <?php echo (int) $berandaTeamTargetsTahun; ?>. Grafik akumulasi tetap ditampilkan setelah data diisi di admin.
                    </p>
                <?php else: ?>

                    <div class="row g-4 gov-team-target-dash-grid">
                        <?php foreach (org_team_targets_tim_list() as $tim): ?>
                            <?php
                            $pack = $govTeamTargetChartPayload[$tim] ?? null;
                            if ($pack === null) {
                                continue;
                            }
                            $icon = $timIcons[$tim] ?? 'fa-users';
                            $accordionId = 'govTeamTargetAcc-' . $tim;
                            $avgPct = (int) ($pack['pct'] ?? 0);
                            $gaugeStatus = (string) ($pack['status'] ?? org_team_targets_status_from_avg_pct($avgPct));
                            ?>
                            <div class="col-12 col-md-4 d-flex">
                                <article class="gov-team-target-dash-card gov-team-target-dash-card--<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?> gov-team-target-dash-card--status-<?php echo htmlspecialchars($gaugeStatus, ENT_QUOTES, 'UTF-8'); ?> w-100">
                                    <header class="gov-team-target-dash-card__head">
                                        <span class="gov-team-target-dash-card__icon" aria-hidden="true">
                                            <i class="fa-solid <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                                        </span>
                                        <div class="gov-team-target-dash-card__titles">
                                            <h3 class="gov-team-target-dash-card__title"><?php echo htmlspecialchars((string) $pack['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="gov-team-target-dash-card__meta mb-0"><?php echo (int) ($pack['count'] ?? 0); ?> kegiatan · rata-rata <?php echo $avgPct; ?>%</p>
                                        </div>
                                    </header>

                                    <div class="gov-team-target-dash-card__chart-wrap">
                                        <div id="govTeamTargetChart-<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>"
                                             class="gov-team-target-dash-card__chart"
                                             data-tim="<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>"
                                             role="img"
                                             aria-label="Grafik progres rata-rata <?php echo htmlspecialchars((string) $pack['label'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo $avgPct; ?> persen"></div>
                                    </div>

                                    <div class="accordion gov-team-target-accordion" id="<?php echo htmlspecialchars($accordionId, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php foreach ($pack['items'] as $idx => $item): ?>
                                            <?php
                                            $collapseId = $accordionId . '-item-' . $idx;
                                            $st = (string) ($item['status'] ?? 'direncanakan');
                                            $pctItem = (int) ($item['pct'] ?? 0);
                                            $dotClass = org_team_targets_status_dot_class($st);
                                            $isFirst = $idx === 0;
                                            ?>
                                            <div class="accordion-item gov-team-target-accordion__item">
                                                <h4 class="accordion-header" id="heading-<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button class="accordion-button<?php echo $isFirst ? '' : ' collapsed'; ?> gov-team-target-accordion__btn"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            aria-expanded="<?php echo $isFirst ? 'true' : 'false'; ?>"
                                                            aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <span class="gov-team-target-dot <?php echo htmlspecialchars($dotClass, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true" title="<?php echo htmlspecialchars((string) ($item['status_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></span>
                                                        <span class="gov-team-target-accordion__name"><?php echo htmlspecialchars((string) ($item['kegiatan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <span class="gov-team-target-accordion__pct"><?php echo $pctItem; ?>%</span>
                                                    </button>
                                                </h4>
                                                <div id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                                                     class="accordion-collapse collapse<?php echo $isFirst ? ' show' : ''; ?>"
                                                     aria-labelledby="heading-<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                                                     data-bs-parent="#<?php echo htmlspecialchars($accordionId, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div class="accordion-body gov-team-target-accordion__body">
                                                        <p class="gov-team-target-accordion__caption"><?php echo htmlspecialchars((string) ($item['target_caption'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <div class="gov-team-target-slim-progress" role="progressbar"
                                                             aria-valuenow="<?php echo $pctItem; ?>"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100"
                                                             aria-label="Progres <?php echo htmlspecialchars((string) ($item['kegiatan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <span class="gov-team-target-slim-progress__fill gov-team-target-slim-progress__fill--<?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?>" style="width: <?php echo $pctItem; ?>%;"></span>
                                                        </div>
                                                        <span class="gov-team-target-accordion__status-tag <?php echo htmlspecialchars(org_team_targets_status_bs_badge($st), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php echo htmlspecialchars((string) ($item['status_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <footer class="gov-team-target-section__legend gov-team-target-section__legend--after-cards" aria-label="Keterangan status">
                        <span class="gov-team-target-status-badge gov-team-target-status-badge--direncanakan">Direncanakan</span>
                        <span class="gov-team-target-status-badge gov-team-target-status-badge--berjalan">Berjalan</span>
                        <span class="gov-team-target-status-badge gov-team-target-status-badge--selesai">Selesai</span>
                    </footer>
                <?php endif; ?>

                <div class="gov-team-target-overview-wrap">
                    <article class="gov-team-target-overview-card" aria-labelledby="gov-team-target-overview-title">
                        <header class="gov-team-target-overview-card__head">
                            <p class="gov-team-target-overview-card__eyebrow">Akumulasi Capaian Bagian Organisasi</p>
                            <h3 id="gov-team-target-overview-title" class="gov-team-target-overview-card__title">Rata-rata Capaian Tim Kerja</h3>
                            <p class="gov-team-target-overview-card__meta mb-0">Perbandingan rata-rata progres ketiga tim berdasarkan target kegiatan tahun <?php echo (int) $berandaTeamTargetsTahun; ?>.</p>
                        </header>
                        <div id="govTeamTargetOverviewChart"
                             class="gov-team-target-overview-card__chart"
                             role="img"
                             aria-label="Grafik akumulasi capaian tim kerja"></div>
                    </article>
                </div>

                    <script type="application/json" id="gov-team-target-charts-data"><?php echo $govTeamTargetChartJson; ?></script>
            </div>
        </section>
