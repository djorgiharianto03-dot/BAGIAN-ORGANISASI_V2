<?php

/**
 * Kategori bagian untuk arsip surat masuk & keluar (nilai disimpan = kunci slug).
 *
 * @return array<string, string>
 */
function org_arsip_kategori_bagian_map(): array
{
    return [
        'kelembagaan_anjab' => 'Kelembagaan dan Anjab',
        'kinerja_rb' => 'Kinerja dan RB',
        'pelayanan_tatalaksana' => 'Pelayanan Publik dan Tata Laksana',
        'kepegawaian' => 'Kepegawaian',
        'keuangan' => 'Keuangan',
        'kabag' => 'Kabag',
    ];
}

/**
 * @param array<string, mixed> $metaRow baris meta dari JSON atau DB
 */
function org_arsip_kategori_bagian_label(array $metaRow): string
{
    $k = trim((string) ($metaRow['kategori_bagian'] ?? ''));
    $map = org_arsip_kategori_bagian_map();

    if ($k === '') {
        return '—';
    }

    return $map[$k] ?? $k;
}
