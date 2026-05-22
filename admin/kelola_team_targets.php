<?php

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
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'team_targets_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';

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
    org_team_targets_ensure_table($db);
}

if (isset($_GET['tahun_baru_submit']) || isset($_GET['tahun_baru'])) {
    $editTahun = org_team_targets_normalize_tahun($_GET['tahun_baru'] ?? (int) date('Y'));
} else {
    $editTahun = org_team_targets_normalize_tahun($_GET['tahun'] ?? (int) date('Y'));
}
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$postedAction = $isPost ? trim((string) ($_POST['action'] ?? '')) : '';
$postedCsrf = (string) ($_POST['csrf_token'] ?? '');
$csrfValid = !$isPost || ($csrfToken !== '' && $postedCsrf !== '' && hash_equals($csrfToken, $postedCsrf));

if ($isPost && !$csrfValid) {
    $flashErr = 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
} elseif ($isPost && $db === null) {
    $flashErr = 'Tidak dapat terhubung ke database.';
} elseif ($isPost && $csrfValid && $db !== null && $postedAction === 'targets_simpan') {
    $editTahun = org_team_targets_normalize_tahun($_POST['tahun'] ?? $editTahun);
    $byTim = org_team_targets_parse_post_by_tim();
    $tampilBeranda = org_team_targets_parse_tampil_beranda_from_post();
    $totalRows = 0;
    foreach ($byTim as $rows) {
        $totalRows += count($rows);
    }
    if ($totalRows === 0 && $tampilBeranda) {
        $flashErr = 'Tambahkan minimal satu kegiatan target, atau matikan opsi «Tampilkan di beranda».';
    } elseif (!org_team_targets_replace_year($db, $editTahun, $byTim, $tampilBeranda)) {
        $dbErr = org_team_targets_last_error();
        $flashErr = 'Gagal menyimpan target ke database.'
            . ($dbErr !== '' ? ' ' . $dbErr : ' Periksa koneksi MySQL dan struktur tabel.');
    } elseif ($totalRows > 0 && $tampilBeranda && org_team_targets_count_all($db, $editTahun) < 1) {
        $flashErr = 'Data tidak tersimpan ke tabel. Muat ulang halaman admin atau buka kelola_team_targets.php sekali (migrasi tabel).';
    } else {
        $visLabel = $tampilBeranda ? 'ditampilkan' : 'disembunyikan';
        org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menyimpan target tim kerja tahun ' . $editTahun . ' (' . $visLabel . ' di beranda).');
        org_beranda_invalidate_heavy_caches($editTahun);
        $flashOk = 'Target tahun ' . $editTahun . ' berhasil disimpan (' . $totalRows . ' kegiatan).'
            . ($tampilBeranda ? ' Tampil di beranda.' : ' Disembunyikan dari beranda — aktifkan «Tampilkan di beranda» lalu simpan lagi.');
    }
}

$groupedEdit = $db !== null ? org_team_targets_fetch_grouped_by_year($db, $editTahun) : org_team_targets_empty_grouped();
$availableYears = $db !== null ? org_team_targets_fetch_available_years($db) : [(int) date('Y')];
$tampilBerandaEdit = $db !== null ? org_team_targets_tampil_beranda($db, $editTahun) : true;
$tableReady = $db !== null && org_team_targets_table_exists($db);

$timMeta = [
    'kelembagaan' => ['label' => 'Kelembagaan dan Anjab', 'hint' => 'Contoh: Penyusunan Anjab ABK, Evaluasi Kelembagaan'],
    'rb' => ['label' => 'Kinerja dan RB', 'hint' => 'Contoh: Evaluasi SAKIP, Penyusunan Renstra'],
    'yanlik' => ['label' => 'Pelayanan Publik dan Tata Laksana', 'hint' => 'Contoh: Standar Pelayanan, SOP Layanan'],
];

$pageTitle = 'Kelola Target Tim Kerja';
$adminName = htmlspecialchars($namaAdminSess !== '' ? $namaAdminSess : 'Admin', ENT_QUOTES, 'UTF-8');
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
        .team-target-block { border: 1px solid #e2e8f0; border-radius: 0.65rem; background: #f8fafc; }
        .team-target-block__head { padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; background: #fff; border-radius: 0.65rem 0.65rem 0 0; }
        .team-target-row { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.65rem; margin-bottom: 0.5rem; }
        .team-target-row .form-label { font-size: 0.78rem; margin-bottom: 0.2rem; }
        .badge-status-direncanakan { background: #94a3b8; }
        .badge-status-berjalan { background: #2563eb; }
        .badge-status-selesai { background: #059669; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg dash-navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Dashboard Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navTeamTarget">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navTeamTarget">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Beranda admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_dashboard_widgets.php">Widget beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="kelola_team_targets.php">Target tim kerja</a></li>
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
                <h1 class="h4 mb-1">Kelola Target Tahunan Tim Kerja</h1>
                <p class="text-muted small mb-0">Tambahkan kegiatan sebanyak yang diperlukan per tim untuk satu tahun.</p>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="dashboard.php"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali</a>
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
            <div class="alert alert-warning">Tidak dapat terhubung ke database.</div>
        <?php elseif (!$tableReady): ?>
            <div class="alert alert-warning">Tabel belum siap. Simpan sekali atau jalankan <code>install/team_targets.sql</code>.</div>
        <?php else: ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="tahun_pilih" class="form-label small mb-1">Tahun</label>
                            <select class="form-select form-select-sm" id="tahun_pilih" name="tahun" onchange="this.form.submit()">
                                <?php foreach ($availableYears as $y): ?>
                                    <option value="<?php echo (int) $y; ?>"<?php echo (int) $y === $editTahun ? ' selected' : ''; ?>><?php echo (int) $y; ?></option>
                                <?php endforeach; ?>
                                <?php if (!in_array($editTahun, $availableYears, true)): ?>
                                    <option value="<?php echo $editTahun; ?>" selected><?php echo $editTahun; ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="tahun_baru" class="form-label small mb-1">Tahun baru</label>
                            <input type="number" class="form-control form-control-sm" id="tahun_baru" name="tahun_baru" min="2000" max="2100"
                                   value="<?php echo $editTahun + 1; ?>" style="width:6rem;">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" name="tahun_baru_submit" value="1">Buka tahun</button>
                        </div>
                        <div class="col ms-auto text-end">
                            <a class="btn btn-sm btn-outline-primary" href="../index.php?tahun=<?php echo $editTahun; ?>#beranda-team-targets" target="_blank" rel="noopener">
                                <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Pratinjau beranda
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <form method="post" id="formTeamTargets">
                <input type="hidden" name="action" value="targets_simpan">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="tahun" value="<?php echo $editTahun; ?>">

                <?php foreach (org_team_targets_tim_list() as $tim): ?>
                    <?php
                    $meta = $timMeta[$tim] ?? ['label' => $tim, 'hint' => ''];
                    $rows = $groupedEdit[$tim] ?? [];
                    ?>
                    <div class="team-target-block mb-4">
                        <div class="team-target-block__head d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h2 class="h6 mb-0"><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($meta['hint'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-target" data-tim="<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Tambah kegiatan
                            </button>
                        </div>
                        <div class="p-3">
                            <div class="target-rows-wrap" id="targetRows-<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>" data-tim="<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($rows === []): ?>
                                    <p class="text-muted small target-empty-hint mb-2">Belum ada kegiatan. Klik «Tambah kegiatan».</p>
                                <?php endif; ?>
                                <?php foreach ($rows as $rowIdx => $row): ?>
                                    <div class="team-target-row" data-target-row>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-7">
                                                <label class="form-label">Nama kegiatan</label>
                                                <input type="text" class="form-control form-control-sm" name="team_targets[<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>][<?php echo (int) $rowIdx; ?>][kegiatan]"
                                                       value="<?php echo htmlspecialchars((string) ($row['kegiatan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" required placeholder="Nama kegiatan">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Status</label>
                                                <select class="form-select form-select-sm" name="team_targets[<?php echo htmlspecialchars($tim, ENT_QUOTES, 'UTF-8'); ?>][<?php echo (int) $rowIdx; ?>][status]">
                                                    <?php foreach (org_team_targets_status_list() as $st): ?>
                                                        <option value="<?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ($row['status'] ?? '') === $st ? ' selected' : ''; ?>>
                                                            <?php echo htmlspecialchars(org_team_targets_status_label($st), ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-target" title="Hapus baris"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <input type="hidden" name="tampil_beranda" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="tampil_beranda" name="tampil_beranda" value="1"
                                <?php echo $tampilBerandaEdit ? ' checked' : ''; ?>>
                            <label class="form-check-label fw-semibold" for="tampil_beranda">Tampilkan di beranda</label>
                        </div>
                        <p class="text-muted small mb-0 mt-2">
                            Jika dimatikan, section <strong>Target Tahun <?php echo $editTahun; ?></strong> tidak muncul di halaman beranda (meskipun data target tersimpan).
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i>Simpan target <?php echo $editTahun; ?></button>
                    <a href="kelola_team_targets.php" class="btn btn-outline-secondary">Muat ulang</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <template id="tplTargetRow">
        <div class="team-target-row" data-target-row>
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label">Nama kegiatan</label>
                    <input type="text" class="form-control form-control-sm" data-field="kegiatan" maxlength="255" required placeholder="Nama kegiatan">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" data-field="status">
                        <option value="direncanakan">Direncanakan</option>
                        <option value="berjalan">Berjalan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-target" title="Hapus baris"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        var tpl = document.getElementById('tplTargetRow');
        if (!tpl) return;

        function hideEmptyHint(wrap) {
            var hint = wrap.querySelector('.target-empty-hint');
            if (hint) hint.classList.add('d-none');
        }

        function nextRowIndex(wrap) {
            return wrap.querySelectorAll('[data-target-row]').length;
        }

        function assignRowFieldNames(row, tim, rowIdx) {
            var keg = row.querySelector('[data-field="kegiatan"]') || row.querySelector('input[name*="[kegiatan]"]');
            var st = row.querySelector('[data-field="status"]') || row.querySelector('select[name*="[status]"]');
            if (keg) keg.name = 'team_targets[' + tim + '][' + rowIdx + '][kegiatan]';
            if (st) st.name = 'team_targets[' + tim + '][' + rowIdx + '][status]';
        }

        function bindRow(row, tim, rowIdx) {
            assignRowFieldNames(row, tim, rowIdx);
            var rm = row.querySelector('.btn-remove-target');
            if (rm) {
                rm.addEventListener('click', function () {
                    var wrap = row.parentElement;
                    row.remove();
                    if (wrap && wrap.querySelectorAll('[data-target-row]').length === 0) {
                        var hint = wrap.querySelector('.target-empty-hint');
                        if (!hint) {
                            hint = document.createElement('p');
                            hint.className = 'text-muted small target-empty-hint mb-2';
                            hint.textContent = 'Belum ada kegiatan. Klik «Tambah kegiatan».';
                            wrap.insertBefore(hint, wrap.firstChild);
                        } else {
                            hint.classList.remove('d-none');
                        }
                    }
                });
            }
        }

        document.querySelectorAll('.target-rows-wrap').forEach(function (wrap) {
            var tim = wrap.getAttribute('data-tim') || '';
            wrap.querySelectorAll('[data-target-row]').forEach(function (row, rowIdx) {
                bindRow(row, tim, rowIdx);
            });
        });

        document.querySelectorAll('.btn-add-target').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tim = btn.getAttribute('data-tim') || '';
                var wrap = document.getElementById('targetRows-' + tim);
                if (!wrap || !tpl.content) return;
                hideEmptyHint(wrap);
                var rowIdx = nextRowIndex(wrap);
                var node = tpl.content.firstElementChild.cloneNode(true);
                wrap.appendChild(node);
                bindRow(node, tim, rowIdx);
                var inp = node.querySelector('[data-field="kegiatan"]');
                if (inp) inp.focus();
            });
        });
    }());
    </script>
</body>
</html>
