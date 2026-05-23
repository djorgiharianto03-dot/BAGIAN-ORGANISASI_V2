<?php

/**
 * SEO dasar halaman beranda (meta, canonical, Open Graph, JSON-LD, logo resmi).
 */

/** URL publik produksi (canonical, sitemap, schema). */
function org_beranda_seo_production_base_url(): string
{
    return 'https://www.bagorga.kepulauanarukab.go.id';
}

/**
 * Basis URL absolut situs (produksi tetap www; dev mengikuti request).
 */
function org_beranda_seo_public_base_url(): string
{
    $productionHosts = [
        'www.bagorga.kepulauanarukab.go.id',
        'bagorga.kepulauanarukab.go.id',
    ];
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if (preg_match('/^([^:]+)(:\d+)?$/', $host, $m)) {
        $host = (string) ($m[1] ?? $host);
    }
    if (in_array($host, $productionHosts, true)) {
        return org_beranda_seo_production_base_url();
    }

    if (!function_exists('org_request_is_https')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $scheme = org_request_is_https() ? 'https' : 'http';
    if ($host === '') {
        $host = 'localhost';
    }

    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $root = rtrim((string) ORG_WEB_ROOT, '/');

    return $scheme . '://' . $host . ($root === '' ? '' : $root);
}

function org_beranda_seo_page_title(): string
{
    return 'Bagian Organisasi Setda Kabupaten Kepulauan Aru';
}

function org_beranda_seo_meta_description(): string
{
    return 'Website resmi Bagian Organisasi Setda Kabupaten Kepulauan Aru yang memuat informasi kelembagaan, reformasi birokrasi, pelayanan publik, tata laksana, dan akuntabilitas kinerja pemerintah daerah.';
}

function org_beranda_seo_logo_alt(): string
{
    return 'Logo Bagian Organisasi Setda Kabupaten Kepulauan Aru';
}

function org_beranda_seo_h1_text(): string
{
    return org_beranda_seo_page_title();
}

function org_beranda_seo_canonical_url(): string
{
    if (!function_exists('org_home_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $base = rtrim(org_beranda_seo_public_base_url(), '/');
    $home = org_home_url();
    if ($home === '/') {
        return $base . '/';
    }

    return $base . $home;
}

/**
 * Path logo tanpa query cache-bust (untuk schema.org / OG stabil).
 */
function org_beranda_seo_logo_path_only(string $logoWebPath): string
{
    $logoWebPath = trim(str_replace('\\', '/', $logoWebPath));
    if ($logoWebPath === '') {
        return '';
    }
    $path = strtok($logoWebPath, '?');

    return $path !== false ? ltrim($path, '/') : ltrim($logoWebPath, '/');
}

/**
 * URL absolut logo (sama berkas dengan navbar: logo.png|jpg|… di akar situs).
 *
 * @param bool $useProductionBase true untuk JSON-LD / identitas publik produksi
 */
function org_beranda_seo_logo_absolute_url(string $logoWebPath, bool $useProductionBase = false): string
{
    $pathOnly = org_beranda_seo_logo_path_only($logoWebPath);
    if ($pathOnly === '') {
        return '';
    }
    $base = $useProductionBase
        ? org_beranda_seo_production_base_url()
        : rtrim(org_beranda_seo_public_base_url(), '/');

    return $base . '/' . $pathOnly;
}

/**
 * URL absolut logo untuk browser (termasuk ?v= jika ada).
 */
function org_beranda_seo_logo_browser_absolute_url(string $logoWebPath): string
{
    $logoWebPath = trim(str_replace('\\', '/', $logoWebPath));
    if ($logoWebPath === '') {
        return '';
    }

    return rtrim(org_beranda_seo_public_base_url(), '/') . '/' . ltrim($logoWebPath, '/');
}

/**
 * @return array<string, mixed>
 */
function org_beranda_seo_json_ld(string $logoAbsoluteUrl = ''): array
{
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentOrganization',
        'name' => 'Bagian Organisasi Setda Kabupaten Kepulauan Aru',
        'alternateName' => 'Bagian Organisasi Setda Aru',
        'url' => org_beranda_seo_production_base_url(),
    ];
    $logoAbsoluteUrl = trim($logoAbsoluteUrl);
    if ($logoAbsoluteUrl !== '') {
        $data['logo'] = $logoAbsoluteUrl;
    }

    return $data;
}

/**
 * @param string $logoWebPath Path relatif logo (dari bootstrap), boleh kosong.
 */
function org_beranda_seo_head_markup(string $logoWebPath = ''): string
{
    if (!function_exists('org_site_logo_web_path')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    $desc = org_beranda_seo_meta_description();
    $canonical = org_beranda_seo_canonical_url();
    $title = org_beranda_seo_page_title();
    $logoAlt = org_beranda_seo_logo_alt();

    $out = '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta name="robots" content="index, follow">' . "\n"
        . '<link rel="canonical" href="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta property="og:type" content="website">' . "\n"
        . '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta property="og:description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta property="og:url" content="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta property="og:locale" content="id_ID">' . "\n"
        . '<meta name="twitter:card" content="summary">' . "\n"
        . '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<meta name="twitter:description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n";

    $logo = trim($logoWebPath);
    if ($logo === '') {
        $logo = org_site_logo_web_path();
    }

    if ($logo !== '') {
        $iconHref = org_beranda_seo_logo_browser_absolute_url($logo);
        $ogImageHref = org_beranda_seo_logo_absolute_url($logo, false);
        $schemaLogoHref = org_beranda_seo_logo_absolute_url($logo, true);

        if (!function_exists('org_assets_preload_logo_markup')) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
        }
        $out .= org_assets_preload_logo_markup($logo);
        $out .= '<link rel="icon" href="' . htmlspecialchars($iconHref, ENT_QUOTES, 'UTF-8') . '" sizes="any">' . "\n"
            . '<link rel="apple-touch-icon" href="' . htmlspecialchars($iconHref, ENT_QUOTES, 'UTF-8') . '">' . "\n"
            . '<meta property="og:image" content="' . htmlspecialchars($ogImageHref, ENT_QUOTES, 'UTF-8') . '">' . "\n"
            . '<meta property="og:image:alt" content="' . htmlspecialchars($logoAlt, ENT_QUOTES, 'UTF-8') . '">' . "\n"
            . '<meta name="twitter:image" content="' . htmlspecialchars($ogImageHref, ENT_QUOTES, 'UTF-8') . '">' . "\n"
            . '<meta name="twitter:image:alt" content="' . htmlspecialchars($logoAlt, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    $schemaLogo = $logo !== '' ? org_beranda_seo_logo_absolute_url($logo, true) : '';
    $out .= '<script type="application/ld+json">'
        . json_encode(org_beranda_seo_json_ld($schemaLogo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";

    return $out;
}
