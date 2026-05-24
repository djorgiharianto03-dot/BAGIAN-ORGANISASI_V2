<?php

declare(strict_types=1);



/** @var int $sgPortalDocCount @var int $sgPortalInfoCount @var int $sgPortalGaleriCount */

/** @var int $sgPortalLayananCount @var int $berandaTotalToday @var int $berandaTotalWeek */



/**
 * Fitur strategis — bukan duplikat navbar/hero CTA (Dokumen & Layanan hanya di nav + hero).
 */
$sgQuickLinks = [
    [
        'href' => org_page_url('index.php', 'beranda-dashboard-widgets'),
        'icon' => 'fa-chart-line',
        'label' => 'Dashboard Kinerja',
        'desc' => 'Monitoring indikator & capaian OPD',
    ],
    [
        'href' => org_page_url('index.php', 'beranda-pusat-informasi'),
        'icon' => 'fa-bullhorn',
        'label' => 'Pengumuman Terbaru',
        'desc' => 'Informasi & pengumuman resmi',
    ],
    [
        'href' => org_page_url('profil.php', 'profil-struktur-organisasi'),
        'icon' => 'fa-sitemap',
        'label' => 'Struktur Organisasi',
        'desc' => 'Visi, struktur & tata kelola',
    ],
];

$sgEOrgQuick = [
    'href' => org_page_url('e_organisasi.php'),
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
/* Hero compact — teks + CTA + statistik (referensi gambar 2) */
.sg-homepage #sg-hero .sg-hero__visual-col,
.sg-homepage #sg-hero .shortcut-grid,
.sg-homepage #sg-hero .sg-hero__grid-floor,
.sg-homepage #sg-hero .sg-ambient-layer,
.sg-homepage #sg-hero .beranda-hero-fx {
    display: none !important;
}

.sg-homepage #sg-hero .sg-hero__title-secondary,
.sg-homepage #sg-hero .sg-hero__title-primary,
.sg-homepage #sg-hero .sg-hero__title-org,
.sg-homepage #sg-hero .sg-hero__tagline,
.sg-homepage #sg-hero .sg-hero__copy {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
}

.sg-homepage #sg-hero .sg-hero__title,
.sg-homepage #sg-hero .sg-hero__tagline,
.sg-homepage #sg-hero .sg-hero__cta,
.sg-homepage #sg-hero .sg-hero-stats {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
}

.sg-homepage #sg-hero .sg-hero__title-secondary {
    overflow: visible;
    clip: auto;
    white-space: normal;
    line-height: 1.35;
}

.sg-homepage #sg-hero .sg-hero__copy {
    min-width: 0;
    max-width: 42rem;
    overflow: visible;
}

.sg-homepage #sg-hero .sg-hero__cta {
    display: flex !important;
    flex-wrap: wrap;
    gap: 0.5rem;
}

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
</style>

<div id="sgPortalLoader" class="sg-portal-loader" aria-hidden="true" hidden>

    <div class="sg-portal-loader__inner">

        <div class="sg-portal-loader__ring"></div>

        <p class="sg-portal-loader__label">Smart Governance Portal</p>

    </div>

</div>



<section class="hero-section sg-hero sg-hero--minimal sg-hero--compact" id="sg-hero" aria-label="Smart Governance Portal">

    <div class="sg-hero__bg" aria-hidden="true"></div>


    <div class="container-global hero-inner hero-inner--stacked">

        <div class="sg-hero__copy hero-text" style="display:block!important;opacity:1!important;visibility:visible!important;position:relative;z-index:5;max-width:42rem;">

            <h1 class="sg-hero__title" style="display:flex!important;flex-direction:column!important;gap:.2rem;margin:0!important;color:#fff!important;opacity:1!important;visibility:visible!important;">

                <span class="sg-hero__title-secondary" style="display:block!important;opacity:1!important;visibility:visible!important;color:#bae6fd!important;-webkit-text-fill-color:#bae6fd!important;font-size:.6875rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;line-height:1.35;margin:0 0 .35rem;">Smart Governance Portal</span>

                <span class="sg-hero__title-primary" style="display:block!important;opacity:1!important;visibility:visible!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:clamp(1.625rem,1.35rem + .9vw,2.125rem);font-weight:800;line-height:1.12;margin:0;">Bagian Organisasi</span>

                <span class="sg-hero__title-org" style="display:block!important;opacity:1!important;visibility:visible!important;color:rgba(226,232,240,.92)!important;-webkit-text-fill-color:rgba(226,232,240,.92)!important;font-size:clamp(.9375rem,.875rem + .25vw,1.0625rem);font-weight:700;line-height:1.4;margin:.2rem 0 0;">Sekretariat Daerah Kabupaten Kepulauan Aru</span>

            </h1>

            <p class="sg-hero__tagline" style="display:block!important;opacity:1!important;visibility:visible!important;color:rgba(203,213,225,.92)!important;-webkit-text-fill-color:rgba(203,213,225,.92)!important;font-size:clamp(.875rem,.82rem + .2vw,.975rem);font-weight:400;line-height:1.55;margin:.5rem 0 0;max-width:38rem;">Mewujudkan Tata Kelola Pemerintahan Digital.</p>

            <div class="sg-hero__cta" style="display:flex!important;opacity:1!important;visibility:visible!important;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;">

                <a href="<?php echo org_href('dokumen.php'); ?>" class="sg-btn sg-btn--hero-primary">

                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>

                    Pusat Dokumen

                </a>

                <a href="<?php echo org_href('layanan.php'); ?>" class="sg-btn sg-btn--hero-secondary">

                    <i class="fa-solid fa-handshake" aria-hidden="true"></i>

                    Layanan Publik

                </a>

            </div>

        </div>



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

