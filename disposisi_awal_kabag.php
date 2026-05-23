<?php

/**
 * Sub Admin E-Organisasi: input disposisi awal ke Kabag (satu kali per arsip) dan pantau status / tanda terima Kabag.
 * Tanpa akses ke halaman Monitoring Disposisi penuh.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_kategori_bagian.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';

org_require_level_access(['sub_admin_eorganisasi']);

$pageTitle = 'Disposisi awal & tanda terima Kabag — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-dakb mode-eorganisasi';

/**
 * @return array<string, true>
 */
function dakb_arsip_surat_column_set(mysqli $db): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $cache = [];
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res) {
        while ($c = $res->fetch_assoc()) {
            $f = strtolower(trim((string) ($c['Field'] ?? '')));
            if ($f !== '') {
                $cache[$f] = true;
            }
        }
        $res->free();
    }

    return $cache;
}

function dakb_row_is_arsip_masuk(array $row): bool
{
    $jenisRaw = strtolower(trim((string) ($row['jenis_surat'] ?? $row['jenis'] ?? $row['tipe'] ?? '')));

    return $jenisRaw === '' || $jenisRaw === 'masuk';
}

/**
 * Fragment SQL: baris disposisi akar (ke Kabag) — parent_id null jika kolom ada.
 */
function dakb_row_parent_filter_sql(mysqli $db): string
{
    $res = $db->query('SHOW COLUMNS FROM `surat_disposisi` LIKE \'parent_id\'');
    $has = $res !== false && $res->num_rows > 0;
    if ($res) {
        $res->free();
    }
    if ($has) {
        return '(`d`.`parent_id` IS NULL)';
    }

    return '1=1';
}

$db = org_db();
$sessionUser = trim((string) ($_SESSION['admin_username'] ?? ''));
$sessionRoleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));

$message = '';
$messageType = 'info';

$hasArsipTable = false;
$hasDispoTable = false;
if ($db instanceof mysqli) {
    $rA = $db->query("SHOW TABLES LIKE 'arsip_surat'");
    $rD = $db->query("SHOW TABLES LIKE 'surat_disposisi'");
    $hasArsipTable = $rA !== false && $rA->num_rows > 0;
    $hasDispoTable = $rD !== false && $rD->num_rows > 0;
    if ($rA) {
        $rA->free();
    }
    if ($rD) {
        $rD->free();
    }
}

$tablesOk = $hasArsipTable && $hasDispoTable && $db instanceof mysqli;
$arsipCols = ($db instanceof mysqli && $hasArsipTable) ? dakb_arsip_surat_column_set($db) : [];
$hasKategoriBagian = isset($arsipCols['kategori_bagian']);

$suratMasuk = [];
$mdispArsipIdsWithDisposisi = [];
/** id_arsip => 'Diterima' jika Kabag sudah menandai terima, selain itu '-' */
$dakbKabagTerimaLabelByArsip = [];

if (isset($_GET['saved']) && (string) $_GET['saved'] === '1') {
    $message = 'Disposisi awal berhasil dikirim ke Kabag_organisasi. Status tanda terima Kabag tampil pada kolom yang sama.';
    $messageType = 'success';
}

if ($tablesOk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dakb_action'])) {
    $action = (string) $_POST['dakb_action'];
    if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi keamanan tidak valid.';
        $messageType = 'danger';
    } elseif (!($db instanceof mysqli)) {
        $message = 'Database tidak tersedia.';
        $messageType = 'danger';
    } elseif ($action === 'dakb_input_awal') {
        $idArsip = (int) ($_POST['id_arsip'] ?? 0);
        $pengirimDipilih = strtolower(trim((string) ($_POST['pengirim_disposisi'] ?? '')));
        $instruksi = trim((string) ($_POST['instruksi_awal'] ?? ''));
        $tujuanAwal = 'Kabag_organisasi';
        $mapPengirim = [
            'bupati' => 'Bupati',
            'sekda' => 'Sekda',
            'asisten3' => 'Asisten III',
        ];
        if ($idArsip <= 0 || $instruksi === '' || !isset($mapPengirim[$pengirimDipilih])) {
            $message = 'Lengkapi surat masuk, pengirim disposisi, dan instruksi.';
            $messageType = 'warning';
        } else {
            $stA = $db->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
            if ($stA === false) {
                $message = 'Gagal memvalidasi data arsip.';
                $messageType = 'danger';
            } else {
                $stA->bind_param('i', $idArsip);
                $stA->execute();
                $rA = $stA->get_result();
                $aRow = $rA ? $rA->fetch_assoc() : null;
                $stA->close();
                if (!is_array($aRow)) {
                    $message = 'Arsip surat tidak ditemukan.';
                    $messageType = 'warning';
                } else {
                    $jenisArsip = strtolower(trim((string) ($aRow['jenis_surat'] ?? $aRow['jenis'] ?? $aRow['tipe'] ?? '')));
                    if ($jenisArsip !== '' && $jenisArsip !== 'masuk') {
                        $message = 'Input disposisi awal hanya diperbolehkan untuk arsip surat masuk.';
                        $messageType = 'warning';
                    } else {
                        $stDup = $db->prepare('SELECT 1 FROM `surat_disposisi` WHERE `id_arsip` = ? LIMIT 1');
                        if ($stDup === false) {
                            $message = 'Gagal memvalidasi status disposisi.';
                            $messageType = 'danger';
                        } else {
                            $stDup->bind_param('i', $idArsip);
                            $stDup->execute();
                            $rDup = $stDup->get_result();
                            $sudahAda = $rDup !== false && $rDup->num_rows > 0;
                            $stDup->close();
                            if ($sudahAda) {
                                $message = 'Surat ini sudah didisposisikan. Satu arsip hanya satu input awal ke Kabag_organisasi.';
                                $messageType = 'warning';
                            } else {
                                $pengirimAwal = $mapPengirim[$pengirimDipilih];
                                $parentNull = null;
                                $catatanAwal = 'Diinput oleh: ' . $sessionUser . ' (' . org_staff_role_label($sessionRoleNorm) . ')';
                                $stI = $db->prepare(
                                    'INSERT INTO `surat_disposisi` (`id_arsip`, `parent_id`, `pengirim_username`, `penerima_username`, `instruksi`, `file_bukti`, `status`, `catatan_kabag`)
                                     VALUES (?, ?, ?, ?, ?, NULL, \'pending\', ?)'
                                );
                                if ($stI === false) {
                                    $message = 'Gagal menyiapkan penyimpanan disposisi awal.';
                                    $messageType = 'danger';
                                } else {
                                    $stI->bind_param('iissss', $idArsip, $parentNull, $pengirimAwal, $tujuanAwal, $instruksi, $catatanAwal);
                                    if ($stI->execute()) {
                                        $stI->close();
                                        org_redirect('disposisi_awal_kabag.php', 'saved=1');
                                        exit;
                                    }
                                    $message = 'Gagal menyimpan disposisi awal.';
                                    $messageType = 'danger';
                                    $stI->close();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

if ($tablesOk && $db instanceof mysqli) {
    $resA = $db->query('SELECT * FROM `arsip_surat` LIMIT 1000');
    if ($resA) {
        while ($r = $resA->fetch_assoc()) {
            if (!is_array($r)) {
                continue;
            }
            if (dakb_row_is_arsip_masuk($r)) {
                $suratMasuk[] = $r;
            }
        }
        $resA->free();
    }

    $idsMasukForDispo = [];
    foreach ($suratMasuk as $smRow) {
        $iax = (int) ($smRow['id'] ?? 0);
        if ($iax > 0) {
            $idsMasukForDispo[$iax] = true;
        }
    }
    $idListMasuk = array_keys($idsMasukForDispo);
    if ($idListMasuk !== []) {
        $inListMasuk = implode(',', array_map(static fn (int $x): int => $x, $idListMasuk));
        $rdMasuk = $db->query('SELECT DISTINCT `id_arsip` FROM `surat_disposisi` WHERE `id_arsip` IN (' . $inListMasuk . ')');
        if ($rdMasuk) {
            while ($xr = $rdMasuk->fetch_assoc()) {
                $kia = (int) ($xr['id_arsip'] ?? 0);
                if ($kia > 0) {
                    $mdispArsipIdsWithDisposisi[$kia] = true;
                }
            }
            $rdMasuk->free();
        }

        $needleKab = 'Diinput oleh: ' . $sessionUser;
        $parentSql = dakb_row_parent_filter_sql($db);
        $sqlKab = 'SELECT `d`.`id`, `d`.`id_arsip`, `d`.`status`, `d`.`catatan_kabag`, `d`.`penerima_username`
            FROM `surat_disposisi` `d`
            WHERE `d`.`id_arsip` IN (' . $inListMasuk . ')
              AND LOWER(REPLACE(TRIM(`d`.`penerima_username`), \' \', \'\')) = \'kabag_organisasi\'
              AND ' . $parentSql . '
            ORDER BY `d`.`id` DESC';
        $rsKab = $db->query($sqlKab);
        $bestKab = [];
        if ($rsKab) {
            while ($rk = $rsKab->fetch_assoc()) {
                if (!is_array($rk)) {
                    continue;
                }
                $catK = (string) ($rk['catatan_kabag'] ?? '');
                if ($sessionUser !== '' && stripos($catK, $needleKab) === false) {
                    continue;
                }
                if ($sessionUser === '' && stripos($catK, 'Diinput oleh:') === false) {
                    continue;
                }
                $iaKab = (int) ($rk['id_arsip'] ?? 0);
                $idKab = (int) ($rk['id'] ?? 0);
                if ($iaKab <= 0 || $idKab <= 0) {
                    continue;
                }
                if (!isset($bestKab[$iaKab]) || $idKab > $bestKab[$iaKab]['id']) {
                    $bestKab[$iaKab] = ['id' => $idKab, 'status' => strtolower(trim((string) ($rk['status'] ?? '')))];
                }
            }
            $rsKab->free();
        }
        foreach ($bestKab as $iaKab => $rowKab) {
            $dakbKabagTerimaLabelByArsip[$iaKab] = ($rowKab['status'] === 'diterima') ? 'Diterima' : '-';
        }
    }
}

$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.page-dakb { font-family: 'Poppins', sans-serif; background: #f3f7fd; }
.page-dakb .site-main { max-width: 1100px; }
</style>
HTML;

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div class="container site-main section-spacing page-dakb">
    <nav class="mb-3" aria-label="Navigasi">
        <a class="small text-decoration-none" href="<?php echo org_href('e_organisasi.php'); ?>">&larr; Kembali ke E-Organisasi</a>
    </nav>

    <h1 class="h4 mb-2 text-dark">Disposisi awal ke Kabag</h1>
    <p class="text-muted small mb-4">Input disposisi dari surat masuk yang diarsipkan untuk alur disposisi (satu kali per surat). Kolom <strong>Kabag</strong>: tulisan <strong>Diterima</strong> jika Kabag sudah menandai terima; jika belum, tanda <strong>(-)</strong>.</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!$tablesOk): ?>
        <div class="alert alert-warning">Tabel <code>arsip_surat</code> dan <code>surat_disposisi</code> diperlukan.</div>
    <?php else: ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Surat masuk — input disposisi awal</h2>
                <p class="small text-muted mb-3">Sumber data sama dengan daftar surat masuk di Arsip (jenis masuk).</p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nomor</th>
                                <?php if ($hasKategoriBagian): ?><th>Kategori</th><?php endif; ?>
                                <th>Nama file</th>
                                <th>Tanggal</th>
                                <th>Kabag</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suratMasuk as $r): ?>
                                <?php
                                $idRow = (int) ($r['id'] ?? 0);
                                $nomorShow = (string) ($r['nomor_surat'] ?? $r['nomor'] ?? '');
                                $fnameDisp = org_arsip_surat_row_display_filename($r);
                                $kabCol = '-';
                                if (!empty($mdispArsipIdsWithDisposisi[$idRow])) {
                                    $kabCol = $dakbKabagTerimaLabelByArsip[$idRow] ?? '-';
                                }
                                ?>
                                <tr id="dakb-arsip-<?php echo $idRow; ?>">
                                    <td><?php echo $idRow; ?></td>
                                    <td><?php echo htmlspecialchars($nomorShow !== '' ? $nomorShow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if ($hasKategoriBagian): ?>
                                        <td class="small"><?php echo htmlspecialchars(org_arsip_kategori_bagian_label($r), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($fnameDisp !== '' ? $fnameDisp : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($r['created_at'] ?? $r['tanggal'] ?? $r['tgl_upload'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center text-nowrap"><?php echo $kabCol === 'Diterima' ? '<span class="badge text-bg-success">Diterima</span>' : '-'; ?></td>
                                    <td class="text-end text-nowrap">
                                        <?php if (!empty($mdispArsipIdsWithDisposisi[$idRow])): ?>
                                            <span class="badge text-bg-success">Sudah didisposisikan</span>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#dakbModalInput<?php echo $idRow; ?>">Input Disposisi</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($suratMasuk === []): ?>
                                <tr><td colspan="<?php echo (int) (6 + ($hasKategoriBagian ? 1 : 0)); ?>" class="text-muted">Belum ada arsip surat masuk.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php foreach ($suratMasuk as $r): ?>
            <?php
            $idArsipRow = (int) ($r['id'] ?? 0);
            if ($idArsipRow <= 0 || !empty($mdispArsipIdsWithDisposisi[$idArsipRow])) {
                continue;
            }
            ?>
            <div class="modal fade" id="dakbModalInput<?php echo $idArsipRow; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post">
                            <div class="modal-header">
                                <h2 class="modal-title h5">Input disposisi awal</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="dakb_action" value="dakb_input_awal">
                                <input type="hidden" name="id_arsip" value="<?php echo $idArsipRow; ?>">
                                <p class="small mb-2"><span class="text-muted">Tujuan:</span> <strong>Kabag_organisasi</strong></p>
                                <p class="small mb-3"><span class="text-muted">Nomor:</span> <?php echo htmlspecialchars((string) ($r['nomor_surat'] ?? $r['nomor'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="mb-3">
                                    <label class="form-label">Pengirim disposisi</label>
                                    <select class="form-select" name="pengirim_disposisi" required>
                                        <option value="">— Pilih pengirim —</option>
                                        <option value="bupati">Bupati</option>
                                        <option value="sekda">Sekda</option>
                                        <option value="asisten3">Asisten III</option>
                                    </select>
                                </div>
                                <label class="form-label">Instruksi</label>
                                <textarea class="form-control" name="instruksi_awal" rows="4" required maxlength="20000" placeholder="Isi disposisi untuk Kabag"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan &amp; kirim ke Kabag</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
