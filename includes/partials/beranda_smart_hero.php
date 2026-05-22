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

<style id="sg-beranda-layout-fix">

.sg-homepage #sg-hero .sg-hero__title,

.sg-homepage #sg-hero .sg-hero__tagline,

.sg-homepage #sg-hero .sg-reveal,

.sg-homepage #sg-hero .sg-quick-menu,

.sg-homepage #sg-hero .sg-hero-stats {

    opacity: 1 !important;

    visibility: visible !important;

    transform: none !important;

}

/* Statistik: hanya angka + label, bukan kartu navigasi */
.sg-homepage #sg-hero .sg-hero-stats .sg-stat-display {
    display: block;
    cursor: default;
    pointer-events: none;
    text-align: center;
    padding: 1rem 0.85rem;
}

.sg-homepage #sg-hero .sg-hero-stats .sg-stat-display:hover,
.sg-homepage #sg-hero .sg-hero-stats .sg-stat-display:focus {
    transform: none !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12) !important;
}

.sg-homepage #sg-hero .sg-hero-stats .sg-stat-display .sg-stat-glass__body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    width: 100%;
    text-align: center;
}

/* Governance hub — layout & klik (CSS kritis, tanpa file ekstra) */
.sg-homepage .sg-hero__visual-col.hero-visual {
    width: 100%;
    max-width: 400px;
    min-width: 0;
    transform: none;
}
.sg-homepage .sg-hero__visual-frame,
.sg-homepage .sg-hero__holo {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: visible;
}
.sg-homepage .sg-hero__holo {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: min(300px, 42vw);
    padding: 0.25rem 0;
    overflow: visible;
}
.sg-homepage .sg-command-center--interactive {
    position: relative;
    width: 100%;
    max-width: min(340px, 100%);
    aspect-ratio: 1;
    margin: 0 auto;
    overflow: visible;
}
.sg-homepage .sg-command-center__mesh {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    animation: none !important;
}
.sg-homepage .sg-command-center__node-btn {
    position: absolute;
    z-index: 4;
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.1rem;
    color: #e0f2fe;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(147, 197, 253, 0.35);
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.sg-homepage .sg-command-center__node-btn--1 { top: 6%; left: 4%; }
.sg-homepage .sg-command-center__node-btn--2 { top: 6%; right: 4%; }
.sg-homepage .sg-command-center__node-btn--3 { bottom: 10%; left: 6%; }
.sg-homepage .sg-command-center__node-btn--4 { bottom: 6%; right: 4%; }
.sg-homepage .sg-command-center__node-btn:hover,
.sg-homepage .sg-command-center__node-btn:focus-visible {
    transform: translateY(-2px);
    border-color: rgba(125, 211, 252, 0.65);
    box-shadow: 0 8px 20px rgba(0, 12, 30, 0.35);
    outline: none;
}
.sg-homepage .sg-command-center__hub-btn {
    position: absolute;
    inset: 24%;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    border: none;
    background: transparent;
    text-decoration: none;
    border-radius: 18px;
}
.sg-homepage .sg-command-center__hub-ring {
    position: absolute;
    inset: 0;
    border-radius: 18px;
    border: 1px solid rgba(34, 211, 238, 0.45);
    pointer-events: none;
}
.sg-homepage .sg-command-center__hub-core {
    position: relative;
    z-index: 2;
    width: auto;
    min-width: 5.75rem;
    max-width: 6.5rem;
    height: auto;
    min-height: 5.75rem;
    padding: 0.45rem 0.4rem 0.4rem;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    text-align: center;
    box-sizing: border-box;
    color: #e0f2fe;
    background: linear-gradient(145deg, rgba(34, 211, 238, 0.4), rgba(37, 99, 235, 0.3));
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 10px 28px rgba(34, 211, 238, 0.25);
    pointer-events: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.sg-homepage .sg-command-center__hub-core i {
    font-size: 1.35rem;
    line-height: 1;
    flex-shrink: 0;
}
.sg-homepage .sg-command-center__hub-label {
    display: block;
    font-size: 0.5625rem;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: 0.02em;
    color: #f0f9ff;
    max-width: 100%;
    overflow: visible;
    white-space: normal;
    word-break: break-word;
}
.sg-homepage .sg-command-center__hub-btn:hover .sg-command-center__hub-core,
.sg-homepage .sg-command-center__hub-btn:focus-visible .sg-command-center__hub-core {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 14px 32px rgba(34, 211, 238, 0.4);
    outline: none;
}
.sg-homepage .sg-command-center__tooltip,
.sg-homepage .sg-command-center__hub-tooltip {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    padding: 0.3rem 0.55rem;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
    color: #f8fafc;
    background: rgba(2, 22, 48, 0.92);
    border: 1px solid rgba(125, 211, 252, 0.35);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.sg-homepage .sg-command-center__node-btn--2 .sg-command-center__tooltip,
.sg-homepage .sg-command-center__node-btn--4 .sg-command-center__tooltip {
    left: auto;
    right: 0;
    transform: none;
}
.sg-homepage .sg-command-center__node-btn:hover .sg-command-center__tooltip,
.sg-homepage .sg-command-center__node-btn:focus-visible .sg-command-center__tooltip,
.sg-homepage .sg-command-center__hub-btn:hover .sg-command-center__hub-tooltip,
.sg-homepage .sg-command-center__hub-btn:focus-visible .sg-command-center__hub-tooltip {
    opacity: 1;
    visibility: visible;
}
@media (prefers-reduced-motion: reduce) {
    .sg-homepage .sg-command-center__node-btn,
    .sg-homepage .sg-command-center__hub-core {
        transition: none;
    }
}

/* Hero — tipografi awal (rail penuh: beranda-portal-rail.css) */
.sg-homepage #sg-hero .sg-hero__copy {
    min-width: 0;
    max-width: 100%;
    overflow: visible;
}
.sg-homepage #sg-hero .sg-hero__title,
.sg-homepage #sg-hero .sg-hero__tagline {
    overflow: visible;
    word-wrap: break-word;
}
.sg-homepage #sg-hero .sg-hero__bg,
.sg-homepage #sg-hero .sg-hero__grid-floor,
.sg-homepage #sg-hero .sg-ambient-layer {
    left: 0;
    right: 0;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}
.sg-homepage #sg-hero .sg-ambient-glow--b {
    right: 0;
    left: auto;
}

</style>

<div id="sgPortalLoader" class="sg-portal-loader" aria-hidden="true">

    <div class="sg-portal-loader__inner">

        <div class="sg-portal-loader__ring"></div>

        <p class="sg-portal-loader__label">Smart Governance Portal</p>

    </div>

</div>



<section class="hero-section sg-hero sg-hero--ultra sg-hero--govtech" id="sg-hero" aria-label="Smart Governance Portal">

    <div class="sg-hero__bg" aria-hidden="true"></div>

    <div class="sg-hero__grid-floor" aria-hidden="true"></div>

    <?php $sgAmbientVariant = 'hero'; $sgParticleCount = 10; require __DIR__ . DIRECTORY_SEPARATOR . 'sg_ambient_layer.php'; ?>



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

