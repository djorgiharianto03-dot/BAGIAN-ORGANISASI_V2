<?php
declare(strict_types=1);

/**
 * Aset halaman beranda — CSS non-blocking & skrip lazy.
 */

function org_beranda_site_global_stylesheet_link(): string
{
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
    ob_start();
    require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php';
    $cached = (string) ob_get_clean();

    return $cached;
}

function org_beranda_bundle_stylesheet_async_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda.bundle.min.css';
    if (!is_file($fs)) {
        return org_beranda_bundle_stylesheet_link_fallback();
    }

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    $rel = 'assets/css/beranda.bundle.min.css';
    $fsPath = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);

    return org_asset_stylesheet_async($rel, is_file($fsPath));
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

function org_beranda_deferred_script_tag(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';

    return org_asset_script_defer('assets/js/beranda-deferred-load.js');
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
