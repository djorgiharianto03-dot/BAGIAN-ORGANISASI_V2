<?php
declare(strict_types=1);

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
