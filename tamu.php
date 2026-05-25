<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);

$pageTitle = 'Buku Tamu — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-tamu-dashboard mode-eorganisasi';
$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .page-tamu-dashboard {
        font-family: 'Poppins', sans-serif;
        background:
            radial-gradient(900px 380px at 8% -8%, rgba(37, 99, 235, 0.16), rgba(37, 99, 235, 0)),
            radial-gradient(880px 360px at 98% -4%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0)),
            #f3f7fd;
    }
    .page-tamu-dashboard .site-main {
        width: 100%;
        max-width: none;
        padding-left: clamp(12px, 2.2vw, 28px);
        padding-right: clamp(12px, 2.2vw, 28px);
    }
    .page-tamu-dashboard .tamu-card {
        border-radius: 15px;
        border: 0;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.11);
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }
    .page-tamu-dashboard .tamu-hero {
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
    .page-tamu-dashboard .tamu-hero::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: linear-gradient(90deg, #1d4ed8 0%, #0ea5e9 55%, #22c55e 100%);
        opacity: 0.92;
    }
    .page-tamu-dashboard .tamu-hero__title {
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
    .page-tamu-dashboard .tamu-hero__title::after {
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
    .page-tamu-dashboard .tamu-hero__heading {
        text-align: center;
        width: 100%;
    }
    .page-tamu-dashboard .tamu-hero__subtitle {
        color: #4b607a;
        margin-bottom: 0;
    }
    .page-tamu-dashboard .tamu-pill {
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
    .page-tamu-dashboard .tamu-btn-save {
        border: 0;
        color: #fff;
        font-weight: 600;
        background-image: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.32);
    }
    .page-tamu-dashboard .tamu-btn-save:hover {
        color: #fff;
        filter: brightness(1.04);
    }
    .page-tamu-dashboard .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding-top: 4px;
    }
    .page-tamu-dashboard .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .page-tamu-dashboard .tamu-title {
        color: #0f2748;
        font-weight: 700;
    }
    .page-tamu-dashboard .card-body > .tamu-title + .text-muted.small {
        margin-bottom: 1rem !important;
    }
    .page-tamu-dashboard .form-label {
        font-weight: 600;
        color: #1f3b63;
        margin-bottom: 0.4rem;
    }
    .page-tamu-dashboard .form-control,
    .page-tamu-dashboard .form-select {
        border: 1px solid #d5e1f0;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .page-tamu-dashboard .form-control:focus,
    .page-tamu-dashboard .form-select:focus {
        border-color: #8cb5ea;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        background: #fff;
    }
    .page-tamu-dashboard .tamu-toolbar {
        padding: 0.7rem 0.8rem;
        border: 1px solid #e2ebf7;
        border-radius: 12px;
        background: #f7faff;
    }
    .page-tamu-dashboard .tamu-filter-wrap {
        padding: 0.75rem;
        border: 1px solid #e4edf8;
        border-radius: 12px;
        background: #fbfdff;
    }
    .page-tamu-dashboard #tamuDataTable {
        min-width: 840px;
    }
    .page-tamu-dashboard #tamuDataTable thead th {
        background: #eff5fc;
        color: #1b3f6f;
        font-size: 0.79rem;
        font-weight: 700;
        border-bottom: 1px solid #dce7f5;
        white-space: nowrap;
    }
    .page-tamu-dashboard #tamuDataTable tbody td {
        border-color: #edf2f9;
    }
    .page-tamu-dashboard #tamuDataTable tbody tr:hover {
        background: #f8fbff;
    }
    .page-tamu-dashboard .btn-outline-success {
        border-color: #1f9d63;
        color: #1f9d63;
    }
    .page-tamu-dashboard .btn-outline-success:hover {
        background: #1f9d63;
        border-color: #1f9d63;
        color: #fff;
    }
    .page-tamu-dashboard .btn-success {
        background-image: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        border-color: #15803d;
    }
    .page-tamu-dashboard .btn-success:hover {
        filter: brightness(1.03);
    }
    @media (max-width: 767.98px) {
        .page-tamu-dashboard .tamu-hero {
            border-radius: 16px;
        }
        .page-tamu-dashboard .tamu-toolbar,
        .page-tamu-dashboard .tamu-filter-wrap {
            padding: 0.65rem;
        }
    }
    .page-tamu-dashboard .table > :not(caption) > * > * {
        vertical-align: middle;
    }
</style>
HTML;

$message = '';
$messageType = 'info';
$guestRows = [];
$tujuanOptions = [
    'Kepala Bagian Organisasi',
    'Tim Kelembagaan dan Anjab',
    'Kinerja dan RB',
    'Pelayanan Publik dan Tata Laksana',
    'Memberikan/Mengantar Surat Masuk',
    'Kepegawaian',
    'Keuangan',
];
$personnelOptions = [];
$idField = '';
$editMode = false;
$submitted = [
    'nama' => '',
    'no_hp' => '',
    'instansi' => '',
    'tujuan_bertamu' => '',
    'nama_personel' => '',
    'keperluan' => '',
];
$perPage = 25;
$allowedPerPage = [10, 25, 50, 100];
$requestedPerPage = (int) ($_GET['per_page'] ?? $perPage);
if (in_array($requestedPerPage, $allowedPerPage, true)) {
    $perPage = $requestedPerPage;
}
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalRows = 0;
$totalPages = 1;

$db = org_db();
if (!($db instanceof mysqli)) {
    $message = 'Koneksi database tidak tersedia.';
    $messageType = 'danger';
} else {
    $tableCheck = $db->query("SHOW TABLES LIKE 'tamu'");
    $tableExists = $tableCheck !== false && $tableCheck->num_rows > 0;
    if (!$tableExists) {
        $message = "Tabel 'tamu' belum ditemukan pada database.";
        $messageType = 'warning';
    } else {
        $columns = [];
        $colRes = $db->query("SHOW COLUMNS FROM tamu");
        if ($colRes !== false) {
            while ($col = $colRes->fetch_assoc()) {
                $field = (string) ($col['Field'] ?? '');
                if ($field !== '') {
                    $columns[$field] = $col;
                }
            }
        }
        $idField = isset($columns['id']) ? 'id' : (isset($columns['tamu_id']) ? 'tamu_id' : '');

        /* Sumber utama nama personel: personnel.json (selaras dengan halaman
           Profil → Struktur & Personel). Tabel MySQL `personnel`/`personel`
           bersifat opsional (mirror untuk migrasi) sehingga dianggap sebagai
           pelengkap saja. Pendekatan ini memastikan dropdown selalu terisi
           bila admin mengelola personel dari halaman Profil. */
        if (isset($personnelData) && is_array($personnelData)) {
            foreach ($personnelData as $personItem) {
                if (!is_array($personItem)) {
                    continue;
                }
                $personName = trim((string) ($personItem['name'] ?? $personItem['nama'] ?? ''));
                if ($personName !== '') {
                    $personnelOptions[] = $personName;
                }
            }
        }

        /* Tambahan opsional: gabungkan personel dari tabel MySQL jika ada
           (mirror data lama / migrasi). Nama yang sudah ada di JSON tidak
           akan diduplikasi. */
        $personnelTable = '';
        foreach (['personnel', 'personel'] as $candidateTable) {
            $personnelTableCheck = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($candidateTable) . "'");
            if ($personnelTableCheck !== false && $personnelTableCheck->num_rows > 0) {
                $personnelTable = $candidateTable;
                break;
            }
        }
        if ($personnelTable !== '') {
            $orderField = '`name`';
            $personnelColRes = $db->query('SHOW COLUMNS FROM `' . $personnelTable . '`');
            $personnelCols = [];
            if ($personnelColRes !== false) {
                while ($pc = $personnelColRes->fetch_assoc()) {
                    $field = (string) ($pc['Field'] ?? '');
                    if ($field !== '') {
                        $personnelCols[$field] = true;
                    }
                }
            }
            if (!isset($personnelCols['name']) && isset($personnelCols['nama'])) {
                $orderField = '`nama`';
            }
            $personnelRes = $db->query('SELECT * FROM `' . $personnelTable . '` ORDER BY ' . $orderField . ' ASC');
            if ($personnelRes !== false) {
                while ($prow = $personnelRes->fetch_assoc()) {
                    $personName = trim((string) ($prow['name'] ?? $prow['nama'] ?? ''));
                    if ($personName !== '') {
                        $personnelOptions[] = $personName;
                    }
                }
            }
        }

        /* Deduplikasi (case-insensitive untuk hindari 'Ali' vs 'ali')
           lalu urutkan alfabet sesuai locale Indonesia. */
        $seenLowercase = [];
        $dedup = [];
        foreach ($personnelOptions as $optName) {
            $key = mb_strtolower(trim($optName), 'UTF-8');
            if ($key === '' || isset($seenLowercase[$key])) {
                continue;
            }
            $seenLowercase[$key] = true;
            $dedup[] = $optName;
        }
        usort($dedup, static function (string $a, string $b): int {
            return strnatcasecmp($a, $b);
        });
        $personnelOptions = $dedup;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'delete_tamu') {
            $rowId = trim((string) ($_POST['row_id'] ?? ''));
            if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
                $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
                $messageType = 'danger';
            } elseif ($idField === '' || $rowId === '') {
                $message = 'Hapus gagal: ID data tidak tersedia.';
                $messageType = 'warning';
            } else {
                $stmtDelete = $db->prepare("DELETE FROM `tamu` WHERE `$idField` = ? LIMIT 1");
                if ($stmtDelete !== false) {
                    $stmtDelete->bind_param('s', $rowId);
                    if ($stmtDelete->execute()) {
                        $message = 'Data tamu berhasil dihapus.';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menghapus data tamu.';
                        $messageType = 'danger';
                    }
                    $stmtDelete->close();
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'update_tamu') {
            $rowId = trim((string) ($_POST['row_id'] ?? ''));
            $formData = [
                'nama' => trim((string) ($_POST['nama'] ?? '')),
                'no_hp' => trim((string) ($_POST['no_hp'] ?? '')),
                'instansi' => trim((string) ($_POST['instansi'] ?? '')),
                'tujuan_bertamu' => trim((string) ($_POST['tujuan_bertamu'] ?? '')),
                'nama_personel' => trim((string) ($_POST['nama_personel'] ?? '')),
                'keperluan' => trim((string) ($_POST['keperluan'] ?? '')),
            ];
            $submitted = $formData;
            if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
                $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
                $messageType = 'danger';
            } elseif ($idField === '' || $rowId === '') {
                $message = 'Update gagal: ID data tidak tersedia.';
                $messageType = 'warning';
            } elseif ($formData['nama'] === '' || $formData['tujuan_bertamu'] === '' || $formData['nama_personel'] === '' || $formData['keperluan'] === '') {
                $message = 'Nama, tujuan bertamu, nama personel, dan keperluan wajib diisi.';
                $messageType = 'warning';
            } else {
                $fieldMap = [
                    'nama' => $formData['nama'],
                    'nama_tamu' => $formData['nama'],
                    'no_hp' => $formData['no_hp'],
                    'no_telp' => $formData['no_hp'],
                    'telepon' => $formData['no_hp'],
                    'instansi' => $formData['instansi'],
                    'asal_instansi' => $formData['instansi'],
                    'tujuan_bertamu' => $formData['tujuan_bertamu'],
                    'tujuan' => $formData['tujuan_bertamu'],
                    'bidang_tujuan' => $formData['tujuan_bertamu'],
                    'unit_tujuan' => $formData['tujuan_bertamu'],
                    'nama_personel' => $formData['nama_personel'],
                    'personel' => $formData['nama_personel'],
                    'personnel' => $formData['nama_personel'],
                    'petugas' => $formData['nama_personel'],
                    'keperluan' => $formData['keperluan'],
                    'pesan' => $formData['keperluan'],
                ];
                $setParts = [];
                $vals = [];
                foreach ($fieldMap as $f => $v) {
                    if (isset($columns[$f])) {
                        $setParts[] = "`$f` = ?";
                        $vals[] = $v;
                    }
                }
                if (count($setParts) > 0) {
                    $vals[] = $rowId;
                    $sqlUp = "UPDATE `tamu` SET " . implode(', ', $setParts) . " WHERE `$idField` = ? LIMIT 1";
                    $stUp = $db->prepare($sqlUp);
                    if ($stUp !== false) {
                        $types = str_repeat('s', count($vals));
                        $refs = [];
                        $refs[] = &$types;
                        foreach ($vals as $i => $v) {
                            $refs[] = &$vals[$i];
                        }
                        call_user_func_array([$stUp, 'bind_param'], $refs);
                        if ($stUp->execute()) {
                            $message = 'Data tamu berhasil diperbarui.';
                            $messageType = 'success';
                        } else {
                            $message = 'Gagal memperbarui data tamu.';
                            $messageType = 'danger';
                        }
                        $stUp->close();
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'save_tamu') {
            $formData = [
                'nama' => trim((string) ($_POST['nama'] ?? '')),
                'no_hp' => trim((string) ($_POST['no_hp'] ?? '')),
                'instansi' => trim((string) ($_POST['instansi'] ?? '')),
                'tujuan_bertamu' => trim((string) ($_POST['tujuan_bertamu'] ?? '')),
                'nama_personel' => trim((string) ($_POST['nama_personel'] ?? '')),
                'keperluan' => trim((string) ($_POST['keperluan'] ?? '')),
            ];
            $submitted = $formData;

            if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
                $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
                $messageType = 'danger';
            } elseif ($formData['nama'] === '' || $formData['tujuan_bertamu'] === '' || $formData['nama_personel'] === '' || $formData['keperluan'] === '') {
                $message = 'Nama, tujuan bertamu, nama personel, dan keperluan wajib diisi.';
                $messageType = 'warning';
            } elseif (!in_array($formData['tujuan_bertamu'], $tujuanOptions, true)) {
                $message = 'Pilihan tujuan bertamu tidak valid.';
                $messageType = 'warning';
            } elseif (count($personnelOptions) > 0 && !in_array($formData['nama_personel'], $personnelOptions, true)) {
                $message = 'Pilihan nama personel tidak valid.';
                $messageType = 'warning';
            } else {
                $candidateMap = [
                    'nama' => $formData['nama'],
                    'nama_tamu' => $formData['nama'],
                    'no_hp' => $formData['no_hp'],
                    'no_telp' => $formData['no_hp'],
                    'telepon' => $formData['no_hp'],
                    'instansi' => $formData['instansi'],
                    'asal_instansi' => $formData['instansi'],
                    'tujuan_bertamu' => $formData['tujuan_bertamu'],
                    'tujuan' => $formData['tujuan_bertamu'],
                    'bidang_tujuan' => $formData['tujuan_bertamu'],
                    'unit_tujuan' => $formData['tujuan_bertamu'],
                    'nama_personel' => $formData['nama_personel'],
                    'personel' => $formData['nama_personel'],
                    'personnel' => $formData['nama_personel'],
                    'petugas' => $formData['nama_personel'],
                    'keperluan' => $formData['keperluan'],
                    'pesan' => $formData['keperluan'],
                ];

                $insertData = [];
                foreach ($candidateMap as $field => $value) {
                    if (isset($columns[$field]) && !array_key_exists($field, $insertData)) {
                        $insertData[$field] = $value;
                    }
                }

                foreach (['created_at', 'tanggal', 'tanggal_kunjungan'] as $dateField) {
                    if (isset($columns[$dateField]) && !array_key_exists($dateField, $insertData)) {
                        $insertData[$dateField] = date('Y-m-d H:i:s');
                    }
                }

                if (count($insertData) === 0) {
                    $message = "Kolom pada tabel 'tamu' tidak cocok dengan field form.";
                    $messageType = 'danger';
                } else {
                    $insertFields = array_keys($insertData);
                    $escapedFields = array_map(static fn(string $f): string => '`' . str_replace('`', '', $f) . '`', $insertFields);
                    $placeholders = implode(', ', array_fill(0, count($insertFields), '?'));
                    $sql = 'INSERT INTO `tamu` (' . implode(', ', $escapedFields) . ') VALUES (' . $placeholders . ')';
                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        $message = 'Gagal menyiapkan query simpan buku tamu.';
                        $messageType = 'danger';
                    } else {
                        $types = str_repeat('s', count($insertFields));
                        $vals = array_values($insertData);
                        $bindParams = [];
                        $bindParams[] = &$types;
                        foreach ($vals as $i => $v) {
                            $bindParams[] = &$vals[$i];
                        }
                        call_user_func_array([$stmt, 'bind_param'], $bindParams);
                        if ($stmt->execute()) {
                            $message = 'Data tamu berhasil disimpan.';
                            $messageType = 'success';
                        } else {
                            $message = 'Gagal menyimpan data tamu.';
                            $messageType = 'danger';
                        }
                        $stmt->close();
                    }
                }
            }
        }

        $orderBy = 'id DESC';
        if (!isset($columns['id'])) {
            if (isset($columns['created_at'])) {
                $orderBy = 'created_at DESC';
            } elseif (isset($columns['tanggal'])) {
                $orderBy = 'tanggal DESC';
            } else {
                $orderBy = '1 DESC';
            }
        }
        if (isset($_GET['export']) && (string) $_GET['export'] === 'excel') {
            $exportRows = [];
            $exportQuery = $db->query('SELECT * FROM `tamu` ORDER BY ' . $orderBy);
            if ($exportQuery !== false) {
                while ($erow = $exportQuery->fetch_assoc()) {
                    $exportRows[] = $erow;
                }
            }

            $filename = 'laporan_buku_tamu_' . date('Ymd_His') . '.xls';
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo "No\tNama\tTujuan Bertamu\tPersonel Dituju\tInstansi\tKontak\tKeperluan\tTanggal\n";
            $no = 1;
            foreach ($exportRows as $guest) {
                $nama = (string) ($guest['nama'] ?? $guest['nama_tamu'] ?? '-');
                $tujuanBertamu = (string) ($guest['tujuan_bertamu'] ?? $guest['bidang_tujuan'] ?? $guest['unit_tujuan'] ?? $guest['tujuan'] ?? '-');
                $namaPersonel = (string) ($guest['nama_personel'] ?? $guest['personel'] ?? $guest['personnel'] ?? $guest['petugas'] ?? '-');
                $instansi = (string) ($guest['instansi'] ?? $guest['asal_instansi'] ?? '-');
                $kontak = (string) ($guest['no_hp'] ?? $guest['no_telp'] ?? $guest['telepon'] ?? '-');
                $keperluan = (string) ($guest['keperluan'] ?? $guest['pesan'] ?? '-');
                $tanggal = (string) ($guest['created_at'] ?? $guest['tanggal'] ?? $guest['tanggal_kunjungan'] ?? '-');

                $cells = [$no, $nama, $tujuanBertamu, $namaPersonel, $instansi, $kontak, $keperluan, $tanggal];
                $safeCells = array_map(static function ($cell): string {
                    $v = str_replace(["\t", "\r", "\n"], ' ', (string) $cell);
                    return $v === '' ? '-' : $v;
                }, $cells);
                echo implode("\t", $safeCells) . "\n";
                $no++;
            }
            exit;
        }
        $editMode = false;
        $editId = trim((string) ($_GET['edit_id'] ?? ''));
        if ($editId !== '' && $idField !== '') {
            $stmtEdit = $db->prepare("SELECT * FROM `tamu` WHERE `$idField` = ? LIMIT 1");
            if ($stmtEdit !== false) {
                $stmtEdit->bind_param('s', $editId);
                if ($stmtEdit->execute()) {
                    $resEdit = $stmtEdit->get_result();
                    if ($resEdit !== false && $resEdit->num_rows > 0) {
                        $rowEdit = $resEdit->fetch_assoc();
                        $submitted['nama'] = (string) ($rowEdit['nama'] ?? $rowEdit['nama_tamu'] ?? '');
                        $submitted['no_hp'] = (string) ($rowEdit['no_hp'] ?? $rowEdit['no_telp'] ?? $rowEdit['telepon'] ?? '');
                        $submitted['instansi'] = (string) ($rowEdit['instansi'] ?? $rowEdit['asal_instansi'] ?? '');
                        $submitted['tujuan_bertamu'] = (string) ($rowEdit['tujuan_bertamu'] ?? $rowEdit['unit_tujuan'] ?? $rowEdit['bidang_tujuan'] ?? $rowEdit['tujuan'] ?? '');
                        $submitted['nama_personel'] = (string) ($rowEdit['nama_personel'] ?? $rowEdit['personel'] ?? $rowEdit['personnel'] ?? $rowEdit['petugas'] ?? '');
                        $submitted['keperluan'] = (string) ($rowEdit['keperluan'] ?? $rowEdit['pesan'] ?? '');
                        $editMode = true;
                    }
                }
                $stmtEdit->close();
            }
        }

        $countQuery = $db->query('SELECT COUNT(*) AS c FROM `tamu`');
        if ($countQuery !== false) {
            $countRow = $countQuery->fetch_assoc();
            $totalRows = (int) ($countRow['c'] ?? 0);
            $countQuery->free();
        }
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $listQuery = $db->query('SELECT * FROM `tamu` ORDER BY ' . $orderBy . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset);
        if ($listQuery !== false) {
            while ($row = $listQuery->fetch_assoc()) {
                $guestRows[] = $row;
            }
        }
    }
}

$extraHeadMarkup = $extraHeadMarkup ?? '';
$extraFooterMarkup = $extraFooterMarkup ?? '';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup, true);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
    <div class="container-fluid site-main">
        <section class="section-spacing">
            <div class="tamu-hero p-4 p-lg-5 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div class="tamu-hero__heading">
                        <h1 class="tamu-hero__title mb-0">Dashboard Buku Tamu Digital</h1>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="tamu-pill"><i class="fa-solid fa-shield-halved"></i> Sistem Terpusat</span>
                        <span class="tamu-pill"><i class="fa-solid fa-file-lines"></i> Siap Laporan</span>
                    </div>
                </div>
            </div>
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="card tamu-card h-100">
                        <div class="card-body p-4">
                            <h2 class="h4 tamu-title mb-2">Input Buku Tamu</h2>
                            <p class="text-muted small mb-4">Isi data kunjungan untuk tersimpan ke tabel tamu.</p>

                            <form method="post" novalidate id="tamuInputForm">
                                <input type="hidden" name="action" value="<?php echo !empty($editMode) ? 'update_tamu' : 'save_tamu'; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if (!empty($editMode)): ?>
                                    <input type="hidden" name="row_id" value="<?php echo htmlspecialchars((string) ($_GET['edit_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($submitted['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="instansi" class="form-label">Instansi</label>
                                    <input type="text" class="form-control" id="instansi" name="instansi" value="<?php echo htmlspecialchars($submitted['instansi'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="tujuan_bertamu" class="form-label">Tujuan Bertamu</label>
                                    <select class="form-select" id="tujuan_bertamu" name="tujuan_bertamu" required>
                                        <option value="">-- Pilih tujuan bertamu --</option>
                                        <?php foreach ($tujuanOptions as $tujuanOpt): ?>
                                            <option value="<?php echo htmlspecialchars($tujuanOpt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $submitted['tujuan_bertamu'] === $tujuanOpt ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tujuanOpt, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nama_personel" class="form-label">Nama Personel</label>
                                    <select class="form-select" id="nama_personel" name="nama_personel" required>
                                        <option value="">-- Pilih nama personel --</option>
                                        <?php foreach ($personnelOptions as $personnelName): ?>
                                            <option value="<?php echo htmlspecialchars($personnelName, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $submitted['nama_personel'] === $personnelName ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($personnelName, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="namaPersonelHint" class="form-text text-muted">Ketik di dropdown untuk mencari nama personel.</div>
                                    <?php if (count($personnelOptions) === 0): ?>
                                        <div class="form-text text-warning">
                                            Data personel belum tersedia. Tambahkan personel terlebih dahulu di
                                            <a href="<?php echo function_exists('org_page_url') ? htmlspecialchars(org_page_url('profil.php') . '#profil-daftar-personel', ENT_QUOTES, 'UTF-8') : 'profil.php#profil-daftar-personel'; ?>">Profil → Daftar Personel</a>.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">No. HP</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($submitted['no_hp'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="keperluan" class="form-label">Keperluan</label>
                                    <textarea class="form-control" id="keperluan" name="keperluan" rows="3" required><?php echo htmlspecialchars($submitted['keperluan'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <button type="submit" class="btn tamu-btn-save">
                                    <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><?php echo !empty($editMode) ? 'Update Data Tamu' : 'Simpan Buku Tamu'; ?>
                                </button>
                                <?php if (!empty($editMode)): ?>
                                    <a href="tamu.php" class="btn btn-outline-secondary ms-2">Batal Edit</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card tamu-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 tamu-toolbar">
                                <h2 class="h5 tamu-title mb-0">Data Tamu Terbaru</h2>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="tamu.php?export=excel" class="btn btn-sm btn-outline-success js-block-enter-action">
                                        <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i>Download Excel
                                    </a>
                                    <a href="export_excel.php?type=tamu" class="btn btn-sm btn-success js-block-enter-action">
                                        <i class="fa-solid fa-file-export me-1" aria-hidden="true"></i>Export Laporan
                                    </a>
                                </div>
                            </div>
                            <div class="row g-2 mb-3 tamu-filter-wrap">
                                <div class="col-12 col-md-6">
                                    <input type="search" id="tamuFilterSearch" class="form-control form-control-sm" placeholder="Cari nama/instansi/personel...">
                                </div>
                                <div class="col-12 col-md-6">
                                    <select id="tamuFilterTujuan" class="form-select form-select-sm">
                                        <option value="all">Semua Tujuan</option>
                                        <?php foreach ($tujuanOptions as $tujuanOpt): ?>
                                            <option value="<?php echo htmlspecialchars($tujuanOpt, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tujuanOpt, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (count($guestRows) > 0): ?>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="text-muted small">
                                        Menampilkan <?php echo (int) count($guestRows); ?> dari total <?php echo (int) $totalRows; ?> data tamu.
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="tamuPerPageSelect" class="small text-muted mb-0">Tampil</label>
                                        <select id="tamuPerPageSelect" class="form-select form-select-sm" style="width:auto; min-width:88px;">
                                            <?php foreach ($allowedPerPage as $pp): ?>
                                                <option value="<?php echo (int) $pp; ?>" <?php echo $pp === $perPage ? 'selected' : ''; ?>>
                                                    <?php echo (int) $pp; ?>/hal
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="text-muted small">
                                            Halaman <?php echo (int) $currentPage; ?> / <?php echo (int) $totalPages; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" id="tamuDataTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama</th>
                                                <th>Tujuan Bertamu</th>
                                                <th>Personel Dituju</th>
                                                <th>Instansi</th>
                                                <th>Kontak</th>
                                                <th>Keperluan</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($guestRows as $guest): ?>
                                                <?php
                                                $nama = (string) ($guest['nama'] ?? $guest['nama_tamu'] ?? '-');
                                                $tujuanBertamu = (string) ($guest['tujuan_bertamu'] ?? $guest['bidang_tujuan'] ?? $guest['unit_tujuan'] ?? $guest['tujuan'] ?? '-');
                                                $namaPersonel = (string) ($guest['nama_personel'] ?? $guest['personel'] ?? $guest['personnel'] ?? $guest['petugas'] ?? '-');
                                                $instansi = (string) ($guest['instansi'] ?? $guest['asal_instansi'] ?? '-');
                                                $kontak = (string) ($guest['no_hp'] ?? $guest['no_telp'] ?? $guest['telepon'] ?? $guest['email'] ?? '-');
                                                $keperluan = (string) ($guest['keperluan'] ?? $guest['pesan'] ?? $guest['tujuan'] ?? '-');
                                                $rowId = (string) ($guest[$idField ?? ''] ?? '');
                                                $searchHay = strtolower(trim($nama . ' ' . $instansi . ' ' . $namaPersonel));
                                                ?>
                                                <tr data-search="<?php echo htmlspecialchars($searchHay, ENT_QUOTES, 'UTF-8'); ?>" data-tujuan="<?php echo htmlspecialchars($tujuanBertamu, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td><?php echo htmlspecialchars($nama !== '' ? $nama : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($tujuanBertamu !== '' ? $tujuanBertamu : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($namaPersonel !== '' ? $namaPersonel : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($instansi !== '' ? $instansi : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($kontak !== '' ? $kontak : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($keperluan !== '' ? $keperluan : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-center">
                                                        <?php if (($idField ?? '') !== '' && $rowId !== ''): ?>
                                                            <a href="tamu.php?edit_id=<?php echo rawurlencode($rowId); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_tamu">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                                <input type="hidden" name="row_id" value="<?php echo htmlspecialchars($rowId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data tamu ini?');">Hapus</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if ($totalPages > 1): ?>
                                    <nav class="mt-3" aria-label="Navigasi halaman data tamu">
                                        <ul class="pagination pagination-sm mb-0 justify-content-end flex-wrap">
                                            <?php
                                            $prevPage = max(1, $currentPage - 1);
                                            $nextPage = min($totalPages, $currentPage + 1);
                                            ?>
                                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="tamu.php?page=<?php echo (int) $prevPage; ?>&amp;per_page=<?php echo (int) $perPage; ?>" aria-label="Sebelumnya">«</a>
                                            </li>
                                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                                <?php if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 1): ?>
                                                    <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                                                        <a class="page-link" href="tamu.php?page=<?php echo (int) $p; ?>&amp;per_page=<?php echo (int) $perPage; ?>"><?php echo (int) $p; ?></a>
                                                    </li>
                                                <?php elseif ($p === 2 && $currentPage > 4): ?>
                                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                                <?php elseif ($p === $totalPages - 1 && $currentPage < $totalPages - 3): ?>
                                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="tamu.php?page=<?php echo (int) $nextPage; ?>&amp;per_page=<?php echo (int) $perPage; ?>" aria-label="Berikutnya">»</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Belum ada data tamu.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                return;
            }
            window.jQuery(function ($) {
                $('#nama_personel').select2({
                    placeholder: '-- Pilih nama personel --',
                    width: '100%',
                    allowClear: true
                });
            });
            const searchEl = document.getElementById('tamuFilterSearch');
            const tujuanEl = document.getElementById('tamuFilterTujuan');
            const perPageEl = document.getElementById('tamuPerPageSelect');
            const rows = Array.from(document.querySelectorAll('#tamuDataTable tbody tr'));
            const normalize = function (v) { return String(v || '').toLowerCase().trim(); };
            const applyFilter = function () {
                const q = normalize(searchEl ? searchEl.value : '');
                const tujuan = normalize(tujuanEl ? tujuanEl.value : 'all');
                rows.forEach(function (row) {
                    const hay = normalize(row.getAttribute('data-search'));
                    const t = normalize(row.getAttribute('data-tujuan'));
                    const okQ = q === '' || hay.indexOf(q) !== -1;
                    const okT = tujuan === 'all' || t === tujuan;
                    row.style.display = (okQ && okT) ? '' : 'none';
                });
            };
            if (searchEl) searchEl.addEventListener('input', applyFilter);
            if (tujuanEl) tujuanEl.addEventListener('change', applyFilter);
            if (perPageEl) {
                perPageEl.addEventListener('change', function () {
                    const val = parseInt(perPageEl.value || '25', 10);
                    const params = new URLSearchParams(window.location.search);
                    params.set('per_page', String(Number.isFinite(val) ? val : 25));
                    params.set('page', '1');
                    window.location.search = params.toString();
                });
            }

            const formEl = document.getElementById('tamuInputForm');
            if (formEl) {
                formEl.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }
                    const target = event.target;
                    if (target instanceof HTMLTextAreaElement || target instanceof HTMLButtonElement) {
                        return;
                    }
                    event.preventDefault();
                });
            }

            const blockedEnterLinks = document.querySelectorAll('.js-block-enter-action');
            blockedEnterLinks.forEach(function (linkEl) {
                linkEl.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });
            });
        }());
    </script>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
