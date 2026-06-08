<?php

/**
 * SEO dasar halaman beranda (meta, canonical, Open Graph, JSON-LD, logo resmi).
 *
 * Nama situs & navigasi utama juga dipakai untuk structured data WebSite /
 * SiteNavigationElement agar Google menampilkan nama resmi (bukan hostname)
 * dan sitelinks: Profil, Layanan, Dokumen, Informasi.
 */

/** Nama resmi situs — konsisten di title, og:site_name, dan schema.org WebSite. */
function org_seo_site_name(): string
{
    return 'Bagian Organisasi Setda Kabupaten Kepulauan Aru';
}

/**
 * @return list<string>
 */
function org_seo_site_alternate_names(): array
{
    return [
        'Bagian Organisasi Setda Aru',
        'Bagorga Kepulauan Aru',
    ];
}

function org_seo_website_id(): string
{
    return org_beranda_seo_production_base_url() . '/#website';
}

function org_seo_organization_id(): string
{
    return org_beranda_seo_production_base_url() . '/#organization';
}

function org_seo_navigation_id(): string
{
    return org_beranda_seo_production_base_url() . '/#main-navigation';
}

/**
 * Navigasi utama yang diinginkan tampil sebagai sitelinks Google.
 *
 * @return list<array{name: string, path: string}>
 */
function org_seo_main_navigation_items(): array
{
    return [
        ['name' => 'Profil', 'path' => 'profil'],
        ['name' => 'Layanan', 'path' => 'layanan'],
        ['name' => 'Dokumen', 'path' => 'dokumen'],
        ['name' => 'Informasi', 'path' => 'berita'],
    ];
}

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
    return org_seo_site_name();
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
    return org_seo_homepage_json_ld_graph($logoAbsoluteUrl);
}

/**
 * JSON-LD beranda: organisasi + WebSite (nama situs) + navigasi utama.
 *
 * @return array<string, mixed>
 */
function org_seo_homepage_json_ld_graph(string $logoAbsoluteUrl = ''): array
{
    $base = org_beranda_seo_production_base_url();
    $home = $base . '/';

    $organization = [
        '@type' => 'GovernmentOrganization',
        '@id' => org_seo_organization_id(),
        'name' => org_seo_site_name(),
        'alternateName' => org_seo_site_alternate_names(),
        'url' => $base,
    ];
    $logoAbsoluteUrl = trim($logoAbsoluteUrl);
    if ($logoAbsoluteUrl !== '') {
        $organization['logo'] = $logoAbsoluteUrl;
    }

    $website = [
        '@type' => 'WebSite',
        '@id' => org_seo_website_id(),
        'url' => $home,
        'name' => org_seo_site_name(),
        'alternateName' => org_seo_site_alternate_names(),
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => org_seo_organization_id()],
        'hasPart' => ['@id' => org_seo_navigation_id()],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $base . '/dokumen?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $navElements = [];
    $position = 1;
    foreach (org_seo_main_navigation_items() as $item) {
        $navElements[] = [
            '@type' => 'SiteNavigationElement',
            'position' => $position,
            'name' => $item['name'],
            'url' => $base . '/' . $item['path'],
        ];
        $position++;
    }

    $navigation = [
        '@type' => 'ItemList',
        '@id' => org_seo_navigation_id(),
        'name' => 'Navigasi Utama',
        'itemListElement' => $navElements,
    ];

    return [
        '@context' => 'https://schema.org',
        '@graph' => [$organization, $website, $navigation],
    ];
}

/**
 * WebSite ringkas untuk halaman selain beranda (nama situs di semua halaman).
 *
 * @return array<string, mixed>
 */
function org_seo_global_website_json_ld(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => org_seo_website_id(),
        'url' => org_beranda_seo_production_base_url() . '/',
        'name' => org_seo_site_name(),
        'alternateName' => org_seo_site_alternate_names(),
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => org_seo_organization_id()],
    ];
}

function org_seo_site_name_meta_markup(): string
{
    $name = htmlspecialchars(org_seo_site_name(), ENT_QUOTES, 'UTF-8');

    return '<meta property="og:site_name" content="' . $name . '">' . "\n"
        . '<meta name="application-name" content="' . $name . '">' . "\n";
}

function org_seo_sitemap_link_markup(): string
{
    $href = htmlspecialchars(org_beranda_seo_production_base_url() . '/sitemap.xml', ENT_QUOTES, 'UTF-8');

    return '<link rel="sitemap" type="application/xml" title="Sitemap" href="' . $href . '">' . "\n";
}

/**
 * Script WebSite global — dilewati di beranda (sudah ada @graph lengkap).
 */
function org_seo_global_website_script_markup(bool $skipOnHomepage = false): string
{
    if ($skipOnHomepage) {
        return '';
    }

    return '<script type="application/ld+json">'
        . json_encode(org_seo_global_website_json_ld(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
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

    $out = org_seo_site_name_meta_markup()
        . org_seo_sitemap_link_markup()
        . '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n"
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
        . json_encode(org_seo_homepage_json_ld_graph($schemaLogo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";

    return $out;
}
