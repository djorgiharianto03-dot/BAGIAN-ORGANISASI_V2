<?php

/**
 * Aset vendor lokal (Bootstrap, Font Awesome, Chart.js, ApexCharts, AOS, Swiper, Fancybox).
 */

function org_vendor_fs_root(): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'vendor';
}

function org_vendor_web_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base . '/assets/vendor';
}

/**
 * @return string URL web dengan ?v=filemtime bila file ada
 */
function org_vendor_url(string $relativePath): string
{
    $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
    $url = org_vendor_web_base() . '/' . $relativePath;
    $fs = org_vendor_fs_root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($fs)) {
        $url .= '?v=' . rawurlencode((string) filemtime($fs));
    }

    return $url;
}

function org_vendor_stylesheet(string $relativePath): string
{
    return '<link rel="stylesheet" href="' . htmlspecialchars(org_vendor_url($relativePath), ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_vendor_stylesheet_preload(string $relativePath): string
{
    $href = org_vendor_url($relativePath);

    return '<link rel="preload" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
        . '<noscript>' . org_vendor_stylesheet($relativePath) . '</noscript>';
}

function org_vendor_script(string $relativePath, bool $defer = true): string
{
    $deferAttr = $defer ? ' defer' : '';

    return '<script src="' . htmlspecialchars(org_vendor_url($relativePath), ENT_QUOTES, 'UTF-8') . '"' . $deferAttr . '></script>' . "\n";
}

function org_vendor_script_preload(string $relativePath): string
{
    return '<link rel="preload" href="' . htmlspecialchars(org_vendor_url($relativePath), ENT_QUOTES, 'UTF-8') . '" as="script">' . "\n";
}

/** Path relatif dari /assets/vendor/ */
function org_vendor_bootstrap_css(): string
{
    return 'bootstrap/5.3.3/bootstrap.min.css';
}

function org_vendor_bootstrap_js(): string
{
    return 'bootstrap/5.3.3/bootstrap.bundle.min.js';
}

function org_vendor_fontawesome_css(): string
{
    return 'fontawesome/6.5.1/css/all.min.css';
}

function org_vendor_chartjs_js(): string
{
    return 'chartjs/4.4.1/chart.umd.min.js';
}

function org_vendor_apexcharts_js(): string
{
    return 'apexcharts/3.49.1/apexcharts.min.js';
}

function org_vendor_aos_css(): string
{
    return 'aos/2.3.4/aos.css';
}

function org_vendor_aos_js(): string
{
    return 'aos/2.3.4/aos.js';
}

function org_vendor_swiper_css(): string
{
    return 'swiper/11/swiper-bundle.min.css';
}

function org_vendor_swiper_js(): string
{
    return 'swiper/11/swiper-bundle.min.js';
}

function org_vendor_fancybox_css(): string
{
    return 'fancybox/5.0/fancybox.css';
}

function org_vendor_fancybox_js(): string
{
    return 'fancybox/5.0/fancybox.umd.js';
}

function org_beranda_bundle_css_rel(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';

    return org_assets_beranda_css_bundle_rel();
}

function org_beranda_bundle_css_url(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';

    return org_assets_beranda_css_bundle_href();
}

function org_beranda_bundle_stylesheet_link(): string
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
    if (!org_assets_beranda_css_bundle_available()) {
        return '';
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars(org_beranda_bundle_css_url(), ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
