<?php

/**
 * Memastikan struktur folder uploads ada (deploy CloudPanel / clone Git baru).
 */
function org_upload_subdirectory_names(): array
{
    return [
        'perpustakaan_digital',
        'foto_struktur',
        'gallery',
        'tugas_pegawai',
        'layanan_assets',
        'disposisi_bukti',
        'surat_masuk',
        'surat_keluar',
        'pengumuman',
        'pusat_informasi',
    ];
}

function org_ensure_upload_directories(?string $root = null): void
{
    if ($root === null || $root === '') {
        $root = defined('ORG_ROOT') ? ORG_ROOT : dirname(__DIR__);
    }
    $uploadRoot = $root . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadRoot) && !@mkdir($uploadRoot, 0775, true) && !is_dir($uploadRoot)) {
        return;
    }
    foreach (org_upload_subdirectory_names() as $sub) {
        $path = $uploadRoot . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
    }
}

/**
 * Format ukuran byte ke string ringkas (KB / MB).
 */
function org_format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        $mb = $bytes / (1024 * 1024);

        return rtrim(rtrim(number_format($mb, 1, '.', ''), '0'), '.') . ' MB';
    }
    if ($bytes >= 1024) {
        return (int) round($bytes / 1024) . ' KB';
    }

    return $bytes . ' B';
}

/**
 * Pesan error upload yang manusiawi & spesifik per kode UPLOAD_ERR_*.
 *
 * Penting: PHP membatasi upload via `upload_max_filesize` (per file) dan
 * `post_max_size` (total POST). Bila admin menyalahkan "format gambar"
 * padahal sebenarnya ukuran melebihi `upload_max_filesize`, mereka tidak
 * akan tahu penyebab sebenarnya. Helper ini mengembalikan pesan presisi.
 */
function org_upload_error_message(int $errorCode, int $maxBytesAppLimit = 0): string
{
    $appLimitTxt = $maxBytesAppLimit > 0 ? ' (batas aplikasi ' . org_format_bytes($maxBytesAppLimit) . ')' : '';
    $iniMax = (int) org_upload_parse_ini_size((string) ini_get('upload_max_filesize'));
    $postMax = (int) org_upload_parse_ini_size((string) ini_get('post_max_size'));
    $iniMaxTxt = $iniMax > 0 ? org_format_bytes($iniMax) : '?';
    $postMaxTxt = $postMax > 0 ? org_format_bytes($postMax) : '?';

    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return '';
        case UPLOAD_ERR_INI_SIZE:
            return 'Ukuran berkas melebihi batas server (upload_max_filesize = ' . $iniMaxTxt . ')' . $appLimitTxt . '. Naikkan setting PHP atau kompres berkas terlebih dahulu.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Ukuran berkas melebihi batas formulir' . $appLimitTxt . '.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload terputus di tengah jalan. Coba lagi dengan koneksi yang stabil.';
        case UPLOAD_ERR_NO_FILE:
            return 'Belum ada berkas yang dipilih. Klik tombol pilih berkas terlebih dahulu.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Server tidak menemukan folder sementara untuk upload. Hubungi administrator server.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Server gagal menulis berkas ke disk. Periksa izin folder uploads.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload diblokir oleh ekstensi PHP di server.';
    }
    if ($postMax > 0 && $maxBytesAppLimit > 0 && $maxBytesAppLimit > $postMax) {
        return 'Ukuran berkas terlalu besar (post_max_size = ' . $postMaxTxt . ').';
    }

    return 'Gagal mengunggah berkas. Coba lagi atau hubungi administrator.';
}

/**
 * Parse string ukuran php.ini (mis. "2M", "8M", "512K", "1G") menjadi byte.
 */
function org_upload_parse_ini_size(string $val): int
{
    $val = trim($val);
    if ($val === '') {
        return 0;
    }
    $unit = strtolower(substr($val, -1));
    $num = (float) $val;
    switch ($unit) {
        case 'g':
            $num *= 1024;
            // no break
        case 'm':
            $num *= 1024;
            // no break
        case 'k':
            $num *= 1024;
            break;
    }

    return (int) $num;
}
