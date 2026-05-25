<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_kategori_bagian.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);

/** Koneksi DB untuk sinkron kolom disposisi (tabel arsip_surat / surat_disposisi). */
$dbArsip = org_db();
$arsipSessionRole = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$canArsipAdminDelete = in_array($arsipSessionRole, ['super_admin', 'admin'], true);

$pageTitle = 'Arsip Surat — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-arsip-dashboard mode-eorganisasi';
if (!defined('ORG_ARSIP_MAX_UPLOAD_BYTES')) {
    define('ORG_ARSIP_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
}
$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .page-arsip-dashboard {
        font-family: 'Poppins', sans-serif;
        background:
            radial-gradient(900px 380px at 8% -8%, rgba(37, 99, 235, 0.16), rgba(37, 99, 235, 0)),
            radial-gradient(880px 360px at 98% -4%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0)),
            #f3f7fd;
    }
    .page-arsip-dashboard .site-main {
        width: 100%;
        max-width: none;
        padding-left: clamp(12px, 2.2vw, 28px);
        padding-right: clamp(12px, 2.2vw, 28px);
    }
    .page-arsip-dashboard .arsip-card {
        border-radius: 15px;
        border: 0;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.11);
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }
    .page-arsip-dashboard .arsip-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid #d9e8fb;
        background:
            radial-gradient(620px 220px at -8% -14%, rgba(37, 99, 235, 0.18), rgba(37, 99, 235, 0)),
            radial-gradient(540px 220px at 108% -18%, rgba(14, 165, 233, 0.17), rgba(14, 165, 233, 0)),
            linear-gradient(135deg, #ffffff 0%, #f5faff 100%);
        box-shadow:
            0 20px 40px rgba(15, 23, 42, 0.11),
            inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }
    .page-arsip-dashboard .arsip-hero::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: linear-gradient(90deg, #1d4ed8 0%, #0ea5e9 55%, #22c55e 100%);
        opacity: 0.92;
    }
    .page-arsip-dashboard .arsip-hero__title {
        margin: 0;
        font-size: clamp(1.35rem, 1.05rem + 1.25vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -0.015em;
        line-height: 1.15;
        color: #08264a;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.9);
        position: relative;
        display: inline-block;
        padding-bottom: 0.35rem;
    }
    .page-arsip-dashboard .arsip-hero__title::after {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: 0;
        width: min(170px, 48%);
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #1d4ed8 0%, #0ea5e9 100%);
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.28);
    }
    .page-arsip-dashboard .arsip-hero__heading {
        text-align: center;
        width: 100%;
    }
    .page-arsip-dashboard .arsip-hero__subtitle { color: #4b607a; margin-bottom: 0; }
    .page-arsip-dashboard .arsip-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        color: #0f3f88;
        background: rgba(234, 242, 255, 0.8);
        border: 1px solid #c7dcff;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.12);
    }
    .page-arsip-dashboard .arsip-title { color: #0f2748; font-weight: 700; }
    .page-arsip-dashboard .arsip-btn-save {
        border: 0; color: #fff; font-weight: 600;
        background-image: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.32);
    }
    .page-arsip-dashboard .arsip-btn-save:hover { color: #fff; filter: brightness(1.04); }
    /* Tabel arsip: muat lebar viewport, teks membungkus, aksi ringkas */
    .page-arsip-dashboard .digital-library__table-wrap {
        max-width: 100%;
    }
    .page-arsip-dashboard #arsipDataTable.digital-library__table {
        table-layout: fixed;
        width: 100%;
        font-size: 0.8125rem;
    }
    .page-arsip-dashboard #arsipDataTable.digital-library__table thead th {
        white-space: normal;
        line-height: 1.25;
        padding: 0.5rem 0.4rem;
        font-size: 0.72rem;
        letter-spacing: 0.02em;
        vertical-align: bottom;
        hyphens: auto;
    }
    .page-arsip-dashboard #arsipDataTable.digital-library__table tbody td {
        padding: 0.45rem 0.4rem;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .page-arsip-dashboard #arsipDataTable .arsip-tbl__cell--jenis,
    .page-arsip-dashboard #arsipDataTable .arsip-tbl__cell--kat,
    .page-arsip-dashboard #arsipDataTable .arsip-tbl__cell--dispo {
        font-size: 0.76rem;
    }
    .page-arsip-dashboard #arsipDataTable .arsip-tbl__cell--tgl {
        font-variant-numeric: tabular-nums;
        white-space: normal;
        line-height: 1.35;
    }
    .page-arsip-dashboard #arsipDataTable .digital-library__actions {
        white-space: nowrap;
        width: 1%;
        padding-left: 0.25rem;
    }
    .page-arsip-dashboard #arsipDataTable .digital-library__actions .btn {
        padding: 0.28rem 0.42rem;
        line-height: 1;
        min-width: 2.1rem;
    }
    .page-arsip-dashboard #arsipDataTable .digital-library__actions .btn + .btn {
        margin-left: 0.2rem;
    }
    @media (max-width: 767.98px) {
        .page-arsip-dashboard #arsipDataTable.digital-library__table {
            min-width: 0 !important;
        }
    }
    .page-arsip-dashboard .arsip-workspace {
        display: grid;
        grid-template-columns: minmax(300px, 400px) minmax(0, 1fr);
        gap: clamp(1.15rem, 2.2vw, 1.85rem);
        align-items: start;
        width: 100%;
    }
    @media (max-width: 1199.98px) {
        .page-arsip-dashboard .arsip-workspace {
            grid-template-columns: 1fr;
        }
        .page-arsip-dashboard .arsip-workspace__form {
            position: static;
        }
    }
    .page-arsip-dashboard .arsip-workspace__form {
        position: sticky;
        top: 0.85rem;
        z-index: 2;
    }
    .page-arsip-dashboard .arsip-form-card .card-body {
        padding: clamp(1.15rem, 2vw, 1.5rem) !important;
    }
    .page-arsip-dashboard .arsip-form-card .form-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.35rem;
    }
    .page-arsip-dashboard .arsip-form-card .form-control,
    .page-arsip-dashboard .arsip-form-card .form-select {
        border-radius: 10px;
        border-color: #cbd5e1;
        font-size: 0.9rem;
        padding: 0.55rem 0.75rem;
    }
    .page-arsip-dashboard .arsip-form-card .form-control:focus,
    .page-arsip-dashboard .arsip-form-card .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .page-arsip-dashboard .arsip-file-field {
        border: 1.5px dashed #93c5fd;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f7ff 100%);
        padding: 0.85rem 1rem;
    }
    .page-arsip-dashboard .arsip-file-field .form-control {
        border-style: dashed;
        background: #fff;
    }
    .page-arsip-dashboard .arsip-btn-save {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 10px;
        margin-top: 0.25rem;
    }
    .page-arsip-dashboard .arsip-data-card .card-body {
        padding: clamp(1.15rem, 2vw, 1.5rem) !important;
        min-width: 0;
    }
    .page-arsip-dashboard .arsip-chart-box {
        position: relative;
        height: 220px;
        margin-bottom: 1.15rem;
        padding: 0.85rem 1rem 0.5rem;
        border-radius: 14px;
        border: 1px solid #e2ebf7;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .page-arsip-dashboard .arsip-data-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #e8eef5;
    }
    .page-arsip-dashboard .arsip-data-card .library-doc-category-filter {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(9.5rem, 1fr));
        gap: 0.55rem;
    }
    @media (min-width: 1200px) {
        .page-arsip-dashboard .arsip-data-card .library-doc-category-filter {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .page-arsip-dashboard .arsip-list-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem 1rem;
        margin-bottom: 1rem;
    }
    .page-arsip-dashboard .arsip-data-search-form {
        flex: 1 1 280px;
        min-width: min(100%, 320px);
        max-width: 100%;
    }
    .page-arsip-dashboard .arsip-data-search__wrap {
        display: flex;
        flex-wrap: nowrap;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
    }
    .page-arsip-dashboard .arsip-data-search__field {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: center;
    }
    .page-arsip-dashboard .arsip-data-search__icon {
        position: absolute;
        left: 0.85rem;
        color: #64748b;
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 1;
    }
    .page-arsip-dashboard .arsip-data-search__input {
        width: 100%;
        min-width: 0;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.55rem 0.75rem 0.55rem 2.35rem;
        font-size: 0.9rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .page-arsip-dashboard .arsip-data-search__input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        outline: none;
    }
    .page-arsip-dashboard .arsip-data-search__input::placeholder {
        color: #94a3b8;
    }
    .page-arsip-dashboard .arsip-data-search__btn {
        flex: 0 0 auto;
        white-space: nowrap;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.55rem 1.15rem;
        min-height: 42px;
    }
    .page-arsip-dashboard .arsip-data-search__reset {
        flex: 0 0 auto;
        white-space: nowrap;
        border-radius: 10px;
        min-height: 42px;
        align-self: stretch;
        display: inline-flex;
        align-items: center;
    }
    .page-arsip-dashboard .arsip-list-toolbar__pages {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .page-arsip-dashboard .arsip-list-toolbar__pages .form-select {
        border-radius: 10px;
        min-width: 5.5rem;
    }
    @media (max-width: 575.98px) {
        .page-arsip-dashboard .arsip-data-search__wrap {
            flex-wrap: wrap;
        }
        .page-arsip-dashboard .arsip-data-search__field {
            flex: 1 1 100%;
        }
        .page-arsip-dashboard .arsip-data-search__btn,
        .page-arsip-dashboard .arsip-data-search__reset {
            flex: 1 1 auto;
        }
    }
</style>
HTML;

if (!isset($messageType) || $messageType === '') {
    $messageType = 'info';
}
$submitted = [
    'jenis_surat' => '',
    'kategori_bagian' => '',
    'nomor_surat' => '',
    'instansi_asal' => '',
    'instansi_tujuan' => '',
    'perihal_ringkasan' => '',
    'ikut_monitoring_disposisi' => true,
];
$arsipPerPage = 30;
$arsipAllowedPerPage = [15, 30, 50, 100];
$arsipRequestedPerPage = (int) ($_GET['per_page'] ?? $arsipPerPage);
 $arsipSearchQuery = trim((string) ($_GET['q'] ?? ''));
if (in_array($arsipRequestedPerPage, $arsipAllowedPerPage, true)) {
    $arsipPerPage = $arsipRequestedPerPage;
}
$arsipCurrentPage = max(1, (int) ($_GET['page'] ?? 1));
$arsipTotalRows = 0;
$arsipTotalPages = 1;
$arsipDirMap = [
    'masuk' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk',
    'keluar' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar',
];
$arsipChartCacheFile = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.arsip_chart_cache.json';
$arsipChartCacheTtl = 180;

$arsipInvalidateChartCache = static function () use ($arsipChartCacheFile): void {
    if (is_file($arsipChartCacheFile)) {
        @unlink($arsipChartCacheFile);
    }
};

$arsipBuildEmptyMonthBuckets = static function (): array {
    $map = [];
    for ($i = 11; $i >= 0; $i--) {
        $ym = date('Y-m', strtotime("-{$i} months"));
        $map[$ym] = ['masuk' => 0, 'keluar' => 0];
    }

    return $map;
};

/** Satu pass folder: isi bucket bulan untuk grafik (tanpa sort / tanpa array besar). */
$arsipAggregateMonthsFromDisk = static function (array $dirMap) use ($arsipBuildEmptyMonthBuckets): array {
    $arsipMonthMap = $arsipBuildEmptyMonthBuckets();
    foreach ($dirMap as $jenisKey => $dirPath) {
        if (!is_dir($dirPath)) {
            continue;
        }
        try {
            $it = new FilesystemIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        } catch (Throwable $e) {
            continue;
        }
        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $ext = strtolower((string) pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                continue;
            }
            $ym = date('Y-m', (int) $fileInfo->getMTime());
            if (isset($arsipMonthMap[$ym][$jenisKey])) {
                $arsipMonthMap[$ym][$jenisKey]++;
            }
        }
    }

    $labels = array_map(static fn(string $ym): string => date('M Y', strtotime($ym . '-01')), array_keys($arsipMonthMap));
    $masuk = array_map(static fn(array $v): int => (int) ($v['masuk'] ?? 0), array_values($arsipMonthMap));
    $keluar = array_map(static fn(array $v): int => (int) ($v['keluar'] ?? 0), array_values($arsipMonthMap));

    return ['labels' => $labels, 'masuk' => $masuk, 'keluar' => $keluar];
};

$arsipMetaFile = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'arsip_surat_meta.json';
$arsipMetaMap = [];
if (is_file($arsipMetaFile)) {
    $metaRaw = file_get_contents($arsipMetaFile);
    if ($metaRaw !== false && $metaRaw !== '') {
        $metaDecoded = json_decode($metaRaw, true);
        if (is_array($metaDecoded)) {
            $arsipMetaMap = $metaDecoded;
        }
    }
}

$collectArsipRows = static function (array $dirMap, int $maxRows = 0): array {
    $rows = [];
    foreach ($dirMap as $jenisKey => $dirPath) {
        if (!is_dir($dirPath)) {
            continue;
        }
        try {
            $it = new FilesystemIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        } catch (Throwable $e) {
            continue;
        }
        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $name = $fileInfo->getFilename();
            $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                continue;
            }
            $rows[] = [
                'jenis' => $jenisKey,
                'nama_file' => $name,
                'ukuran_bytes' => (int) $fileInfo->getSize(),
                'tgl_upload' => (int) $fileInfo->getMTime(),
                'meta_key' => $jenisKey . '|' . $name,
            ];
        }
    }
    usort($rows, static function (array $a, array $b): int {
        return ($b['tgl_upload'] ?? 0) <=> ($a['tgl_upload'] ?? 0);
    });
    if ($maxRows > 0 && count($rows) > $maxRows) {
        return array_slice($rows, 0, $maxRows);
    }

    return $rows;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && (string) $_POST['action'] === 'upload_arsip') {
    if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
        $messageType = 'danger';
    } else {
        $jenisSurat = strtolower(trim((string) ($_POST['jenis_surat'] ?? '')));
        $kategoriBagian = trim((string) ($_POST['kategori_bagian'] ?? ''));
        $nomorSurat = trim((string) ($_POST['nomor_surat'] ?? ''));
        $instansiAsal = trim((string) ($_POST['instansi_asal'] ?? ''));
        $instansiTujuan = trim((string) ($_POST['instansi_tujuan'] ?? ''));
        $perihalRingkasan = trim((string) ($_POST['perihal_ringkasan'] ?? ''));
        $ikutMonitoringDisposisi = $jenisSurat === 'masuk'
            && isset($_POST['ikut_monitoring_disposisi'])
            && (string) $_POST['ikut_monitoring_disposisi'] !== '0';
        $submitted = [
            'jenis_surat' => $jenisSurat,
            'kategori_bagian' => $kategoriBagian,
            'nomor_surat' => $nomorSurat,
            'instansi_asal' => $instansiAsal,
            'instansi_tujuan' => $instansiTujuan,
            'perihal_ringkasan' => $perihalRingkasan,
            'ikut_monitoring_disposisi' => $ikutMonitoringDisposisi,
        ];
        $arsipBagianMap = org_arsip_kategori_bagian_map();

        if (!in_array($jenisSurat, ['masuk', 'keluar'], true)) {
            $message = 'Jenis surat tidak valid. Pilih surat masuk atau surat keluar.';
            $messageType = 'warning';
        } elseif ($kategoriBagian === '' || !array_key_exists($kategoriBagian, $arsipBagianMap)) {
            $message = 'Kategori bagian wajib dipilih dari daftar yang tersedia.';
            $messageType = 'warning';
        } elseif ($nomorSurat === '' || $instansiAsal === '' || $instansiTujuan === '' || $perihalRingkasan === '') {
            $message = 'Nomor surat, instansi asal, instansi tujuan, dan perihal/ringkasan wajib diisi.';
            $messageType = 'warning';
        } elseif (!isset($_FILES['arsip_pdf'])) {
            $message = 'File PDF belum dipilih.';
            $messageType = 'warning';
        } else {
            $file = $_FILES['arsip_pdf'];
            if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = 'Terjadi kesalahan saat upload file.';
                $messageType = 'danger';
            } elseif ((int) ($file['size'] ?? 0) <= 0) {
                $message = 'Ukuran file tidak valid.';
                $messageType = 'warning';
            } elseif ((int) ($file['size'] ?? 0) > ORG_ARSIP_MAX_UPLOAD_BYTES) {
                $message = 'Ukuran file melebihi batas maksimal 20 MB.';
                $messageType = 'warning';
            } else {
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($file['name'] ?? '')));
                $ext = strtolower((string) pathinfo($safeName, PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    $message = 'Format file tidak didukung. Gunakan PDF.';
                    $messageType = 'warning';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo !== false ? (string) finfo_file($finfo, (string) $file['tmp_name']) : '';
                    if ($finfo !== false) {
                        finfo_close($finfo);
                    }

                    if ($mimeType !== 'application/pdf') {
                        $message = 'File yang diunggah harus berupa PDF valid.';
                        $messageType = 'warning';
                    } else {
                        $target_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                        $target_dir .= $jenisSurat === 'masuk' ? 'surat_masuk' : 'surat_keluar';
                        $target_dir .= DIRECTORY_SEPARATOR;

                        if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
                            $message = 'Folder tujuan tidak dapat dibuat.';
                            $messageType = 'danger';
                        } else {
                            $baseName = pathinfo($safeName, PATHINFO_FILENAME);
                            if ($baseName === '') {
                                $baseName = 'arsip_surat';
                            }
                            $targetName = $baseName . '.pdf';
                            $counter = 1;
                            while (is_file($target_dir . $targetName)) {
                                $counter++;
                                $targetName = $baseName . '_' . $counter . '.pdf';
                            }

                            $targetPath = $target_dir . $targetName;
                            if (move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
                                $metaKey = $jenisSurat . '|' . $targetName;
                                $arsipMetaMap[$metaKey] = [
                                    'jenis' => $jenisSurat,
                                    'nama_file' => $targetName,
                                    'nomor_surat' => $nomorSurat,
                                    'kategori_bagian' => $kategoriBagian,
                                    'perihal_ringkasan' => $perihalRingkasan,
                                    'instansi_asal' => $instansiAsal,
                                    'instansi_tujuan' => $instansiTujuan,
                                    'ikut_monitoring_disposisi' => $jenisSurat === 'masuk' ? $ikutMonitoringDisposisi : true,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ];
                                if (!is_dir(dirname($arsipMetaFile))) {
                                    @mkdir(dirname($arsipMetaFile), 0777, true);
                                }
                                @file_put_contents($arsipMetaFile, json_encode($arsipMetaMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                if (
                                    $dbArsip instanceof mysqli
                                    && org_arsip_surat_table_exists($dbArsip)
                                    && org_arsip_meta_row_syncs_to_monitoring_table($jenisSurat, $arsipMetaMap[$metaKey])
                                ) {
                                    $existingId = org_arsip_surat_find_id_by_file($dbArsip, $jenisSurat, $targetName);
                                    if ($existingId > 0) {
                                        $arsipMetaMap[$metaKey]['arsip_surat_id'] = $existingId;
                                    } else {
                                        $newArsipId = org_arsip_surat_insert_from_meta($dbArsip, $jenisSurat, $targetName, $arsipMetaMap[$metaKey]);
                                        if ($newArsipId !== null) {
                                            $arsipMetaMap[$metaKey]['arsip_surat_id'] = $newArsipId;
                                        }
                                    }
                                    if (isset($arsipMetaMap[$metaKey]['arsip_surat_id'])) {
                                        @file_put_contents($arsipMetaFile, json_encode($arsipMetaMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    }
                                }
                                $message = 'File PDF berhasil disimpan ke folder uploads/' . ($jenisSurat === 'masuk' ? 'surat_masuk' : 'surat_keluar') . '/';
                                if ($jenisSurat === 'masuk') {
                                    $msgDispo = $ikutMonitoringDisposisi
                                        ? ($arsipSessionRole === 'sub_admin_eorganisasi'
                                            ? 'Surat ini masuk daftar disposisi awal; input dan status tanda terima Kabag di halaman Disposisi awal & tanda terima Kabag (E-Organisasi), dengan berkas PDF yang sama.'
                                            : 'Surat ini masuk daftar Monitoring Disposisi (Surat Masuk); input disposisi dilakukan di sana dengan berkas PDF yang sama.')
                                        : 'Surat ini hanya disimpan di Arsip (tidak muncul di Monitoring untuk alur disposisi).';
                                    $message .= ' ' . $msgDispo;
                                }
                                $messageType = 'success';
                                $arsipInvalidateChartCache();
                            } else {
                                $message = 'Gagal menyimpan file ke server.';
                                $messageType = 'danger';
                            }
                        }
                    }
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && (string) $_POST['action'] === 'hapus_arsip_json') {
    if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
        $messageType = 'danger';
    } elseif (!$canArsipAdminDelete) {
        $message = 'Anda tidak memiliki izin menghapus arsip.';
        $messageType = 'danger';
    } else {
        $returnPage = max(1, (int) ($_POST['return_page'] ?? 1));
        $returnPer = (int) ($_POST['return_per_page'] ?? 30);
        if (!in_array($returnPer, $arsipAllowedPerPage, true)) {
            $returnPer = 30;
        }
        $returnQ = trim((string) ($_POST['return_q'] ?? ''));
        $metaKeyRaw = trim((string) ($_POST['meta_key'] ?? ''));
        $parts = explode('|', $metaKeyRaw, 2);
        $jenisDel = strtolower((string) ($parts[0] ?? ''));
        $fileName = isset($parts[1]) ? basename((string) $parts[1]) : '';
        if (
            !in_array($jenisDel, ['masuk', 'keluar'], true)
            || $fileName === ''
            || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/', $fileName)
        ) {
            $message = 'Referensi arsip tidak valid.';
            $messageType = 'warning';
        } else {
            $canonicalKey = $jenisDel . '|' . $fileName;
            $subDir = $jenisDel === 'masuk' ? 'surat_masuk' : 'surat_keluar';
            $absDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subDir);
            $absFile = ($absDir !== false) ? realpath($absDir . DIRECTORY_SEPARATOR . $fileName) : false;
            $inTree = $absDir !== false && $absFile !== false
                && str_starts_with($absFile, $absDir . DIRECTORY_SEPARATOR);
            if (!$inTree) {
                $message = 'Berkas tidak ditemukan atau jalur tidak valid.';
                $messageType = 'warning';
            } else {
                if (is_file($absFile)) {
                    @unlink($absFile);
                }
                $metaForDel = $arsipMetaMap[$canonicalKey] ?? $arsipMetaMap[$metaKeyRaw] ?? [];
                $delDbId = 0;
                if (is_array($metaForDel) && isset($metaForDel['arsip_surat_id'])) {
                    $delDbId = (int) $metaForDel['arsip_surat_id'];
                }
                if ($dbArsip instanceof mysqli && org_arsip_surat_table_exists($dbArsip)) {
                    org_arsip_surat_delete_by_file($dbArsip, $jenisDel, $fileName, $delDbId > 0 ? $delDbId : null);
                }
                unset($arsipMetaMap[$canonicalKey], $arsipMetaMap[$metaKeyRaw]);
                if (!is_dir(dirname($arsipMetaFile))) {
                    @mkdir(dirname($arsipMetaFile), 0777, true);
                }
                @file_put_contents($arsipMetaFile, json_encode($arsipMetaMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $arsipInvalidateChartCache();
                $_SESSION['flash_message'] = 'Arsip surat berhasil dihapus.';
                $_SESSION['flash_type'] = 'success';
                $qs = [];
                if ($returnPer !== 30) {
                    $qs['per_page'] = $returnPer;
                }
                if ($returnQ !== '') {
                    $qs['q'] = $returnQ;
                }
                if ($returnPage > 1) {
                    $qs['page'] = $returnPage;
                }
                $redir = 'arsip.php' . ($qs !== [] ? ('?' . http_build_query($qs)) : '');
                header('Location: ' . $redir, true, 303);
                exit;
            }
        }
    }
}

if ($dbArsip instanceof mysqli) {
    org_arsip_sync_meta_to_arsip_surat_table($dbArsip, $arsipMetaFile, $arsipDirMap, 350);
}

$isExportExcel = isset($_GET['export']) && (string) $_GET['export'] === 'excel';
$arsipRows = $collectArsipRows($arsipDirMap, $isExportExcel ? 0 : 1500);
$arsipDispoByNomor = [];
if ($dbArsip instanceof mysqli) {
    $tblA = $dbArsip->query("SHOW TABLES LIKE 'arsip_surat'");
    $tblD = $dbArsip->query("SHOW TABLES LIKE 'surat_disposisi'");
    $okA = $tblA !== false && $tblA->num_rows > 0;
    $okD = $tblD !== false && $tblD->num_rows > 0;
    if ($tblA) {
        $tblA->free();
    }
    if ($tblD) {
        $tblD->free();
    }
    if ($okA && $okD) {
        $qDisp = $dbArsip->query(
            "SELECT a.`nomor_surat`, COUNT(*) AS jml
             FROM `surat_disposisi` d
             INNER JOIN `arsip_surat` a ON a.`id` = d.`id_arsip`
             WHERE TRIM(COALESCE(a.`nomor_surat`, '')) <> ''
             GROUP BY a.`nomor_surat`"
        );
        if ($qDisp) {
            while ($rr = $qDisp->fetch_assoc()) {
                $nom = trim((string) ($rr['nomor_surat'] ?? ''));
                if ($nom !== '') {
                    $arsipDispoByNomor[$nom] = (int) ($rr['jml'] ?? 0);
                }
            }
            $qDisp->free();
        }
    }
}
if ($arsipSearchQuery !== '') {
    $qNeedle = function_exists('mb_strtolower') ? mb_strtolower($arsipSearchQuery, 'UTF-8') : strtolower($arsipSearchQuery);
    $arsipRows = array_values(array_filter($arsipRows, static function (array $row) use ($arsipMetaMap, $arsipDispoByNomor, $qNeedle): bool {
        $jenis = (string) ($row['jenis'] ?? '');
        $metaKey = (string) ($row['meta_key'] ?? '');
        $meta = $arsipMetaMap[$metaKey] ?? [];
        $nomor = trim((string) ($meta['nomor_surat'] ?? ''));
        $ikutMon = $jenis === 'masuk' ? org_arsip_meta_row_syncs_to_monitoring_table('masuk', $meta) : true;
        if ($jenis === 'masuk' && $ikutMon) {
            $kategoriDispo = ($nomor !== '' && isset($arsipDispoByNomor[$nomor])) ? 'sudah disposisi' : 'belum disposisi';
        } elseif ($jenis === 'masuk') {
            $kategoriDispo = 'arsip saja tanpa monitoring disposisi';
        } else {
            $kategoriDispo = '';
        }
        $jenisLabel = $jenis === 'masuk' ? 'surat masuk' : ($jenis === 'keluar' ? 'surat keluar' : $jenis);
        $kbSlug = trim((string) ($meta['kategori_bagian'] ?? ''));
        $kbLabel = org_arsip_kategori_bagian_label($meta);
        $haystack = implode(' ', [
            (string) ($row['nama_file'] ?? ''),
            $jenis,
            $jenisLabel,
            (string) ($meta['nomor_surat'] ?? ''),
            $kbSlug,
            $kbLabel,
            (string) ($meta['perihal_ringkasan'] ?? ''),
            (string) ($meta['instansi_asal'] ?? ''),
            (string) ($meta['instansi_tujuan'] ?? ''),
            $kategoriDispo,
        ]);
        $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);
        return $qNeedle === '' || str_contains($haystack, $qNeedle);
    }));
}

$arsipMonthLabels = [];
$arsipMasukSeries = [];
$arsipKeluarSeries = [];
$chartCacheHit = false;
if (!$isExportExcel && is_file($arsipChartCacheFile)) {
    $cacheRaw = @file_get_contents($arsipChartCacheFile);
    if ($cacheRaw !== false && $cacheRaw !== '') {
        $cacheDecoded = json_decode($cacheRaw, true);
        if (
            is_array($cacheDecoded)
            && isset($cacheDecoded['exp'], $cacheDecoded['labels'], $cacheDecoded['masuk'], $cacheDecoded['keluar'])
            && is_array($cacheDecoded['labels'])
            && is_array($cacheDecoded['masuk'])
            && is_array($cacheDecoded['keluar'])
            && (int) $cacheDecoded['exp'] > time()
        ) {
            $arsipMonthLabels = $cacheDecoded['labels'];
            $arsipMasukSeries = array_map(static fn($n): int => (int) $n, $cacheDecoded['masuk']);
            $arsipKeluarSeries = array_map(static fn($n): int => (int) $n, $cacheDecoded['keluar']);
            $chartCacheHit = true;
        }
    }
}
if (!$chartCacheHit) {
    $series = $arsipAggregateMonthsFromDisk($arsipDirMap);
    $arsipMonthLabels = $series['labels'];
    $arsipMasukSeries = $series['masuk'];
    $arsipKeluarSeries = $series['keluar'];
    if (!$isExportExcel) {
        $cachePayload = [
            'exp' => time() + max(60, $arsipChartCacheTtl),
            'labels' => $arsipMonthLabels,
            'masuk' => $arsipMasukSeries,
            'keluar' => $arsipKeluarSeries,
        ];
        @file_put_contents(
            $arsipChartCacheFile,
            json_encode($cachePayload, JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

if ($isExportExcel) {
    $filename = 'laporan_arsip_surat_' . date('Ymd_His') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "No\tJenis Surat\tKategori Bagian\tNomor Surat\tNama File\tPerihal/Ringkasan\tInstansi Asal\tInstansi Tujuan\tMonitoring Disposisi\tUkuran\tTanggal Upload\n";
    $no = 1;
    foreach ($arsipRows as $row) {
        $jenis = (string) ($row['jenis'] ?? '');
        $jenisLabel = $jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar';
        $namaFile = (string) ($row['nama_file'] ?? '-');
        $metaKey = (string) ($row['meta_key'] ?? '');
        $metaRow = $arsipMetaMap[$metaKey] ?? [];
        $nomorSuratExcel = trim((string) ($metaRow['nomor_surat'] ?? '-'));
        $kategoriExcel = org_arsip_kategori_bagian_label($metaRow);
        $perihalRingkasan = trim((string) ($metaRow['perihal_ringkasan'] ?? '-'));
        $instansiAsal = trim((string) ($metaRow['instansi_asal'] ?? '-'));
        $instansiTujuan = trim((string) ($metaRow['instansi_tujuan'] ?? '-'));
        $ukuranBytes = (int) ($row['ukuran_bytes'] ?? 0);
        $ukuranLabel = $ukuranBytes > 0 ? number_format($ukuranBytes / 1024, 2, ',', '.') . ' KB' : '0 KB';
        $tglUpload = (int) ($row['tgl_upload'] ?? 0);
        $tglLabel = $tglUpload > 0 ? date('d-m-Y H:i:s', $tglUpload) : '-';
        $monLabel = '-';
        if ($jenis === 'masuk') {
            $monLabel = org_arsip_meta_row_syncs_to_monitoring_table('masuk', $metaRow) ? 'Ya' : 'Tidak';
        }

        $cells = [$no, $jenisLabel, $kategoriExcel, $nomorSuratExcel, $namaFile, $perihalRingkasan, $instansiAsal, $instansiTujuan, $monLabel, $ukuranLabel, $tglLabel];
        $safeCells = array_map(static function ($cell): string {
            $v = str_replace(["\t", "\r", "\n"], ' ', (string) $cell);
            return $v === '' ? '-' : $v;
        }, $cells);
        echo implode("\t", $safeCells) . "\n";
        $no++;
    }
    exit;
}

$extraHeadMarkup = $extraHeadMarkup ?? '';
$extraFooterMarkup = $extraFooterMarkup ?? '';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup, true);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
$arsipTotalRows = count($arsipRows);
$arsipTotalPages = max(1, (int) ceil($arsipTotalRows / $arsipPerPage));
$arsipCurrentPage = min($arsipCurrentPage, $arsipTotalPages);
$arsipOffset = ($arsipCurrentPage - 1) * $arsipPerPage;
$arsipRowsPage = array_slice($arsipRows, $arsipOffset, $arsipPerPage);
$arsipSearchQs = $arsipSearchQuery !== '' ? '&amp;q=' . rawurlencode($arsipSearchQuery) : '';
$arsipCountMasuk = 0;
$arsipCountKeluar = 0;
$arsipCountSudahDispo = 0;
$arsipCountBelumDispo = 0;
$arsipCountArsipSaja = 0;
foreach ($arsipRows as $rrCount) {
    $j = (string) ($rrCount['jenis'] ?? '');
    if ($j === 'masuk') {
        $arsipCountMasuk++;
        $mk = (string) ($rrCount['meta_key'] ?? '');
        $mr = $arsipMetaMap[$mk] ?? [];
        if (!org_arsip_meta_row_syncs_to_monitoring_table('masuk', $mr)) {
            $arsipCountArsipSaja++;
        } else {
            $nom = trim((string) ($mr['nomor_surat'] ?? ''));
            if ($nom !== '' && isset($arsipDispoByNomor[$nom])) {
                $arsipCountSudahDispo++;
            } else {
                $arsipCountBelumDispo++;
            }
        }
    } elseif ($j === 'keluar') {
        $arsipCountKeluar++;
    }
}
?>
    <div class="container site-main">
        <section class="section-spacing">
            <div class="arsip-hero p-4 p-lg-5 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div class="arsip-hero__heading">
                        <h1 class="arsip-hero__title mb-0">Dashboard Arsip Surat Digital</h1>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="arsip-pill"><i class="fa-solid fa-building-shield"></i> Dokumentasi Resmi</span>
                        <span class="arsip-pill"><i class="fa-solid fa-file-excel"></i> Laporan Siap Unduh</span>
                    </div>
                </div>
            </div>
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="arsip-workspace">
                <aside class="arsip-workspace__form">
                    <div class="card arsip-card arsip-form-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white flex-shrink-0" style="width:2.5rem;height:2.5rem;background:linear-gradient(135deg,#1d4ed8,#0ea5e9);">
                                    <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <h2 class="h5 arsip-title mb-1">Input Arsip Surat</h2>
                                    <p class="text-muted small mb-0">Unggah PDF dan metadata surat. Surat masuk dapat dialurkan ke disposisi.</p>
                                </div>
                            </div>

                            <form method="post" enctype="multipart/form-data" id="form-upload-arsip">
                                <input type="hidden" name="action" value="upload_arsip">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mb-3">
                                    <label for="jenis_surat" class="form-label">Jenis surat</label>
                                    <select class="form-select" id="jenis_surat" name="jenis_surat" required>
                                        <option value="">-- Pilih jenis surat --</option>
                                        <option value="masuk" <?php echo $submitted['jenis_surat'] === 'masuk' ? 'selected' : ''; ?>>Surat Masuk</option>
                                        <option value="keluar" <?php echo $submitted['jenis_surat'] === 'keluar' ? 'selected' : ''; ?>>Surat Keluar</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="kategori_bagian" class="form-label">Kategori bagian</label>
                                    <select class="form-select" id="kategori_bagian" name="kategori_bagian" required>
                                        <option value="">-- Pilih kategori --</option>
                                        <?php foreach (org_arsip_kategori_bagian_map() as $kbVal => $kbLabel): ?>
                                            <option value="<?php echo htmlspecialchars($kbVal, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $submitted['kategori_bagian'] === $kbVal ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kbLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nomor_surat" class="form-label">Nomor Surat</label>
                                    <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" value="<?php echo htmlspecialchars($submitted['nomor_surat'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: 090/BAGORG/ORG/III/2026" maxlength="191" autocomplete="off" required>
                                </div>
                                <div class="mb-3">
                                    <label for="instansi_asal" class="form-label">Instansi Asal Pengirim Surat</label>
                                    <input type="text" class="form-control" id="instansi_asal" name="instansi_asal" value="<?php echo htmlspecialchars($submitted['instansi_asal'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Bappeda Kabupaten Kepulauan Aru" required>
                                </div>
                                <div class="mb-3">
                                    <label for="instansi_tujuan" class="form-label">Instansi Tujuan Kirim Surat</label>
                                    <input type="text" class="form-control" id="instansi_tujuan" name="instansi_tujuan" value="<?php echo htmlspecialchars($submitted['instansi_tujuan'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Bagian Organisasi Setda Kab. Kepulauan Aru" required>
                                </div>
                                <div class="mb-3">
                                    <label for="perihal_ringkasan" class="form-label">Perihal / Ringkasan Isi Surat</label>
                                    <textarea class="form-control" id="perihal_ringkasan" name="perihal_ringkasan" rows="3" placeholder="Contoh: Permohonan data kelembagaan untuk evaluasi triwulan II" required><?php echo htmlspecialchars($submitted['perihal_ringkasan'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="mb-3" id="arsip-monitoring-dispo-wrap" style="display:none;">
                                    <div class="form-check rounded border border-primary border-opacity-25 bg-primary bg-opacity-10 px-3 py-2">
                                        <input class="form-check-input" type="checkbox" name="ikut_monitoring_disposisi" value="1" id="ikut_monitoring_disposisi" <?php echo !empty($submitted['ikut_monitoring_disposisi']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="ikut_monitoring_disposisi">
                                            <?php if ($arsipSessionRole === 'sub_admin_eorganisasi'): ?>
                                            Surat ini akan didisposisikan (muncul di menu <strong>Disposisi awal &amp; tanda terima Kabag</strong> di E-Organisasi)
                                            <?php else: ?>
                                            Surat ini akan didisposisikan (muncul di menu <strong>Monitoring Disposisi → Surat Masuk</strong>)
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <p class="small text-muted mt-2 mb-0"><?php echo $arsipSessionRole === 'sub_admin_eorganisasi'
                                        ? 'Input instruksi disposisi dilakukan di halaman Disposisi awal &amp; tanda terima Kabag; berkas PDF tetap mengikuti arsip surat masuk di folder <code>uploads/surat_masuk/</code>.'
                                        : 'Input instruksi disposisi dilakukan nanti di Monitoring; berkas PDF tetap mengikuti arsip surat masuk di folder <code>uploads/surat_masuk/</code>.'; ?> Kosongkan centang jika surat hanya untuk arsip tanpa alur disposisi.</p>
                                </div>
                                <div class="mb-3">
                                    <label for="arsip_pdf" class="form-label">Berkas PDF</label>
                                    <div class="arsip-file-field">
                                        <input type="file" class="form-control" id="arsip_pdf" name="arsip_pdf" accept=".pdf,application/pdf" required>
                                        <p class="small text-muted mb-0 mt-2">Format PDF, maks. <?php echo htmlspecialchars(org_format_file_size((int) ORG_ARSIP_MAX_UPLOAD_BYTES), ENT_QUOTES, 'UTF-8'); ?>.</p>
                                    </div>
                                </div>
                                <button type="submit" class="btn arsip-btn-save">
                                    <i class="fa-solid fa-upload me-1" aria-hidden="true"></i>Upload Arsip
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>
                <section class="arsip-workspace__data">
                    <div class="card arsip-card arsip-data-card h-100">
                        <div class="card-body p-4">
                            <div class="arsip-data-toolbar">
                                <h2 class="h5 arsip-title mb-0">Data Arsip Terbaru</h2>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="arsip.php?export=excel<?php echo $arsipSearchQs; ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i>Download Excel
                                    </a>
                                    <a href="export_excel.php?type=surat" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-file-export me-1" aria-hidden="true"></i>Export Laporan
                                    </a>
                                </div>
                            </div>
                            <div class="arsip-chart-box">
                                <canvas id="arsipMonthlyChart" aria-label="Grafik surat masuk dan keluar per bulan"></canvas>
                            </div>
                            <div class="arsip-list-toolbar">
                                <form method="get" class="arsip-data-search-form" role="search">
                                    <input type="hidden" name="per_page" value="<?php echo (int) $arsipPerPage; ?>">
                                    <div class="arsip-data-search__wrap">
                                        <div class="arsip-data-search__field">
                                            <i class="fa-solid fa-magnifying-glass arsip-data-search__icon" aria-hidden="true"></i>
                                            <input type="search" id="arsipSearchQ" name="q" class="arsip-data-search__input" placeholder="Cari nomor, file, kategori, perihal, atau instansi…" value="<?php echo htmlspecialchars($arsipSearchQuery, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                                        </div>
                                        <button type="submit" class="btn btn-primary arsip-data-search__btn">Cari</button>
                                        <?php if ($arsipSearchQuery !== ''): ?>
                                            <a href="arsip.php?per_page=<?php echo (int) $arsipPerPage; ?>" class="btn btn-outline-secondary arsip-data-search__reset">Reset</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                                <div class="arsip-list-toolbar__pages">
                                    <label for="arsipPerPageSelect" class="small text-muted mb-0">Tampil</label>
                                    <select id="arsipPerPageSelect" class="form-select form-select-sm" aria-label="Jumlah baris per halaman">
                                        <?php foreach ($arsipAllowedPerPage as $pp): ?>
                                            <option value="<?php echo (int) $pp; ?>" <?php echo $pp === $arsipPerPage ? 'selected' : ''; ?>>
                                                <?php echo (int) $pp; ?>/hal
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (count($arsipRows) > 0): ?>
                                <div class="library-doc-category-filter mb-3" role="tablist" aria-label="Filter kategori arsip surat">
                                    <button type="button" class="library-doc-category-filter__btn is-active" data-arsip-cat="semua">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Semua Arsip</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipTotalRows; ?> data</span>
                                        </span>
                                    </button>
                                    <button type="button" class="library-doc-category-filter__btn" data-arsip-cat="masuk">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Surat Masuk</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipCountMasuk; ?> data</span>
                                        </span>
                                    </button>
                                    <button type="button" class="library-doc-category-filter__btn" data-arsip-cat="keluar">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Surat Keluar</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipCountKeluar; ?> data</span>
                                        </span>
                                    </button>
                                    <button type="button" class="library-doc-category-filter__btn" data-arsip-cat="sudah_disposisi">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Sudah Disposisi</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipCountSudahDispo; ?> data</span>
                                        </span>
                                    </button>
                                    <button type="button" class="library-doc-category-filter__btn" data-arsip-cat="belum_disposisi">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Belum Disposisi</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipCountBelumDispo; ?> data</span>
                                        </span>
                                    </button>
                                    <button type="button" class="library-doc-category-filter__btn" data-arsip-cat="arsip_saja">
                                        <span class="library-doc-category-filter__text-wrap">
                                            <span class="library-doc-category-filter__label">Arsip saja</span>
                                            <span class="library-doc-category-filter__count"><?php echo (int) $arsipCountArsipSaja; ?> data</span>
                                        </span>
                                    </button>
                                </div>
                                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                                    <label for="arsipBagianFilter" class="small text-muted mb-0">Kategori bagian</label>
                                    <select id="arsipBagianFilter" class="form-select form-select-sm" style="max-width: min(100%, 340px);" aria-label="Filter kategori bagian pada tabel">
                                        <option value="">Semua kategori</option>
                                        <option value="__none__">Belum diklasifikasi</option>
                                        <?php foreach (org_arsip_kategori_bagian_map() as $kbVal => $kbLabel): ?>
                                            <option value="<?php echo htmlspecialchars($kbVal, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($kbLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">
                                        Menampilkan <?php echo (int) count($arsipRowsPage); ?> dari total <?php echo (int) $arsipTotalRows; ?> arsip<?php echo $arsipSearchQuery !== '' ? ' (hasil pencarian)' : ''; ?>.
                                    </span>
                                    <span class="text-muted small">Halaman <?php echo (int) $arsipCurrentPage; ?> / <?php echo (int) $arsipTotalPages; ?></span>
                                </div>
                                <div class="digital-library__table-wrap table-responsive">
                                    <table class="table digital-library__table table-sm table-hover align-middle mb-0" id="arsipDataTable">
                                        <colgroup>
                                            <col style="width: 7%;">
                                            <col style="width: 10%;">
                                            <col style="width: 9%;">
                                            <col style="width: 9%;">
                                            <col style="width: 18%;">
                                            <col style="width: 10%;">
                                            <col style="width: 10%;">
                                            <col style="width: 9%;">
                                            <col style="width: 8%;">
                                            <col style="width: 10%;">
                                        </colgroup>
                                        <thead class="table-light">
                                            <tr>
                                                <th title="Jenis surat">Jenis</th>
                                                <th title="Kategori bagian organisasi">Kategori</th>
                                                <th title="Nomor surat">Nomor</th>
                                                <th title="Nama berkas PDF">Berkas</th>
                                                <th title="Perihal / ringkasan">Perihal</th>
                                                <th title="Instansi asal">Asal</th>
                                                <th title="Instansi tujuan">Tujuan</th>
                                                <th title="Status monitoring / disposisi (surat masuk)">Disposisi</th>
                                                <th title="Tanggal unggah">Tgl.</th>
                                                <th class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($arsipRowsPage as $row): ?>
                                                <?php
                                                $jenis = (string) ($row['jenis'] ?? '');
                                                $jenisLabel = $jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar';
                                                $metaKey = (string) ($row['meta_key'] ?? '');
                                                $metaRow = $arsipMetaMap[$metaKey] ?? [];
                                                $nomorSuratRow = trim((string) ($metaRow['nomor_surat'] ?? ''));
                                                $perihalRingkasanRow = (string) ($metaRow['perihal_ringkasan'] ?? '-');
                                                $instansiAsalRow = (string) ($metaRow['instansi_asal'] ?? '-');
                                                $instansiTujuanRow = (string) ($metaRow['instansi_tujuan'] ?? '-');
                                                $nomorSuratNorm = trim((string) ($metaRow['nomor_surat'] ?? ''));
                                                $ikutMonRow = $jenis === 'masuk' ? org_arsip_meta_row_syncs_to_monitoring_table('masuk', $metaRow) : true;
                                                if ($jenis === 'masuk' && !$ikutMonRow) {
                                                    $kategoriDispoRow = 'Arsip saja';
                                                    $catFilter = 'arsip_saja';
                                                } elseif ($jenis === 'masuk') {
                                                    $isSudahDispo = $nomorSuratNorm !== '' && isset($arsipDispoByNomor[$nomorSuratNorm]);
                                                    $kategoriDispoRow = $isSudahDispo ? 'Sudah Disposisi' : 'Belum Disposisi';
                                                    $catFilter = $isSudahDispo ? 'sudah_disposisi' : 'belum_disposisi';
                                                } else {
                                                    $kategoriDispoRow = '-';
                                                    $catFilter = $jenis;
                                                }
                                                $kbSlugRow = trim((string) ($metaRow['kategori_bagian'] ?? ''));
                                                $kbLabelRow = org_arsip_kategori_bagian_label($metaRow);
                                                $tgl = (int) ($row['tgl_upload'] ?? 0);
                                                $tglDateStr = $tgl > 0 ? date('d-m-Y', $tgl) : '-';
                                                $tglTimeStr = $tgl > 0 ? date('H:i', $tgl) : '';
                                                $arsipJenis = $jenis === 'masuk' ? 'masuk' : 'keluar';
                                                $arsipFile = (string) ($row['nama_file'] ?? '');
                                                $docPath = function_exists('org_page_url')
                                                    ? org_page_url('download_arsip.php') . '?jenis=' . rawurlencode($arsipJenis) . '&file=' . rawurlencode($arsipFile)
                                                    : 'download_arsip.php?jenis=' . rawurlencode($arsipJenis) . '&file=' . rawurlencode($arsipFile);
                                                ?>
                                                <tr data-jenis="<?php echo htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8'); ?>" data-arsip-cat="<?php echo htmlspecialchars($catFilter, ENT_QUOTES, 'UTF-8'); ?>" data-arsip-bagian="<?php echo htmlspecialchars($kbSlugRow, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td class="arsip-tbl__cell--jenis"><?php echo htmlspecialchars($jenisLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break small arsip-tbl__cell--kat" title="<?php echo htmlspecialchars($kbLabelRow, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($kbLabelRow, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break small"><?php echo htmlspecialchars($nomorSuratRow !== '' ? $nomorSuratRow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break small"><?php echo htmlspecialchars((string) ($row['nama_file'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break"><?php echo htmlspecialchars($perihalRingkasanRow !== '' ? $perihalRingkasanRow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break small"><?php echo htmlspecialchars($instansiAsalRow !== '' ? $instansiAsalRow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-break small"><?php echo htmlspecialchars($instansiTujuanRow !== '' ? $instansiTujuanRow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="arsip-tbl__cell--dispo"><?php echo htmlspecialchars($kategoriDispoRow, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="arsip-tbl__cell--tgl small"><?php echo htmlspecialchars($tglDateStr, ENT_QUOTES, 'UTF-8'); ?><?php echo $tglTimeStr !== '' ? '<br>' . htmlspecialchars($tglTimeStr, ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td class="text-end digital-library__actions text-nowrap">
                                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($docPath, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" title="Lihat berkas" aria-label="Lihat berkas PDF di tab baru">
                                                            <i class="fas fa-eye" aria-hidden="true"></i><span class="visually-hidden"> Lihat</span>
                                                        </a>
                                                        <a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars($docPath, ENT_QUOTES, 'UTF-8'); ?>" download title="Unduh berkas" aria-label="Unduh berkas PDF">
                                                            <i class="fa-solid fa-download" aria-hidden="true"></i><span class="visually-hidden"> Unduh</span>
                                                        </a>
                                                        <?php if ($canArsipAdminDelete): ?>
                                                            <form method="post" class="d-inline" onsubmit="return confirm('Hapus arsip ini beserta metadata di JSON? File PDF akan dihapus dari server. Tindakan ini tidak dapat dibatalkan.');">
                                                                <input type="hidden" name="action" value="hapus_arsip_json">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                                <input type="hidden" name="meta_key" value="<?php echo htmlspecialchars($metaKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <input type="hidden" name="return_page" value="<?php echo (int) $arsipCurrentPage; ?>">
                                                                <input type="hidden" name="return_per_page" value="<?php echo (int) $arsipPerPage; ?>">
                                                                <input type="hidden" name="return_q" value="<?php echo htmlspecialchars($arsipSearchQuery, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Hapus arsip" aria-label="Hapus arsip">
                                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i><span class="visually-hidden"> Hapus</span>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if ($arsipTotalPages > 1): ?>
                                    <nav class="mt-3" aria-label="Navigasi halaman arsip">
                                        <ul class="pagination pagination-sm mb-0 justify-content-end flex-wrap">
                                            <?php
                                            $arsipPrevPage = max(1, $arsipCurrentPage - 1);
                                            $arsipNextPage = min($arsipTotalPages, $arsipCurrentPage + 1);
                                            ?>
                                            <li class="page-item <?php echo $arsipCurrentPage <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="arsip.php?page=<?php echo (int) $arsipPrevPage; ?>&amp;per_page=<?php echo (int) $arsipPerPage; ?><?php echo $arsipSearchQs; ?>" aria-label="Sebelumnya">«</a>
                                            </li>
                                            <?php for ($p = 1; $p <= $arsipTotalPages; $p++): ?>
                                                <?php if ($p === 1 || $p === $arsipTotalPages || abs($p - $arsipCurrentPage) <= 1): ?>
                                                    <li class="page-item <?php echo $p === $arsipCurrentPage ? 'active' : ''; ?>">
                                                        <a class="page-link" href="arsip.php?page=<?php echo (int) $p; ?>&amp;per_page=<?php echo (int) $arsipPerPage; ?><?php echo $arsipSearchQs; ?>"><?php echo (int) $p; ?></a>
                                                    </li>
                                                <?php elseif ($p === 2 && $arsipCurrentPage > 4): ?>
                                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                                <?php elseif ($p === $arsipTotalPages - 1 && $arsipCurrentPage < $arsipTotalPages - 3): ?>
                                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $arsipCurrentPage >= $arsipTotalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="arsip.php?page=<?php echo (int) $arsipNextPage; ?>&amp;per_page=<?php echo (int) $arsipPerPage; ?><?php echo $arsipSearchQs; ?>" aria-label="Berikutnya">»</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Belum ada arsip surat yang tersimpan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const labels = <?php echo json_encode($arsipMonthLabels); ?>;
            const masuk = <?php echo json_encode($arsipMasukSeries); ?>;
            const keluar = <?php echo json_encode($arsipKeluarSeries); ?>;
            const ctx = document.getElementById('arsipMonthlyChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Surat Masuk', data: masuk, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.15)', tension: 0.3, fill: true },
                            { label: 'Surat Keluar', data: keluar, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.12)', tension: 0.3, fill: true }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
            var perPageEl = document.getElementById('arsipPerPageSelect');
            if (perPageEl) {
                perPageEl.addEventListener('change', function () {
                    var val = parseInt(perPageEl.value || '30', 10);
                    var params = new URLSearchParams(window.location.search);
                    params.set('per_page', String(isFinite(val) ? val : 30));
                    params.set('page', '1');
                    window.location.search = params.toString();
                });
            }
            var catBtns = document.querySelectorAll('[data-arsip-cat]');
            var bagianSel = document.getElementById('arsipBagianFilter');
            function arsipApplyTableFilters() {
                var activeCatBtn = document.querySelector('[data-arsip-cat].is-active');
                var cat = activeCatBtn ? String(activeCatBtn.getAttribute('data-arsip-cat') || 'semua') : 'semua';
                var bagian = bagianSel ? String(bagianSel.value || '') : '';
                document.querySelectorAll('#arsipDataTable tbody tr[data-arsip-cat]').forEach(function (tr) {
                    var rowCat = String(tr.getAttribute('data-arsip-cat') || '');
                    var rowBag = String(tr.getAttribute('data-arsip-bagian') || '');
                    var okCat = (cat === 'semua' || rowCat === cat);
                    var okBag = true;
                    if (bagian === '__none__') {
                        okBag = (rowBag === '');
                    } else if (bagian !== '') {
                        okBag = (rowBag === bagian);
                    }
                    tr.style.display = (okCat && okBag) ? '' : 'none';
                });
            }
            if (catBtns.length > 0) {
                catBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        catBtns.forEach(function (x) { x.classList.remove('is-active'); });
                        btn.classList.add('is-active');
                        arsipApplyTableFilters();
                    });
                });
            }
            if (bagianSel) {
                bagianSel.addEventListener('change', arsipApplyTableFilters);
            }
            var jenisArsipSel = document.getElementById('jenis_surat');
            var arsipMonWrap = document.getElementById('arsip-monitoring-dispo-wrap');
            var arsipMonChk = document.getElementById('ikut_monitoring_disposisi');
            var arsipPrevJenis = jenisArsipSel ? String(jenisArsipSel.value || '') : '';
            function arsipSyncMonitoringCheckbox() {
                if (!jenisArsipSel || !arsipMonWrap) {
                    return;
                }
                var v = String(jenisArsipSel.value || '');
                var isMasuk = v === 'masuk';
                arsipMonWrap.style.display = isMasuk ? '' : 'none';
                if (!isMasuk && arsipMonChk) {
                    arsipMonChk.checked = false;
                } else if (isMasuk && arsipPrevJenis === 'keluar' && arsipMonChk) {
                    arsipMonChk.checked = true;
                }
                arsipPrevJenis = v;
            }
            if (jenisArsipSel) {
                jenisArsipSel.addEventListener('change', arsipSyncMonitoringCheckbox);
                arsipSyncMonitoringCheckbox();
            }
        }());
    </script>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
