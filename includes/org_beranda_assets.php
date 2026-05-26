<?php

/**
 * Aset halaman beranda — CSS non-blocking & skrip lazy.
 * Build permanen: assets/css/*.min.css (bukan uploads/.cache).
 */

function org_beranda_assets_prepare_builds(): void
{
    static $prepared = false;
    if ($prepared) {
        return;
    }
    $prepared = true;
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_build_assets.php';
    org_build_assets_ensure_beranda();
}

function org_beranda_site_global_stylesheet_link(): string
{
    /* Jangan pakai site-global.min.css — beranda portal memakai site_styles.php (sama Profil). */
    return '';
}

/** Inline site_styles.php — identik halaman Profil / subhalaman portal. */
function org_beranda_portal_site_styles_markup(): string
{
    static $cached = null;
    if (is_string($cached)) {
        return $cached;
    }
    $partial = __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php';
    if (!is_file($partial)) {
        $cached = '';

        return $cached;
    }
    ob_start();
    try {
        require $partial;
        $cached = (string) ob_get_clean();
    } catch (Throwable) {
        ob_end_clean();
        $cached = '';
    }

    return $cached;
}

/** CSS header portal yang sama dengan halaman Profil (mobile-first, navbar, enterprise). */
function org_beranda_portal_header_stylesheet_links(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_mobile_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';

    return org_mobile_stylesheet_link()
        . org_navbar_stylesheet_link()
        . org_tailwind_stylesheet_link()
        . org_asset_stylesheet_link('assets/css/smart-governance-portal.css?v=16')
        . org_asset_stylesheet_link('assets/css/smart-governance-enterprise.css?v=3');
}

function org_beranda_site_styles_markup(): string
{
    return org_beranda_portal_site_styles_markup();
}

function org_beranda_bundle_stylesheet_async_link(): string
{
    org_beranda_assets_prepare_builds();
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    if (!org_assets_beranda_css_bundle_available()) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';

        return org_beranda_bundle_stylesheet_link_fallback();
    }

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $rel = org_assets_beranda_css_bundle_rel() . '?v=' . org_assets_beranda_css_bundle_version();

    /* Blocking — async bundle menimpa footer cascade & header sync (Profil parity). */
    return org_asset_preload_link($rel, 'style')
        . org_asset_stylesheet_link($rel);
}

function org_beranda_bundle_stylesheet_link_fallback(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
    $link = org_beranda_bundle_stylesheet_link();
    if ($link !== '') {
        return $link;
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_async('assets/css/beranda-page.css');
}

function org_beranda_shell_stylesheet_async_link(): string
{
    org_beranda_assets_prepare_builds();
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    if (!org_assets_beranda_shell_bundle_available()) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_container_global_assets.php';

        return org_container_global_stylesheet_link_async();
    }

    $rel = org_assets_beranda_shell_css_bundle_rel() . '?v=' . org_assets_beranda_shell_css_bundle_version();

    return org_asset_preload_link($rel, 'style')
        . org_asset_stylesheet_async($rel, false);
}

function org_container_global_stylesheet_link_async(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $links = '';
    foreach ([
        'assets/css/org-container-global.css?v=44',
        'assets/css/sg-portal-panel-layout.css?v=6',
        'assets/css/sg-portal-shell-align.css?v=5',
        'assets/css/org-overflow-guard.css?v=1',
        'assets/css/smart-governance-portal-layout-fix.css?v=19',
    ] as $rel) {
        $links .= org_asset_stylesheet_async($rel, str_contains($rel, '?'));
    }

    return $links;
}

/**
 * Head beranda: vendor non-blocking + critical layout kecil.
 */
function org_beranda_header_vendor_markup(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_modal_layer_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_assets.php';

    /* Font portal sync — sebelum site_styles (metrik teks = Profil) */
    $out = org_assets_fonts_portal_sync_markup();
    $out .= org_vendor_stylesheet_preload(org_vendor_bootstrap_css());
    $out .= org_vendor_stylesheet_preload(org_vendor_fontawesome_css());
    $out .= org_asset_stylesheet_async('assets/css/org-dark-mode.css?v=1', true);
    $out .= org_beranda_lite_stylesheet_link();
    $out .= org_beranda_portal_site_styles_markup();
    $out .= org_beranda_portal_header_stylesheet_links();
    $out .= org_motion_stylesheet_link();
    $out .= org_theme_stylesheet_link();
    $out .= org_typography_stylesheet_link();
    $out .= org_modal_layer_stylesheet_link();

    if (!org_assets_beranda_css_bundle_available()) {
        $out .= org_beranda_layout_fix_stylesheet_link();
        $out .= org_beranda_lightweight_stylesheet_link();
        $out .= org_beranda_mobile_stylesheet_link();
        $out .= org_beranda_design_system_stylesheet_link();
        $out .= org_beranda_dashboard_cards_stylesheet_link();
        $out .= org_beranda_home_layout_stylesheet_link();
    }

    /* Horizontal News Card untuk section Pusat Informasi & Pengumuman.
       Dimuat di luar branch bundle agar selalu tersedia tanpa rebuild bundle. */
    $out .= org_beranda_news_horizontal_stylesheet_link();

    /* Hero Identity Upgrade — aksen gold + primary CTA pada subhero beranda.
       Selalu dimuat (ringan, ~2KB), scope ketat ke body.sg-homepage. */
    $out .= org_beranda_hero_identity_stylesheet_link();

    /* Section Title Polish — aksen bar gold + eyebrow + link pill konsisten
       untuk semua judul section beranda. Ringan ~3KB, scope ketat. */
    $out .= org_beranda_section_titles_stylesheet_link();

    $rail = 'max-width:1320px!important;width:100%!important;margin-left:auto!important;margin-right:auto!important;padding-left:clamp(1rem,2.5vw,32px)!important;padding-right:clamp(1rem,2.5vw,32px)!important;box-sizing:border-box!important';
    $out .= '<style id="sg-beranda-head-critical">'
        . 'html.sg-portal-html-home,body.sg-homepage.sg-portal-page{background:#f4f7fb!important;--layout-max-width:1320px;--sg-rail-width:1320px;--portal-content-gutter:clamp(1rem,2.5vw,32px);--sg-portal-header-offset:5.5rem}'
        . 'body.sg-homepage #sgPortalLoader{display:none!important}'
        . 'html.sg-portal-html-home{overflow-y:auto!important;overflow-x:clip!important;width:100%!important;max-width:100%!important;scrollbar-gutter:auto!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>.org-hero.sg-subhero{width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important;box-sizing:border-box!important;overflow:visible!important}'
        . 'body.sg-homepage.sg-portal-page #sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>section#sg-hero{display:none!important;visibility:hidden!important;height:0!important;overflow:hidden!important;margin:0!important;padding:0!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>.org-hero.sg-subhero .org-hero__container,body.sg-homepage.sg-portal-page .site-layout-main>.org-hero.sg-subhero .org-hero__title,body.sg-homepage.sg-portal-page .site-layout-main>.org-hero.sg-subhero .org-hero__lead,body.sg-homepage.sg-portal-page .site-layout-main>.org-hero.sg-subhero .org-eyebrow{opacity:1!important;visibility:visible!important;transform:none!important;overflow:visible!important;clip:auto!important;max-height:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__nav a{pointer-events:auto!important;cursor:pointer}'
        . 'body.sg-homepage.sg-portal-page :is(.site-layout-main>.org-hero.sg-subhero .container-global,#beranda-root.container-global,.site-footer .container-global){' . $rail . '}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main{width:100%!important;max-width:100%!important;background:#f4f7fb!important;display:block!important;min-height:0!important}'
        . 'body.sg-homepage.sg-portal-page #beranda-root{display:flex!important;visibility:visible!important;opacity:1!important;min-height:0!important;margin-top:0!important;background:#f4f7fb!important}'
        . 'body.sg-homepage #beranda-pusat-informasi,body.sg-homepage #beranda-galeri-kegiatan{display:block!important;visibility:visible!important;opacity:1!important}'
        . 'body.sg-homepage #beranda-pusat-informasi .pi-portal-grid--beranda{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,260px),1fr));gap:1rem;width:100%}'
        . 'body.sg-homepage #beranda-pusat-informasi .pi-portal-grid__cell{display:flex!important;min-width:0}'
        . 'body.sg-homepage #beranda-galeri-kegiatan .beranda-galeri-scroll{display:flex!important;flex-wrap:nowrap!important;gap:1rem;overflow-x:auto!important;-webkit-overflow-scrolling:touch;scroll-snap-type:x proximity;padding-bottom:.35rem}'
        . 'body.sg-homepage #beranda-galeri-kegiatan .beranda-galeri-scroll .beranda-galeri-item{flex:0 0 clamp(200px,38vw,300px);scroll-snap-align:start}'
        . '</style>' . "\n";

    return $out;
}

function org_beranda_lite_boot_script(): string
{
    return <<<'HTML'
<script>
(function(){try{var d=document.documentElement,b=document.body;if(!b||!b.classList.contains('sg-homepage'))return;b.classList.add('is-lite-render','is-perf-lite');var m=window.matchMedia;var low=(m&&m('(max-width:767.98px)').matches&&m('(pointer:coarse)').matches)||(navigator.connection&&(navigator.connection.saveData||/2g/.test(navigator.connection.effectiveType||'')))||(navigator.deviceMemory&&navigator.deviceMemory<=4);if(low||m&&m('(prefers-reduced-motion:reduce)').matches){b.classList.add('is-effects-off');d.classList.add('is-effects-off');}}catch(e){}})();
</script>

HTML;
}

function org_beranda_lite_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-lite.css');
}

/**
 * CSS section #beranda-root (dulu inline di index.php) — async jika sudah ada di bundle.
 */
function org_beranda_sections_stylesheet_markup(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    org_beranda_assets_prepare_builds();

    if (org_assets_beranda_css_bundle_available()) {
        return '';
    }

    return org_asset_stylesheet_async('assets/css/beranda-sections.css');
}

/**
 * Head tambahan index.php: section CSS + Fancybox.
 *
 * Catatan (2026-05-26): parameter $loadPoppinsFont dipertahankan untuk
 * kompatibilitas tanda tangan lama, tetapi Poppins tidak lagi dimuat — seluruh
 * tipografi situs sudah diseragamkan ke Plus Jakarta Sans (lihat
 * assets/css/org-typography.css + token --font-sans).
 */
function org_beranda_index_extra_head_markup(bool $loadPoppinsFont = false): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_beranda_sections_stylesheet_markup();
}

/** Layout beranda — max-width & tipografi normal (muat sinkron, setelah lite). */
function org_beranda_layout_fix_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-layout-fix.css');
}

/** Beranda — override ringan (sync, setelah layout-fix). */
function org_beranda_lightweight_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-lightweight.css');
}

/** Beranda — responsive mobile (sync, setelah lightweight). */
function org_beranda_mobile_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-mobile.css?v=3');
}

/** Beranda — design system UI (sync, cascade terakhir). */
function org_beranda_design_system_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-design-system.css');
}

/** @deprecated Hero lama — Beranda memakai sg-subhero via subpages.css. */
function org_beranda_nav_hero_stylesheet_link(): string
{
    return '';
}

/** Beranda — final polish UI (sync, cascade paling akhir). */
function org_beranda_premium_polish_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-premium-polish.css?v=2');
}

/** @deprecated Beranda memakai CSS portal yang sama dengan Profil. */
function org_beranda_nav_panel_critical_markup(): string
{
    return '';
}

/** Beranda — hero compact (referensi screenshot). Muat paling akhir setelah unify. */
function org_beranda_hero_reference_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-hero-reference.css');
}

/** @deprecated Subhero beranda memakai komponen portal (sama Profil). */
function org_beranda_hero_offset_sync_script(): string
{
    return '';
}

/** @deprecated Hero #sg-hero diganti subhero compact. */
function org_beranda_hero_text_lock_script(): string
{
    return '';
}

/** Beranda — kartu statistik & dashboard enterprise (sync, cascade terakhir). */
function org_beranda_dashboard_cards_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-dashboard-cards.css');
}

/**
 * Beranda — Horizontal News Card (Pusat Informasi & Pengumuman).
 *
 * Selalu dimuat dari file terpisah agar tidak tergantung status bundle.
 * Prefix kelas `org-news-card` + scope `#beranda-pusat-informasi` menjamin
 * selektor menang cascade tanpa menyentuh komponen lain.
 */
function org_beranda_news_horizontal_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-news-horizontal.css?v=2');
}

/**
 * Beranda — Hero Identity Upgrade (aksen gold + primary CTA subhero).
 *
 * File terpisah, scope ketat ke `body.sg-homepage`. Tidak memengaruhi
 * subhero halaman lain. ~2KB un-minified, ringan dan aman.
 */
function org_beranda_hero_identity_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-hero-identity.css?v=1');
}

/**
 * Beranda — Section Title Polish (judul section beranda lebih bagus).
 *
 * Menambah aksen bar gold di atas judul, eyebrow small-caps, dan "Lihat semua"
 * pill. Semua via pseudo-element ::before / ::after, tanpa perubahan HTML.
 * Scope ketat ke `body.sg-homepage #beranda-root` agar tidak menabrak
 * halaman subpage lain. Ukuran file ~3KB.
 */
function org_beranda_section_titles_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-section-titles.css?v=1');
}

/** Beranda — layout compact hero, quick access, spacing (cascade terakhir). */
function org_beranda_home_layout_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-home-layout.css?v=8')
        . org_beranda_rail_unify_stylesheet_link()
        . org_beranda_viewport_align_stylesheet_link()
        . org_beranda_premium_polish_stylesheet_link();
}

/** Beranda — selaraskan lebar viewport (Chrome/Firefox), muat paling akhir. */
function org_beranda_viewport_align_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-viewport-align.css?v=3');
}

/** Rail 1180px — menimpa site_styles (1200px) & org-container-global. */
function org_beranda_rail_unify_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-rail-unify.css?v=3');
}

/**
 * Hero beranda — SYNC paling akhir (setelah shell async) di header.php.
 * Cari di production: beranda-hero-fix-active.css + komentar HERO FIX ACTIVE
 */
function org_beranda_hero_fix_active_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-hero-fix-active.css?v=3');
}

function org_beranda_lite_render_script_tag(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    $rel = org_assets_beranda_js_relpath('beranda-lite-render.js');
    $out = org_asset_script_preload($rel);

    return $out . org_asset_script_defer($rel);
}

function org_beranda_header_nav_sync_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-header-nav-sync.css?v=11');
}

/**
 * @deprecated v=11: tidak diperlukan lagi karena Beranda tidak punya override
 *             header. Fungsi tetap ada agar pemanggil di cascade markup tidak rusak.
 */
function org_beranda_header_nav_relock_script(): string
{
    return '';
}

function org_beranda_header_nav_relock_script_legacy(): string
{
    return <<<'HTML'
<script id="sg-beranda-header-relock">
(function () {
    'use strict';
    if (!document.body || !document.body.classList.contains('sg-homepage')) return;

    function cleanInline() {
        var sels = [
            '.site-header__topbar',
            '.site-header__brand-row',
            '.org-navbar__brand',
            '.site-header__brand-row > a',
            '.site-header__logo',
            '.org-navbar__logo'
        ];
        sels.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (el) {
                if (!el || !el.style) return;
                ['justify-content','align-items','align-self','flex','padding','margin','width','max-width','height','max-height','transform','position','left','right','top'].forEach(function (p) {
                    el.style.removeProperty(p);
                });
            });
        });
    }

    cleanInline();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', cleanInline);
    }
    window.addEventListener('load', cleanInline);
})();
</script>

HTML;
}

/**
 * @deprecated v=11: semua halaman pakai satu desain navbar dari
 *             smart-governance-portal-nav.css. Tidak menghasilkan markup lagi.
 *             Fungsi tetap ada agar pemanggil di header.php / footer.php tidak rusak.
 */
function org_beranda_header_nav_critical_footer_markup(): string
{
    return '';
}

function org_beranda_header_nav_critical_footer_markup_legacy(): string
{
    /* v=9: MINIMAL — hanya samakan warna panel Beranda dengan Profil.
       Layout/positioning/spacing TIDAK diubah dari sini — biar mengikuti
       smart-governance-portal-nav.css (sama Profil). Versi v=8 sebelumnya
       merusak layout karena memaksa display/flex/margin-top/min-height/
       position di banyak wrapper. */
    $home = 'html body.sg-homepage.sg-portal-page';

    $transparent = 'background:transparent!important;background-color:transparent!important;background-image:none!important;border:0!important;box-shadow:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important';

    /* Panel TUNGGAL — hanya warna/border/shadow/radius (TIDAK menyentuh
       display/position/min-height/margin/padding/flex). */
    $panel = 'background:rgba(2,22,48,.94)!important;'
        . 'background-color:rgba(2,22,48,.94)!important;'
        . 'background-image:none!important;'
        . '-webkit-backdrop-filter:none!important;'
        . 'backdrop-filter:none!important;'
        . 'border:1px solid rgba(147,197,253,.18)!important;'
        . 'border-radius:20px!important;'
        . 'box-shadow:inset 0 1px 0 rgba(255,255,255,.07),0 10px 32px rgba(0,10,28,.42)!important';
    $panelScrolled = 'background:rgba(2,22,48,.97)!important;'
        . 'background-color:rgba(2,22,48,.97)!important;'
        . 'background-image:none!important';

    $css = '<style id="sg-beranda-header-match-profil">'
        /* Wrapper KECIL di dalam gradient — paksa transparent.
           CATATAN: .site-header__gradient TIDAK DISERTAKAN — itu adalah background
           gelap utama header (gradient) yang harus tetap visible. v=8/v=9
           sebelumnya membuatnya transparent → header tampak "kosong/floating". */
        . "{$home} .site-header--sg-portal .site-header__rail.container-global,"
        . "{$home} .site-header--sg-portal .header-inner.container-global,"
        . "{$home} .site-header__inner.header-inner,"
        . "{$home} .site-header__rail .site-header__topbar,"
        . "{$home} .header-inner .site-header__topbar,"
        . "{$home} .site-header--sg-portal .site-header__brand-row,"
        . "{$home} .site-header--sg-portal .org-navbar__brand.site-header__brand-row,"
        . "{$home} .site-header__rail .navbar-wrapper,"
        . "{$home} .site-header__rail .org-navbar__nav-shell,"
        . "{$home} .navbar-wrapper,"
        . "{$home} .org-navbar__nav-shell,"
        . "{$home} .site-header__nav-panel,"
        . "{$home} .site-header__nav-row,"
        . "{$home} .org-navbar__nav-row,"
        . "{$home} .site-header--sg-portal .site-header__nav,"
        . "{$home} .site-header__actions,"
        . "{$home} .site-header__actions-end,"
        . "{$home} .org-navbar__nav,"
        . "{$home} .org-navbar__actions-end{" . $transparent . '}'
        /* PANEL TUNGGAL — hanya warna/border/shadow/radius */
        . "{$home} .site-header--sg-portal .navbar-panel,"
        . "{$home} .site-header--sg-portal .org-navbar__nav-wrap.navbar-panel,"
        . "{$home} .site-header--sg-portal .site-header__nav-wrap.navbar-panel,"
        . "{$home} .site-header__nav-wrap.navbar-panel{" . $panel . '}'
        . "{$home} .site-header--sg-portal.is-scrolled .navbar-panel,"
        . "{$home} .site-header--sg-portal.is-scrolled .org-navbar__nav-wrap.navbar-panel,"
        . "{$home} .site-header--sg-portal.is-scrolled .site-header__nav-wrap.navbar-panel{" . $panelScrolled . '}'
        . '</style>' . "\n";

    return $css;
}

/** Muat ulang CSS navbar paling akhir (timpa bundle async beranda). */
function org_beranda_navbar_footer_cascade_markup(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

    return org_portal_navbar_footer_cascade_markup()
        . org_beranda_header_nav_sync_stylesheet_link()
        . org_beranda_header_nav_critical_footer_markup()
        . org_beranda_header_nav_relock_script();
}

function org_beranda_deferred_script_tag(): string
{
    return '';
}

/** Muat setelah ApexCharts di footer (grafik target tim kerja). */
function org_beranda_team_target_charts_script_tag(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    $rel = org_assets_beranda_js_relpath('beranda-team-target-charts.js');

    return org_asset_script_defer($rel);
}

function org_beranda_portal_header_offset_script(): string
{
    return <<<'HTML'
<script>
(function () {
    'use strict';
    function syncPortalHeaderOffset() {
        var header = document.querySelector('.site-header--sg-portal');
        if (!header || !document.body) return;
        var h = Math.ceil(header.getBoundingClientRect().height);
        if (h > 0) {
            document.body.style.setProperty('--sg-portal-header-offset', h + 'px');
        }
        var isHome = document.body.classList.contains('sg-homepage');
        var main = document.querySelector('.site-layout-main');
        if (main) {
            main.style.paddingTop = isHome ? '0' : (h > 0 ? h + 'px' : '');
        }
    }
    function onScrollHeader() {
        var header = document.querySelector('.site-header--sg-portal');
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 16);
    }
    function layout() {
        syncPortalHeaderOffset();
        onScrollHeader();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', layout);
    } else {
        layout();
    }
    window.addEventListener('scroll', onScrollHeader, { passive: true });
    window.addEventListener('resize', layout, { passive: true });
})();
</script>

HTML;
}

function org_beranda_footer_vendor_base_script(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';

    return '<script>window.ORG_VENDOR_BASE=' . json_encode(org_vendor_web_base(), JSON_UNESCAPED_SLASHES)
        . ';</script>' . "\n";
}

/**
 * Variabel global + Chart.js (dan opsional Apex) sebelum beranda-deferred-load.js.
 */
function org_beranda_footer_chart_scripts(bool $loadApexCharts = false): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $webRoot = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $assetBase = $webRoot;

    $out = org_beranda_footer_vendor_base_script();
    $out .= '<script>window.ORG_WEB_ROOT=' . json_encode($webRoot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . ';window.ORG_ASSET_BASE=' . json_encode($assetBase, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . ';</script>' . "\n";
    $out .= org_vendor_script_preload(org_vendor_chartjs_js());
    $out .= org_vendor_script(org_vendor_chartjs_js(), true);
    if ($loadApexCharts) {
        $out .= org_vendor_script_preload(org_vendor_apexcharts_js());
        $out .= org_vendor_script(org_vendor_apexcharts_js(), true);
    }

    return $out;
}
