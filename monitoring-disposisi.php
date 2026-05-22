<?php
if (!headers_sent()) {
    $qs = isset($_SERVER['QUERY_STRING']) && (string) $_SERVER['QUERY_STRING'] !== ''
        ? ('?' . (string) $_SERVER['QUERY_STRING'])
        : '';
    header('Location: monitoring_disposisi.php' . $qs);
    exit;
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);

$pageTitle = 'Monitoring Disposisi — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-mdisp mode-eorganisasi';

/** Username Kabag (normalisasi: huruf kecil, tanpa spasi) — samakan dengan akun di tabel users. */
function org_mdisp_is_kabag_organisasi(string $username): bool
{
    $n = strtolower(preg_replace('/\s+/u', '', trim($username)));

    return $n === 'kabag_organisasi';
}

$db = org_db();
$sessionUser = trim((string) ($_SESSION['admin_username'] ?? ''));
$isKabag = org_mdisp_is_kabag_organisasi($sessionUser);
$isStaff = $sessionUser !== '' && !$isKabag;

$tab = strtolower(trim((string) ($_GET['tab'] ?? 'monitoring')));
if (!in_array($tab, ['masuk', 'keluar', 'monitoring'], true)) {
    $tab = 'monitoring';
}

$message = '';
$messageType = 'info';

$tablesOk = false;
if ($db instanceof mysqli) {
    $r1 = $db->query("SHOW TABLES LIKE 'dispositions'");
    $r2 = $db->query("SHOW TABLES LIKE 'surat'");
    $tablesOk = ($r1 !== false && $r1->num_rows > 0) && ($r2 !== false && $r2->num_rows > 0);
    if ($r1) {
        $r1->free();
    }
    if ($r2) {
        $r2->free();
    }
}

$buktiDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'disposisi_bukti';
$buktiWeb = 'uploads/disposisi_bukti/';
if (!is_dir($buktiDir)) {
    @mkdir($buktiDir, 0777, true);
}

if ($tablesOk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mdisp_action'])) {
    $action = (string) $_POST['mdisp_action'];
    if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi keamanan tidak valid.';
        $messageType = 'danger';
    } elseif (!($db instanceof mysqli)) {
        $message = 'Database tidak tersedia.';
        $messageType = 'danger';
    } else {
        $idDisp = (int) ($_POST['id_disp'] ?? 0);

        if ($action === 'mdisp_tindak_lanjut' && $isKabag) {
            $penerima = trim((string) ($_POST['penerima_username'] ?? ''));
            $instruksi = trim((string) ($_POST['instruksi_baru'] ?? ''));
            if ($idDisp <= 0 || $penerima === '' || $instruksi === '') {
                $message = 'Lengkapi disposisi, penerima staf, dan instruksi.';
                $messageType = 'warning';
            } elseif (org_mdisp_is_kabag_organisasi($penerima)) {
                $message = 'Pilih staf selain akun Kabag.';
                $messageType = 'warning';
            } else {
                $st0 = $db->prepare('SELECT `id_arsip` FROM `dispositions` WHERE `id` = ? LIMIT 1');
                if ($st0 === false) {
                    $message = 'Gagal memproses.';
                    $messageType = 'danger';
                } else {
                    $st0->bind_param('i', $idDisp);
                    $st0->execute();
                    $r0 = $st0->get_result();
                    $row0 = $r0 ? $r0->fetch_assoc() : null;
                    $st0->close();
                    $idArsip = is_array($row0) ? (int) ($row0['id_arsip'] ?? 0) : 0;
                    if ($idArsip <= 0) {
                        $message = 'Disposisi tidak ditemukan.';
                        $messageType = 'warning';
                    } else {
                        $pengirim = $sessionUser;
                        $st = $db->prepare(
                            'INSERT INTO `dispositions` (`id_arsip`, `parent_id`, `pengirim_username`, `penerima_username`, `instruksi`, `file_bukti`, `status`, `catatan_kabag`)
                             VALUES (?, ?, ?, ?, ?, NULL, \'pending\', NULL)'
                        );
                        if ($st !== false) {
                            $st->bind_param('iisss', $idArsip, $idDisp, $pengirim, $penerima, $instruksi);
                            if ($st->execute()) {
                                $message = 'Disposisi lanjutan ke staf berhasil dibuat.';
                                $messageType = 'success';
                            } else {
                                $message = 'Gagal menyimpan disposisi lanjutan.';
                                $messageType = 'danger';
                            }
                            $st->close();
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_minta_revisi' && $isKabag) {
            $catatan = trim((string) ($_POST['catatan_kabag'] ?? ''));
            if ($idDisp <= 0 || $catatan === '') {
                $message = 'Isi catatan revisi.';
                $messageType = 'warning';
            } else {
                $st = $db->prepare(
                    'UPDATE `dispositions` SET `status` = \'revisi\', `catatan_kabag` = ? WHERE `id` = ? AND `status` IN (\'selesai\',\'dikerjakan\',\'fix\')'
                );
                if ($st !== false) {
                    $st->bind_param('si', $catatan, $idDisp);
                    if ($st->execute() && $st->affected_rows > 0) {
                        $message = 'Permintaan revisi telah dikirim ke staf.';
                        $messageType = 'success';
                    } else {
                        $message = 'Tidak dapat meminta revisi pada baris ini (status belum sesuai atau ID tidak ada).';
                        $messageType = 'warning';
                    }
                    $st->close();
                }
            }
        } elseif ($action === 'mdisp_terima' && $isStaff) {
            $st = $db->prepare(
                'UPDATE `dispositions` SET `status` = \'diterima\' WHERE `id` = ? AND LOWER(TRIM(`penerima_username`)) = LOWER(?) AND `status` = \'pending\''
            );
            if ($st !== false) {
                $st->bind_param('is', $idDisp, $sessionUser);
                if ($st->execute() && $st->affected_rows > 0) {
                    $message = 'Disposisi diterima.';
                    $messageType = 'success';
                } else {
                    $message = 'Tidak dapat menerima (bukan penerima atau status bukan menunggu).';
                    $messageType = 'warning';
                }
                $st->close();
            }
        } elseif ($action === 'mdisp_upload_bukti' && $isStaff) {
            if ($idDisp <= 0 || !isset($_FILES['bukti_file']) || (int) ($_FILES['bukti_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = 'Pilih file bukti yang valid.';
                $messageType = 'warning';
            } else {
                $f = $_FILES['bukti_file'];
                $maxB = 20 * 1024 * 1024;
                if ((int) ($f['size'] ?? 0) > $maxB) {
                    $message = 'Ukuran file maksimal 20 MB.';
                    $messageType = 'warning';
                } else {
                    $tmp = (string) ($f['tmp_name'] ?? '');
                    $orig = basename((string) ($f['name'] ?? 'bukti'));
                    $ext = strtolower((string) pathinfo($orig, PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed, true)) {
                        $message = 'Format file: PDF atau gambar (jpg, png, webp).';
                        $messageType = 'warning';
                    } else {
                        $mime = '';
                        if (function_exists('finfo_open')) {
                            $fi = finfo_open(FILEINFO_MIME_TYPE);
                            if ($fi !== false) {
                                $mime = (string) finfo_file($fi, $tmp);
                                finfo_close($fi);
                            }
                        }
                        $okMime = in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true);
                        if (!$okMime && $mime !== '') {
                            $message = 'Jenis file tidak didukung.';
                            $messageType = 'warning';
                        } else {
                            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
                            if ($safe === '') {
                                $safe = 'bukti';
                            }
                            $fn = $safe . '_' . $idDisp . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            $dest = $buktiDir . DIRECTORY_SEPARATOR . $fn;
                            if (move_uploaded_file($tmp, $dest)) {
                                $rel = $buktiWeb . $fn;
                                $st = $db->prepare(
                                    'UPDATE `dispositions` SET `file_bukti` = ?, `status` = \'selesai\' WHERE `id` = ? AND LOWER(TRIM(`penerima_username`)) = LOWER(?) AND `status` IN (\'diterima\',\'dikerjakan\')'
                                );
                                if ($st !== false) {
                                    $st->bind_param('sis', $rel, $idDisp, $sessionUser);
                                    if ($st->execute() && $st->affected_rows > 0) {
                                        $message = 'Bukti berhasil diunggah; status ditandai selesai.';
                                        $messageType = 'success';
                                    } else {
                                        @unlink($dest);
                                        $message = 'Upload gagal: cek status disposisi dan hak sebagai penerima.';
                                        $messageType = 'warning';
                                    }
                                    $st->close();
                                }
                            } else {
                                $message = 'Gagal menyimpan file ke server.';
                                $messageType = 'danger';
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_fix' && $isStaff) {
            $st = $db->prepare(
                'UPDATE `dispositions` SET `status` = \'fix\' WHERE `id` = ? AND LOWER(TRIM(`penerima_username`)) = LOWER(?) AND `status` = \'revisi\''
            );
            if ($st !== false) {
                $st->bind_param('is', $idDisp, $sessionUser);
                if ($st->execute() && $st->affected_rows > 0) {
                    $message = 'Revisi ditandai selesai (FIX).';
                    $messageType = 'success';
                } else {
                    $message = 'Tidak dapat FIX (bukan penerima atau status bukan revisi).';
                    $messageType = 'warning';
                }
                $st->close();
            }
        } else {
            $message = 'Aksi tidak diizinkan.';
            $messageType = 'danger';
        }
    }
}

$suratMasuk = [];
$suratKeluar = [];
$dispositions = [];
$staffOptions = [];

if ($tablesOk && $db instanceof mysqli) {
    $resM = $db->query("SELECT `id`, `meta_key`, `nama_file`, `jenis`, `created_at` FROM `surat` WHERE `jenis` = 'masuk' ORDER BY `id` DESC LIMIT 500");
    if ($resM) {
        while ($r = $resM->fetch_assoc()) {
            if (is_array($r)) {
                $suratMasuk[] = $r;
            }
        }
        $resM->free();
    }
    $resK = $db->query("SELECT `id`, `meta_key`, `nama_file`, `jenis`, `created_at` FROM `surat` WHERE `jenis` = 'keluar' ORDER BY `id` DESC LIMIT 500");
    if ($resK) {
        while ($r = $resK->fetch_assoc()) {
            if (is_array($r)) {
                $suratKeluar[] = $r;
            }
        }
        $resK->free();
    }

    $sqlD = 'SELECT d.*, s.`meta_key` AS surat_meta, s.`nama_file` AS surat_file, s.`jenis` AS surat_jenis
        FROM `dispositions` d
        INNER JOIN `surat` s ON s.`id` = d.`id_arsip`
        ORDER BY d.`created_at` DESC LIMIT 500';
    $resD = $db->query($sqlD);
    if ($resD) {
        while ($r = $resD->fetch_assoc()) {
            if (is_array($r)) {
                $dispositions[] = $r;
            }
        }
        $resD->free();
    }

    if ($isKabag && org_staff_users_table_exists($db)) {
        foreach (org_staff_users_fetch_all($db) as $u) {
            if (!is_array($u)) {
                continue;
            }
            $un = trim((string) ($u['username'] ?? ''));
            if ($un === '' || org_mdisp_is_kabag_organisasi($un)) {
                continue;
            }
            $staffOptions[] = $u;
        }
    }
}

$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.page-mdisp { font-family: 'Poppins', sans-serif; background: #f3f7fd; }
.page-mdisp .site-main { max-width: 1280px; }
.page-mdisp .nav-tabs .nav-link { font-weight: 600; }
.page-mdisp .table-wrap { overflow-x: auto; }
.catatan-revisi { background: #fff8e6; border-left: 4px solid #f59e0b; }
</style>
HTML;

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';

$tabMasukActive = $tab === 'masuk' ? 'active' : '';
$tabKeluarActive = $tab === 'keluar' ? 'active' : '';
$tabMonActive = $tab === 'monitoring' ? 'active' : '';
$tabMasukShow = $tab === 'masuk' ? 'show active' : '';
$tabKeluarShow = $tab === 'keluar' ? 'show active' : '';
$tabMonShow = $tab === 'monitoring' ? 'show active' : '';
?>
<div class="container site-main section-spacing page-mdisp">
    <nav class="mb-3" aria-label="Navigasi">
        <a class="small text-decoration-none" href="e_organisasi.php">&larr; Kembali ke E-Organisasi</a>
    </nav>

    <h1 class="h3 mb-3 text-dark">Monitoring Disposisi</h1>
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if (!$tablesOk): ?>
        <div class="alert alert-warning">Tabel <code>surat</code> dan/atau <code>dispositions</code> belum tersedia. Jalankan migrasi SQL terlebih dahulu.</div>
    <?php else: ?>
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $tabMasukActive; ?>" href="monitoring-disposisi.php?tab=masuk">Surat Masuk</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $tabKeluarActive; ?>" href="monitoring-disposisi.php?tab=keluar">Surat Keluar</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $tabMonActive; ?>" href="monitoring-disposisi.php?tab=monitoring">Monitoring Disposisi</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade <?php echo $tabMasukShow; ?>" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Daftar surat masuk (tabel <code>surat</code>)</h2>
                        <div class="table-wrap">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light"><tr><th>ID</th><th>Meta</th><th>Berkas</th><th>Tanggal</th></tr></thead>
                                <tbody>
                                    <?php foreach ($suratMasuk as $sm): ?>
                                        <tr>
                                            <td><?php echo (int) ($sm['id'] ?? 0); ?></td>
                                            <td class="text-break small"><?php echo htmlspecialchars((string) ($sm['meta_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($sm['nama_file'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-nowrap small"><?php echo htmlspecialchars((string) ($sm['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($suratMasuk === []): ?>
                                        <tr><td colspan="4" class="text-muted">Belum ada data.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo $tabKeluarShow; ?>" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Daftar surat keluar (tabel <code>surat</code>)</h2>
                        <div class="table-wrap">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light"><tr><th>ID</th><th>Meta</th><th>Berkas</th><th>Tanggal</th></tr></thead>
                                <tbody>
                                    <?php foreach ($suratKeluar as $sk): ?>
                                        <tr>
                                            <td><?php echo (int) ($sk['id'] ?? 0); ?></td>
                                            <td class="text-break small"><?php echo htmlspecialchars((string) ($sk['meta_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($sk['nama_file'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-nowrap small"><?php echo htmlspecialchars((string) ($sk['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($suratKeluar === []): ?>
                                        <tr><td colspan="4" class="text-muted">Belum ada data.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo $tabMonShow; ?>" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <?php if ($isKabag): ?>
                                Anda masuk sebagai <strong>Kabag Organisasi</strong> — dapat meneruskan disposisi ke staf dan meminta revisi.
                            <?php elseif ($isStaff): ?>
                                Tampilan staf: aksi pada baris yang <strong>Anda</strong> sebagai penerima.
                            <?php else: ?>
                                Akun ini tidak dikenali sebagai Kabag_organisasi; gunakan akun staf untuk aksi penerima.
                            <?php endif; ?>
                        </p>
                        <div class="table-wrap">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Surat</th>
                                        <th>Pengirim</th>
                                        <th>Penerima</th>
                                        <th>Instruksi</th>
                                        <th>Status</th>
                                        <th>Catatan Kabag</th>
                                        <th>Bukti</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dispositions as $d): ?>
                                        <?php
                                        $did = (int) ($d['id'] ?? 0);
                                        $st = (string) ($d['status'] ?? '');
                                        $penerima = trim((string) ($d['penerima_username'] ?? ''));
                                        $penerimaMatch = $sessionUser !== '' && strcasecmp($penerima, $sessionUser) === 0;
                                        $showCatatan = ($st === 'revisi') || (trim((string) ($d['catatan_kabag'] ?? '')) !== '');
                                        $catatanHtml = trim((string) ($d['catatan_kabag'] ?? ''));
                                        ?>
                                        <tr class="<?php echo $st === 'revisi' ? 'catatan-revisi' : ''; ?>">
                                            <td><?php echo $did; ?></td>
                                            <td class="small text-break">
                                                <span class="badge text-bg-light text-dark border"><?php echo htmlspecialchars((string) ($d['surat_jenis'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php echo htmlspecialchars((string) ($d['surat_file'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars((string) ($d['pengirim_username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($penerima, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="small" style="max-width:220px;"><?php echo nl2br(htmlspecialchars((string) ($d['instruksi'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="small" style="max-width:200px;">
                                                <?php if ($showCatatan && $catatanHtml !== ''): ?>
                                                    <?php echo nl2br(htmlspecialchars($catatanHtml, ENT_QUOTES, 'UTF-8')); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small">
                                                <?php
                                                $fb = trim((string) ($d['file_bukti'] ?? ''));
                                                if ($fb !== ''): ?>
                                                    <a href="<?php echo htmlspecialchars($fb, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Lihat</a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <?php if ($isKabag): ?>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTindak<?php echo $did; ?>">Tindak Lanjuti ke Staf</button>
                                                    <?php if (in_array($st, ['selesai', 'dikerjakan', 'fix'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalRevisi<?php echo $did; ?>">Minta revisi</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($isStaff && $penerimaMatch): ?>
                                                    <?php if ($st === 'pending'): ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Terima disposisi ini?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="mdisp_action" value="mdisp_terima">
                                                            <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                                            <button type="submit" class="btn btn-sm btn-success">Terima</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if (in_array($st, ['diterima', 'dikerjakan'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUpload<?php echo $did; ?>">Upload Bukti</button>
                                                    <?php endif; ?>
                                                    <?php if ($st === 'revisi'): ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Tandai revisi selesai (FIX)?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <input type="hidden" name="mdisp_action" value="mdisp_fix">
                                                            <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning text-dark">FIX</button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($dispositions === []): ?>
                                        <tr><td colspan="9" class="text-muted">Belum ada disposisi.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isKabag): ?>
            <?php foreach ($dispositions as $d): ?>
                <?php $did = (int) ($d['id'] ?? 0); ?>
                <div class="modal fade" id="modalTindak<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h2 class="modal-title h5">Tindak lanjuti #<?php echo $did; ?></h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="mdisp_action" value="mdisp_tindak_lanjut">
                                    <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Penerima staf</label>
                                        <select name="penerima_username" class="form-select" required>
                                            <option value="">— Pilih —</option>
                                            <?php foreach ($staffOptions as $so): ?>
                                                <option value="<?php echo htmlspecialchars((string) ($so['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars(trim((string) ($so['nama'] ?? '')) !== '' ? (string) $so['nama'] : (string) ($so['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                    (<?php echo htmlspecialchars((string) ($so['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Instruksi untuk staf</label>
                                        <textarea name="instruksi_baru" class="form-control" rows="4" required maxlength="20000" placeholder="Perintah kerja kepada staf"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php $stRow = (string) ($d['status'] ?? ''); ?>
                <?php if (in_array($stRow, ['selesai', 'dikerjakan', 'fix'], true)): ?>
                    <div class="modal fade" id="modalRevisi<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header">
                                        <h2 class="modal-title h5">Minta revisi #<?php echo $did; ?></h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="mdisp_action" value="mdisp_minta_revisi">
                                        <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                        <label class="form-label">Catatan perbaikan untuk staf</label>
                                        <textarea name="catatan_kabag" class="form-control" rows="4" required maxlength="20000" placeholder="Jelaskan bagian yang perlu diperbaiki"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-dark">Kirim revisi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($isStaff): ?>
            <?php foreach ($dispositions as $d): ?>
                <?php
                $did = (int) ($d['id'] ?? 0);
                $penerima = trim((string) ($d['penerima_username'] ?? ''));
                $penerimaMatch = $sessionUser !== '' && strcasecmp($penerima, $sessionUser) === 0;
                $st = (string) ($d['status'] ?? '');
                ?>
                <?php if ($penerimaMatch && in_array($st, ['diterima', 'dikerjakan'], true)): ?>
                    <div class="modal fade" id="modalUpload<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h2 class="modal-title h5">Upload bukti #<?php echo $did; ?></h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="mdisp_action" value="mdisp_upload_bukti">
                                        <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                        <label class="form-label">File (PDF atau gambar, maks. 20 MB)</label>
                                        <input type="file" name="bukti_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Unggah</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
