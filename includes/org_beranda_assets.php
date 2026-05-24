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
    org_beranda_assets_prepare_builds();
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site-global.min.css';
    if (!is_file($fs)) {
        return '';
    }

    /* Sync — urutan cascade sama Profil (site_styles → portal-nav) */
    return org_asset_stylesheet_link('assets/css/site-global.min.css');
}

/** CSS header portal yang sama dengan halaman Profil (mobile-first, navbar, enterprise). */
function org_beranda_portal_header_stylesheet_links(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_mobile_assets.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';

    return org_mobile_stylesheet_link()
        . org_navbar_stylesheet_link()
        . org_asset_stylesheet_link('assets/css/smart-governance-enterprise.css?v=3');
}

function org_beranda_site_styles_markup(): string
{
    static $cached = null;
    if (is_string($cached)) {
        return $cached;
    }
    $external = org_beranda_site_global_stylesheet_link();
    if ($external !== '') {
        $cached = $external;

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

    return org_asset_preload_link($rel, 'style')
        . org_asset_stylesheet_async($rel, false);
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
        'assets/css/org-container-global.css?v=39',
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

    /* Font portal sync — sebelum site-global (metrik teks = Profil) */
    $out = org_assets_fonts_portal_sync_markup();
    $out .= org_vendor_stylesheet_preload(org_vendor_bootstrap_css());
    $out .= org_vendor_stylesheet_preload(org_vendor_fontawesome_css());
    $out .= org_asset_stylesheet_async('assets/css/org-dark-mode.css?v=1', true);
    $out .= org_asset_stylesheet_async('assets/css/org-modal-layer.css', true);
    $out .= org_beranda_lite_stylesheet_link();
    $out .= org_beranda_site_global_stylesheet_link();
    $out .= org_beranda_portal_header_stylesheet_links();

    if (!org_assets_beranda_css_bundle_available()) {
        $out .= org_beranda_layout_fix_stylesheet_link();
        $out .= org_beranda_lightweight_stylesheet_link();
        $out .= org_beranda_mobile_stylesheet_link();
        $out .= org_beranda_design_system_stylesheet_link();
        $out .= org_beranda_nav_hero_stylesheet_link();
        $out .= org_beranda_dashboard_cards_stylesheet_link();
        $out .= org_beranda_home_layout_stylesheet_link();
    }

    $rail = 'max-width:1320px!important;width:100%!important;margin-left:auto!important;margin-right:auto!important;padding-left:clamp(1rem,2.5vw,32px)!important;padding-right:clamp(1rem,2.5vw,32px)!important;box-sizing:border-box!important';
    $out .= '<style id="sg-beranda-head-critical">'
        . 'html.sg-portal-html-home,body.sg-homepage.sg-portal-page{background:#f4f7fb!important;--layout-max-width:1320px;--sg-rail-width:1320px;--portal-content-gutter:clamp(1rem,2.5vw,32px)'
        . 'body.sg-homepage #sgPortalLoader{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-header--sg-portal{position:fixed!important;top:0;left:0;right:0;width:100%!important;max-width:100%!important;margin:0!important;z-index:1200;pointer-events:auto;box-sizing:border-box}'
        . 'body.sg-homepage.sg-portal-page .site-header__gradient{display:block;width:100%!important;max-width:100%!important;margin:0!important;box-sizing:border-box}'
        . 'html.sg-portal-html-home{overflow-y:auto!important;overflow-x:clip!important;width:100%!important;max-width:100%!important;scrollbar-gutter:auto!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>section#sg-hero{width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important;box-sizing:border-box!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__nav a{pointer-events:auto!important;cursor:pointer}'
        . 'body.sg-homepage.sg-portal-page :is(.header-inner.container-global,.navbar-wrapper.container-global,.site-layout-main>#sg-hero .container-global,#beranda-root.container-global,.site-footer .container-global){' . $rail . '}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main{width:100%!important;max-width:100%!important;background:#f4f7fb!important;display:block!important;min-height:0!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero{width:100%!important;max-width:100%!important;margin:0!important;padding-left:0!important;padding-right:0!important;min-height:0!important;height:auto!important;overflow:visible!important;padding-top:calc(var(--sg-portal-header-offset,6.5rem) + .65rem)!important;padding-bottom:.5rem!important;background:linear-gradient(180deg,#0a3d6b,#0c4a7a)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__bg{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .container-global.hero-inner,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .hero-inner--stacked{display:flex!important;flex-direction:column!important;grid-template-columns:unset!important;align-items:stretch!important;gap:.65rem!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__copy{overflow:visible!important;min-width:0!important;padding-top:.15rem!important;opacity:1!important;visibility:visible!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-secondary{display:block!important;color:rgba(186,230,253,.92)!important;-webkit-text-fill-color:rgba(186,230,253,.92)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-primary{display:block!important;color:#fff!important;-webkit-text-fill-color:#fff!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-org{display:block!important;color:rgba(226,232,240,.92)!important;-webkit-text-fill-color:rgba(226,232,240,.92)!important}'
        . 'body.sg-homepage .sg-hero:not(.sg-hero--ultra):not(.sg-hero--minimal){min-height:0!important}'
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
 * Head tambahan index.php: section CSS + Fancybox (+ Poppins hanya jika chart/KPI).
 */
function org_beranda_index_extra_head_markup(bool $loadPoppinsFont = false): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    $out = org_beranda_sections_stylesheet_markup();

    if ($loadPoppinsFont) {
        $out .= org_asset_preload_link(
            'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
            'style',
            true
        );
        $out .= '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&amp;display=swap"></noscript>' . "\n";
    }

    return $out;
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

    return org_asset_stylesheet_link('assets/css/beranda-mobile.css?v=2');
}

/** Beranda — design system UI (sync, cascade terakhir). */
function org_beranda_design_system_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-design-system.css');
}

/** Beranda — navbar & hero premium ringan (sync, setelah design system). */
function org_beranda_nav_hero_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-nav-hero.css?v=3');
}

/** Beranda — final polish UI (sync, cascade paling akhir). */
function org_beranda_premium_polish_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-premium-polish.css?v=2');
}

/** Beranda — header/navbar selaras subhalaman (muat paling akhir). */
function org_beranda_header_nav_unify_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-header-nav-unify.css?v=10');
}

/**
 * Critical inline — header + teks hero (paling akhir di head, menang atas bundle async).
 */
function org_beranda_head_critical_final_markup(): string
{
    return '<style id="sg-beranda-head-critical-final">'
        . 'body.sg-homepage.sg-portal-page .site-header__rail.container-global{max-width:1320px!important;padding-left:clamp(1rem,2.5vw,32px)!important;padding-right:clamp(1rem,2.5vw,32px)!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__logo{max-height:48px!important;width:auto!important}'
        . '@media(min-width:992px){body.sg-homepage.sg-portal-page .site-header__logo{max-height:52px!important}}'
        . 'body.sg-homepage.sg-portal-page .navbar-panel,body.sg-homepage.sg-portal-page .site-header__nav-wrap.navbar-panel{min-height:88px!important;border-radius:20px!important;padding:.35rem 0!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__rail .navbar-wrapper{margin-top:clamp(.5rem,1.2vw,1.125rem)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__copy,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-reveal{opacity:1!important;visibility:visible!important;transform:none!important;display:block!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-secondary{display:block!important;color:rgba(186,230,253,.92)!important;-webkit-text-fill-color:rgba(186,230,253,.92)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-primary{display:block!important;color:#fff!important;-webkit-text-fill-color:#fff!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-org{display:block!important;color:rgba(226,232,240,.92)!important;-webkit-text-fill-color:rgba(226,232,240,.92)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__cta{display:flex!important;flex-wrap:wrap!important;gap:.5rem!important;margin-top:.65rem!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero-stats{opacity:1!important;visibility:visible!important}'
        . '</style>' . "\n";
}

/** Beranda — kartu statistik & dashboard enterprise (sync, cascade terakhir). */
function org_beranda_dashboard_cards_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-dashboard-cards.css');
}

/** Beranda — layout compact hero, quick access, spacing (cascade terakhir). */
function org_beranda_home_layout_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_stylesheet_link('assets/css/beranda-home-layout.css?v=8')
        . org_beranda_rail_unify_stylesheet_link()
        . org_beranda_hero_fix_active_stylesheet_link()
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

    return org_asset_stylesheet_link('assets/css/beranda-hero-fix-active.css?v=2');
}

function org_beranda_lite_render_script_tag(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    $rel = org_assets_beranda_js_relpath('beranda-lite-render.js');
    $out = org_asset_script_preload($rel);

    return $out . org_asset_script_defer($rel);
}

function org_beranda_deferred_script_tag(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    $rel = org_assets_beranda_js_relpath('beranda-deferred-load.js');

    return org_asset_script_defer($rel);
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
        var hero = document.getElementById('sg-hero');
        if (hero && isHome && h > 0) {
            hero.style.paddingTop = h + 'px';
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
