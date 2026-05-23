<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tugas_db.php';

org_tugas_require_access();

$tugasId = (int) ($_GET['id'] ?? 0);
if ($tugasId < 1) {
    $_SESSION['flash_message'] = 'Tugas tidak ditemukan.';
    $_SESSION['flash_type'] = 'warning';
    org_redirect('manajemen_tugas.php');
}

$roleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$sessionUsername = trim((string) ($_SESSION['admin_username'] ?? ''));
$viewerUserId = (int) ($_SESSION['admin_user_id'] ?? 0);
$db = org_db();
$isKabag = org_staff_session_is_kabag($db instanceof mysqli ? $db : null);

$row = null;
if ($db instanceof mysqli && org_tugas_table_exists($db)) {
    $row = org_tugas_fetch_by_id_for_viewer($db, $tugasId, $viewerUserId, $isKabag);
}

if ($row === null) {
    $_SESSION['flash_message'] = 'Tugas tidak ditemukan atau Anda tidak memiliki akses.';
    $_SESSION['flash_type'] = 'danger';
    org_redirect('manajemen_tugas.php');
}

$st = org_tugas_status_normalize((string) ($row['status'] ?? ''));
$badgeClass = org_tugas_status_badge_class($st);
$fileName = (string) ($row['file_tugas'] ?? '');
$hasFile = $fileName !== '';
$canPreview = $hasFile && org_tugas_file_can_inline_preview($fileName);
$fileLabel = $hasFile ? org_tugas_file_type_label($fileName) : '';

$viewFileUrl = org_tugas_view_file_url($tugasId);
$downloadUrl = org_tugas_download_url($tugasId);
$listUrl = 'manajemen_tugas.php';

$uploaderLabel = trim((string) ($row['uploader_nama'] ?? '')) !== ''
    ? (string) $row['uploader_nama']
    : (string) ($row['username'] ?? '—');
$created = (string) ($row['created_at'] ?? '');
$updated = (string) ($row['updated_at'] ?? '');
$tglBuat = $created !== '' ? date('d F Y, H:i', strtotime($created)) : '—';
$tglUpdate = $updated !== '' ? date('d F Y, H:i', strtotime($updated)) : '—';
$catatanKabag = trim((string) ($row['catatan_kabag'] ?? ''));

$pageTitle = 'Lihat Tugas — ' . (string) ($row['judul_tugas'] ?? 'Manajemen Tugas');
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-lihat-tugas mode-eorganisasi';

$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .page-lihat-tugas {
        font-family: 'Poppins', sans-serif;
        background: #f3f6fd;
    }
    .lihat-tugas-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
    }
    .lihat-tugas-meta dt {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .lihat-tugas-meta dd {
        font-weight: 500;
        color: #0f2748;
    }
    .lihat-tugas-preview {
        border: 1px solid #dce7f5;
        border-radius: 12px;
        overflow: hidden;
        background: #f8fafc;
        min-height: 480px;
    }
    .lihat-tugas-preview iframe {
        width: 100%;
        min-height: 72vh;
        border: 0;
        display: block;
    }
    .lihat-tugas-no-preview {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #64748b;
    }
</style>
HTML;

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<div class="container site-main py-4">
    <nav class="mb-3">
        <a href="<?php echo htmlspecialchars($listUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar
        </a>
    </nav>

    <div class="card lihat-tugas-card mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h4 fw-bold text-primary-emphasis mb-2"><?php echo htmlspecialchars((string) ($row['judul_tugas'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>
                    <span class="badge <?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?> fs-6">
                        <?php echo htmlspecialchars(org_tugas_status_label($st), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($hasFile): ?>
                        <a href="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Unduh Lampiran
                        </a>
                    <?php endif; ?>
                    <?php if ($isKabag): ?>
                        <a href="<?php echo htmlspecialchars($listUrl, ENT_QUOTES, 'UTF-8'); ?>#tugas-<?php echo $tugasId; ?>" class="btn btn-warning btn-sm text-dark">
                            <i class="fa-solid fa-check-double me-1" aria-hidden="true"></i>Validasi
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="row lihat-tugas-meta g-3 mb-4">
                <?php if ($isKabag): ?>
                    <div class="col-md-6">
                        <dt>Pegawai / Pengunggah</dt>
                        <dd class="mb-0"><?php echo htmlspecialchars($uploaderLabel, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <dt>Tanggal Unggah</dt>
                    <dd class="mb-0"><?php echo htmlspecialchars($tglBuat, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="col-md-6">
                    <dt>Terakhir Diperbarui</dt>
                    <dd class="mb-0"><?php echo htmlspecialchars($tglUpdate, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <?php if ($hasFile): ?>
                    <div class="col-md-6">
                        <dt>Jenis Lampiran</dt>
                        <dd class="mb-0"><i class="fa-solid fa-file me-1 text-primary" aria-hidden="true"></i><?php echo htmlspecialchars($fileLabel, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>

            <div class="mb-4">
                <h2 class="h6 fw-bold text-primary-emphasis mb-2">Deskripsi</h2>
                <div class="p-3 rounded-3 bg-light border border-light-subtle text-body" style="white-space: pre-wrap;"><?php echo htmlspecialchars((string) ($row['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <?php if ($catatanKabag !== ''): ?>
                <div class="alert alert-warning border-0 mb-4" role="status">
                    <h2 class="h6 fw-bold mb-2"><i class="fa-solid fa-comment-dots me-1" aria-hidden="true"></i>Catatan Kabag</h2>
                    <p class="mb-0 small" style="white-space: pre-wrap;"><?php echo htmlspecialchars($catatanKabag, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($hasFile): ?>
                <h2 class="h6 fw-bold text-primary-emphasis mb-3">
                    <i class="fa-solid fa-eye me-1 text-primary" aria-hidden="true"></i>Pratinjau Lampiran
                </h2>
                <div class="lihat-tugas-preview">
                    <?php if ($canPreview): ?>
                        <iframe
                            src="<?php echo htmlspecialchars($viewFileUrl, ENT_QUOTES, 'UTF-8'); ?>"
                            title="Pratinjau dokumen tugas"
                            loading="lazy"
                        ></iframe>
                    <?php else: ?>
                        <div class="lihat-tugas-no-preview">
                            <i class="fa-solid fa-file-word fa-3x mb-3 text-primary opacity-50" aria-hidden="true"></i>
                            <p class="mb-2">Pratinjau di browser hanya tersedia untuk file <strong>PDF</strong>.</p>
                            <p class="small mb-3">Untuk file <?php echo htmlspecialchars($fileLabel, ENT_QUOTES, 'UTF-8'); ?>, silakan unduh untuk membuka di aplikasi Anda.</p>
                            <a href="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Unduh <?php echo htmlspecialchars($fileLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
