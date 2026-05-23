<?php
declare(strict_types=1);

/** @var list<string> $berandaVisitLabels @var list<int> $berandaVisitValues */
/** @var int $berandaTotalToday @var int $berandaTotalWeek */
/** @var int $sgPortalDocCount @var int $sgPortalInfoCount @var int $sgPortalGaleriCount */
/** @var int $sgPortalLayananCount @var list<array<string, mixed>> $pusatInformasiPosts */
/** @var list<string> $libraryDocumentFiles @var array<string, array<string, mixed>> $libraryDocumentStatsMap */
/** @var string $prosesSaranUrlEsc */

$berandaVisitLabels = $berandaVisitLabels ?? [];
$berandaVisitValues = $berandaVisitValues ?? [];
$pusatInformasiPosts = $pusatInformasiPosts ?? [];
$libraryDocumentFiles = $libraryDocumentFiles ?? [];
$libraryDocumentStatsMap = $libraryDocumentStatsMap ?? [];

$sgChartLabelsJson = json_encode($berandaVisitLabels, JSON_UNESCAPED_UNICODE);
$sgChartValuesJson = json_encode($berandaVisitValues, JSON_UNESCAPED_UNICODE);
if ($sgChartLabelsJson === false) {
    $sgChartLabelsJson = '[]';
}
if ($sgChartValuesJson === false) {
    $sgChartValuesJson = '[]';
}

$layananFeatured = [];
$layananFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'layanan_data.json';
if (is_file($layananFile)) {
    $raw = file_get_contents($layananFile);
    if ($raw !== false && $raw !== '') {
        $parsed = json_decode($raw, true);
        if (is_array($parsed)) {
            foreach (array_slice($parsed, 0, 6) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $nama = trim((string) ($row['nama'] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $layananFeatured[] = [
                    'nama' => $nama,
                    'deskripsi' => trim((string) ($row['deskripsi'] ?? '')),
                    'kategori' => trim((string) ($row['kategori'] ?? 'Layanan')),
                    'link' => trim((string) ($row['link'] ?? '')),
                ];
            }
        }
    }
}

$topDocs = [];
foreach (array_slice($libraryDocumentFiles, 0, 8) as $fn) {
    $fn = (string) $fn;
    if ($fn === '') {
        continue;
    }
    $stat = $libraryDocumentStatsMap[$fn] ?? [];
    $topDocs[] = [
        'name' => $fn,
        'downloads' => (int) ($stat['download_count'] ?? 0),
    ];
}
usort($topDocs, static function (array $a, array $b): int {
    return ($b['downloads'] <=> $a['downloads']) ?: strcmp($a['name'], $b['name']);
});
$topDocs = array_slice($topDocs, 0, 5);

$timelineItems = [];
foreach (array_slice($pusatInformasiPosts, 0, 5) as $pi) {
    if (!is_array($pi)) {
        continue;
    }
    $timeLabel = '';
    $rawCreated = (string) ($pi['created_at'] ?? '');
    if ($rawCreated !== '') {
        $tsCreated = strtotime($rawCreated);
        $timeLabel = $tsCreated !== false ? date('d M Y', $tsCreated) : $rawCreated;
    }
    $timelineItems[] = [
        'title' => (string) ($pi['judul'] ?? 'Informasi'),
        'time' => $timeLabel,
        'kat' => ($pi['kategori'] ?? '') === 'pengumuman' ? 'Pengumuman' : 'Berita',
    ];
}
if (count($timelineItems) === 0) {
    $timelineItems[] = [
        'title' => 'Portal Smart Governance aktif',
        'time' => date('d M Y'),
        'kat' => 'Sistem',
    ];
}

$transparencyPct = min(100, max(5, (int) round(($berandaTotalWeek / max(1, count($berandaVisitValues))) * 12)));
?>

<!-- 2. Dashboard transparansi publik -->
<section class="sg-section sg-section-embed" id="sg-section-transparency" aria-labelledby="sg-transparency-title">
    <div class="container-global">
        <header class="sg-section__head">
            <div>
                <p class="sg-section__eyebrow">Transparansi</p>
                <h2 id="sg-transparency-title" class="sg-section__title">Dashboard Transparansi Publik</h2>
                <p class="sg-section__desc">Pemantauan kunjungan dan aktivitas portal secara terbuka untuk masyarakat.</p>
            </div>
            <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="fa-solid fa-signal me-1"></i> Live</span>
        </header>
        <div class="sg-transparency-grid">
            <div class="row g-3">
                <div class="col-6">
                    <article class="sg-card">
                        <div class="sg-card__icon"><i class="fa-solid fa-user-group"></i></div>
                        <h3 class="sg-card__title">Tamu hari ini</h3>
                        <p class="sg-card__text mb-0"><span class="display-6 fw-bold text-primary" data-sg-count="<?php echo (int) $berandaTotalToday; ?>">0</span></p>
                    </article>
                </div>
                <div class="col-6">
                    <article class="sg-card">
                        <div class="sg-card__icon"><i class="fa-solid fa-calendar-week"></i></div>
                        <h3 class="sg-card__title">7 hari</h3>
                        <p class="sg-card__text mb-0"><span class="display-6 fw-bold text-primary" data-sg-count="<?php echo (int) $berandaTotalWeek; ?>">0</span></p>
                    </article>
                </div>
                <div class="col-12">
                    <article class="sg-card">
                        <h3 class="sg-card__title">Indeks keterbukaan portal</h3>
                        <p class="sg-card__text">Estimasi engagement berdasarkan kunjungan mingguan.</p>
                        <div class="sg-progress" role="progressbar" aria-valuenow="<?php echo $transparencyPct; ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="sg-progress__fill" data-sg-pct="<?php echo $transparencyPct; ?>"></div>
                        </div>
                        <p class="small text-muted mb-0 mt-2"><?php echo $transparencyPct; ?>% · <?php echo (int) $sgPortalInfoCount; ?> publikasi · <?php echo (int) $sgPortalDocCount; ?> dokumen</p>
                    </article>
                </div>
            </div>
            <div class="sg-transparency-chart">
                <p class="small fw-semibold text-muted mb-2">Tren kunjungan 14 hari</p>
                <canvas
                    id="sgTransparencyChart"
                    data-labels="<?php echo htmlspecialchars($sgChartLabelsJson, ENT_QUOTES, 'UTF-8'); ?>"
                    data-values="<?php echo htmlspecialchars($sgChartValuesJson, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-label="Grafik kunjungan tamu"
                ></canvas>
            </div>
        </div>
    </div>
</section>

<!-- 3. Layanan publik unggulan -->
<section class="sg-section sg-section--alt sg-section-embed" id="sg-section-layanan" aria-labelledby="sg-layanan-title">
    <div class="container-global">
        <header class="sg-section__head">
            <div>
                <p class="sg-section__eyebrow">Pelayanan</p>
                <h2 id="sg-layanan-title" class="sg-section__title">Layanan Publik Unggulan</h2>
                <p class="sg-section__desc">Akses cepat ke layanan digital Bagian Organisasi.</p>
            </div>
            <a href="<?php echo org_href('layanan.php'); ?>" class="sg-section__link">Semua layanan <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </header>
        <div class="row g-3">
            <?php if (count($layananFeatured) === 0): ?>
                <div class="col-12">
                    <p class="text-muted mb-0">Belum ada layanan terdaftar. Kunjungi halaman Layanan untuk informasi lebih lanjut.</p>
                </div>
            <?php else: ?>
                <?php foreach ($layananFeatured as $lay): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="sg-card">
                        <div class="sg-card__icon"><i class="fa-solid fa-handshake"></i></div>
                        <h3 class="sg-card__title"><?php echo htmlspecialchars($lay['nama'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="sg-card__text"><?php echo htmlspecialchars(mb_strimwidth($lay['deskripsi'] !== '' ? $lay['deskripsi'] : $lay['kategori'], 0, 100, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="<?php echo org_href('layanan.php'); ?>" class="sg-section__link small">Detail layanan</a>
                    </article>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 6. Timeline aktivitas -->
<section class="sg-section sg-section-embed" id="sg-section-timeline" aria-labelledby="sg-timeline-title">
    <div class="container-global">
        <header class="sg-section__head">
            <div>
                <p class="sg-section__eyebrow">Aktivitas</p>
                <h2 id="sg-timeline-title" class="sg-section__title">Timeline Aktivitas</h2>
                <p class="sg-section__desc">Update informasi dan publikasi terbaru dari organisasi.</p>
            </div>
            <a href="<?php echo org_href('berita.php'); ?>" class="sg-section__link">Arsip lengkap</a>
        </header>
        <div class="row">
            <div class="col-lg-7">
                <div class="sg-card">
                    <div class="sg-timeline">
                        <?php foreach ($timelineItems as $ti): ?>
                        <article class="sg-timeline__item">
                            <p class="sg-timeline__time mb-0"><?php echo htmlspecialchars((string) ($ti['time'] !== '' ? $ti['time'] : $ti['kat']), ENT_QUOTES, 'UTF-8'); ?></p>
                            <h3 class="sg-timeline__title"><?php echo htmlspecialchars((string) $ti['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="sg-timeline__text"><?php echo htmlspecialchars((string) $ti['kat'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="sg-card h-100 d-flex flex-column justify-content-center text-center p-4">
                    <div class="sg-card__icon mx-auto"><i class="fa-solid fa-chart-pie"></i></div>
                    <h3 class="sg-card__title">Analytics &amp; Governance</h3>
                    <p class="sg-card__text">Monitoring capaian, dokumen, dan layanan dalam satu ekosistem digital pemerintahan.</p>
                    <div class="row g-2 mt-2 text-start">
                        <div class="col-6"><span class="badge text-bg-light border w-100 py-2"><?php echo (int) $sgPortalGaleriCount; ?> galeri</span></div>
                        <div class="col-6"><span class="badge text-bg-light border w-100 py-2"><?php echo (int) $sgPortalInfoCount; ?> informasi</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Digital document center -->
<section class="sg-section sg-section--alt sg-section-embed" id="sg-section-documents" aria-labelledby="sg-documents-title">
    <div class="container-global">
        <header class="sg-section__head">
            <div>
                <p class="sg-section__eyebrow">Perpustakaan</p>
                <h2 id="sg-documents-title" class="sg-section__title">Digital Document Center</h2>
                <p class="sg-section__desc">Unduh dokumen resmi kelembagaan, pelayanan, dan SAKIP.</p>
            </div>
            <a href="<?php echo org_href('dokumen.php'); ?>" class="sg-section__link">Buka perpustakaan</a>
        </header>
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="sg-card">
                    <?php if (count($topDocs) === 0): ?>
                        <p class="text-muted mb-0">Belum ada dokumen di perpustakaan digital.</p>
                    <?php else: ?>
                        <ul class="sg-doc-list">
                            <?php foreach ($topDocs as $doc): ?>
                            <li class="sg-doc-list__item">
                                <span class="sg-doc-list__icon"><i class="fa-solid fa-file-lines"></i></span>
                                <span class="sg-doc-list__name" title="<?php echo htmlspecialchars($doc['name'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($doc['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="badge text-bg-primary rounded-pill"><?php echo (int) $doc['downloads']; ?> unduh</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="sg-card h-100">
                    <div class="sg-card__icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                    <h3 class="sg-card__title">Cari dokumen</h3>
                    <p class="sg-card__text">Gunakan pencarian cepat untuk menemukan regulasi dan dokumen publik.</p>
                    <form method="get" action="<?php echo org_href('dokumen.php'); ?>" class="mt-3">
                        <label class="visually-hidden" for="sgDocSearch">Cari dokumen</label>
                        <div class="input-group">
                            <input type="search" class="form-control rounded-start-pill" id="sgDocSearch" name="q" placeholder="Kata kunci dokumen…">
                            <button type="submit" class="btn btn-primary rounded-end-pill px-4">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. Smart complaint system -->
<section class="sg-section sg-section-embed" id="sg-section-complaint" aria-labelledby="sg-complaint-title">
    <div class="container-global">
        <header class="sg-section__head">
            <div>
                <p class="sg-section__eyebrow">Partisipasi</p>
                <h2 id="sg-complaint-title" class="sg-section__title">Smart Complaint System</h2>
                <p class="sg-section__desc">Sampaikan saran, kritik, dan masukan untuk perbaikan layanan publik digital.</p>
            </div>
        </header>
        <div class="sg-complaint">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <h3 class="h4 fw-bold mb-2">Suara Anda penting</h3>
                    <p class="mb-0 opacity-90">Masukan Anda membantu kami meningkatkan transparansi dan kualitas pelayanan organisasi.</p>
                </div>
                <div class="col-lg-7">
                    <form id="formSaranPortal" class="row g-3" method="post" action="<?php echo $prosesSaranUrlEsc; ?>" data-saran-endpoint="<?php echo $prosesSaranUrlEsc; ?>" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-md-6">
                            <label class="form-label" for="sg_saran_nama">Nama</label>
                            <input type="text" class="form-control" id="sg_saran_nama" name="nama" required maxlength="190" placeholder="Nama lengkap">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sg_saran_email">Email</label>
                            <input type="email" class="form-control" id="sg_saran_email" name="email" required maxlength="190" placeholder="email@contoh.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="sg_saran_pesan">Pesan</label>
                            <textarea class="form-control" id="sg_saran_pesan" name="pesan" rows="4" required maxlength="20000" placeholder="Tulis masukan Anda…"></textarea>
                        </div>
                        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                            <button type="submit" class="sg-btn sg-btn--primary border-0">
                                <i class="fa-solid fa-paper-plane"></i> Kirim masukan
                            </button>
                            <div class="small opacity-85" id="sgSaranPortalStatus" role="status" aria-live="polite"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
