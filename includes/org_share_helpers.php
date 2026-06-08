<?php

/**
 * Helper share post Pusat Informasi (berita & pengumuman).
 *
 * Mengeluarkan markup tombol share yang ditangani sepenuhnya oleh
 * `assets/js/org-share.js` (native Web Share API + fallback popover berisi
 * WhatsApp/Facebook/X/Telegram/Email/Copy-Link).
 *
 * URL yang di-share selalu memakai base URL publik (lihat
 * `org_beranda_seo_public_base_url()`), sehingga aman dipakai baik di
 * production maupun di environment dev.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_seo.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';

if (!function_exists('org_share_build_post_url')) {
    /**
     * Bangun URL absolut ke halaman detail informasi.
     */
    function org_share_build_post_url(int $postId): string
    {
        if ($postId < 1) {
            return rtrim(org_beranda_seo_public_base_url(), '/') . '/';
        }
        $base = rtrim(org_beranda_seo_public_base_url(), '/');
        /* `org_page_url('informasi.php')` → "/informasi" (clean URL).
           Tambahkan query id secara manual untuk menghindari html-escape
           dari `org_href()` yang tidak cocok untuk URL yang akan kita
           lempar ke JS via data-attribute. */
        $path = org_page_url('informasi.php');
        return $base . $path . (str_contains($path, '?') ? '&' : '?') . 'id=' . $postId;
    }
}

if (!function_exists('org_share_excerpt_for')) {
    /**
     * Excerpt pendek (≤180 char) untuk dipakai sebagai body share.
     */
    function org_share_excerpt_for(array $post): string
    {
        $raw = (string) ($post['isi_teks'] ?? '');
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
        if ($clean === '') {
            return '';
        }
        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            if (mb_strlen($clean, 'UTF-8') > 180) {
                return rtrim(mb_substr($clean, 0, 177, 'UTF-8'), " .,;:-") . '…';
            }
            return $clean;
        }
        return strlen($clean) > 180 ? rtrim(substr($clean, 0, 177), " .,;:-") . '…' : $clean;
    }
}

if (!function_exists('org_share_label_for')) {
    /**
     * Label kategori untuk dipakai dalam teks share ("Berita"/"Pengumuman").
     */
    function org_share_label_for(array $post): string
    {
        $k = strtolower((string) ($post['kategori'] ?? ''));
        return $k === 'pengumuman' ? 'Pengumuman' : 'Berita';
    }
}

if (!function_exists('org_share_button_html')) {
    /**
     * Tombol ikon share untuk kartu di list (kecil, position: absolute).
     * Markup-nya ditempatkan SEBLAH dari pembungkus <a> agar nesting button-
     * dalam-anchor (HTML invalid) tidak terjadi.
     *
     * @param array<string, mixed> $post  Baris pusat informasi (butuh: id, judul, isi_teks, kategori).
     * @param string $variant  'card' (default) | 'hero' | 'carousel' | 'inline-detail'
     */
    function org_share_button_html(array $post, string $variant = 'card'): string
    {
        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            return '';
        }
        $url = org_share_build_post_url($id);
        $judul = (string) ($post['judul'] ?? 'Informasi');
        $label = org_share_label_for($post);
        $excerpt = org_share_excerpt_for($post);
        /* Caption Web Share API: "[Berita] Judul…\n\nExcerpt…\n\nURL" — dipakai
           oleh navigator.share() di mobile yang mendukungnya. */
        $shareText = '[' . $label . '] ' . $judul;
        if ($excerpt !== '') {
            $shareText .= "\n\n" . $excerpt;
        }

        $title = htmlspecialchars($judul, ENT_QUOTES, 'UTF-8');
        $textAttr = htmlspecialchars($shareText, ENT_QUOTES, 'UTF-8');
        $urlAttr = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $labelAttr = htmlspecialchars('Bagikan ' . strtolower($label) . ': ' . $judul, ENT_QUOTES, 'UTF-8');
        $variantAttr = htmlspecialchars($variant, ENT_QUOTES, 'UTF-8');

        $classMap = [
            'card' => 'org-share-btn org-share-btn--card',
            'hero' => 'org-share-btn org-share-btn--hero',
            'carousel' => 'org-share-btn org-share-btn--carousel',
            'inline-detail' => 'org-share-btn org-share-btn--inline',
        ];
        $cls = $classMap[$variant] ?? $classMap['card'];

        $html = '<button type="button" class="' . $cls . '"'
            . ' data-org-share="1"'
            . ' data-org-share-variant="' . $variantAttr . '"'
            . ' data-org-share-title="' . $title . '"'
            . ' data-org-share-text="' . $textAttr . '"'
            . ' data-org-share-url="' . $urlAttr . '"'
            . ' aria-label="' . $labelAttr . '"'
            . ' title="Bagikan ' . strtolower($label) . '">';
        $html .= '<i class="fa-solid fa-share-nodes" aria-hidden="true"></i>';
        if ($variant === 'inline-detail') {
            $html .= '<span class="org-share-btn__label">Bagikan</span>';
        }
        $html .= '</button>';

        return $html;
    }
}

if (!function_exists('org_share_post_image_absolute_url')) {
    /**
     * URL absolut gambar post (siap dipakai sebagai og:image).
     * Mengembalikan string kosong jika post tidak punya gambar — caller
     * boleh fallback ke logo situs.
     */
    function org_share_post_image_absolute_url(array $post): string
    {
        $gfile = trim((string) ($post['nama_gambar'] ?? ''));
        if ($gfile === '') {
            return '';
        }
        $base = rtrim(org_beranda_seo_public_base_url(), '/');
        $home = function_exists('org_home_url') ? org_home_url() : '/';
        $home = $home === '' ? '/' : $home;
        if ($home !== '/' && substr($home, -1) !== '/') {
            $home .= '/';
        }
        $prefix = function_exists('org_pusat_informasi_upload_web_prefix')
            ? org_pusat_informasi_upload_web_prefix()
            : 'uploads/pusat_informasi/';
        return $base . $home . ltrim($prefix, '/') . rawurlencode($gfile);
    }
}

if (!function_exists('org_share_post_image_dimensions')) {
    /**
     * Dimensi & MIME gambar post untuk og:image:width / height / type.
     * Membaca langsung dari disk (ringan, hanya getimagesize). Mengembalikan
     * array kosong kalau gambar tidak ada atau gagal dibaca.
     *
     * @return array{width:int, height:int, mime:string}|array{}
     */
    function org_share_post_image_dimensions(array $post): array
    {
        $gfile = trim((string) ($post['nama_gambar'] ?? ''));
        if ($gfile === '') {
            return [];
        }
        $rootDir = defined('ORG_ROOT') ? ORG_ROOT : dirname(__DIR__);
        $prefix = function_exists('org_pusat_informasi_upload_web_prefix')
            ? org_pusat_informasi_upload_web_prefix()
            : 'uploads/pusat_informasi/';
        $absPath = $rootDir . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, rtrim($prefix, '/')) . DIRECTORY_SEPARATOR . $gfile;
        if (!is_file($absPath)) {
            return [];
        }
        $info = @getimagesize($absPath);
        if (!is_array($info) || count($info) < 2) {
            return [];
        }
        return [
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
            'mime'   => (string) ($info['mime'] ?? 'image/jpeg'),
        ];
    }
}

if (!function_exists('org_share_post_meta_html')) {
    /**
     * Render Open Graph + Twitter Card meta tags untuk detail post.
     * Aman dipanggil dari informasi.php BEFORE `org_portal_apply_assets()`
     * — tinggal di-prepend ke `$extraHeadMarkup` agar muncul di <head>.
     *
     * Hasilnya memastikan crawler WhatsApp/Facebook/Telegram/X menampilkan
     * preview lengkap (judul, deskripsi, GAMBAR post — bukan logo) saat
     * URL informasi dibagikan.
     *
     * @param array<string, mixed> $post Baris pusat_informasi (id, judul, isi_teks, kategori, nama_gambar, created_at)
     */
    function org_share_post_meta_html(array $post): string
    {
        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            return '';
        }
        $judul = trim((string) ($post['judul'] ?? ''));
        if ($judul === '') {
            $judul = 'Pusat Informasi';
        }
        $label = org_share_label_for($post);
        $excerpt = org_share_excerpt_for($post);
        if ($excerpt === '') {
            $excerpt = $label . ' resmi dari Bagian Organisasi Setda Kabupaten Kepulauan Aru.';
        }
        $url = org_share_build_post_url($id);
        $siteName = 'Bagian Organisasi Setda Kepulauan Aru';

        /* Gambar absolut — pakai gambar post; fallback ke logo situs supaya
           crawler tetap dapat preview meskipun post belum punya gambar. */
        $imgAbs = org_share_post_image_absolute_url($post);
        $imgDims = $imgAbs !== '' ? org_share_post_image_dimensions($post) : [];
        $imgAlt = $judul;
        $imgIsLogo = false;
        if ($imgAbs === '') {
            $logoPath = function_exists('org_site_logo_web_path') ? org_site_logo_web_path() : '';
            if ($logoPath !== '') {
                $imgAbs = org_beranda_seo_logo_absolute_url($logoPath, false);
                $imgAlt = function_exists('org_beranda_seo_logo_alt') ? org_beranda_seo_logo_alt() : $siteName;
                $imgIsLogo = true;
            }
        }

        $tglRaw = (string) ($post['created_at'] ?? '');
        $publishedIso = '';
        if ($tglRaw !== '') {
            $ts = strtotime($tglRaw);
            if ($ts !== false) {
                $publishedIso = date('c', $ts);
            }
        }

        $esc = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        /* Description meta — banyak crawler & search engine juga membaca ini
           selain og:description. */
        $out = '<meta name="description" content="' . $esc($excerpt) . '">' . "\n"
            . '<link rel="canonical" href="' . $esc($url) . '">' . "\n"
            . '<meta property="og:type" content="article">' . "\n"
            . '<meta property="og:site_name" content="' . $esc($siteName) . '">' . "\n"
            . '<meta property="og:locale" content="id_ID">' . "\n"
            . '<meta property="og:title" content="' . $esc($judul) . '">' . "\n"
            . '<meta property="og:description" content="' . $esc($excerpt) . '">' . "\n"
            . '<meta property="og:url" content="' . $esc($url) . '">' . "\n"
            . '<meta property="article:section" content="' . $esc($label) . '">' . "\n";

        if ($publishedIso !== '') {
            $out .= '<meta property="article:published_time" content="' . $esc($publishedIso) . '">' . "\n";
        }

        if ($imgAbs !== '') {
            $out .= '<meta property="og:image" content="' . $esc($imgAbs) . '">' . "\n"
                . '<meta property="og:image:secure_url" content="' . $esc($imgAbs) . '">' . "\n"
                . '<meta property="og:image:alt" content="' . $esc($imgAlt) . '">' . "\n";
            if (!empty($imgDims['mime'])) {
                $out .= '<meta property="og:image:type" content="' . $esc((string) $imgDims['mime']) . '">' . "\n";
            }
            if (!empty($imgDims['width']) && !empty($imgDims['height'])) {
                $out .= '<meta property="og:image:width" content="' . (int) $imgDims['width'] . '">' . "\n"
                    . '<meta property="og:image:height" content="' . (int) $imgDims['height'] . '">' . "\n";
            }

            /* Twitter Card — summary_large_image hanya jika gambar cukup besar
               (≥ 300×157 sesuai spec); kalau pakai logo (kecil) jatuh ke summary. */
            $twCard = 'summary_large_image';
            if ($imgIsLogo || (!empty($imgDims['width']) && (int) $imgDims['width'] < 300)) {
                $twCard = 'summary';
            }
            $out .= '<meta name="twitter:card" content="' . $esc($twCard) . '">' . "\n"
                . '<meta name="twitter:title" content="' . $esc($judul) . '">' . "\n"
                . '<meta name="twitter:description" content="' . $esc($excerpt) . '">' . "\n"
                . '<meta name="twitter:image" content="' . $esc($imgAbs) . '">' . "\n"
                . '<meta name="twitter:image:alt" content="' . $esc($imgAlt) . '">' . "\n";
        } else {
            $out .= '<meta name="twitter:card" content="summary">' . "\n"
                . '<meta name="twitter:title" content="' . $esc($judul) . '">' . "\n"
                . '<meta name="twitter:description" content="' . $esc($excerpt) . '">' . "\n";
        }

        return $out;
    }
}

if (!function_exists('org_share_assets_html')) {
    /**
     * Hanya mengembalikan tag <link> dan <script> sekali per request, sehingga
     * aman dipanggil dari beberapa partial sekaligus.
     */
    function org_share_assets_html(): string
    {
        static $emitted = false;
        if ($emitted) {
            return '';
        }
        $emitted = true;
        $css = '<link rel="stylesheet" href="'
            . htmlspecialchars(org_asset_url('assets/css/org-share.css?v=2'), ENT_QUOTES, 'UTF-8')
            . '">' . "\n";
        $js = '<script src="'
            . htmlspecialchars(org_asset_url('assets/js/org-share.js?v=1'), ENT_QUOTES, 'UTF-8')
            . '" defer></script>' . "\n";
        return $css . $js;
    }
}
