<?php
declare(strict_types=1);

/**
 * SEO dasar halaman beranda (meta, favicon, JSON-LD ringan).
 */

function org_beranda_seo_page_title(): string
{
    return 'Bagian Organisasi — Portal Resmi Sekretariat Daerah Kabupaten Kepulauan Aru';
}

function org_beranda_seo_meta_description(): string
{
    return 'Portal resmi Bagian Organisasi Sekretariat Daerah Kabupaten Kepulauan Aru: '
        . 'pengumuman, dokumen digital, layanan publik, dashboard kinerja, galeri kegiatan, dan statistik kunjungan website.';
}

function org_beranda_seo_canonical_url(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $root = rtrim((string) ORG_WEB_ROOT, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path = $root === '' ? '/index.php' : $root . '/index.php';

    return $scheme . '://' . $host . $path;
}

/**
 * @param string $logoWebPath Path relatif logo (dari bootstrap), boleh kosong.
 */
function org_beranda_seo_head_markup(string $logoWebPath = ''): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $assetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $desc = org_beranda_seo_meta_description();
    $canonical = org_beranda_seo_canonical_url();
    $title = org_beranda_seo_page_title();

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
    if ($logo !== '') {
        $iconHref = htmlspecialchars($assetBase . '/' . ltrim($logo, '/'), ENT_QUOTES, 'UTF-8');
        if (!function_exists('org_assets_preload_logo_markup')) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_production_assets.php';
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_assets_perf.php';
        }
        $out .= org_assets_preload_logo_markup($logo);
        $out .= '<link rel="icon" href="' . $iconHref . '" sizes="any">' . "\n"
            . '<link rel="apple-touch-icon" href="' . $iconHref . '">' . "\n"
            . '<meta property="og:image" content="' . htmlspecialchars(org_beranda_seo_canonical_og_image($logo), ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Bagian Organisasi — Sekretariat Daerah Kabupaten Kepulauan Aru',
        'description' => $desc,
        'url' => $canonical,
        'inLanguage' => 'id-ID',
    ];
    $out .= '<script type="application/ld+json">'
        . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";

    return $out;
}

function org_beranda_seo_canonical_og_image(string $logoWebPath): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $root = rtrim((string) ORG_WEB_ROOT, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path = $root === '' ? '/' . ltrim($logoWebPath, '/') : $root . '/' . ltrim($logoWebPath, '/');

    return $scheme . '://' . $host . $path;
}
