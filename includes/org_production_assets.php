<?php

/**
 * Helper aset production — bundle, preload, font ringan.
 */

/** Naikkan saat deploy bundle agar browser tidak pakai cache lama (meski filemtime sama). */
const ORG_ASSETS_BERANDA_CSS_BUNDLE_MANUAL_VERSION = 26;

/** Naikkan saat deploy perubahan navbar portal (portal-nav saja). */
const ORG_ASSETS_PORTAL_NAV_MANUAL_VERSION = 12;

const ORG_ASSETS_BERANDA_SHELL_CSS_BUNDLE_MANUAL_VERSION = 7;

function org_assets_beranda_css_bundle_rel(): string
{
    return 'assets/css/beranda.bundle.min.css';
}

function org_assets_beranda_css_bundle_fs(): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, org_assets_beranda_css_bundle_rel());
}

function org_assets_beranda_css_bundle_version(): string
{
    $fs = org_assets_beranda_css_bundle_fs();
    if (!is_file($fs)) {
        return (string) ORG_ASSETS_BERANDA_CSS_BUNDLE_MANUAL_VERSION;
    }

    return (string) (int) filemtime($fs) . '-' . ORG_ASSETS_BERANDA_CSS_BUNDLE_MANUAL_VERSION;
}

/**
 * URL penuh beranda.bundle.min.css dengan ?v=filemtime-manual.
 */
function org_assets_beranda_css_bundle_href(): string
{
    if (!function_exists('org_asset_web_base')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    }
    $base = org_asset_web_base();

    return $base . '/' . org_assets_beranda_css_bundle_rel() . '?v=' . rawurlencode(org_assets_beranda_css_bundle_version());
}

function org_assets_beranda_shell_css_bundle_rel(): string
{
    return 'assets/css/beranda-shell.bundle.min.css';
}

function org_assets_beranda_shell_css_bundle_fs(): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, org_assets_beranda_shell_css_bundle_rel());
}

function org_assets_beranda_shell_css_bundle_version(): string
{
    $fs = org_assets_beranda_shell_css_bundle_fs();
    if (!is_file($fs)) {
        return (string) ORG_ASSETS_BERANDA_SHELL_CSS_BUNDLE_MANUAL_VERSION;
    }

    return (string) (int) filemtime($fs) . '-' . ORG_ASSETS_BERANDA_SHELL_CSS_BUNDLE_MANUAL_VERSION;
}

function org_assets_beranda_shell_css_bundle_href(): string
{
    if (!function_exists('org_asset_web_base')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
    }
    $base = org_asset_web_base();

    return $base . '/' . org_assets_beranda_shell_css_bundle_rel() . '?v=' . rawurlencode(org_assets_beranda_shell_css_bundle_version());
}

function org_assets_beranda_css_bundle_available(): bool
{
    $fs = org_assets_beranda_css_bundle_fs();

    return is_file($fs) && (int) filesize($fs) >= 256;
}

function org_assets_beranda_shell_bundle_available(): bool
{
    $fs = org_assets_beranda_shell_css_bundle_fs();

    return is_file($fs) && (int) filesize($fs) >= 128;
}

/**
 * Path JS beranda: pakai .min.js bila ada (production build).
 */
function org_assets_beranda_js_relpath(string $basename): string
{
    $base = preg_replace('/\.min\.js$/i', '', $basename);
    $base = preg_replace('/\.js$/i', '', $base) ?? $basename;
    $minRel = 'assets/js/' . $base . '.min.js';
    $minFs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $minRel);
    if (is_file($minFs) && (int) filesize($minFs) >= 32) {
        return $minRel;
    }

    return 'assets/js/' . $base . '.js';
}

/**
 * @param 'style'|'script'|'image'|'font' $as
 */
function org_asset_preload_link(string $relativeOrUrl, string $as = 'style', bool $isAbsolute = false): string
{
    if ($relativeOrUrl === '') {
        return '';
    }
    if ($isAbsolute) {
        $href = $relativeOrUrl;
    } else {
        $base = org_asset_web_base();
        $href = $base . '/' . ltrim($relativeOrUrl, '/');
        $fs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim(explode('?', $relativeOrUrl)[0], '/'));
        if (is_file($fs) && !str_contains($relativeOrUrl, '?')) {
            $href .= '?v=' . rawurlencode((string) filemtime($fs));
        }
    }
    $esc = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
    $attrs = 'rel="preload" href="' . $esc . '" as="' . htmlspecialchars($as, ENT_QUOTES, 'UTF-8') . '"';
    if ($as === 'style') {
        $attrs .= ' onload="this.onload=null;this.rel=\'stylesheet\'"';
    }
    if ($as === 'font') {
        $attrs .= ' type="font/woff2" crossorigin';
    }
    if ($as === 'image') {
        $attrs .= ' fetchpriority="high"';
    }

    return '<link ' . $attrs . '>' . "\n";
}

/** Font beranda: Inter saja, non-blocking. */
function org_assets_fonts_beranda_markup(): string
{
    return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
        . org_asset_preload_link(
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            'style',
            true
        )
        . '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"></noscript>' . "\n";
}

/** Font halaman portal — preload (non-blocking). */
function org_assets_fonts_portal_markup(): string
{
    return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
        . org_asset_preload_link(
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap',
            'style',
            true
        )
        . '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@500;600;700&amp;display=swap"></noscript>' . "\n";
}

/** Font portal sync — beranda header (metrik sama Profil, tanpa flash fallback). */
function org_assets_fonts_portal_sync_markup(): string
{
    return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
        . '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@500;600;700&amp;display=swap" rel="stylesheet">' . "\n";
}

/**
 * Preload logo beranda (LCP).
 */
function org_assets_preload_logo_markup(string $logoWebPath): string
{
    $logoWebPath = trim($logoWebPath);
    if ($logoWebPath === '') {
        return '';
    }
    $base = org_asset_web_base();
    $href = $base . '/' . ltrim($logoWebPath, '/');

    return org_asset_preload_link($href, 'image', true);
}
