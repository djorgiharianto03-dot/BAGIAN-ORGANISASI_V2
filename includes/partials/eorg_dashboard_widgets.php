<?php

/** @var array<string, mixed> $eorgHubMetrics */
/** @var array{labels: list<string>, tamu: list<int>, disposisi: list<int>} $eorgChart */
/** @var list<array<string, string>> $eorgActivity */

$m = $eorgHubMetrics ?? [];
$chart = $eorgChart ?? ['labels' => [], 'tamu' => [], 'disposisi' => []];
$activity = $eorgActivity ?? [];

$pendingTotal = (int) ($m['badge_arsip'] ?? 0) + (int) ($m['badge_disposisi'] ?? 0) + (int) ($m['badge_tugas'] ?? 0);
$progressPct = (int) ($m['progress_pct'] ?? 0);

$progressItems = [
    [
        'label' => 'Arsip belum disposisi',
        'count' => (int) ($m['badge_arsip'] ?? 0),
        'tone' => 'emerald',
        'icon' => 'fa-folder-open',
    ],
    [
        'label' => 'Disposisi menunggu',
        'count' => (int) ($m['badge_disposisi'] ?? 0),
        'tone' => 'violet',
        'icon' => 'fa-clipboard-check',
    ],
    [
        'label' => 'Tugas perlu validasi',
        'count' => (int) ($m['badge_tugas'] ?? 0),
        'tone' => 'amber',
        'icon' => 'fa-list-check',
    ],
];
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
org_tailwind_bootstrap();
?>
            <div class="org-dash eo-dash">
                <header class="org-dash__header eo-dash__top">
                    <div class="eo-dash__welcome">
                        <p class="org-eyebrow eo-dash__eyebrow">Enterprise Workspace</p>
                        <h2 class="org-heading-2 eo-dash__heading">Ringkasan operasional</h2>
                        <p class="org-text-muted eo-dash__sub">Pantau aktivitas layanan internal Bagian Organisasi secara real-time.</p>
                    </div>
                    <div class="org-card org-card--flat eo-dash__clock" aria-live="polite">
                        <span class="org-dash-stat__label eo-dash__clock-label">Waktu sistem</span>
                        <time class="org-dash-stat__value eo-dash__clock-time" id="eorgHubTime">00:00:00</time>
                        <p class="org-text-muted eo-dash__clock-date" id="eorgHubDayDate">—</p>
                    </div>
                </header>

                <div class="org-dash__stats eo-dash__stats">
                    <?php
                    org_component('dashboard_stat', [
                        'label' => 'Tamu hari ini',
                        'value' => (int) ($m['tamu_today'] ?? 0),
                        'hint' => 'Kunjungan tercatat',
                        'tone' => 'blue',
                        'icon' => 'fa-user-group',
                    ]);
                    org_component('dashboard_stat', [
                        'label' => 'Menunggu tindak lanjut',
                        'value' => $pendingTotal,
                        'hint' => 'Arsip · disposisi · tugas',
                        'tone' => 'violet',
                        'icon' => 'fa-hourglass-half',
                    ]);
                    org_component('dashboard_stat', [
                        'label' => 'Status sistem',
                        'value' => !empty($m['service_online']) ? 'Online' : 'Offline',
                        'hint' => (string) ($m['server_label'] ?? '—'),
                        'tone' => 'emerald',
                        'icon' => 'fa-server',
                    ]);
                    org_component('dashboard_stat', [
                        'label' => 'Progres hari ini',
                        'valueHtml' => '<span id="eorgHubProgressPct">' . $progressPct . '%</span>',
                        'hint' => (string) ($m['progress_hint'] ?? ''),
                        'hintId' => 'eorgHubProgressHint',
                        'tone' => 'amber',
                        'icon' => 'fa-chart-pie',
                    ]);
                    ?>
                </div>

                <div class="org-dash__widgets eo-dash__widgets">
                    <section class="org-dash-widget eo-widget eo-widget--chart" aria-labelledby="eo-chart-title">
                        <header class="eo-widget__head">
                            <h3 id="eo-chart-title" class="org-dash-widget__title eo-widget__title"><i class="fa-solid fa-chart-column" aria-hidden="true"></i> Aktivitas 7 hari</h3>
                        </header>
                        <div class="eo-widget__body">
                            <canvas class="org-dash-widget__chart" id="eorgHubChart" height="200" aria-label="Grafik kunjungan tamu dan disposisi"></canvas>
                        </div>
                    </section>

                    <section class="org-dash-widget eo-widget eo-widget--progress" aria-labelledby="eo-progress-title">
                        <header class="eo-widget__head">
                            <h3 id="eo-progress-title" class="eo-widget__title"><i class="fa-solid fa-bars-progress" aria-hidden="true"></i> Antrian layanan</h3>
                        </header>
                        <div class="eo-widget__body eo-widget__body--stack">
                            <?php foreach ($progressItems as $pi): ?>
                                <?php
                                $cnt = (int) ($pi['count'] ?? 0);
                                $maxRef = max(1, $pendingTotal);
                                $barPct = $pendingTotal > 0 ? (int) round(($cnt / $maxRef) * 100) : 0;
                                ?>
                                <div class="eo-progress-row">
                                    <div class="eo-progress-row__meta">
                                        <span class="eo-progress-row__icon eo-progress-row__icon--<?php echo htmlspecialchars((string) ($pi['tone'] ?? 'blue'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid <?php echo htmlspecialchars((string) ($pi['icon'] ?? 'fa-circle'), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                        </span>
                                        <span class="eo-progress-row__label"><?php echo htmlspecialchars((string) ($pi['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="eo-progress-row__count"><?php echo $cnt; ?></span>
                                    </div>
                                    <div class="org-dash-progress__track eo-progress-row__track" role="presentation">
                                        <span class="org-dash-progress__bar eo-progress-row__fill eo-progress-row__fill--<?php echo htmlspecialchars((string) ($pi['tone'] ?? 'blue'), ENT_QUOTES, 'UTF-8'); ?>" style="width: <?php echo $barPct; ?>%;"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="eo-progress-overall" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo $progressPct; ?>" id="eorgHubProgressBar" data-progress="<?php echo $progressPct; ?>">
                                <div class="eo-progress-overall__label">
                                    <span>Capaian operasional</span>
                                    <strong id="eorgHubProgressPctDup"><?php echo $progressPct; ?>%</strong>
                                </div>
                                <div class="org-dash-progress__track eo-progress-overall__track">
                                    <div class="org-dash-progress__bar eo-progress-overall__fill" id="eorgHubProgressFill"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="org-dash-widget eo-widget eo-widget--feed" aria-labelledby="eo-feed-title">
                        <header class="eo-widget__head">
                            <h3 id="eo-feed-title" class="org-dash-widget__title eo-widget__title"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Aktivitas terbaru</h3>
                        </header>
                        <div class="eo-widget__body">
                            <?php if (count($activity) > 0): ?>
                                <ul class="eo-feed">
                                    <?php foreach ($activity as $act): ?>
                                        <?php
                                        $waktu = (string) ($act['waktu'] ?? '');
                                        $waktuFmt = $waktu !== '' ? date('d/m H:i', strtotime($waktu)) : '—';
                                        $nama = trim((string) ($act['nama_admin'] ?? $act['id_admin'] ?? 'Admin'));
                                        $aksi = trim((string) ($act['aksi'] ?? ''));
                                        ?>
                                        <li class="eo-feed__item">
                                            <span class="eo-feed__dot" aria-hidden="true"></span>
                                            <div class="eo-feed__content">
                                                <p class="eo-feed__text"><?php echo htmlspecialchars($aksi, ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="eo-feed__meta"><?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($waktuFmt, ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="eo-feed__empty mb-0">Belum ada aktivitas audit yang tercatat hari ini.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
