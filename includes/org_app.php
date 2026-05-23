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

/**
 * Path URL aman (encode segment, mis. spasi → %20).
 *
 * @param list<string> $segments
 */
function org_url_path_from_segments(string ...$segments): string
{
    $parts = [];
    foreach ($segments as $segment) {
        $segment = trim(str_replace('\\', '/', $segment), '/');
        if ($segment === '') {
            continue;
        }
        foreach (explode('/', $segment) as $piece) {
            $piece = trim($piece);
            if ($piece !== '') {
                $parts[] = rawurlencode($piece);
            }
        }
    }

    return $parts === [] ? '/' : '/' . implode('/', $parts);
}

/** Beranda tanpa /index.php (mis. / atau /subfolder/). */
function org_home_url(): string
{
    $prefix = org_site_path_prefix();
    if ($prefix === '') {
        return '/';
    }

    return org_url_path_from_segments($prefix) . '/';
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
        $url = org_url_path_from_segments($prefix, $slug);
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
 * Slug clean URL dari REQUEST_URI (mis. profil, admin/dashboard).
 */
function org_current_request_slug(): string
{
    $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    if (!is_string($uriPath) || $uriPath === '') {
        return '';
    }
    $uriPath = str_replace('\\', '/', rawurldecode($uriPath));
    $prefix = org_site_path_prefix();
    if ($prefix !== '') {
        if (str_starts_with($uriPath, $prefix . '/')) {
            $uriPath = substr($uriPath, strlen($prefix));
        } elseif ($uriPath === $prefix) {
            $uriPath = '/';
        }
    }
    $uriPath = trim($uriPath, '/');
    if ($uriPath === '' || $uriPath === 'index') {
        return '';
    }

    return $uriPath;
}

/**
 * Fallback router: jika Apache mengarahkan /profil ke index.php, muat skrip yang benar.
 * Dipanggil hanya dari index.php sebelum ORG_BERANDA_PAGE.
 */
function org_dispatch_clean_url_from_index(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    $scriptFile = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($scriptFile === '' || basename($scriptFile) !== 'index.php') {
        return;
    }
    $slug = org_current_request_slug();
    if ($slug === '' || str_contains($slug, '..')) {
        return;
    }
    $root = defined('ORG_ROOT') ? ORG_ROOT : dirname(__DIR__);
    if (preg_match('#^admin/([^/]+)$#', $slug, $adminMatch)) {
        $target = $root . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $adminMatch[1] . '.php';
    } elseif (!str_contains($slug, '/')) {
        $target = $root . DIRECTORY_SEPARATOR . $slug . '.php';
    } else {
        return;
    }
    if (is_file($target)) {
        require $target;
        exit;
    }
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
    $query = '';
    if (str_contains($relativePath, '?')) {
        [$relativePath, $queryPart] = explode('?', $relativePath, 2);
        $query = '?' . $queryPart;
    }
    $prefix = org_site_path_prefix();
    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

    return ($prefix === '' ? '' : org_url_path_from_segments($prefix)) . '/' . $encodedPath . $query;
}

/** Prefix URL encoded untuk href aset (mis. /BAGIAN%20ORGANISASI_V2). */
function org_public_asset_base(): string
{
    $prefix = org_site_path_prefix();

    return $prefix === '' ? '' : org_url_path_from_segments($prefix);
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
