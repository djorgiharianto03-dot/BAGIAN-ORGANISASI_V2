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

    return org_asset_stylesheet_async('assets/css/site-global.min.css');
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
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda.bundle.min.css';
    if (!is_file($fs)) {
        return org_beranda_bundle_stylesheet_link_fallback();
    }

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $rel = 'assets/css/beranda.bundle.min.css';
    $fsPath = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);

    $out = org_asset_preload_link($rel, 'style');
    $out .= org_asset_stylesheet_async($rel, is_file($fsPath));

    return $out;
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
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-shell.bundle.min.css';
    if (!is_file($fs)) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_container_global_assets.php';

        return org_container_global_stylesheet_link_async();
    }

    $rel = 'assets/css/beranda-shell.bundle.min.css';
    $out = org_asset_preload_link($rel, 'style');

    return $out . org_asset_stylesheet_async($rel);
}

function org_container_global_stylesheet_link_async(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $links = '';
    foreach ([
        'assets/css/org-container-global.css?v=37',
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

    $out = org_assets_fonts_beranda_markup();
    $out .= org_vendor_stylesheet_preload(org_vendor_bootstrap_css());
    $out .= org_vendor_stylesheet_preload(org_vendor_fontawesome_css());
    $out .= org_asset_stylesheet_async('assets/css/org-dark-mode.css?v=1', true);
    $out .= org_asset_stylesheet_async('assets/css/org-navbar.css?v=12', true);
    $out .= org_asset_stylesheet_async('assets/css/org-modal-layer.css', true);
    $out .= org_beranda_lite_stylesheet_link();

    if (!org_assets_beranda_css_bundle_available()) {
        $out .= org_beranda_layout_fix_stylesheet_link();
        $out .= org_beranda_lightweight_stylesheet_link();
        $out .= org_beranda_mobile_stylesheet_link();
        $out .= org_beranda_design_system_stylesheet_link();
        $out .= org_beranda_nav_hero_stylesheet_link();
        $out .= org_beranda_dashboard_cards_stylesheet_link();
        $out .= org_beranda_home_layout_stylesheet_link();
    }

    $rail = 'max-width:1180px!important;width:100%!important;margin-left:auto!important;margin-right:auto!important;padding-left:clamp(1rem,2vw,1.25rem)!important;padding-right:clamp(1rem,2vw,1.25rem)!important;box-sizing:border-box!important';
    $out .= '<style id="sg-beranda-head-critical">'
        . 'html.sg-portal-html-home,body.sg-homepage.sg-portal-page{background:#f4f7fb!important}'
        . 'body.sg-homepage #sgPortalLoader{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-header--sg-portal{position:fixed!important;top:0;left:0;right:0;z-index:1200;pointer-events:auto}'
        . 'body.sg-homepage.sg-portal-page .site-header__nav a{pointer-events:auto!important;cursor:pointer}'
        . 'body.sg-homepage.sg-portal-page .header-inner.container-global,body.sg-homepage.sg-portal-page .navbar-wrapper.container-global,body.sg-homepage.sg-portal-page #beranda-root.container-global,body.sg-homepage.sg-portal-page .site-footer .container-global{' . $rail . '}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main{background:#f4f7fb!important;display:block!important;min-height:0!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero,body.sg-homepage .sg-hero.sg-hero--minimal{min-height:0!important;height:auto!important;max-height:none!important;padding-bottom:.5rem!important;overflow:visible!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero.sg-hero--minimal .sg-hero__bg{display:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero.sg-hero--minimal{background:linear-gradient(180deg,#0a3d6b 0%,#0c4a7a 100%)!important}'
        . 'body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .container-global.hero-inner,body.sg-homepage.sg-portal-page .site-layout-main>#sg-hero .stats-grid{position:relative!important;z-index:2!important}'
        . 'body.sg-homepage .sg-hero:not(.sg-hero--ultra):not(.sg-hero--minimal){min-height:0!important}'
        . 'body.sg-homepage.sg-portal-page #beranda-root{display:flex!important;flex-direction:column!important;visibility:visible!important;opacity:1!important;min-height:0!important;margin-top:0!important;background:#f4f7fb!important}'
        . 'body.sg-homepage.sg-portal-page #beranda-root>.beranda-section,body.sg-homepage.sg-portal-page #beranda-root>section,body.sg-homepage.sg-portal-page #beranda-root>#beranda-team-targets{content-visibility:visible!important;contain-intrinsic-size:unset!important;visibility:visible!important;opacity:1!important;display:block!important}'
        . 'body.sg-homepage.sg-portal-page .site-footer.site-footer--modern{background:#f4f7fb!important;margin-top:0!important}'
        . 'body.sg-homepage.sg-portal-page .site-footer__cta-band,body.sg-homepage.sg-portal-page .org-footer__cta.site-footer__cta-band{background:#f0f4f8!important;color:#0f2744!important;border-top:1px solid #e2e8f0!important;box-shadow:none!important}'
        . 'body.sg-homepage.sg-portal-page .site-footer__links-band{background:#eef2f6!important;border-top:1px solid #e2e8f0!important}'
        . 'body.sg-homepage.sg-portal-page .site-footer__nav a{color:#475569!important}'
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

    return org_asset_stylesheet_link('assets/css/beranda-mobile.css');
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

    return org_asset_stylesheet_link('assets/css/beranda-nav-hero.css');
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

    return org_asset_stylesheet_link('assets/css/beranda-home-layout.css?v=5');
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
