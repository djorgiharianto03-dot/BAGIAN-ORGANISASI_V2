<?php
declare(strict_types=1);

/** Helper aset non-blocking (CSS async preload, script defer). */

function org_asset_web_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }

    return ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
}

function org_asset_stylesheet_async(string $relativePath, bool $withVersion = true): string
{
    $base = org_asset_web_base();
    $href = $base . '/' . ltrim($relativePath, '/');
    if ($withVersion && str_contains($relativePath, '?') === false) {
        $fs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
        if (is_file($fs)) {
            $href .= '?v=' . rawurlencode((string) filemtime($fs));
        }
    }
    $esc = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');

    return '<link rel="preload" href="' . $esc . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
        . '<noscript><link rel="stylesheet" href="' . $esc . '"></noscript>' . "\n";
}

function org_asset_stylesheet_link(string $relativePath): string
{
    $base = org_asset_web_base();
    $href = $base . '/' . ltrim($relativePath, '/');
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim(explode('?', $relativePath)[0], '/'));
    if (is_file($fs) && !str_contains($relativePath, '?')) {
        $href .= '?v=' . rawurlencode((string) filemtime($fs));
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_asset_script_defer(string $relativePath): string
{
    if (str_starts_with(ltrim($relativePath, '/'), 'assets/js/')) {
        if (!function_exists('org_assets_beranda_js_relpath')) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
        }
        $relativePath = org_assets_beranda_js_relpath(basename($relativePath));
    }
    $base = org_asset_web_base();
    $src = $base . '/' . ltrim($relativePath, '/');
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim(explode('?', $relativePath)[0], '/'));
    if (is_file($fs) && !str_contains($relativePath, '?')) {
        $src .= '?v=' . rawurlencode((string) filemtime($fs));
    }

    return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}

function org_asset_script_preload(string $relativePath): string
{
    if (str_starts_with(ltrim($relativePath, '/'), 'assets/js/')) {
        if (!function_exists('org_assets_beranda_js_relpath')) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
        }
        $relativePath = org_assets_beranda_js_relpath(basename($relativePath));
    }
    $base = org_asset_web_base();
    $src = $base . '/' . ltrim($relativePath, '/');
    $fs = ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim(explode('?', $relativePath)[0], '/'));
    if (is_file($fs) && !str_contains($relativePath, '?')) {
        $src .= '?v=' . rawurlencode((string) filemtime($fs));
    }

    return '<link rel="preload" href="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" as="script">' . "\n";
}
