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
    $out .= org_modal_layer_stylesheet_link();

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
        . 'html.sg-portal-html-home,body.sg-homepage.sg-portal-page{background:#f4f7fb!important;--layout-max-width:1320px;--sg-rail-width:1320px;--portal-content-gutter:clamp(1rem,2.5vw,32px);--sg-portal-header-offset:11rem}'
        . 'body.sg-homepage #sgPortalLoader{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-header--sg-portal{position:fixed!important;top:0;left:0;right:0;width:100%!important;max-width:100%!important;margin:0!important;z-index:1100;pointer-events:auto;box-sizing:border-box}'
        . 'body.sg-homepage.sg-portal-page .site-header__gradient{display:block;width:100%!important;max-width:100%!important;margin:0!important;box-sizing:border-box}'
        . 'html.sg-portal-html-home{overflow-y:auto!important;overflow-x:clip!important;width:100%!important;max-width:100%!important;scrollbar-gutter:auto!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>section#sg-hero{background:linear-gradient(180deg,#0a3d6b 0%,#0c4a7a 100%)!important;background-color:#0a3d6b!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;padding-top:calc(var(--sg-portal-header-offset,11rem) + .75rem)!important;overflow:visible!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__bg,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-ambient-layer,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__grid-floor{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__copy,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-secondary,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-primary,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-org,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__tagline{opacity:1!important;visibility:visible!important;transform:none!important;display:block!important;color:#fff!important;-webkit-text-fill-color:currentColor!important;overflow:visible!important;clip:auto!important;max-height:none!important;-webkit-line-clamp:unset!important;line-clamp:unset!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-secondary{color:rgba(186,230,253,.92)!important;-webkit-text-fill-color:rgba(186,230,253,.92)!important;font-weight:600!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-primary{font-weight:800!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__title-org{color:rgba(226,232,240,.92)!important;-webkit-text-fill-color:rgba(226,232,240,.92)!important;font-weight:700!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__tagline{font-weight:400!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__visual-col,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .shortcut-grid{display:none!important;visibility:hidden!important;height:0!important;overflow:hidden!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__tagline{display:block!important;opacity:1!important;visibility:visible!important;color:rgba(203,213,225,.92)!important;-webkit-text-fill-color:rgba(203,213,225,.92)!important;font-size:clamp(.875rem,.82rem+.2vw,.975rem)!important;line-height:1.55!important;margin:.5rem 0 0!important;max-width:38rem!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .sg-hero__cta{display:flex!important;opacity:1!important;visibility:visible!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage.sg-portal-page .site-layout-main>section#sg-hero{width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important;box-sizing:border-box!important}'
        . 'body.sg-homepage.sg-portal-page .site-header__nav a{pointer-events:auto!important;cursor:pointer}'
        . 'body.sg-homepage.sg-portal-page :is(.site-header__rail.container-global,.header-inner.container-global,.site-layout-main>#sg-hero .container-global,#beranda-root.container-global,.site-footer .container-global){' . $rail . '}'
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

    return org_asset_stylesheet_link('assets/css/beranda-nav-hero.css?v=4');
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

/** Sinkron offset header fixed → teks hero tidak tertutup navbar. */
function org_beranda_hero_offset_sync_script(): string
{
    return <<<'HTML'
<script id="sg-hero-offset-sync">
(function () {
    'use strict';
  function syncHeroOffset() {
    var header = document.querySelector('.site-header--sg-portal');
    var hero = document.getElementById('sg-hero');
    if (!header || !hero) return;
    var h = Math.ceil(header.getBoundingClientRect().height);
    if (h < 1) return;
    document.body.style.setProperty('--sg-portal-header-offset', h + 'px');
    /* Biarkan padding-top dari CSS calc(var) — jangan kosongkan sebelum offset valid */
  }
  syncHeroOffset();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncHeroOffset);
  }
  window.addEventListener('load', syncHeroOffset);
  window.addEventListener('resize', syncHeroOffset, { passive: true });
  if (typeof ResizeObserver !== 'undefined') {
    var header = document.querySelector('.site-header--sg-portal');
    if (header) {
      new ResizeObserver(syncHeroOffset).observe(header);
    }
  }
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(syncHeroOffset);
  }
  requestAnimationFrame(function () {
    syncHeroOffset();
    requestAnimationFrame(syncHeroOffset);
  });
})();
</script>

HTML;
}

/** Paksa teks hero tampil setelah CSS async/bundle selesai dimuat. */
function org_beranda_hero_text_lock_script(): string
{
    return org_beranda_hero_offset_sync_script() . <<<'HTML'
<script id="sg-hero-text-lock">
(function () {
    'use strict';
    function lockHeroText() {
        var hero = document.getElementById('sg-hero');
        if (!hero) return;
        var paint = {
            '.sg-hero__copy': { display: 'block', color: '#fff' },
            '.sg-hero__title': { display: 'flex', color: '#fff' },
            '.sg-hero__title-secondary': { display: 'block', color: 'rgba(186,230,253,0.92)' },
            '.sg-hero__title-primary': { display: 'block', color: '#ffffff' },
            '.sg-hero__title-org': { display: 'block', color: 'rgba(226,232,240,0.92)' },
            '.sg-hero__tagline': { display: 'block', color: 'rgba(203,213,225,0.92)' },
            '.sg-hero__cta': { display: 'flex', color: '#fff' }
        };
        Object.keys(paint).forEach(function (sel) {
            hero.querySelectorAll(sel).forEach(function (el) {
                el.style.setProperty('opacity', '1', 'important');
                el.style.setProperty('visibility', 'visible', 'important');
                el.style.setProperty('display', paint[sel].display, 'important');
                el.style.setProperty('color', paint[sel].color, 'important');
                el.style.setProperty('-webkit-text-fill-color', paint[sel].color, 'important');
                el.style.setProperty('transform', 'none', 'important');
                el.style.setProperty('overflow', 'visible', 'important');
                el.style.setProperty('max-height', 'none', 'important');
                el.style.setProperty('-webkit-line-clamp', 'unset', 'important');
            });
        });
        var title = hero.querySelector('.sg-hero__title');
        if (title) {
            title.style.setProperty('flex-direction', 'column', 'important');
            title.style.setProperty('gap', '0.2rem', 'important');
        }
        var cta = hero.querySelector('.sg-hero__cta');
        if (cta) {
            cta.style.setProperty('flex-wrap', 'wrap', 'important');
            cta.style.setProperty('gap', '0.5rem', 'important');
        }
        var primary = hero.querySelector('.sg-hero__title-primary');
        if (primary) {
            primary.style.setProperty('font-weight', '800', 'important');
        }
        var org = hero.querySelector('.sg-hero__title-org');
        if (org) {
            org.style.setProperty('font-weight', '700', 'important');
        }
        var secondary = hero.querySelector('.sg-hero__title-secondary');
        if (secondary) {
            secondary.style.setProperty('font-weight', '600', 'important');
        }
        var tagline = hero.querySelector('.sg-hero__tagline');
        if (tagline) {
            tagline.style.setProperty('font-weight', '400', 'important');
        }
        hero.querySelectorAll('.sg-reveal').forEach(function (el) {
            el.classList.add('is-visible');
        });
    }
    lockHeroText();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', lockHeroText);
    }
    window.addEventListener('load', lockHeroText);
})();
</script>

HTML;
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

/** Muat ulang CSS navbar paling akhir (timpa bundle async beranda). */
function org_beranda_navbar_footer_cascade_markup(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

    return org_portal_navbar_footer_cascade_markup();
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
        var hero = document.getElementById('sg-hero');
        if (hero && isHome && h > 0) {
            hero.style.setProperty('padding-top', (h + 10) + 'px', 'important');
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
