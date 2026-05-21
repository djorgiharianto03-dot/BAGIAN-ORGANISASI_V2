<?php declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tugas_db.php';

org_tugas_require_access();

$tugasId = (int) ($_GET['id'] ?? 0);
if ($tugasId < 1) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Permintaan tidak valid.';
    exit;
}

$db = org_db();
if (!($db instanceof mysqli)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Database tidak tersedia.';
    exit;
}

$viewerUserId = (int) ($_SESSION['admin_user_id'] ?? 0);
$roleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$sessionUsername = trim((string) ($_SESSION['admin_username'] ?? ''));
$isKabag = org_staff_session_is_kabag($db);

$row = org_tugas_fetch_by_id_for_viewer($db, $tugasId, $viewerUserId, $isKabag);
if ($row === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Tugas tidak ditemukan atau Anda tidak memiliki akses.';
    exit;
}

$storedFile = (string) ($row['file_tugas'] ?? '');
$targetReal = org_tugas_resolve_file_path($storedFile);
if ($targetReal === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Berkas tugas tidak ditemukan.';
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo !== false ? (string) finfo_file($finfo, $targetReal) : 'application/octet-stream';
if ($finfo !== false) {
    finfo_close($finfo);
}

$inline = org_tugas_file_can_inline_preview($storedFile);
$downloadName = basename($storedFile);
$judul = trim((string) ($row['judul_tugas'] ?? ''));
if ($judul !== '') {
    $safeJudul = preg_replace('/[^A-Za-z0-9._-]+/', '_', $judul) ?? 'tugas';
    $ext = pathinfo($downloadName, PATHINFO_EXTENSION);
    if ($ext !== '') {
        $downloadName = $safeJudul . '.' . $ext;
    }
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($targetReal));
header(
    'Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
    . '; filename="' . str_replace('"', '', $downloadName) . '"'
);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=60');
header('X-Frame-Options: SAMEORIGIN');

readfile($targetReal);
exit;
