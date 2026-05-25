<?php

/**
 * SEO sub-halaman portal (Profil, Layanan, Dokumen, Informasi/Berita, Galeri, dst.)
 *
 * Tujuan: meningkatkan peluang Google menampilkan sitelinks pada hasil
 * pencarian situs. Tidak menyentuh CSS, layout, navbar, atau hero.
 *
 * Output `org_subpage_seo_head_markup()`:
 *   - <meta name="description"> unik per halaman
 *   - <meta name="robots" content="index, follow">
 *   - <link rel="canonical"> dengan URL produksi absolut
 *   - <meta property="og:*">  (judul/deskripsi/url) ringkas
 *   - <script type="application/ld+json"> BreadcrumbList (Beranda  Halaman)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_seo.php';

if (!function_exists('org_subpage_seo_absolute_url')) {
    function org_subpage_seo_absolute_url(string $relativePath): string
    {
        $base = rtrim(org_beranda_seo_production_base_url(), '/');
        $rel = '/' . ltrim($relativePath, '/');

        return $base . $rel;
    }
}

if (!function_exists('org_subpage_seo_breadcrumb_jsonld')) {
    /**
     * @return string Plain JSON string (sudah di-encode).
     */
    function org_subpage_seo_breadcrumb_jsonld(string $crumbName, string $relativePath): string
    {
        $home = rtrim(org_beranda_seo_production_base_url(), '/') . '/';
        $itemUrl = org_subpage_seo_absolute_url($relativePath);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Beranda',
                    'item' => $home,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $crumbName,
                    'item' => $itemUrl,
                ],
            ],
        ];

        return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('org_subpage_seo_head_markup')) {
    /**
     * @param string $pageTitle     Title penuh untuk meta (mis. "Profil  Bagian Organisasi Setda Kabupaten Kepulauan Aru").
     * @param string $description   Meta description unik per halaman (130-160 karakter).
     * @param string $relativePath  Path canonical TANPA query, tanpa domain (mis. "profil" atau "berita").
     * @param string $crumbName     Label breadcrumb (mis. "Profil").
     */
    function org_subpage_seo_head_markup(
        string $pageTitle,
        string $description,
        string $relativePath,
        string $crumbName
    ): string {
        $pageTitle = trim($pageTitle);
        $description = trim($description);
        $relativePath = trim($relativePath);
        $crumbName = trim($crumbName);
        if ($pageTitle === '' || $description === '' || $relativePath === '' || $crumbName === '') {
            return '';
        }

        $canonical = org_subpage_seo_absolute_url($relativePath);
        $title = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8');

        $out = '<meta name="description" content="' . $desc . '">' . "\n"
            . '<meta name="robots" content="index, follow">' . "\n"
            . '<link rel="canonical" href="' . $url . '">' . "\n"
            . '<meta property="og:type" content="website">' . "\n"
            . '<meta property="og:title" content="' . $title . '">' . "\n"
            . '<meta property="og:description" content="' . $desc . '">' . "\n"
            . '<meta property="og:url" content="' . $url . '">' . "\n"
            . '<meta property="og:locale" content="id_ID">' . "\n"
            . '<meta name="twitter:card" content="summary">' . "\n"
            . '<meta name="twitter:title" content="' . $title . '">' . "\n"
            . '<meta name="twitter:description" content="' . $desc . '">' . "\n";

        $out .= '<script type="application/ld+json">'
            . org_subpage_seo_breadcrumb_jsonld($crumbName, $relativePath)
            . '</script>' . "\n";

        return $out;
    }
}
