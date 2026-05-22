<?php

if (!function_exists('beranda_pi_badge_meta')) {
    /**
     * @param array<string, mixed> $pi
     * @return array{text: string, class: string}
     */
    function beranda_pi_badge_meta(array $pi): array
    {
        $isFeatured = !empty((int) ($pi['is_featured'] ?? 0));
        $isPeng = (string) ($pi['kategori'] ?? '') === 'pengumuman';
        if ($isFeatured) {
            return [
                'text' => $isPeng ? 'PENGUMUMAN UTAMA' : 'BERITA UTAMA',
                'class' => 'pub-pi-card__badge--utama',
            ];
        }
        if ($isPeng) {
            return ['text' => 'PENGUMUMAN', 'class' => 'pub-pi-card__badge--pengumuman'];
        }

        return ['text' => 'BERITA', 'class' => 'pub-pi-card__badge--berita'];
    }

    /**
     * Label kategori untuk baris meta kartu (beranda / halaman publikasi).
     */
    function beranda_pi_kategori_label(array $pi): string
    {
        $isFeatured = !empty((int) ($pi['is_featured'] ?? 0));
        $isPeng = (string) ($pi['kategori'] ?? '') === 'pengumuman';
        if ($isFeatured) {
            return $isPeng ? 'Pengumuman utama' : 'Berita utama';
        }

        return $isPeng ? 'Pengumuman' : 'Berita';
    }
}
