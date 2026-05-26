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
            . htmlspecialchars(org_asset_url('assets/css/org-share.css?v=1'), ENT_QUOTES, 'UTF-8')
            . '">' . "\n";
        $js = '<script src="'
            . htmlspecialchars(org_asset_url('assets/js/org-share.js?v=1'), ENT_QUOTES, 'UTF-8')
            . '" defer></script>' . "\n";
        return $css . $js;
    }
}
