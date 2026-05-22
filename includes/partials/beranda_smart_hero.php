<?php

declare(strict_types=1);



/** @var int $sgPortalDocCount @var int $sgPortalInfoCount @var int $sgPortalGaleriCount */

/** @var int $sgPortalLayananCount @var int $berandaTotalToday @var int $berandaTotalWeek */



/**
 * Fitur strategis — bukan duplikat navbar/hero CTA (Dokumen & Layanan hanya di nav + hero).
 */
$sgQuickLinks = [
    [
        'href' => 'index.php#beranda-dashboard-widgets',
        'icon' => 'fa-chart-line',
        'label' => 'Dashboard Kinerja',
        'desc' => 'Monitoring indikator & capaian OPD',
    ],
    [
        'href' => 'index.php#beranda-pusat-informasi',
        'icon' => 'fa-bullhorn',
        'label' => 'Pengumuman Terbaru',
        'desc' => 'Informasi & pengumuman resmi',
    ],
    [
        'href' => 'profil.php#profil-struktur-organisasi',
        'icon' => 'fa-sitemap',
        'label' => 'Struktur Organisasi',
        'desc' => 'Visi, struktur & tata kelola',
    ],
];

$sgEOrgQuick = [
    'href' => 'e_organisasi.php',
    'icon' => 'fa-network-wired',
    'label' => 'E-Organisasi',
    'desc' => 'Masuk untuk akses internal',
];
if (empty($canAccessEOrganisasi)) {
    $sgEOrgQuick['href'] = '#loginModal';
    $sgEOrgQuick['open_login_modal'] = true;
}
$sgQuickLinks[] = $sgEOrgQuick;

?>

<div id="sgPortalLoader" class="sg-portal-loader" aria-hidden="true">

    <div class="sg-portal-loader__inner">

        <div class="sg-portal-loader__ring"></div>

        <p class="sg-portal-loader__label">Smart Governance Portal</p>

    </div>

</div>



<section class="hero-section sg-hero sg-hero--ultra sg-hero--govtech" id="sg-hero" aria-label="Smart Governance Portal">

    <div class="sg-hero__bg" aria-hidden="true"></div>

    <div class="sg-hero__grid-floor" aria-hidden="true"></div>

    <div id="beranda-hero-fx" class="beranda-hero-fx" data-beranda-fx="hero" aria-hidden="true"></div>



    <div class="container-global hero-inner">

        <div class="sg-hero__copy hero-text sg-reveal">

            <h1 class="sg-hero__title">

                <span class="sg-hero__title-secondary">Smart Governance Portal</span>

                <span class="sg-hero__title-primary">Bagian Organisasi</span>

                <span class="sg-hero__title-org">Sekretariat Daerah Kabupaten Kepulauan Aru</span>

            </h1>

            <p class="sg-hero__tagline">

                Transformasi Digital Tata Kelola Pemerintahan yang Modern, Efisien, dan Transparan.

            </p>

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



        <div class="sg-hero__visual-col hero-visual sg-reveal sg-reveal--delay">

            <div class="sg-hero__visual-frame">

                <div class="sg-hero__holo" aria-hidden="true">

                    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'sg_command_center_illus.php'; ?>

                </div>

            </div>

        </div>

    </div>



    <div class="container-global shortcut-grid">

        <nav class="sg-quick-menu sg-reveal sg-reveal--delay-2" aria-label="Fitur strategis portal">

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

        <div class="sg-hero-stats sg-reveal sg-reveal--delay-3" role="group" aria-label="Statistik kunjungan portal">

            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">

                <div class="sg-stat-glass__body">

                    <p class="sg-stat-glass__num mb-0" data-sg-count="<?php echo (int) $berandaTotalToday; ?>">0</p>

                    <p class="sg-stat-glass__label mb-0">Tamu Hari Ini</p>

                </div>

            </article>

            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">

                <div class="sg-stat-glass__body">

                    <p class="sg-stat-glass__num mb-0" data-sg-count="<?php echo (int) $berandaTotalWeek; ?>">0</p>

                    <p class="sg-stat-glass__label mb-0">Kunjungan 7 Hari</p>

                </div>

            </article>

            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">

                <div class="sg-stat-glass__body">

                    <p class="sg-stat-glass__num mb-0" data-sg-count="<?php echo (int) $sgPortalDocCount; ?>">0</p>

                    <p class="sg-stat-glass__label mb-0">Dokumen Digital</p>

                </div>

            </article>

            <article class="sg-stat-glass sg-stat-display sg-hero-stat-card">

                <div class="sg-stat-glass__body">

                    <p class="sg-stat-glass__num mb-0" data-sg-count="<?php echo (int) $sgPortalInfoCount; ?>">0</p>

                    <p class="sg-stat-glass__label mb-0">Publikasi Aktif</p>

                </div>

            </article>

        </div>

    </div>

</section>

