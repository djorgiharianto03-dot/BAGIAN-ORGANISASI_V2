<?php

/** @var int $sgPortalDocCount @var int $sgPortalInfoCount @var int $berandaTotalToday @var int $berandaTotalWeek */

$sgQuickLinks = [
    [
        'href' => 'index.php#beranda-dashboard-widgets',
        'icon' => 'fa-chart-line',
        'label' => 'Dashboard Kinerja',
        'desc' => 'Indikator & capaian OPD',
    ],
    [
        'href' => 'index.php#beranda-pusat-informasi',
        'icon' => 'fa-bullhorn',
        'label' => 'Pengumuman',
        'desc' => 'Informasi resmi terbaru',
    ],
    [
        'href' => 'profil.php#profil-struktur-organisasi',
        'icon' => 'fa-sitemap',
        'label' => 'Struktur Organisasi',
        'desc' => 'Visi & tata kelola',
    ],
];

$sgEOrgQuick = [
    'href' => 'e_organisasi.php',
    'icon' => 'fa-network-wired',
    'label' => 'E-Organisasi',
    'desc' => 'Akses internal',
];
if (empty($canAccessEOrganisasi)) {
    $sgEOrgQuick['href'] = '#loginModal';
    $sgEOrgQuick['open_login_modal'] = true;
}
$sgQuickLinks[] = $sgEOrgQuick;

?>
<section class="hero-section sg-hero sg-hero--compact sg-hero--minimal" id="sg-hero" aria-labelledby="beranda-hero-title">
    <div class="sg-hero__bg" aria-hidden="true"></div>

    <div class="container-global hero-inner hero-inner--stacked">
        <div class="sg-hero__copy hero-text">
            <p class="sg-hero__eyebrow sg-hero__title-secondary">Smart Governance Portal</p>
            <h1 class="sg-hero__title" id="beranda-hero-title">
                <span class="sg-hero__title-primary">Bagian Organisasi</span>
                <span class="sg-hero__title-org">Sekretariat Daerah Kabupaten Kepulauan Aru</span>
            </h1>
            <p class="sg-hero__tagline">Portal informasi, layanan, dan monitoring kinerja Bagian Organisasi.</p>
            <div class="sg-hero__cta">
                <a href="dokumen.php" class="sg-btn sg-btn--hero-primary">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                    Pusat Dokumen
                </a>
                <a href="layanan.php" class="sg-btn sg-btn--hero-secondary">
                    <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                    Layanan Publik
                </a>
            </div>
        </div>

        <nav class="sg-quick-menu sg-hero__quick" aria-label="Fitur strategis portal">
            <?php foreach ($sgQuickLinks as $q): ?>
                <a href="<?php echo htmlspecialchars((string) $q['href'], ENT_QUOTES, 'UTF-8'); ?>" class="sg-quick-menu__card"<?php if (!empty($q['open_login_modal'])): ?> data-bs-toggle="modal" data-bs-target="#loginModal" role="button"<?php endif; ?>>
                    <span class="sg-quick-menu__icon" aria-hidden="true"><i class="fa-solid <?php echo htmlspecialchars((string) $q['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                    <span class="sg-quick-menu__text">
                        <span class="sg-quick-menu__label"><?php echo htmlspecialchars((string) $q['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="sg-quick-menu__desc"><?php echo htmlspecialchars((string) $q['desc'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                    <i class="fa-solid fa-arrow-right sg-quick-menu__arrow" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="container-global stats-grid">
        <div class="sg-hero-stats" role="group" aria-label="Statistik ringkas portal">
            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">
                <div class="sg-stat-glass__body">
                    <p class="sg-stat-glass__num mb-0"><?php echo (int) $berandaTotalToday; ?></p>
                    <p class="sg-stat-glass__label mb-0">Tamu Hari Ini</p>
                </div>
            </article>
            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">
                <div class="sg-stat-glass__body">
                    <p class="sg-stat-glass__num mb-0"><?php echo (int) $berandaTotalWeek; ?></p>
                    <p class="sg-stat-glass__label mb-0">Kunjungan 7 Hari</p>
                </div>
            </article>
            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">
                <div class="sg-stat-glass__body">
                    <p class="sg-stat-glass__num mb-0"><?php echo (int) $sgPortalDocCount; ?></p>
                    <p class="sg-stat-glass__label mb-0">Dokumen Digital</p>
                </div>
            </article>
            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">
                <div class="sg-stat-glass__body">
                    <p class="sg-stat-glass__num mb-0"><?php echo (int) $sgPortalInfoCount; ?></p>
                    <p class="sg-stat-glass__label mb-0">Publikasi Aktif</p>
                </div>
            </article>
        </div>
    </div>
</section>
