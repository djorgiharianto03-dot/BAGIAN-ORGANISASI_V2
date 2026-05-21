<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

$csrfToken = org_csrf_token();

if (empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';

$roleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if ($roleNorm === 'sub_admin_eorganisasi' || $roleNorm === 'sub_admin_publikasi') {
    header('Location: dashboard.php');
    exit;
}

$db = org_db();
$flashOk = '';
$flashErr = '';
$idAdminSess = (string) ($_SESSION['admin_id'] ?? '');
$namaAdminSess = (string) ($_SESSION['admin_display'] ?? 'Admin');

if ($db !== null) {
    org_dashboard_widgets_ensure_table($db);
    org_widget_details_ensure_table($db);
}

/** Simpan widget lalu detail OPD dari POST. */
$saveWidgetWithDetails = static function (
    mysqli $db,
    int $widgetId,
    string $judul,
    string $tipe,
    string $nilaiKiri,
    string $nilaiKanan,
    string $warna,
    int $urutan,
    int $aktif,
    bool $isUpdate
) use ($idAdminSess, $namaAdminSess): array {
    $detailRows = org_widget_details_parse_post_rows();
    if ($isUpdate) {
        if (!org_dashboard_widgets_update($db, $widgetId, $judul, $tipe, $nilaiKiri, $nilaiKanan, $warna, $urutan, $aktif)) {
            return ['ok' => false, 'msg' => 'Gagal memperbarui widget.'];
        }
        if (!org_widget_details_replace_all($db, $widgetId, $detailRows)) {
            return ['ok' => false, 'msg' => 'Widget diperbarui, tetapi gagal menyimpan detail OPD.'];
        }
        org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin memperbarui widget beranda: «' . $judul . '».');

        return ['ok' => true, 'msg' => 'Widget dan detail OPD berhasil diperbarui.', 'id' => $widgetId];
    }
    $newId = org_dashboard_widgets_insert($db, $judul, $tipe, $nilaiKiri, $nilaiKanan, $warna, $urutan, $aktif);
    if ($newId === null || $newId < 1) {
        return ['ok' => false, 'msg' => 'Gagal menyimpan widget baru.'];
    }
    if ($detailRows !== [] && !org_widget_details_replace_all($db, $newId, $detailRows)) {
        return ['ok' => false, 'msg' => 'Widget dibuat, tetapi gagal menyimpan detail OPD.'];
    }
    org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menambah widget beranda: «' . $judul . '».');

    return ['ok' => true, 'msg' => 'Widget berhasil ditambahkan.', 'id' => $newId];
};

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$postedAction = $isPost ? trim((string) ($_POST['action'] ?? '')) : '';
$postedCsrf = (string) ($_POST['csrf_token'] ?? '');
$csrfValid = !$isPost || ($csrfToken !== '' && $postedCsrf !== '' && hash_equals($csrfToken, $postedCsrf));

if ($isPost && !$csrfValid) {
    $flashErr = 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
} elseif ($isPost && $db === null) {
    $flashErr = 'Tidak dapat terhubung ke database.';
} elseif ($isPost && $csrfValid && $db !== null) {
    if ($postedAction === 'widget_simpan') {
        $editId = (int) ($_POST['widget_id'] ?? 0);
        $judul = org_sanitize_plain(trim((string) ($_POST['judul'] ?? '')));
        $tipe = org_dashboard_widgets_normalize_tipe((string) ($_POST['tipe_data'] ?? ''));
        $nilaiKiri = org_sanitize_plain(trim((string) ($_POST['nilai_kiri'] ?? '')));
        $nilaiKanan = org_sanitize_plain(trim((string) ($_POST['nilai_kanan'] ?? '')));
        $warna = org_dashboard_widgets_normalize_warna((string) ($_POST['warna_tema'] ?? ''));
        $urutan = (int) ($_POST['urutan'] ?? 0);
        $aktif = isset($_POST['aktif']) ? 1 : 0;

        if ($judul === '') {
            $flashErr = 'Judul widget wajib diisi.';
        } elseif ($nilaiKiri === '' || $nilaiKanan === '') {
            $flashErr = 'Nilai kiri dan kanan wajib diisi.';
        } elseif ($tipe === 'progres_angka') {
            $kiriNum = (float) str_replace(',', '.', $nilaiKiri);
            $kananNum = (float) str_replace(',', '.', $nilaiKanan);
            if ($kananNum <= 0) {
                $flashErr = 'Untuk progres angka, nilai kanan (target) harus lebih dari 0.';
            } else {
                $res = $saveWidgetWithDetails($db, $editId, $judul, $tipe, $nilaiKiri, $nilaiKanan, $warna, $urutan, $aktif, $editId > 0);
                if ($res['ok']) {
                    $flashOk = $res['msg'];
                    if ($editId < 1 && isset($res['id'])) {
                        header('Location: kelola_dashboard_widgets.php?edit=' . (int) $res['id']);
                        exit;
                    }
                } else {
                    $flashErr = $res['msg'];
                }
            }
        } else {
            $res = $saveWidgetWithDetails($db, $editId, $judul, $tipe, $nilaiKiri, $nilaiKanan, $warna, $urutan, $aktif, $editId > 0);
            if ($res['ok']) {
                $flashOk = $res['msg'];
                if ($editId < 1 && isset($res['id'])) {
                    header('Location: kelola_dashboard_widgets.php?edit=' . (int) $res['id']);
                    exit;
                }
            } else {
                $flashErr = $res['msg'];
            }
        }
    } elseif ($postedAction === 'widget_hapus') {
        $hapusId = (int) ($_POST['widget_id'] ?? 0);
        $rowH = $hapusId > 0 ? org_dashboard_widgets_fetch_by_id($db, $hapusId) : null;
        if ($rowH === null) {
            $flashErr = 'Widget tidak ditemukan.';
        } elseif (org_dashboard_widgets_delete_by_id($db, $hapusId)) {
            org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menghapus widget beranda id ' . (string) $hapusId . ' («' . org_sanitize_plain((string) ($rowH['judul'] ?? '')) . '»).');
            $flashOk = 'Widget telah dihapus.';
        } else {
            $flashErr = 'Gagal menghapus widget.';
        }
    }
}

$widgetRows = $db !== null ? org_dashboard_widgets_fetch_all($db, false) : [];
$tableReady = $db !== null && org_dashboard_widgets_table_exists($db);

$editRow = null;
$editIdGet = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
if ($editIdGet > 0 && $db !== null) {
    $editRow = org_dashboard_widgets_fetch_by_id($db, $editIdGet);
}

$formJudul = $editRow !== null ? (string) ($editRow['judul'] ?? '') : '';
$formTipe = $editRow !== null ? (string) ($editRow['tipe_data'] ?? 'progres_angka') : 'progres_angka';
$formKiri = $editRow !== null ? (string) ($editRow['nilai_kiri'] ?? '') : '';
$formKanan = $editRow !== null ? (string) ($editRow['nilai_kanan'] ?? '') : '';
$formWarna = $editRow !== null ? (string) ($editRow['warna_tema'] ?? 'primary') : 'primary';
$formUrutan = $editRow !== null ? (int) ($editRow['urutan'] ?? 0) : 0;
$formAktif = $editRow === null || (int) ($editRow['aktif'] ?? 1) === 1;

$editDetailRows = [];
$editWidgetId = $editRow !== null ? (int) ($editRow['id'] ?? 0) : 0;
if ($editWidgetId > 0 && $db !== null) {
    $groupedDetails = org_widget_details_fetch_grouped($db, $editWidgetId);
    foreach (['selesai', 'dalam_pengerjaan', 'belum'] as $bucket) {
        foreach ($groupedDetails[$bucket] as $det) {
            $editDetailRows[] = [
                'nama_opd' => (string) ($det['nama_opd'] ?? ''),
                'status' => $bucket,
                'alasan' => (string) ($det['alasan'] ?? ''),
            ];
        }
    }
}

$pageTitle = 'Kelola Widget Beranda — Admin';
$adminName = htmlspecialchars((string) ($_SESSION['admin_display'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; min-height: 100vh; }
        .dash-navbar { background: linear-gradient(135deg, #0c4a6e, #0369a1); box-shadow: 0 2px 12px rgba(8, 47, 73, 0.2); }
        .dash-navbar .nav-link { color: rgba(255,255,255,0.92); font-weight: 500; }
        .dash-navbar .nav-link:hover { color: #fff; }
        .widget-preview { font-size: 0.9rem; }
        .widget-preview .progress { height: 0.55rem; }
        .detail-opd-row { background: #f8fafc; }
        .detail-opd-row .form-label { font-size: 0.78rem; margin-bottom: 0.2rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg dash-navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Dashboard Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navWidget">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navWidget">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Beranda admin</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="kelola_dashboard_widgets.php">Widget beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_team_targets.php">Target tim kerja</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank" rel="noopener">Situs publik</a></li>
                </ul>
                <span class="navbar-text text-white-50 small me-3"><?php echo $adminName; ?></span>
                <form method="post" action="../index.php" class="d-inline mb-0">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Kelola Widget Beranda</h1>
                <p class="text-muted small mb-0">Kartu indikator dinamis di halaman beranda (progress bar &amp; perbandingan nilai).</p>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="dashboard.php"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke dashboard</a>
        </div>

        <?php if ($flashOk !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if ($db === null): ?>
            <div class="alert alert-warning">Tidak dapat terhubung ke database. Periksa <code>config/database.php</code>.</div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold">
                            <?php echo $editRow !== null ? 'Edit widget' : 'Tambah widget'; ?>
                        </div>
                        <div class="card-body">
                            <form method="post" id="formWidget" novalidate>
                                <input type="hidden" name="action" value="widget_simpan">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($editRow !== null): ?>
                                    <input type="hidden" name="widget_id" value="<?php echo (int) ($editRow['id'] ?? 0); ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="judul" name="judul" required maxlength="255"
                                           value="<?php echo htmlspecialchars($formJudul, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="tipe_data" class="form-label">Tipe data <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipe_data" name="tipe_data" required>
                                        <option value="progres_angka"<?php echo $formTipe === 'progres_angka' ? ' selected' : ''; ?>>Progres angka (progress bar)</option>
                                        <option value="perbandingan_nilai"<?php echo $formTipe === 'perbandingan_nilai' ? ' selected' : ''; ?>>Perbandingan nilai (teks → teks)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="nilai_kiri" class="form-label"><span id="label_nilai_kiri">Nilai kiri (pembilang)</span> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nilai_kiri" name="nilai_kiri" required maxlength="255"
                                           value="<?php echo htmlspecialchars($formKiri, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="nilai_kanan" class="form-label"><span id="label_nilai_kanan">Nilai kanan (penyebut / target)</span> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nilai_kanan" name="nilai_kanan" required maxlength="255"
                                           value="<?php echo htmlspecialchars($formKanan, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="warna_tema" class="form-label">Warna tema</label>
                                    <select class="form-select" id="warna_tema" name="warna_tema">
                                        <option value="primary"<?php echo $formWarna === 'primary' ? ' selected' : ''; ?>>Primary (biru)</option>
                                        <option value="success"<?php echo $formWarna === 'success' ? ' selected' : ''; ?>>Success (hijau)</option>
                                        <option value="danger"<?php echo $formWarna === 'danger' ? ' selected' : ''; ?>>Danger (merah)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="urutan" class="form-label">Urutan</label>
                                        <input type="number" class="form-control" id="urutan" name="urutan" min="0" max="9999"
                                               value="<?php echo (int) $formUrutan; ?>">
                                    </div>
                                    <div class="col-6 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1"<?php echo $formAktif ? ' checked' : ''; ?>>
                                            <label class="form-check-label" for="aktif">Tampilkan di beranda</label>
                                        </div>
                                    </div>
                                </div>

                                <?php require __DIR__ . '/../includes/partials/admin_widget_detail_opd_form.php'; ?>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i>
                                        <?php echo $editRow !== null ? 'Simpan perubahan' : 'Tambah widget'; ?>
                                    </button>
                                    <?php if ($editRow !== null): ?>
                                        <a href="kelola_dashboard_widgets.php" class="btn btn-outline-secondary">Batal edit</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                            <span>Daftar widget</span>
                            <span class="badge text-bg-secondary"><?php echo count($widgetRows); ?></span>
                        </div>
                        <?php if (!$tableReady): ?>
                            <div class="card-body">
                                <p class="text-muted small mb-0">Tabel belum siap. Simpan satu widget atau jalankan <code>install/dashboard_widgets.sql</code>.</p>
                            </div>
                        <?php elseif (count($widgetRows) === 0): ?>
                            <div class="card-body text-center text-muted py-4">
                                <i class="fa-solid fa-chart-simple fa-2x mb-2 opacity-50" aria-hidden="true"></i>
                                <p class="mb-0">Belum ada widget. Gunakan formulir di kiri untuk menambah.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Judul</th>
                                            <th>Tipe</th>
                                            <th>Nilai</th>
                                            <th>Tema</th>
                                            <th class="text-center">Urut</th>
                                            <th class="text-center">Aktif</th>
                                            <th class="text-end" style="width: 7rem;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($widgetRows as $row): ?>
                                            <?php
                                            $rid = (int) ($row['id'] ?? 0);
                                            $rtipe = (string) ($row['tipe_data'] ?? '');
                                            $rkiri = (string) ($row['nilai_kiri'] ?? '');
                                            $rkanan = (string) ($row['nilai_kanan'] ?? '');
                                            $rwarna = (string) ($row['warna_tema'] ?? 'primary');
                                            $rpct = $rtipe === 'progres_angka' ? org_dashboard_widgets_hitung_persen($rkiri, $rkanan) : null;
                                            ?>
                                            <tr>
                                                <td class="fw-medium"><?php echo htmlspecialchars((string) ($row['judul'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php if ($rtipe === 'perbandingan_nilai'): ?>
                                                        <span class="badge text-bg-info">Perbandingan</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-primary">Progres</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="widget-preview">
                                                    <?php if ($rtipe === 'perbandingan_nilai'): ?>
                                                        <span><?php echo htmlspecialchars($rkiri, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <i class="fa-solid fa-arrow-right-long mx-1 text-<?php echo htmlspecialchars($rwarna, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                                        <span class="fw-semibold"><?php echo htmlspecialchars($rkanan, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php else: ?>
                                                        <div class="text-muted mb-1"><?php echo htmlspecialchars($rkiri, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($rkanan, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string) $rpct, ENT_QUOTES, 'UTF-8'); ?>%)</div>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-<?php echo htmlspecialchars($rwarna, ENT_QUOTES, 'UTF-8'); ?>" style="width: <?php echo htmlspecialchars((string) $rpct, ENT_QUOTES, 'UTF-8'); ?>%;"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge text-bg-<?php echo htmlspecialchars($rwarna, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rwarna, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?php echo (int) ($row['urutan'] ?? 0); ?></td>
                                                <td class="text-center">
                                                    <?php if ((int) ($row['aktif'] ?? 0) === 1): ?>
                                                        <i class="fa-solid fa-circle-check text-success" title="Aktif" aria-label="Aktif"></i>
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-circle-minus text-muted" title="Nonaktif" aria-label="Nonaktif"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <a href="kelola_dashboard_widgets.php?edit=<?php echo $rid; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Hapus widget ini?');">
                                                        <input type="hidden" name="action" value="widget_hapus">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="widget_id" value="<?php echo $rid; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <template id="tplDetailOpdRow">
        <div class="detail-opd-row border rounded-3 p-2 p-md-3 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label">Nama OPD</label>
                    <input type="text" class="form-control form-control-sm" name="detail_nama_opd[]" maxlength="255" required placeholder="Nama OPD">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm detail-status-select" name="detail_status[]">
                        <option value="selesai">Selesai</option>
                        <option value="dalam_pengerjaan">Dalam Pengerjaan</option>
                        <option value="belum" selected>Belum Ditambahkan</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 detail-alasan-wrap">
                    <label class="form-label">Alasan</label>
                    <input type="text" class="form-control form-control-sm detail-alasan-input" name="detail_alasan[]" maxlength="500" placeholder="Wajib jika belum">
                </div>
                <div class="col-6 col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-detail-opd" title="Hapus baris" aria-label="Hapus baris OPD">
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>
    <script>
    (function () {
        var tipeEl = document.getElementById('tipe_data');
        var labelKiri = document.getElementById('label_nilai_kiri');
        var labelKanan = document.getElementById('label_nilai_kanan');
        if (tipeEl && labelKiri && labelKanan) {
            function syncLabels() {
                var isProgres = tipeEl.value === 'progres_angka';
                labelKiri.textContent = isProgres ? 'Nilai kiri (pembilang / capaian)' : 'Teks kiri (mis. B)';
                labelKanan.textContent = isProgres ? 'Nilai kanan (penyebut / target)' : 'Teks kanan (mis. A)';
            }
            tipeEl.addEventListener('change', syncLabels);
            syncLabels();
        }

        var rowsWrap = document.getElementById('detailOpdRows');
        var btnAdd = document.getElementById('btnAddDetailOpd');
        var tpl = document.getElementById('tplDetailOpdRow');
        var emptyHint = document.getElementById('detailOpdEmptyHint');

        function syncAlasanVisibility(row) {
            if (!row) return;
            var sel = row.querySelector('.detail-status-select');
            var alasan = row.querySelector('.detail-alasan-input');
            if (!sel || !alasan) return;
            var status = sel.value;
            var isBelum = status === 'belum';
            var isSelesai = status === 'selesai';
            alasan.disabled = false;
            alasan.readOnly = isSelesai;
            alasan.required = isBelum;
            if (isSelesai) {
                alasan.value = '';
                alasan.classList.add('bg-light');
            } else if (isBelum) {
                alasan.classList.remove('bg-light');
                alasan.placeholder = 'Wajib diisi (mis. belum upload SK)';
            } else {
                alasan.classList.remove('bg-light');
                alasan.placeholder = 'Opsional (mis. menunggu verifikasi)';
            }
        }

        function bindRow(row) {
            if (!row) return;
            var sel = row.querySelector('.detail-status-select');
            if (sel) sel.addEventListener('change', function () { syncAlasanVisibility(row); });
            var btnRm = row.querySelector('.btn-remove-detail-opd');
            if (btnRm) {
                btnRm.addEventListener('click', function () {
                    row.remove();
                    if (rowsWrap && rowsWrap.querySelectorAll('.detail-opd-row').length === 0 && emptyHint) {
                        emptyHint.classList.remove('d-none');
                    }
                });
            }
            syncAlasanVisibility(row);
        }

        if (rowsWrap) {
            rowsWrap.querySelectorAll('.detail-opd-row').forEach(bindRow);
        }

        if (btnAdd && rowsWrap && tpl && tpl.content) {
            btnAdd.addEventListener('click', function () {
                if (emptyHint) emptyHint.classList.add('d-none');
                var node = tpl.content.firstElementChild;
                if (!node) return;
                var clone = node.cloneNode(true);
                rowsWrap.appendChild(clone);
                bindRow(clone);
            });
        }
    }());
    </script>
</body>
</html>
