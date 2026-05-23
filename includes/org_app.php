<?php

/**
 * URL situs, HTTPS, dan path aset — kompatibel subfolder (VPS / Laragon).
 */
function org_request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    $xf = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    if ($xf === 'https') {
        return true;
    }
    $xfs = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')));

    return $xfs === 'on';
}

/**
 * Redirect HTTP → HTTPS di production (localhost/Laragon dilewati).
 */
function org_force_https_redirect(): void
{
    if (function_exists('org_is_dev_environment') && org_is_dev_environment()) {
        return;
    }
    if (org_request_is_https()) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '') {
        return;
    }
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    if ($uri === '') {
        $uri = '/';
    }
    header('Location: https://' . $host . $uri, true, 301);
    exit;
}

function org_site_path_prefix(): string
{
    if (!function_exists('org_site_web_root')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
    }
    $root = defined('ORG_WEB_ROOT') ? (string) ORG_WEB_ROOT : org_site_web_root();
    $root = rtrim(str_replace('\\', '/', $root), '/');

    return $root === '' || $root === '/' ? '' : $root;
}

/** Beranda tanpa /index.php (mis. / atau /subfolder/). */
function org_home_url(): string
{
    $prefix = org_site_path_prefix();

    return $prefix === '' ? '/' : $prefix . '/';
}

/**
 * Slug clean URL dari path skrip (profil.php → profil, index.php → '').
 */
function org_page_slug(string $script): string
{
    $script = ltrim(str_replace('\\', '/', trim($script)), '/');
    if ($script === '' || str_contains($script, '..')) {
        return '';
    }
    if (preg_match('/\.php$/i', $script)) {
        $script = substr($script, 0, -4);
    }

    return $script === 'index' ? '' : $script;
}

/**
 * Path clean URL ke skrip PHP (mis. /profil atau /subfolder/admin/dashboard).
 */
function org_page_url(string $script, string $fragment = ''): string
{
    $slug = org_page_slug($script);
    if ($slug === '') {
        $url = org_home_url();
    } else {
        $prefix = org_site_path_prefix();
        $url = ($prefix === '' ? '' : $prefix) . '/' . $slug;
    }
    if ($fragment !== '') {
        $url .= '#' . ltrim($fragment, '#');
    }

    return $url;
}

/**
 * Atribut href/action aman HTML — clean URL + escape.
 */
function org_href(string $script, string $query = '', string $fragment = ''): string
{
    $url = org_page_url($script, $fragment);
    if ($query !== '') {
        $url .= (str_contains($url, '?') ? '&' : '?') . ltrim($query, '?&');
    }

    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect HTTP ke clean URL (301/302).
 */
function org_redirect(string $script, string $query = '', string $fragment = '', int $status = 302): never
{
    $url = org_page_url($script, $fragment);
    if ($query !== '') {
        $url .= (str_contains($url, '?') ? '&' : '?') . ltrim($query, '?&');
    }
    if (!headers_sent()) {
        header('Location: ' . $url, true, $status);
    }
    exit;
}

/**
 * Path aset publik (CSS/JS) dengan prefix subfolder; gunakan di href/src.
 */
function org_asset_url(string $relativePath): string
{
    $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
    if ($relativePath === '' || str_contains($relativePath, '..')) {
        return org_home_url();
    }
    $prefix = org_site_path_prefix();

    return ($prefix === '' ? '' : $prefix) . '/' . $relativePath;
}

/**
 * Path web logo utama (navbar/beranda), mis. logo.png?v=… — kosong jika berkas tidak ada.
 */
function org_site_logo_web_path(): string
{
    if (!defined('ORG_ROOT')) {
        return '';
    }
    foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $logoExt) {
        $logoFs = ORG_ROOT . DIRECTORY_SEPARATOR . 'logo.' . $logoExt;
        if (is_file($logoFs)) {
            return 'logo.' . $logoExt . '?v=' . rawurlencode((string) filemtime($logoFs));
        }
    }

    return '';
}
