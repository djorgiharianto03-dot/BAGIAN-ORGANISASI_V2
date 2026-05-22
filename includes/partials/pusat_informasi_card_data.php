<?php

/** @var array<string, mixed> $pi */
/** @var callable|null $highlight */

if (!is_array($pi)) {
    return;
}

$piId = (int) ($pi['id'] ?? 0);
$badge = beranda_pi_badge_meta($pi);
$badge['class'] = str_replace('pub-pi-card__badge', 'np-card__badge', $badge['class']);
$isFeatured = !empty((int) ($pi['is_featured'] ?? 0));
$gfile = trim((string) ($pi['nama_gambar'] ?? ''));
$imgUrl = $gfile !== '' ? org_pusat_informasi_upload_web_prefix() . rawurlencode($gfile) : '';
$rawT = (string) ($pi['isi_teks'] ?? '');
$excerpt = trim(preg_replace('/\s+/u', ' ', strip_tags($rawT)));
if (strlen($excerpt) > 220) {
    $excerpt = function_exists('mb_substr')
        ? mb_substr($excerpt, 0, 217, 'UTF-8') . '…'
        : substr($excerpt, 0, 217) . '…';
}
$judul = (string) ($pi['judul'] ?? '');
$judulHtml = is_callable($highlight)
    ? $highlight($judul, (string) ($pusatSearchQuery ?? ''))
    : htmlspecialchars($judul, ENT_QUOTES, 'UTF-8');
$excerptHtml = is_callable($highlight)
    ? $highlight($excerpt, (string) ($pusatSearchQuery ?? ''))
    : htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
$tgl = (string) ($pi['created_at'] ?? '');
$tglFmt = $tgl !== '' ? date('d M Y', strtotime($tgl)) : '';
$katLabel = beranda_pi_kategori_label($pi);
$isPeng = (string) ($pi['kategori'] ?? '') === 'pengumuman';
$katClass = $isPeng ? 'np-card__cat--pengumuman' : 'np-card__cat--berita';
