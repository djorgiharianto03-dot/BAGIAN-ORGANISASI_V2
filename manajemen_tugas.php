<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tugas_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';

org_tugas_require_access();

$roleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$sessionUsername = trim((string) ($_SESSION['admin_username'] ?? ''));
$viewerUserId = (int) ($_SESSION['admin_user_id'] ?? 0);
$viewerName = (string) ($_SESSION['admin_display'] ?? $sessionUsername ?: 'Pengguna');
$pageTitle = 'Manajemen Tugas — E-Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-manajemen-tugas mode-eorganisasi';


$message = '';
$messageType = 'info';
$tugasRows = [];
$tugasGrouped = [];
$tableOk = false;
$statsPending = 0;
$statsTotal = 0;
$statsPegawai = 0;

$db = org_db();
$isKabag = org_staff_session_is_kabag($db instanceof mysqli ? $db : null);
$canUpload = org_tugas_role_can_upload($roleNorm) && !$isKabag;
$canEditTugas = org_tugas_role_can_edit($roleNorm) && !$isKabag;
$canDeleteTugas = org_tugas_role_can_delete($roleNorm) && !$isKabag;
if (!($db instanceof mysqli)) {
    $message = 'Koneksi database tidak tersedia.';
    $messageType = 'danger';
} elseif (!org_tugas_ensure_table($db)) {
    $message = 'Tabel tugas belum tersedia. Impor install/tugas_pegawai.sql melalui phpMyAdmin.';
    $messageType = 'warning';
} else {
    $tableOk = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tugas_action'])) {
        $action = (string) $_POST['tugas_action'];
        if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
            $message = 'Sesi keamanan tidak valid. Muat ulang halaman dan coba lagi.';
            $messageType = 'danger';
        } elseif ($action === 'upload_tugas') {
            if (!$canUpload) {
                $message = 'Anda tidak memiliki hak untuk mengunggah tugas.';
                $messageType = 'danger';
            } elseif ($viewerUserId < 1) {
                $message = 'Identitas pengguna tidak valid. Silakan login ulang.';
                $messageType = 'danger';
            } else {
                $judul = org_sanitize_plain(trim((string) ($_POST['judul_tugas'] ?? '')));
                $deskripsi = org_sanitize_plain(trim((string) ($_POST['deskripsi'] ?? '')));
                if ($judul === '' || strlen($judul) > 255) {
                    $message = 'Judul tugas wajib diisi (maksimal 255 karakter).';
                    $messageType = 'warning';
                } elseif ($deskripsi === '') {
                    $message = 'Deskripsi tugas wajib diisi.';
                    $messageType = 'warning';
                } else {
                    $upload = org_tugas_process_upload($_FILES['file_tugas'] ?? null);
                    if (!$upload['ok']) {
                        $message = (string) $upload['message'];
                        $messageType = 'warning';
                    } elseif (org_tugas_insert($db, $viewerUserId, $judul, $deskripsi, (string) $upload['filename'])) {
                        org_audit_log_insert(
                            $db,
                            (string) ($_SESSION['admin_username'] ?? ''),
                            $viewerName,
                            'Mengunggah tugas: «' . $judul . '».'
                        );
                        @org_tugas_purge_orphan_files($db);
                        $message = 'Tugas berhasil diunggah dan menunggu validasi Kabag.';
                        $messageType = 'success';
                    } else {
                        org_tugas_unlink_file((string) $upload['filename']);
                        $message = 'Gagal menyimpan data tugas ke database.';
                        $messageType = 'danger';
                    }
                }
            }
        } elseif ($action === 'update_status_kabag') {
            if (!$isKabag) {
                $message = 'Hanya Kabag Organisasi yang dapat memvalidasi status tugas.';
                $messageType = 'danger';
            } else {
                $tugasId = (int) ($_POST['tugas_id'] ?? 0);
                $statusBaru = org_tugas_status_normalize((string) ($_POST['status'] ?? ''));
                $catatan = org_sanitize_plain(trim((string) ($_POST['catatan_kabag'] ?? '')));
                if ($tugasId < 1) {
                    $message = 'Data tugas tidak valid.';
                    $messageType = 'warning';
                } elseif (!in_array($statusBaru, org_tugas_kabag_status_options(), true)) {
                    $message = 'Status yang dipilih tidak valid.';
                    $messageType = 'warning';
                } else {
                    $rowT = org_tugas_fetch_by_id_for_viewer($db, $tugasId, $viewerUserId, true);
                    if ($rowT === null) {
                        $message = 'Tugas tidak ditemukan.';
                        $messageType = 'warning';
                    } elseif ($statusBaru === 'revisi' && $catatan === '') {
                        $message = 'Catatan wajib diisi saat meminta revisi.';
                        $messageType = 'warning';
                    } elseif (org_tugas_update_status_kabag($db, $tugasId, $statusBaru, $catatan)) {
                        org_audit_log_insert(
                            $db,
                            (string) ($_SESSION['admin_username'] ?? ''),
                            $viewerName,
                            'Memvalidasi tugas id ' . $tugasId . ' → status «' . org_tugas_status_label($statusBaru) . '».'
                        );
                        /* Validasi Kabag TIDAK mengubah file_tugas — hanya
                           status + catatan. Sapu orphan file untuk berjaga2
                           bila ada sisa dari aksi sebelumnya yang sempat
                           gagal `unlink()` (file lock Windows). */
                        @org_tugas_purge_orphan_files($db);
                        $message = 'Status tugas berhasil diperbarui.';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal memperbarui status tugas.';
                        $messageType = 'danger';
                    }
                }
            }
        } elseif ($action === 'edit_tugas') {
            if ($isKabag) {
                $message = 'Kabag Organisasi tidak dapat mengubah tugas. Gunakan menu Validasi.';
                $messageType = 'danger';
            } elseif (!$canEditTugas) {
                $message = 'Anda tidak memiliki hak untuk mengubah tugas.';
                $messageType = 'danger';
            } else {
                $tugasId = (int) ($_POST['tugas_id'] ?? 0);
                $judul = org_sanitize_plain(trim((string) ($_POST['judul_tugas'] ?? '')));
                $deskripsi = org_sanitize_plain(trim((string) ($_POST['deskripsi'] ?? '')));
                if ($tugasId < 1) {
                    $message = 'Data tugas tidak valid.';
                    $messageType = 'warning';
                } elseif ($judul === '' || strlen($judul) > 255) {
                    $message = 'Judul tugas wajib diisi (maksimal 255 karakter).';
                    $messageType = 'warning';
                } elseif ($deskripsi === '') {
                    $message = 'Deskripsi tugas wajib diisi.';
                    $messageType = 'warning';
                } else {
                    $rowT = org_tugas_fetch_by_id_for_viewer($db, $tugasId, $viewerUserId, $isKabag);
                    if ($rowT === null) {
                        $message = 'Tugas tidak ditemukan atau Anda tidak memiliki akses.';
                        $messageType = 'warning';
                    } elseif (!org_tugas_user_can_edit_row($rowT, $viewerUserId, $roleNorm, $isKabag)) {
                        $message = 'Tugas ini tidak dapat diedit pada status saat ini.';
                        $messageType = 'warning';
                    } else {
                        $upload = org_tugas_process_upload_optional($_FILES['file_tugas'] ?? null);
                        if (!$upload['ok']) {
                            $message = (string) $upload['message'];
                            $messageType = 'warning';
                        } else {
                            $newFile = trim((string) ($upload['filename'] ?? ''));
                            $newFileParam = $newFile !== '' ? $newFile : null;
                            $stLama = org_tugas_status_normalize((string) ($rowT['status'] ?? ''));
                            $resetPending = $stLama === 'revisi';
                            if (org_tugas_update_content($db, $tugasId, $judul, $deskripsi, $newFileParam, $resetPending)) {
                                org_audit_log_insert(
                                    $db,
                                    (string) ($_SESSION['admin_username'] ?? ''),
                                    $viewerName,
                                    'Mengubah tugas id ' . $tugasId . ': «' . $judul . '».'
                                );
                                @org_tugas_purge_orphan_files($db);
                                $message = $resetPending
                                    ? 'Tugas berhasil diperbarui dan dikirim ulang untuk validasi Kabag.'
                                    : 'Tugas berhasil diperbarui.';
                                $messageType = 'success';
                            } else {
                                if ($newFile !== '') {
                                    org_tugas_unlink_file($newFile);
                                }
                                $message = 'Gagal menyimpan perubahan tugas.';
                                $messageType = 'danger';
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'delete_tugas') {
            if ($isKabag) {
                $message = 'Kabag Organisasi tidak dapat menghapus tugas.';
                $messageType = 'danger';
            } elseif (!$canDeleteTugas) {
                $message = 'Anda tidak memiliki hak untuk menghapus tugas.';
                $messageType = 'danger';
            } else {
                $tugasId = (int) ($_POST['tugas_id'] ?? 0);
                if ($tugasId < 1) {
                    $message = 'Data tugas tidak valid.';
                    $messageType = 'warning';
                } else {
                    $rowT = org_tugas_fetch_by_id_for_viewer($db, $tugasId, $viewerUserId, false);
                    if ($rowT === null) {
                        $message = 'Tugas tidak ditemukan atau Anda tidak memiliki akses.';
                        $messageType = 'warning';
                    } elseif (!org_tugas_user_can_delete_row($rowT, $viewerUserId, $roleNorm, $isKabag)) {
                        $message = 'Tugas ini tidak dapat dihapus pada status saat ini.';
                        $messageType = 'warning';
                    } elseif (org_tugas_delete_by_id($db, $tugasId)) {
                        $judulLog = trim((string) ($rowT['judul_tugas'] ?? ''));
                        org_audit_log_insert(
                            $db,
                            (string) ($_SESSION['admin_username'] ?? ''),
                            $viewerName,
                            'Menghapus tugas id ' . $tugasId . ($judulLog !== '' ? ': «' . $judulLog . '».' : '.')
                        );
                        @org_tugas_purge_orphan_files($db);
                        $message = 'Tugas berhasil dihapus.';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menghapus tugas.';
                        $messageType = 'danger';
                    }
                }
            }
        }
    }

    $filterStatus = trim((string) ($_GET['status'] ?? ''));
    if ($filterStatus !== '' && !in_array(org_tugas_status_normalize($filterStatus), org_tugas_status_list(), true)) {
        $filterStatus = '';
    }
    $tugasRows = org_tugas_fetch_for_viewer(
        $db,
        $viewerUserId,
        $isKabag,
        $filterStatus !== '' ? $filterStatus : null
    );
    $tugasGrouped = org_tugas_group_rows_by_employee($tugasRows);
    $statsTotal = count($tugasRows);
    $statsPegawai = count($tugasGrouped);
    foreach ($tugasRows as $tr) {
        if (org_tugas_status_normalize((string) ($tr['status'] ?? '')) === 'pending') {
            $statsPending++;
        }
    }
}

/**
 * @param array<string, mixed> $row
 */
function manajemen_tugas_render_table_row(array $row, bool $isKabag, bool $canEditRow, bool $canDeleteRow, int $rowNum): void
{
    $tid = (int) ($row['id'] ?? 0);
    $st = org_tugas_status_normalize((string) ($row['status'] ?? ''));
    $badge = org_tugas_status_badge_class($st);
    $dlUrl = htmlspecialchars(org_tugas_download_url($tid), ENT_QUOTES, 'UTF-8');
    $judulRow = htmlspecialchars((string) ($row['judul_tugas'] ?? ''), ENT_QUOTES, 'UTF-8');
    $deskripsiRaw = (string) ($row['deskripsi'] ?? '');
    $deskripsiShort = htmlspecialchars(
        mb_strlen($deskripsiRaw) > 80 ? mb_substr($deskripsiRaw, 0, 80) . '�' : $deskripsiRaw,
        ENT_QUOTES,
        'UTF-8'
    );
    $deskripsiJson = json_encode($deskripsiRaw, JSON_UNESCAPED_UNICODE);
    if ($deskripsiJson === false) {
        $deskripsiJson = '""';
    }
    $deskripsiJsonAttr = htmlspecialchars($deskripsiJson, ENT_QUOTES, 'UTF-8');
    $created = (string) ($row['created_at'] ?? '');
    $tglLabel = $created !== '' ? date('d/m/Y H:i', strtotime($created)) : '�';
    $createdTs = $created !== '' ? (string) strtotime($created) : '0';
    $catatanKabag = trim((string) ($row['catatan_kabag'] ?? ''));
    $fileTugas = trim((string) ($row['file_tugas'] ?? ''));
    $fileExt = $fileTugas !== '' ? org_tugas_file_extension($fileTugas) : '';
    ?>
    <tr id="tugas-<?php echo $tid; ?>" data-created="<?php echo htmlspecialchars($createdTs, ENT_QUOTES, 'UTF-8'); ?>">
        <td class="text-muted small"><?php echo (int) $rowNum; ?></td>
        <td>
            <div class="fw-semibold"><?php echo $judulRow; ?></div>
            <div class="small text-muted"><?php echo $deskripsiShort; ?></div>
            <?php if ($catatanKabag !== '' && ($st === 'revisi' || $st === 'ditolak')): ?>
                <div class="small text-danger mt-1"><i class="fa-solid fa-comment-dots me-1" aria-hidden="true"></i><?php echo htmlspecialchars($catatanKabag, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </td>
        <td>
            <span class="badge <?php echo htmlspecialchars($badge, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars(org_tugas_status_label($st), ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </td>
        <td class="small text-nowrap"><?php echo htmlspecialchars($tglLabel, ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="text-end text-nowrap">
            <?php if ($fileTugas !== ''): ?>
                <button
                    type="button"
                    class="btn btn-sm btn-primary js-tugas-preview"
                    title="Pratinjau lampiran tugas"
                    data-filename="<?php echo htmlspecialchars($fileTugas, ENT_QUOTES, 'UTF-8'); ?>"
                    data-ext="<?php echo htmlspecialchars($fileExt, ENT_QUOTES, 'UTF-8'); ?>"
                    data-judul="<?php echo $judulRow; ?>"
                    data-preview-url="<?php echo htmlspecialchars(org_tugas_view_file_url($tid), ENT_QUOTES, 'UTF-8'); ?>"
                    data-download-url="<?php echo $dlUrl; ?>"
                ><i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Lihat</button>
                <a href="<?php echo $dlUrl; ?>" class="btn btn-sm btn-outline-primary" title="Unduh lampiran">
                    <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Unduh
                </a>
            <?php else: ?>
                <a
                    href="<?php echo htmlspecialchars(org_tugas_lihat_url($tid), ENT_QUOTES, 'UTF-8'); ?>"
                    class="btn btn-sm btn-primary"
                    title="Lihat detail tugas"
                ><i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Lihat</a>
            <?php endif; ?>
            <?php if ($canEditRow): ?>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary js-tugas-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditTugas"
                    data-tugas-id="<?php echo $tid; ?>"
                    data-judul="<?php echo $judulRow; ?>"
                    data-deskripsi-json="<?php echo $deskripsiJsonAttr; ?>"
                    data-filename="<?php echo htmlspecialchars($fileTugas, ENT_QUOTES, 'UTF-8'); ?>"
                    data-status="<?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?>"
                    title="Ubah judul, deskripsi, atau lampiran"
                ><i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Edit</button>
            <?php endif; ?>
            <?php if ($isKabag): ?>
                <button
                    type="button"
                    class="btn btn-sm btn-warning text-dark js-tugas-validate"
                    data-bs-toggle="modal"
                    data-bs-target="#modalValidasiKabag"
                    data-tugas-id="<?php echo $tid; ?>"
                    data-judul="<?php echo $judulRow; ?>"
                    data-status="<?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?>"
                ><i class="fa-solid fa-check-double me-1" aria-hidden="true"></i>Validasi</button>
            <?php endif; ?>
            <?php if ($canDeleteRow): ?>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger js-tugas-delete"
                    data-bs-toggle="modal"
                    data-bs-target="#modalHapusTugas"
                    data-tugas-id="<?php echo $tid; ?>"
                    data-judul="<?php echo $judulRow; ?>"
                    title="Hapus tugas permanen"
                ><i class="fa-solid fa-trash me-1" aria-hidden="true"></i>Hapus</button>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

$csrfToken = org_csrf_token();

$extraHeadMarkup = <<<'HTML'
<style>
    .page-manajemen-tugas {
        font-family: var(--font-sans);
        background:
            radial-gradient(900px 380px at 8% -8%, rgba(99, 102, 241, 0.14), transparent),
            radial-gradient(880px 360px at 98% -4%, rgba(14, 165, 233, 0.12), transparent),
            #f3f6fd;
    }
    .page-manajemen-tugas .site-main {
        max-width: none;
        padding-left: clamp(12px, 2.2vw, 28px);
        padding-right: clamp(12px, 2.2vw, 28px);
    }
    .tugas-hero {
        border-radius: 20px;
        border: 1px solid #dce8fb;
        background: linear-gradient(135deg, #ffffff 0%, #f4f8ff 100%);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
        position: relative;
        overflow: hidden;
    }
    .tugas-hero::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: linear-gradient(90deg, #4f46e5, #0ea5e9, #22c55e);
    }
    .tugas-stat-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .tugas-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
    }
    .tugas-card-main {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
    }
    .tugas-pegawai-accordion { --bs-accordion-border-radius: 14px; }
    .tugas-acc-item {
        border: 1px solid #dce8fb !important;
        border-radius: 14px !important;
        margin-bottom: 0.75rem;
        overflow: visible;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }
    .tugas-acc-item .accordion-button {
        font-family: inherit;
        font-weight: 600;
        color: #1b3f6f;
        background: linear-gradient(135deg, #ffffff 0%, #f4f8ff 100%);
        padding: 1rem 1.15rem;
        box-shadow: none;
        position: relative;
        z-index: 2;
    }
    .tugas-acc-item .accordion-button::after {
        flex-shrink: 0;
        margin-left: 0.75rem;
    }
    .tugas-acc-item .accordion-button:not(.collapsed) {
        color: #1d4ed8;
        background: linear-gradient(135deg, #eef4ff 0%, #f8fbff 100%);
        box-shadow: inset 0 -1px 0 #dce8fb;
    }
    .tugas-acc-item .accordion-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        border-color: #93c5fd;
    }
    .tugas-acc-avatar {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);
        color: #fff;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .tugas-acc-meta { min-width: 0; }
    .tugas-acc-count {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .tugas-acc-body {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    .tugas-acc-toolbar {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.65rem;
        margin-bottom: 0.75rem;
    }
    .tugas-acc-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .tugas-table thead th {
        background: #eef4fc;
        color: #1b3f6f;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .tugas-table tbody tr:hover {
        background: #f8fbff;
    }
    .tugas-btn-primary {
        border: 0;
        color: #fff;
        font-weight: 600;
        background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
    }
    .tugas-btn-primary:hover { color: #fff; filter: brightness(1.05); }
    .tugas-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .tugas-toast-zone {
        position: fixed;
        top: 5.5rem;
        right: 1rem;
        z-index: 1080;
        max-width: min(420px, calc(100vw - 2rem));
    }
    body.page-manajemen-tugas .modal-backdrop {
        z-index: 1090 !important;
    }
    body.page-manajemen-tugas .modal {
        z-index: 1100 !important;
    }
    #modalPreviewTugas .modal-content {
        height: 100%;
        border-radius: 0;
    }
    #modalPreviewTugas .modal-body--preview {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    #modalPreviewTugas.tugas-preview-modal--compact .modal-body--preview {
        padding: 0.75rem;
    }
    #tugasPreviewContainer {
        flex: 1 1 auto;
        min-height: 0;
        width: 100%;
        background: #525659;
        overflow: hidden;
    }
    #modalPreviewTugas.tugas-preview-modal--compact #tugasPreviewContainer {
        min-height: min(70vh, 520px);
        background: #f1f5f9;
        border-radius: 12px;
    }
    #tugasPreviewContainer iframe,
    #tugasPreviewContainer .tugas-preview-pdf-frame {
        display: block;
        width: 100%;
        height: 100%;
        min-height: calc(100vh - 8.5rem);
        border: 0;
        background: #525659;
    }
    #modalPreviewTugas.tugas-preview-modal--compact #tugasPreviewContainer iframe {
        min-height: min(68vh, 640px);
    }
    #tugasPreviewContainer img {
        max-height: min(70vh, 520px);
        width: auto;
        max-width: 100%;
        object-fit: contain;
    }
</style>
HTML;

$extraFooterMarkup = $extraFooterMarkup ?? '';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup, true);

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<div class="tugas-toast-zone" aria-live="polite">
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show shadow" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
</div>

<div class="container site-main py-4">
    <div class="tugas-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="<?php echo org_href('e_organisasi.php'); ?>" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke E-Organisasi
                </a>
                <h1 class="h3 fw-bold text-primary-emphasis mb-2">Manajemen Tugas</h1>
                <p class="text-muted mb-0 small">
                    <?php if ($isKabag): ?>
                        Pantau dan validasi tugas yang diunggah pegawai. Hanya Anda dan pemilik tugas yang dapat melihat setiap entri.
                    <?php elseif ($canUpload): ?>
                        Unggah laporan tugas Anda. Data hanya terlihat oleh Anda dan Kabag Organisasi untuk monitoring.
                    <?php else: ?>
                        Anda dapat melihat tugas yang pernah Anda unggah.
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($canUpload && $tableOk): ?>
                <button type="button" class="btn tugas-btn-primary" data-bs-toggle="modal" data-bs-target="#modalUploadTugas">
                    <i class="fa-solid fa-cloud-arrow-up me-1" aria-hidden="true"></i>Unggah Tugas
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($tableOk): ?>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card tugas-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Total Tugas</div>
                    <div class="fs-3 fw-bold text-primary"><?php echo (int) $statsTotal; ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card tugas-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Menunggu Validasi</div>
                    <div class="fs-3 fw-bold text-warning"><?php echo (int) $statsPending; ?></div>
                </div>
            </div>
        </div>
        <?php if ($isKabag): ?>
        <div class="col-sm-6 col-lg-3">
            <div class="card tugas-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Pegawai</div>
                    <div class="fs-3 fw-bold text-info"><?php echo (int) $statsPegawai; ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card tugas-stat-card border-start border-4 border-info h-100">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-shield fa-lg text-info" aria-hidden="true"></i>
                    <div>
                        <div class="fw-semibold">Mode Kabag</div>
                        <div class="small text-muted">Validasi &amp; monitoring semua tugas.</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="card tugas-card-main">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h6 fw-bold mb-0 text-primary-emphasis">Daftar Tugas per Pegawai</h2>
                <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
                    <label class="small text-muted mb-0" for="filterStatus">Filter status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <?php foreach (org_tugas_status_list() as $st): ?>
                            <option value="<?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($_GET['status']) && org_tugas_status_normalize((string) $_GET['status']) === $st) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(org_tugas_status_label($st), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'manajemen_tugas_accordion.inc.php'; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($canUpload && $tableOk): ?>
<div class="modal fade" id="modalUploadTugas" tabindex="-1" aria-labelledby="modalUploadTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="post" enctype="multipart/form-data" action="<?php echo org_href('manajemen_tugas.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="tugas_action" value="upload_tugas">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5 fw-bold" id="modalUploadTugasLabel">Unggah Tugas Baru</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>
                        File PDF, DOCX, atau XLSX � maksimal 5 MB. Tugas hanya dapat dilihat oleh Anda dan Kabag Organisasi.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="judul_tugas">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul_tugas" name="judul_tugas" maxlength="255" required placeholder="Contoh: Laporan SAKIP Triwulan I">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required placeholder="Uraikan ringkas isi tugas yang diunggah�"></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="file_tugas">Lampiran Dokumen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file_tugas" name="file_tugas" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn tugas-btn-primary">
                        <i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i>Kirim Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEditTugas && $tableOk): ?>
<div class="modal fade" id="modalEditTugas" tabindex="-1" aria-labelledby="modalEditTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="post" enctype="multipart/form-data" action="<?php echo org_href('manajemen_tugas.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="tugas_action" value="edit_tugas">
                <input type="hidden" name="tugas_id" id="editTugasId" value="">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5 fw-bold" id="modalEditTugasLabel">Ubah Tugas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3" id="editTugasHint">
                        Ubah judul, deskripsi, dan/atau lampiran. Kosongkan file jika tidak ingin mengganti dokumen.
                    </div>
                    <p class="small text-muted mb-3">Tugas: <strong id="editTugasJudulLabel">—</strong></p>
                    <p class="small mb-3" id="editTugasFileWrap">Lampiran saat ini: <code id="editTugasFileCurrent">—</code></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="edit_judul_tugas">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_judul_tugas" name="judul_tugas" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="edit_deskripsi">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="4" required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="edit_file_tugas">Lampiran baru (opsional)</label>
                        <input type="file" class="form-control" id="edit_file_tugas" name="file_tugas" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn tugas-btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDeleteTugas && $tableOk): ?>
<div class="modal fade" id="modalHapusTugas" tabindex="-1" aria-labelledby="modalHapusTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?php echo org_href('manajemen_tugas.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="tugas_action" value="delete_tugas">
                <input type="hidden" name="tugas_id" id="hapusTugasId" value="">
                <div class="modal-header border-0">
                    <h2 class="modal-title h5 fw-bold text-danger" id="modalHapusTugasLabel">Hapus Tugas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda yakin ingin menghapus tugas berikut?</p>
                    <p class="fw-semibold mb-2" id="hapusTugasJudul">—</p>
                    <p class="small text-danger mb-0">Tindakan ini permanen: data dan file lampiran akan dihapus dari server.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-1" aria-hidden="true"></i>Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isKabag && $tableOk): ?>
<div class="modal fade" id="modalValidasiKabag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?php echo org_href('manajemen_tugas.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="tugas_action" value="update_status_kabag">
                <input type="hidden" name="tugas_id" id="validasiTugasId" value="">
                <div class="modal-header border-0">
                    <h2 class="modal-title h5 fw-bold">Validasi Tugas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Tugas: <strong id="validasiTugasJudul">—</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="status">Status</label>
                        <select class="form-select" name="status" id="status" required>
                            <?php foreach (org_tugas_kabag_status_options() as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(org_tugas_status_label($opt), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="catatan_kabag">Catatan Kabag</label>
                        <textarea class="form-control" name="catatan_kabag" id="catatan_kabag" rows="3" placeholder="Wajib diisi jika meminta revisi…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check me-1" aria-hidden="true"></i>Simpan Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($tableOk): ?>
<div class="modal fade" id="modalPreviewTugas" tabindex="-1" aria-labelledby="modalPreviewTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0 shadow d-flex flex-column h-100">
            <div class="modal-header border-0 py-2 px-3 bg-white flex-shrink-0">
                <h2 class="modal-title h6 fw-bold text-truncate pe-3 mb-0" id="modalPreviewTugasLabel">Pratinjau Lampiran</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body modal-body--preview p-0">
                <div id="tugasPreviewContainer" class="d-flex align-items-center justify-content-center"></div>
            </div>
            <div class="modal-footer border-0 py-2 px-3 bg-white flex-shrink-0 gap-2">
                <a href="#" id="tugasPreviewOpenTab" class="btn btn-outline-primary d-none" target="_blank" rel="noopener noreferrer">
                    <i class="fa-solid fa-up-right-from-square me-1" aria-hidden="true"></i>Buka di tab baru
                </a>
                <a href="#" id="tugasPreviewDownload" class="btn btn-primary d-none">
                    <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Unduh Berkas
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var hash = window.location.hash || '';
    function tugasRenumberGroupRows(tbody) {
        if (!tbody) {
            return;
        }
        tbody.querySelectorAll('tr[id^="tugas-"]').forEach(function (tr, idx) {
            var numCell = tr.querySelector('td:first-child');
            if (numCell) {
                numCell.textContent = String(idx + 1);
            }
        });
    }

    document.querySelectorAll('.js-tugas-group-sort').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var targetSel = sel.getAttribute('data-target') || '';
            var tbody = targetSel ? document.querySelector(targetSel) : null;
            if (!tbody) {
                return;
            }
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-created]'));
            var asc = sel.value === 'asc';
            rows.sort(function (a, b) {
                var ta = parseInt(a.getAttribute('data-created') || '0', 10) || 0;
                var tb = parseInt(b.getAttribute('data-created') || '0', 10) || 0;
                return asc ? ta - tb : tb - ta;
            });
            rows.forEach(function (tr) {
                tbody.appendChild(tr);
            });
            tugasRenumberGroupRows(tbody);
        });
    });

    if (hash.indexOf('#tugas-') === 0) {
        var row = document.querySelector(hash);
        if (row) {
            var collapseEl = row.closest('.accordion-collapse');
            if (collapseEl && typeof bootstrap !== 'undefined') {
                bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
            }
            row.classList.add('table-warning');
            setTimeout(function () {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 350);
            var validateBtn = row.querySelector('.js-tugas-validate');
            if (validateBtn && typeof bootstrap !== 'undefined') {
                setTimeout(function () { validateBtn.click(); }, 500);
            }
        }
    }
    document.querySelectorAll('.js-tugas-validate').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idEl = document.getElementById('validasiTugasId');
            var judulEl = document.getElementById('validasiTugasJudul');
            var statusEl = document.getElementById('status');
            if (idEl) idEl.value = btn.getAttribute('data-tugas-id') || '';
            if (judulEl) judulEl.textContent = btn.getAttribute('data-judul') || '—';
            if (statusEl) statusEl.value = btn.getAttribute('data-status') || 'diterima';
        });
    });

    document.querySelectorAll('.js-tugas-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idEl = document.getElementById('editTugasId');
            var judulLabel = document.getElementById('editTugasJudulLabel');
            var judulInput = document.getElementById('edit_judul_tugas');
            var deskInput = document.getElementById('edit_deskripsi');
            var fileCurrent = document.getElementById('editTugasFileCurrent');
            var fileInput = document.getElementById('edit_file_tugas');
            var hintEl = document.getElementById('editTugasHint');
            var status = (btn.getAttribute('data-status') || '').toLowerCase();
            if (idEl) idEl.value = btn.getAttribute('data-tugas-id') || '';
            if (judulLabel) judulLabel.textContent = btn.getAttribute('data-judul') || '—';
            if (judulInput) judulInput.value = btn.getAttribute('data-judul') || '';
            if (deskInput) {
                var deskRaw = btn.getAttribute('data-deskripsi-json') || '""';
                try {
                    deskInput.value = JSON.parse(deskRaw);
                } catch (eDesk) {
                    deskInput.value = '';
                }
            }
            if (fileCurrent) fileCurrent.textContent = btn.getAttribute('data-filename') || '—';
            if (fileInput) fileInput.value = '';
            if (hintEl) {
                hintEl.textContent = status === 'revisi'
                    ? 'Tugas diminta revisi. Setelah disimpan, status kembali ke menunggu validasi Kabag.'
                    : 'Ubah judul, deskripsi, dan/atau lampiran. Kosongkan file jika tidak ingin mengganti dokumen.';
                hintEl.className = status === 'revisi'
                    ? 'alert alert-warning py-2 small mb-3'
                    : 'alert alert-info py-2 small mb-3';
            }
        });
    });

    document.querySelectorAll('.js-tugas-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idEl = document.getElementById('hapusTugasId');
            var judulEl = document.getElementById('hapusTugasJudul');
            if (idEl) idEl.value = btn.getAttribute('data-tugas-id') || '';
            if (judulEl) judulEl.textContent = btn.getAttribute('data-judul') || '—';
        });
    });

    var alerts = document.querySelectorAll('.tugas-toast-zone .alert');
    alerts.forEach(function (el) {
        setTimeout(function () {
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(el).close();
                }
            } catch (e) {}
        }, 6000);
    });

    var previewModalEl = document.getElementById('modalPreviewTugas');
    var previewContainer = document.getElementById('tugasPreviewContainer');
    var previewLabel = document.getElementById('modalPreviewTugasLabel');
    var previewDownload = document.getElementById('tugasPreviewDownload');
    var previewOpenTab = document.getElementById('tugasPreviewOpenTab');

    function tugasEscapeAttr(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function tugasOfficeTypeLabel(extNorm) {
        if (extNorm === 'doc' || extNorm === 'docx') {
            return 'Word';
        }
        if (extNorm === 'xls' || extNorm === 'xlsx') {
            return 'Excel';
        }
        return 'Dokumen';
    }

    function tugasHideDownloadButton() {
        if (previewDownload) {
            previewDownload.classList.add('d-none');
            previewDownload.href = '#';
        }
    }

    function tugasHideOpenTabButton() {
        if (previewOpenTab) {
            previewOpenTab.classList.add('d-none');
            previewOpenTab.href = '#';
        }
    }

    function tugasPdfViewerUrl(url) {
        var base = String(url || '').split('#')[0];
        if (base === '') {
            return '';
        }
        return base + '#view=FitH';
    }

    function tugasSetPreviewModalMode(isPdf) {
        if (!previewModalEl) {
            return;
        }
        if (isPdf) {
            previewModalEl.classList.remove('tugas-preview-modal--compact');
        } else {
            previewModalEl.classList.add('tugas-preview-modal--compact');
        }
    }

    function tugasRenderPreview(previewUrl, ext, judul, filename) {
        if (!previewContainer || !previewLabel) {
            return;
        }
        var extNorm = String(ext || '').toLowerCase();
        previewLabel.textContent = judul || filename || 'Pratinjau Lampiran';
        previewContainer.innerHTML = '';
        tugasHideDownloadButton();
        tugasHideOpenTabButton();

        if (previewUrl === '') {
            tugasSetPreviewModalMode(false);
            previewContainer.innerHTML =
                '<div class="text-center py-5 px-3 text-muted">URL pratinjau tidak tersedia.</div>';
            return;
        }

        if (extNorm === 'pdf') {
            tugasSetPreviewModalMode(true);
            var pdfSrc = tugasPdfViewerUrl(previewUrl);
            previewContainer.innerHTML =
                '<iframe class="tugas-preview-pdf-frame" src="' + tugasEscapeAttr(pdfSrc) + '" title="Pratinjau PDF"></iframe>';
            if (previewOpenTab) {
                previewOpenTab.href = previewUrl.split('#')[0];
                previewOpenTab.classList.remove('d-none');
            }
            return;
        }
        tugasSetPreviewModalMode(false);
        if (extNorm === 'png' || extNorm === 'jpg' || extNorm === 'jpeg' || extNorm === 'gif' || extNorm === 'webp') {
            previewContainer.innerHTML =
                '<img src="' + tugasEscapeAttr(previewUrl) + '" class="img-fluid" alt="' + tugasEscapeAttr(judul || filename) + '">';
            return;
        }
        if (extNorm === 'doc' || extNorm === 'docx' || extNorm === 'xls' || extNorm === 'xlsx') {
            previewContainer.innerHTML =
                '<div class="text-center py-5 px-3">' +
                '<i class="fa-solid fa-file-word fa-3x text-primary opacity-50 mb-3 d-block" aria-hidden="true"></i>' +
                '<p class="mb-2">Pratinjau di browser hanya tersedia untuk file <strong>PDF</strong>.</p>' +
                '<p class="small text-muted mb-0">Untuk file <strong>' + tugasEscapeAttr(tugasOfficeTypeLabel(extNorm)) + '</strong>, gunakan tombol <strong>Unduh</strong> di tabel untuk membuka di aplikasi Anda.</p>' +
                '</div>';
            return;
        }

        previewContainer.innerHTML =
            '<div class="text-center py-5 px-3">' +
            '<i class="fa-regular fa-file-lines fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>' +
            '<p class="text-muted mb-3">Pratinjau tidak tersedia untuk format <strong>.' + tugasEscapeAttr(extNorm || 'lain') + '</strong>.</p>' +
            '<p class="small text-muted mb-0">Gunakan tombol <strong>Unduh</strong> di tabel untuk membuka berkas.</p>' +
            '</div>';
    }

    document.querySelectorAll('.js-tugas-preview').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            var filename = btn.getAttribute('data-filename') || '';
            var ext = btn.getAttribute('data-ext') || '';
            var judul = btn.getAttribute('data-judul') || filename;
            var previewUrl = btn.getAttribute('data-preview-url') || '';
            if (filename === '' || previewUrl === '') {
                return;
            }
            tugasRenderPreview(previewUrl, ext, judul, filename);
            if (previewModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(previewModalEl).show();
            }
        });
    });

    if (previewModalEl) {
        previewModalEl.addEventListener('hidden.bs.modal', function () {
            if (previewContainer) {
                previewContainer.innerHTML = '';
            }
            previewModalEl.classList.remove('tugas-preview-modal--compact');
            tugasHideDownloadButton();
            tugasHideOpenTabButton();
        });
    }
})();
</script>

<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
